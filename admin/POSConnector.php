<?php
/**
 * Connecteur API pour intégration avec systèmes de caisse
 * Compatible avec les principales solutions POS (Point of Sale)
 */

class POSConnector {
    private $db;
    private $api_key;
    private $webhook_url;
    
    public function __construct($database_connection, $api_key = null, $webhook_url = null) {
        $this->db = $database_connection;
        $this->api_key = $api_key;
        $this->webhook_url = $webhook_url;
    }
    
    /**
     * Exporter une commande vers le système de caisse
     * 
     * @param int $order_id ID de la commande
     * @return array Résultat de l'export
     */
    public function exportOrderToPOS($order_id) {
        try {
            // Récupérer les détails de la commande
            $order = $this->getOrderDetails($order_id);
            
            if (!$order) {
                return ['success' => false, 'error' => 'Commande non trouvée'];
            }
            
            // Formater les données pour le POS
            $pos_data = $this->formatForPOS($order);
            
            // Envoyer au système de caisse (via API ou webhook)
            if ($this->webhook_url) {
                $result = $this->sendToWebhook($pos_data);
            } else {
                // Sauvegarder localement pour synchronisation ultérieure
                $result = $this->saveForSync($pos_data);
            }
            
            // Enregistrer l'export
            $this->logExport($order_id, $result['success'] ? 'success' : 'failed');
            
            return $result;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Récupérer les détails complets d'une commande
     */
    private function getOrderDetails($order_id) {
        $query = "
            SELECT 
                o.*,
                ts.table_id,
                rt.table_number,
                rt.table_name
            FROM orders o
            LEFT JOIN table_sessions ts ON o.session_token = ts.session_token
            LEFT JOIN restaurant_tables rt ON ts.table_id = rt.id
            WHERE o.id = ?
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        $order = $result->fetch_assoc();
        $stmt->close();
        
        // Récupérer les articles
        $items_query = "SELECT * FROM order_items WHERE order_id = ?";
        $stmt_items = $this->db->prepare($items_query);
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        
        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }
        $stmt_items->close();
        
        $order['items'] = $items;
        
        return $order;
    }
    
    /**
     * Formater les données pour le système POS
     */
    private function formatForPOS($order) {
        // Format standard compatible avec la plupart des POS
        return [
            'external_order_id' => $order['order_number'],
            'created_at' => $order['created_at'],
            'table' => [
                'number' => $order['table_number'],
                'name' => $order['table_name']
            ],
            'customer' => [
                'name' => $order['customer_name']
            ],
            'payment' => [
                'method' => $order['payment_method'],
                'status' => 'pending'
            ],
            'items' => array_map(function($item) {
                return [
                    'name' => $item['food_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                    'notes' => $item['item_comment'] ?? ''
                ];
            }, $order['items']),
            'totals' => [
                'subtotal' => $order['total_price'],
                'tax' => 0, // À calculer selon vos règles
                'total' => $order['total_price']
            ],
            'source' => 'qr_ordering_system',
            'status' => 'new'
        ];
    }
    
    /**
     * Envoyer les données via webhook
     */
    private function sendToWebhook($data) {
        $ch = curl_init($this->webhook_url);
        
        $payload = json_encode($data);
        
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->api_key
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 200 && $http_code < 300) {
            return [
                'success' => true,
                'response' => json_decode($response, true)
            ];
        } else {
            return [
                'success' => false,
                'error' => 'HTTP ' . $http_code,
                'response' => $response
            ];
        }
    }
    
    /**
     * Sauvegarder pour synchronisation ultérieure
     */
    private function saveForSync($data) {
        try {
            $payload = json_encode($data);
            $stmt = $this->db->prepare(
                "INSERT INTO pos_sync_queue (order_number, payload, status) VALUES (?, ?, 'pending')"
            );
            $stmt->bind_param("ss", $data['external_order_id'], $payload);
            $stmt->execute();
            $stmt->close();
            
            return ['success' => true, 'message' => 'Sauvegardé pour synchronisation'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enregistrer l'export dans les logs
     */
    private function logExport($order_id, $status) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO pos_export_log (order_id, status, exported_at) VALUES (?, ?, NOW())"
            );
            $stmt->bind_param("is", $order_id, $status);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            // Ignorer les erreurs de log
        }
    }
    
    /**
     * Synchroniser la file d'attente
     */
    public function syncQueue() {
        $query = "SELECT * FROM pos_sync_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 50";
        $result = mysqli_query($this->db, $query);
        
        $synced = 0;
        $failed = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $data = json_decode($row['payload'], true);
            $result = $this->sendToWebhook($data);
            
            $new_status = $result['success'] ? 'synced' : 'failed';
            
            $stmt = $this->db->prepare(
                "UPDATE pos_sync_queue SET status = ?, synced_at = NOW() WHERE id = ?"
            );
            $stmt->bind_param("si", $new_status, $row['id']);
            $stmt->execute();
            $stmt->close();
            
            if ($result['success']) {
                $synced++;
            } else {
                $failed++;
            }
        }
        
        return [
            'success' => true,
            'synced' => $synced,
            'failed' => $failed
        ];
    }
    
    /**
     * Récupérer les statistiques de synchronisation
     */
    public function getSyncStats() {
        $query = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'synced' THEN 1 ELSE 0 END) as synced,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM pos_sync_queue
        ";
        
        $result = mysqli_query($this->db, $query);
        return mysqli_fetch_assoc($result);
    }
}

/**
 * Scripts SQL pour l'intégration POS
 */
/*
CREATE TABLE IF NOT EXISTS pos_sync_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL,
    payload JSON NOT NULL,
    status ENUM('pending', 'synced', 'failed') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    synced_at DATETIME NULL,
    INDEX idx_status (status),
    INDEX idx_order (order_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pos_export_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    exported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pos_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Configuration par défaut
INSERT INTO pos_config (config_key, config_value) VALUES
('webhook_url', ''),
('api_key', ''),
('auto_export', 'true'),
('sync_interval_minutes', '5')
ON DUPLICATE KEY UPDATE config_key = config_key;
*/
?>
