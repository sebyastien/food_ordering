<?php
/**
 * Gestionnaire de notifications en temps réel pour les serveurs
 * Utilise Server-Sent Events (SSE) pour les notifications push
 */

class NotificationManager {
    private $db;
    private $notification_file = '../data/notifications.json';
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
        
        // Créer le dossier data s'il n'existe pas
        $data_dir = dirname($this->notification_file);
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0755, true);
        }
        
        // Créer le fichier s'il n'existe pas
        if (!file_exists($this->notification_file)) {
            file_put_contents($this->notification_file, json_encode([]));
        }
    }
    
    /**
     * Créer une notification
     * 
     * @param string $type Type de notification (new_order, table_request, session_expiring, etc.)
     * @param string $title Titre de la notification
     * @param string $message Message détaillé
     * @param array $data Données additionnelles
     * @return bool Succès de l'opération
     */
    public function createNotification($type, $title, $message, $data = []) {
        try {
            $notification = [
                'id' => uniqid('notif_', true),
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'created_at' => date('Y-m-d H:i:s'),
                'read' => false,
                'priority' => $this->getPriority($type)
            ];
            
            // Sauvegarder dans la base de données
            $stmt = $this->db->prepare(
                "INSERT INTO notifications (notification_id, type, title, message, data, priority) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $data_json = json_encode($data);
            $stmt->bind_param("sssssi", 
                $notification['id'],
                $type,
                $title,
                $message,
                $data_json,
                $notification['priority']
            );
            $stmt->execute();
            $stmt->close();
            
            // Sauvegarder aussi dans le fichier JSON pour SSE
            $this->appendToNotificationFile($notification);
            
            // Envoyer une notification push si disponible
            $this->sendPushNotification($notification);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erreur notification : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Déterminer la priorité selon le type
     */
    private function getPriority($type) {
        $priorities = [
            'new_order' => 1,           // Haute priorité
            'table_request' => 1,        // Haute priorité
            'session_expiring' => 2,     // Moyenne priorité
            'session_expired' => 2,      // Moyenne priorité
            'order_ready' => 1,          // Haute priorité
            'info' => 3                  // Basse priorité
        ];
        
        return $priorities[$type] ?? 3;
    }
    
    /**
     * Ajouter une notification au fichier JSON
     */
    private function appendToNotificationFile($notification) {
        $notifications = $this->getNotificationsFromFile();
        
        // Garder seulement les 100 dernières notifications
        if (count($notifications) >= 100) {
            array_shift($notifications);
        }
        
        $notifications[] = $notification;
        file_put_contents($this->notification_file, json_encode($notifications));
    }
    
    /**
     * Récupérer les notifications du fichier
     */
    private function getNotificationsFromFile() {
        $content = file_get_contents($this->notification_file);
        return json_decode($content, true) ?? [];
    }
    
    /**
     * Récupérer toutes les notifications non lues
     */
    public function getUnreadNotifications() {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM notifications 
                 WHERE is_read = 0 
                 ORDER BY created_at DESC 
                 LIMIT 50"
            );
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notifications = [];
            while ($row = $result->fetch_assoc()) {
                $row['data'] = json_decode($row['data'], true);
                $notifications[] = $row;
            }
            
            $stmt->close();
            return $notifications;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($notification_id) {
        try {
            $stmt = $this->db->prepare(
                "UPDATE notifications SET is_read = 1, read_at = NOW() 
                 WHERE notification_id = ?"
            );
            $stmt->bind_param("s", $notification_id);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead() {
        try {
            $this->db->query("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Envoyer une notification push (si service worker disponible)
     */
    private function sendPushNotification($notification) {
        // Pour implémenter plus tard avec Web Push API
        // Nécessite des clés VAPID et l'inscription des service workers
        return true;
    }
    
    /**
     * Nettoyer les anciennes notifications (plus de 7 jours)
     */
    public function cleanOldNotifications() {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
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
     * Créer des notifications automatiques pour les événements
     */
    public function notifyNewOrder($order_number, $table_name, $total) {
        return $this->createNotification(
            'new_order',
            'Nouvelle commande',
            "Commande $order_number - $table_name - " . number_format($total, 2) . " €",
            [
                'order_number' => $order_number,
                'table_name' => $table_name,
                'total' => $total
            ]
        );
    }
    
    public function notifyTableRequest($table_name, $qr_identifier) {
        return $this->createNotification(
            'table_request',
            'Demande d\'activation',
            "Un client attend à $table_name",
            [
                'table_name' => $table_name,
                'qr_identifier' => $qr_identifier
            ]
        );
    }
    
    public function notifySessionExpiring($table_name, $minutes_remaining) {
        return $this->createNotification(
            'session_expiring',
            'Session bientôt expirée',
            "$table_name - Plus que $minutes_remaining minutes",
            [
                'table_name' => $table_name,
                'minutes_remaining' => $minutes_remaining
            ]
        );
    }
}

/**
 * Script SQL pour créer la table notifications
 * À exécuter une seule fois
 */
/*
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id VARCHAR(50) NOT NULL UNIQUE,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    priority INT DEFAULT 3,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME NULL,
    INDEX idx_read (is_read),
    INDEX idx_created (created_at),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/
?>
