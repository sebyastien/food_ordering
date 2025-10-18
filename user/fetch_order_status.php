    <?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    include "../admin/connection.php";

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    header('Content-Type: application/json');

    // On attend le paramètre "order_number"
    if (!isset($_GET['order_number']) || empty($_GET['order_number'])) {
        die(json_encode(['error' => 'Numéro de commande invalide.']));
    }

    $order_number = $_GET['order_number'];

    if (!$link) {
        die(json_encode(['error' => 'Erreur de connexion à la base de données.']));
    }

    // La requête SQL recherche par "order_number"
    $stmt = $link->prepare("SELECT status FROM orders WHERE order_number = ?");
    $stmt->bind_param("s", $order_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        echo json_encode(['status' => $order['status']]);
    } else {
        echo json_encode(['error' => 'Commande non trouvée.']);
    }

    $stmt->close();
    $link->close();
    ?>