<?php
/**
 * Générateur de rapports en PDF et Excel
 * Exporte les données des sessions et commandes
 */

class ReportGenerator {
    private $db;
    
    public function __construct($database_connection) {
        $this->db = $database_connection;
    }
    
    /**
     * Générer un rapport de sessions en CSV (compatible Excel)
     */
    public function generateSessionsCSV($date_from, $date_to, $table_id = null) {
        $query = "
            SELECT 
                rt.table_number as 'Numéro Table',
                rt.table_name as 'Nom Table',
                ts.opened_at as 'Ouverture',
                ts.closed_at as 'Fermeture',
                TIMESTAMPDIFF(MINUTE, ts.opened_at, COALESCE(ts.closed_at, NOW())) as 'Durée (min)',
                ts.opened_by as 'Serveur',
                ts.status as 'Statut',
                (SELECT COUNT(*) FROM orders o WHERE o.session_token = ts.session_token) as 'Nb Commandes',
                (SELECT SUM(o.total_price) FROM orders o WHERE o.session_token = ts.session_token) as 'CA Total'
            FROM table_sessions ts
            INNER JOIN restaurant_tables rt ON ts.table_id = rt.id
            WHERE DATE(ts.opened_at) BETWEEN ? AND ?
        ";
        
        $params = [$date_from, $date_to];
        $types = "ss";
        
        if ($table_id !== null) {
            $query .= " AND ts.table_id = ?";
            $params[] = $table_id;
            $types .= "i";
        }
        
        $query .= " ORDER BY ts.opened_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Générer le CSV
        $output = fopen('php://temp', 'r+');
        
        // En-tête UTF-8 BOM pour Excel
        fputs($output, "\xEF\xBB\xBF");
        
        // Récupérer les noms de colonnes
        $first_row = $result->fetch_assoc();
        if ($first_row) {
            fputcsv($output, array_keys($first_row), ';');
            fputcsv($output, array_values($first_row), ';');
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, array_values($row), ';');
            }
        }
        
        rewind($output);
        $csv_content = stream_get_contents($output);
        fclose($output);
        
        return $csv_content;
    }
    
    /**
     * Générer un rapport de commandes en CSV
     */
    public function generateOrdersCSV($date_from, $date_to, $table_id = null) {
        $query = "
            SELECT 
                o.order_number as 'Numéro Commande',
                o.created_at as 'Date',
                rt.table_name as 'Table',
                o.customer_name as 'Client',
                o.payment_method as 'Paiement',
                o.total_price as 'Montant',
                (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as 'Nb Articles'
            FROM orders o
            LEFT JOIN table_sessions ts ON o.session_token = ts.session_token
            LEFT JOIN restaurant_tables rt ON ts.table_id = rt.id
            WHERE DATE(o.created_at) BETWEEN ? AND ?
        ";
        
        $params = [$date_from, $date_to];
        $types = "ss";
        
        if ($table_id !== null) {
            $query .= " AND ts.table_id = ?";
            $params[] = $table_id;
            $types .= "i";
        }
        
        $query .= " ORDER BY o.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Générer le CSV
        $output = fopen('php://temp', 'r+');
        fputs($output, "\xEF\xBB\xBF");
        
        $first_row = $result->fetch_assoc();
        if ($first_row) {
            fputcsv($output, array_keys($first_row), ';');
            fputcsv($output, array_values($first_row), ';');
            
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, array_values($row), ';');
            }
        }
        
        rewind($output);
        $csv_content = stream_get_contents($output);
        fclose($output);
        
        return $csv_content;
    }
    
    /**
     * Générer un rapport HTML pour PDF
     */
    public function generateHTMLReport($date_from, $date_to, $table_id = null) {
        // Récupérer les statistiques
        $stats = $this->getStatistics($date_from, $date_to, $table_id);
        
        // Récupérer les sessions
        $sessions = $this->getSessions($date_from, $date_to, $table_id);
        
        // Générer le HTML
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Rapport d\'activité</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h1 { color: #a40301; }
                .header { text-align: center; margin-bottom: 30px; }
                .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
                .stat-box { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; }
                .stat-value { font-size: 2rem; font-weight: bold; color: #a40301; }
                .stat-label { color: #666; margin-top: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background: #f8f9fa; font-weight: bold; }
                .footer { margin-top: 40px; text-align: center; color: #666; font-size: 0.9rem; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Rapport d\'Activité</h1>
                <p>Période : ' . date('d/m/Y', strtotime($date_from)) . ' - ' . date('d/m/Y', strtotime($date_to)) . '</p>
                <p>Généré le ' . date('d/m/Y à H:i') . '</p>
            </div>
            
            <div class="stats">
                <div class="stat-box">
                    <div class="stat-value">' . number_format($stats['total_sessions']) . '</div>
                    <div class="stat-label">Sessions</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">' . number_format($stats['total_orders']) . '</div>
                    <div class="stat-label">Commandes</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">' . number_format($stats['total_revenue'], 2) . ' €</div>
                    <div class="stat-label">Chiffre d\'affaires</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">' . number_format($stats['avg_duration']) . ' min</div>
                    <div class="stat-label">Durée moyenne</div>
                </div>
            </div>
            
            <h2>Détail des sessions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Table</th>
                        <th>Ouverture</th>
                        <th>Fermeture</th>
                        <th>Durée</th>
                        <th>Serveur</th>
                        <th>Commandes</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($sessions as $session) {
            $html .= '<tr>
                <td>' . htmlspecialchars($session['table_name']) . '</td>
                <td>' . date('d/m H:i', strtotime($session['opened_at'])) . '</td>
                <td>' . ($session['closed_at'] ? date('d/m H:i', strtotime($session['closed_at'])) : '-') . '</td>
                <td>' . $session['duration_minutes'] . ' min</td>
                <td>' . htmlspecialchars($session['opened_by']) . '</td>
                <td>' . $session['orders_count'] . '</td>
                <td>' . number_format($session['total_revenue'], 2) . ' €</td>
            </tr>';
        }
        
        $html .= '</tbody>
            </table>
            
            <div class="footer">
                <p>Document généré automatiquement par le système de gestion</p>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Récupérer les statistiques
     */
    private function getStatistics($date_from, $date_to, $table_id = null) {
        $query = "
            SELECT 
                COUNT(*) as total_sessions,
                AVG(TIMESTAMPDIFF(MINUTE, opened_at, closed_at)) as avg_duration,
                SUM((SELECT COUNT(*) FROM orders o WHERE o.session_token = ts.session_token)) as total_orders,
                SUM((SELECT SUM(o.total_price) FROM orders o WHERE o.session_token = ts.session_token)) as total_revenue
            FROM table_sessions ts
            WHERE DATE(opened_at) BETWEEN ? AND ?
        ";
        
        $params = [$date_from, $date_to];
        $types = "ss";
        
        if ($table_id !== null) {
            $query .= " AND table_id = ?";
            $params[] = $table_id;
            $types .= "i";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Récupérer les sessions
     */
    private function getSessions($date_from, $date_to, $table_id = null) {
        $query = "
            SELECT 
                ts.*,
                rt.table_name,
                TIMESTAMPDIFF(MINUTE, ts.opened_at, COALESCE(ts.closed_at, NOW())) as duration_minutes,
                (SELECT COUNT(*) FROM orders o WHERE o.session_token = ts.session_token) as orders_count,
                (SELECT SUM(o.total_price) FROM orders o WHERE o.session_token = ts.session_token) as total_revenue
            FROM table_sessions ts
            INNER JOIN restaurant_tables rt ON ts.table_id = rt.id
            WHERE DATE(ts.opened_at) BETWEEN ? AND ?
        ";
        
        $params = [$date_from, $date_to];
        $types = "ss";
        
        if ($table_id !== null) {
            $query .= " AND ts.table_id = ?";
            $params[] = $table_id;
            $types .= "i";
        }
        
        $query .= " ORDER BY ts.opened_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sessions = [];
        while ($row = $result->fetch_assoc()) {
            $sessions[] = $row;
        }
        
        return $sessions;
    }
}
?>
