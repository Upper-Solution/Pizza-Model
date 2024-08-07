<?php
session_start();

// Inclui o arquivo de configuração
require_once '../../config/config.php';

// Verificar se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: adm-login.php');
    exit;
}

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Verifica se o ID do pedido e o novo status foram enviados
if (isset($_POST['id']) && isset($_POST['status'])) {
    $orderId = $_POST['id'];
    $newStatus = $_POST['status'];

    try {
        // Atualiza o status do pedido
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$newStatus, $orderId]);
        echo "Status atualizado com sucesso.";
    } catch (PDOException $e) {
        echo "Erro ao atualizar status: " . $e->getMessage();
    }
}

// Fechar conexão
$pdo = null;
?>
