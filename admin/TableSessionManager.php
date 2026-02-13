<?php
/**
 * Classe de gestion des sessions de tables sécurisées
 * Gère l'ouverture, la fermeture et la validation des sessions
 */
class TableSessionManager {
    private $db;
    private $session_duration_minutes = 90; // Durée par défaut : 90 minutes
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Génère un token sécurisé unique
     * @return string Token de 64 caractères
     */
    private function generateSecureToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Ouvre une nouvelle session pour une table
     * Ferme automatiquement toute session précédente
     * * @param int $table_id ID de la table
     * @param string $opened_by Nom du serveur/employé
     * @param int $duration_minutes Durée de la session (optionnel)
     * @return array Résultat avec 'success' et 'token' ou 'error'
     */
    public function openTableSession($table_id, $opened_by = 'Serveur', $duration_minutes = null) {
        try {
            // Vérifier que la table existe
            $stmt = $this->db->prepare("SELECT id, table_name FROM restaurant_tables WHERE id = ? AND is_active = 1");
            $stmt->bind_param("i", $table_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'error' => 'Table non trouvée ou inactive'];
            }
            
            $table = $result->fetch_assoc();
            $stmt->close();
            
            // Fermer toutes les sessions actives pour cette table
            $this->closeTableSession($table_id);
            
            // Générer un nouveau token
            $token = $this->generateSecureToken();
            
            // Définir la durée
            $duration = $duration_minutes ?? $this->session_duration_minutes;
            
            // CORRECTION ICI : On utilise DATE_ADD(NOW()...) pour que MySQL gère l'heure exacte
            // Cela évite les décalages de fuseau horaire entre PHP et la Base de données
            $stmt = $this->db->prepare(
                "INSERT INTO table_sessions (table_id, session_token, status, expires_at, opened_by) 
                 VALUES (?, ?, 'OPEN', DATE_ADD(NOW(), INTERVAL ? MINUTE), ?)"
            );
            
            // Attention au typage : table_id (i), token (s), duration (i), opened_by (s)
            $stmt->bind_param("isis", $table_id, $token, $duration, $opened_by);
            
            if ($stmt->execute()) {
                $session_id = $stmt->insert_id;
                $stmt->close();
                
                return [
                    'success' => true,
                    'token' => $token,
                    'session_id' => $session_id,
                    'table_name' => $table['table_name'],
                    'duration_minutes' => $duration
                ];
            } else {
                return ['success' => false, 'error' => 'Erreur lors de la création de la session'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Ferme une session de table
     * * @param int $table_id ID de la table
     * @return bool Succès de l'opération
     */
    public function closeTableSession($table_id) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE table_sessions 
                 SET status = 'CLOSED', closed_at = NOW() 
                 WHERE table_id = ? AND status = 'OPEN'"
            );
            $stmt->bind_param("i", $table_id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            return $affected > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Ferme une session par son token
     * * @param string $token Token de la session
     * @return bool Succès de l'opération
     */
    public function closeSessionByToken($token) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE table_sessions 
                 SET status = 'CLOSED', closed_at = NOW() 
                 WHERE session_token = ? AND status = 'OPEN'"
            );
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            return $affected > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Valide un token de session
     * * @param string $token Token à valider
     * @return array Résultat avec 'valid' et données de la session ou 'error'
     */
    public function validateToken($token) {
        try {
            $stmt = $this->db->prepare(
                "SELECT ts.*, rt.table_number, rt.table_name, rt.capacity
                 FROM table_sessions ts
                 INNER JOIN restaurant_tables rt ON ts.table_id = rt.id
                 WHERE ts.session_token = ? 
                   AND ts.status = 'OPEN' 
                   AND ts.expires_at > NOW()
                   AND rt.is_active = 1"
            );
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $session = $result->fetch_assoc();
                $stmt->close();
                
                // Calculer les minutes restantes
                $expires_at = new DateTime($session["expires_at"]);
                $now = new DateTime();
                $diff = $now->diff($expires_at);
                $minutes_remaining = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                if ($diff->invert) {
                    $minutes_remaining = 0;
                }
                
                return [
                    "valid" => true,
                    "session_id" => $session["id"],
                    "table_id" => $session["table_id"],
                    "table_number" => $session["table_number"],
                    "table_name" => $session["table_name"],
                    "expires_at" => $session["expires_at"],
                    "opened_by" => $session["opened_by"],
                    "total_orders" => $session["total_orders"],
                    "minutes_remaining" => $minutes_remaining
                ];
            } else {
                $stmt->close();
                return ['valid' => false, 'error' => 'Session invalide, expirée ou fermée'];
            }
            
        } catch (Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Récupère toutes les sessions actives
     * * @return array Liste des sessions actives
     */
    public function getActiveSessions() {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM active_table_sessions ORDER BY table_number ASC"
            );
            $stmt->execute();
            $result = $stmt->get_result();
            $sessions = [];
            
            while ($row = $result->fetch_assoc()) {
                $sessions[] = $row;
            }
            
            $stmt->close();
            return $sessions;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Prolonge la durée d'une session
     * * @param string $token Token de la session
     * @param int $additional_minutes Minutes à ajouter
     * @return bool Succès de l'opération
     */
    public function extendSession($token, $additional_minutes = 30) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE table_sessions 
                 SET expires_at = DATE_ADD(expires_at, INTERVAL ? MINUTE)
                 WHERE session_token = ? AND status = 'OPEN'"
            );
            $stmt->bind_param("is", $additional_minutes, $token);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            return $affected > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Incrémente le compteur de commandes pour une session
     * * @param string $token Token de la session
     * @return bool Succès de l'opération
     */
    public function incrementOrderCount($token) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE table_sessions 
                 SET total_orders = total_orders + 1
                 WHERE session_token = ? AND status = 'OPEN'"
            );
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            
            return $affected > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Nettoie les sessions expirées
     * Ferme automatiquement les sessions dont la date d'expiration est dépassée
     * * @return int Nombre de sessions fermées
     */
    public function cleanExpiredSessions() {
        try {
            $stmt = $this->db->prepare(
                "UPDATE table_sessions 
                 SET status = 'CLOSED', closed_at = NOW() 
                 WHERE status = 'OPEN' AND expires_at <= NOW()"
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
     * Récupère les informations d'une table par son identifiant QR
     * * @param string $qr_identifier Identifiant du QR code
     * @return array|null Informations de la table ou null
     */
    public function getTableByQRIdentifier($qr_identifier) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM restaurant_tables WHERE qr_code_identifier = ? AND is_active = 1"
            );
            $stmt->bind_param("s", $qr_identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $table = $result->fetch_assoc();
                $stmt->close();
                return $table;
            }
            
            $stmt->close();
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Vérifie si une table a une session active
     * * @param int $table_id ID de la table
     * @return array|null Informations de la session active ou null
     */
    public function getActiveSessionForTable($table_id) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM active_table_sessions WHERE table_id = ?"
            );
            $stmt->bind_param("i", $table_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $session = $result->fetch_assoc();
                $stmt->close();
                return $session;
            }
            
            $stmt->close();
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
}
?>