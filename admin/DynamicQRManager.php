<?php
/**
 * Gestionnaire de QR Codes Dynamiques
 * Génère des QR codes uniques et temporaires pour chaque session
 */

class DynamicQRManager {
    private $db;
    private $qr_expiry_minutes = 60; // Durée de validité du QR dynamique
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Générer un QR code dynamique pour une table
     * Ce QR est unique et expire après la durée définie
     * 
     * @param int $table_id ID de la table
     * @param int $session_id ID de la session (optionnel)
     * @return array Résultat avec 'success', 'qr_token', 'qr_url', 'expires_at'
     */
    public function generateDynamicQR($table_id, $session_id = null) {
        try {
            // Vérifier que la table existe
            $stmt = $this->db->prepare("SELECT * FROM restaurant_tables WHERE id = ? AND is_active = 1");
            $stmt->bind_param("i", $table_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'error' => 'Table non trouvée'];
            }
            
            $table = $result->fetch_assoc();
            $stmt->close();
            
            // Générer un token unique et sécurisé
            $qr_token = bin2hex(random_bytes(32)); // 64 caractères
            $expires_at = date('Y-m-d H:i:s', strtotime("+{$this->qr_expiry_minutes} minutes"));
            
            // Enregistrer le QR dynamique
            $stmt = $this->db->prepare(
                "INSERT INTO dynamic_qr_codes (qr_token, table_id, session_id, expires_at, status) 
                 VALUES (?, ?, ?, ?, 'active')"
            );
            $stmt->bind_param("siis", $qr_token, $table_id, $session_id, $expires_at);
            $stmt->execute();
            $qr_id = $stmt->insert_id;
            $stmt->close();
            
            // Construire l'URL du QR
            $base_url = $this->getBaseURL();
            $qr_url = $base_url . "/client/qr_dynamic_entry.php?token=" . $qr_token;
            
            return [
                'success' => true,
                'qr_id' => $qr_id,
                'qr_token' => $qr_token,
                'qr_url' => $qr_url,
                'table_name' => $table['table_name'],
                'expires_at' => $expires_at,
                'valid_for_minutes' => $this->qr_expiry_minutes
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Valider un QR code dynamique
     * 
     * @param string $qr_token Token du QR code
     * @return array Résultat avec 'valid' et données de la table
     */
    public function validateDynamicQR($qr_token) {
        try {
            $stmt = $this->db->prepare(
                "SELECT dqr.*, rt.table_name, rt.capacity, rt.id as table_id
                 FROM dynamic_qr_codes dqr
                 INNER JOIN restaurant_tables rt ON dqr.table_id = rt.id
                 WHERE dqr.qr_token = ? 
                   AND dqr.status = 'active'
                   AND dqr.expires_at > NOW()
                   AND rt.is_active = 1"
            );
            $stmt->bind_param("s", $qr_token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $qr_data = $result->fetch_assoc();
                $stmt->close();
                
                // Incrémenter le compteur de scans
                $this->incrementScanCount($qr_data['id']);
                
                return [
                    'valid' => true,
                    'table_id' => $qr_data['table_id'],
                    'table_name' => $qr_data['table_name'],
                    'session_id' => $qr_data['session_id'],
                    'scanned_count' => $qr_data['scanned_count'] + 1
                ];
            } else {
                $stmt->close();
                return ['valid' => false, 'error' => 'QR code invalide ou expiré'];
            }
            
        } catch (Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Incrémenter le compteur de scans
     */
    private function incrementScanCount($qr_id) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE dynamic_qr_codes 
                 SET scanned_count = scanned_count + 1, last_scanned_at = NOW() 
                 WHERE id = ?"
            );
            $stmt->bind_param("i", $qr_id);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            // Ignorer les erreurs
        }
    }
    
    /**
     * Révoquer un QR code dynamique
     */
    public function revokeQR($qr_token) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE dynamic_qr_codes SET status = 'revoked' WHERE qr_token = ?"
            );
            $stmt->bind_param("s", $qr_token);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            return $affected > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Révoquer tous les QR d'une table
     */
    public function revokeTableQRs($table_id) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE dynamic_qr_codes SET status = 'revoked' WHERE table_id = ? AND status = 'active'"
            );
            $stmt->bind_param("i", $table_id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            return $affected;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Nettoyer les QR expirés
     */
    public function cleanExpiredQRs() {
        try {
            $stmt = $this->db->prepare(
                "UPDATE dynamic_qr_codes 
                 SET status = 'expired' 
                 WHERE status = 'active' AND expires_at <= NOW()"
            );
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            return $affected;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obtenir l'URL de base du site
     */
    private function getBaseURL() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . "://" . $host;
    }
    
    /**
     * Générer l'image du QR code
     */
    public function generateQRImage($qr_url, $size = 300) {
        // Utiliser l'API QR Server (gratuit)
        $api_url = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($qr_url);
        return $api_url;
    }
    
    /**
     * Récupérer les statistiques des QR dynamiques
     */
    public function getQRStats($table_id = null) {
        try {
            $query = "
                SELECT 
                    COUNT(*) as total_qrs,
                    SUM(CASE WHEN status = 'active' AND expires_at > NOW() THEN 1 ELSE 0 END) as active_qrs,
                    SUM(CASE WHEN status = 'expired' OR expires_at <= NOW() THEN 1 ELSE 0 END) as expired_qrs,
                    SUM(CASE WHEN status = 'revoked' THEN 1 ELSE 0 END) as revoked_qrs,
                    SUM(scanned_count) as total_scans
                FROM dynamic_qr_codes
            ";
            
            if ($table_id !== null) {
                $query .= " WHERE table_id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param("i", $table_id);
            } else {
                $stmt = $this->db->prepare($query);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            $stmt->close();
            
            return $stats;
            
        } catch (Exception $e) {
            return null;
        }
    }
}

/**
 * Script SQL pour créer la table dynamic_qr_codes
 */
/*
CREATE TABLE IF NOT EXISTS dynamic_qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qr_token VARCHAR(64) NOT NULL UNIQUE,
    table_id INT NOT NULL,
    session_id INT NULL,
    status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
    scanned_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    last_scanned_at DATETIME NULL,
    INDEX idx_token (qr_token),
    INDEX idx_table (table_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at),
    FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES table_sessions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/
?>
