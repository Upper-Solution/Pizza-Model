<?php
// Inicia a sessão
session_start();

// Inclui o arquivo de configuração
require_once '../../config/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['error' => 'Usuário não está logado']);
    exit;
}

$userEmail = $_SESSION['user_email'];

try {
    // Conectar ao banco de dados
    $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
    
    // Preparar e executar a consulta para obter pedidos do usuário baseado no email
    $stmt = $pdo->prepare("SELECT order_id, order_date, status, items FROM orders WHERE email = ? ORDER BY order_date DESC");
    $stmt->execute([$userEmail]);

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['orders' => $orders]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao consultar pedidos do usuário: ' . $e->getMessage()]);
} finally {
    // Fechar conexão
    $pdo = null;
}
?>
