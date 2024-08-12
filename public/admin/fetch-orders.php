<?php
require_once '../../config/config.php';

try {
    $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

    // Buscando todos os pedidos
    $stmt = $pdo->prepare("SELECT * FROM orders");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);
} catch (Exception $e) {
    echo json_encode([]);
}
?>
