<?php
// Inicia a sessão
session_start();

// Inclui o arquivo de configuração
require_once 'config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
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
} catch (PDOException $e) {
    echo "Erro ao consultar pedidos do usuário: " . $e->getMessage();
} finally {
    // Fechar conexão
    $pdo = null;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="css/meus-pedidos.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Meus Pedidos</title>
</head>
<body>

    <header class="header">
        <?php include 'nav.php'; ?>
    </header>

    <div class="content">
        <h1>Meus Pedidos</h1>
        <?php if (!empty($orders)): ?>
    <?php foreach ($orders as $order): ?>
        <div class="order-card">
            <h2>Pedido #<?php echo htmlspecialchars($order['order_id']); ?></h2>
            <p>Data: <?php echo htmlspecialchars($order['order_date']); ?></p>
            <div class="status-bar">
                <div class="<?php echo ($order['status'] == 'Recebido' || $order['status'] == 'Preparando' || $order['status'] == 'Saiu para Entrega' || $order['status'] == 'Entregue') ? 'completed' : ''; ?>"></div>
                <div class="<?php echo ($order['status'] == 'Preparando' || $order['status'] == 'Saiu para Entrega' || $order['status'] == 'Entregue') ? 'completed' : ''; ?>"></div>
                <div class="<?php echo ($order['status'] == 'Saiu para Entrega' || $order['status'] == 'Entregue') ? 'completed' : ''; ?>"></div>
                <div class="<?php echo ($order['status'] == 'Entregue') ? 'completed' : ''; ?>"></div>
            </div>
            <div class="status-labels">
                <span>Pedido Realizado</span>
                <span>Preparando</span>
                <span>Saiu para Entrega</span>
                <span>Entregue</span>
            </div>
            <div class="order-items">
                <p><strong>Itens:</strong> <?php echo htmlspecialchars($order['items']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>Nenhum pedido encontrado.</p>
<?php endif; ?>

    </div>
</body>
</html>
