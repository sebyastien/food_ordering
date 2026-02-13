<?php
/**
 * Server-Sent Events (SSE) Endpoint pour les notifications en temps réel
 * Les serveurs se connectent à ce fichier pour recevoir les notifications push
 */

// Headers pour SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Pour Nginx

// Empêcher le timeout
set_time_limit(0);
ini_set('max_execution_time', 0);

include "../admin/connection.php";
require_once "NotificationManager.php";

$notificationManager = new NotificationManager($link);

// Envoyer un message de connexion initiale
echo "data: " . json_encode(['type' => 'connected', 'message' => 'Connecté au serveur de notifications']) . "\n\n";
ob_flush();
flush();

$last_check = time();
$last_notification_id = null;

// Boucle infinie pour envoyer les mises à jour
while (true) {
    // Vérifier les nouvelles notifications toutes les 2 secondes
    if (time() - $last_check >= 2) {
        // Récupérer les notifications non lues
        $notifications = $notificationManager->getUnreadNotifications();
        
        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                // Envoyer seulement les nouvelles notifications
                if ($notification['notification_id'] !== $last_notification_id) {
                    $event_data = [
                        'type' => 'notification',
                        'notification' => $notification
                    ];
                    
                    echo "data: " . json_encode($event_data) . "\n\n";
                    ob_flush();
                    flush();
                    
                    $last_notification_id = $notification['notification_id'];
                }
            }
        }
        
        // Envoyer un heartbeat toutes les 30 secondes
        if (time() % 30 == 0) {
            echo "data: " . json_encode(['type' => 'heartbeat', 'timestamp' => time()]) . "\n\n";
            ob_flush();
            flush();
        }
        
        $last_check = time();
    }
    
    // Pause courte pour ne pas surcharger le CPU
    usleep(500000); // 0.5 seconde
    
    // Vérifier si la connexion est toujours active
    if (connection_aborted()) {
        break;
    }
}
?>
