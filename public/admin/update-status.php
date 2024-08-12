<?php
require_once '../../config/config.php';

if (isset($_POST['id']) && isset($_POST['status'])) {
    $orderId = $_POST['id'];
    $newStatus = $_POST['status'];

    try {
        $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

        // Atualizar status do pedido
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$newStatus, $orderId]);

        echo "Status atualizado com sucesso";
    } catch (Exception $e) {
        echo "Erro ao atualizar status";
    }
}
?>
