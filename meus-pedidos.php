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
    <header class="menu-area">
        <div class="logo">
            <a href="index.php">
                <img src="images/logo_pizza.png" alt="logo_pizza.png">
            </a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Início</a></li>
                <li><a href="menu.php">Pizzas</a></li>
                <li><a href="sobre.php">Sobre</a></li>
                <li><a href="contato.php">Contato</a></li>
                <li id="loginItem">
                    <a href="#" class="login-link">
                        <div class="login-icon" id="loginIcon">
                            <?php if (isset($_SESSION['profile_image_base64'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo $_SESSION['profile_image_base64']; ?>" alt="Foto de Perfil" class="profile-icon">
                            <?php else: ?>
                                <i class="fa-solid fa-user"></i>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="profile-menu" id="profileMenu">
                        <a href="meus-pedidos.php" class="profile-menu-item">Acompanhar pedido</a>
                        <a href="profile.php" class="profile-menu-item">Ver Perfil</a>
                        <a href="logout.php" class="profile-menu-item">Sair</a>
                    </div>
                </li>
            </ul>
        </nav>
    </header>

    <div class="content">
        <h1>Meus Pedidos</h1>
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <h2>Pedido #<?php echo htmlspecialchars($order['order_id']); ?></h2>
                    <p>Data: <?php echo htmlspecialchars($order['order_date']); ?></p>
                    <div class="status-bar">
                        <div class="<?php echo ($order['status'] == 'Pedido Realizado' || $order['status'] == 'Preparando' || $order['status'] == 'Saiu para Entrega' || $order['status'] == 'Entregue') ? 'completed' : ''; ?>"></div>
                        <div class="<?php echo ($order['status'] == 'Preparando' || $order['status'] == 'Saiu para Entrega' || $order['status'] == 'Entregue') ? 'completed' : ''; ?>"></div>
                        <div class="<?php echo ($order['status'] == 'Saiu para Entrega' || $order['status'] == 'Entregue') ? 'completed' : ''; ?>"></div>
                        <div class="<?php echo ($order['status'] == 'Entregue') ? 'completed' : ''; ?>"></div>
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
