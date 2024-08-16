<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de configuração
require_once __DIR__ . '/../config/config.php';

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

$user_profile_image_base64 = null;
$fullname = ''; // Inicializa a variável para o nome completo

if ($loggedIn) {
    // Recuperar informações do usuário logado
    $userId = $_SESSION['user_id'];
    try {
        // Conectar ao banco de dados
        $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
        
        // Preparar e executar a consulta
        $stmt = $pdo->prepare("SELECT profile_image, fullname FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Recuperar imagem de perfil do usuário
            $user_profile_image = $user['profile_image'];
            if ($user_profile_image) {
                // Converter bytes para base64
                $user_profile_image_base64 = base64_encode($user_profile_image);
            }

            // Recuperar nome completo
            $fullname = $user['fullname'];

            // Mostrar apenas o primeiro nome se o nome completo estiver disponível
            if (!empty($fullname)) {
                $nameParts = explode(' ', $fullname);
                $displayName = $nameParts[0]; // Apenas o primeiro nome
            }
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar dados do usuário: " . $e->getMessage();
    } finally {
        // Fechar conexão
        $pdo = null;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="../css/icon.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Menu Lateral com Animação</title>
    <link rel="stylesheet" href="../css/menu.css"> <!-- Adiciona o CSS para o menu lateral -->
</head>
<body>
    <div class="menu-area">
        <div class="logo">
            <a href="../client/index.php">
                <img src="../imagens/logo.png" alt="logo.png">
            </a>
        </div>
        <nav>
            <div class="container-menu-mobile">
                <div class="menuMobile-area">
                    <div class="menu-openner"><span>0</span>
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                </div>
                <label for="checkbox" class="menu_hamburger">
                    <input type="checkbox" id="checkbox">
                    <span class="line line-main"></span>
                    <span class="line line-split"></span>
                </label>
            </div>
            <div class="menu">
                <ul>
                    <li><a href="../client/index.php">Início</a></li>
                    <li><a href="../client/menu.php">Cardápio</a></li>
                    <li><a href="../client/sobre.php">Sobre</a></li>
                    <li><a href="../client/contato.php">Contato</a></li>
                    <li id="loginItem">
                        <a href="<?php echo $loggedIn ? '#' : '../client/login.php'; ?>" class="login-link">
                            <div class="login-icon" id="loginIcon">
                                <?php if ($loggedIn && $user_profile_image_base64): ?>
                                    <img src="data:image/jpeg;base64,<?php echo $user_profile_image_base64; ?>" alt="Foto de Perfil" class="profile-icon">
                                <?php else: ?>
                                    <i class="fa-solid fa-user"></i>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php if ($loggedIn): ?>
                            <div class="profile-menu" id="profileMenu">
                                <a href="../client/meus-pedidos.php" class="profile-menu-item">Acompanhar pedido</a>
                                <a href="../client/tela-config.php" class="profile-menu-item">Configurações</a>
                                <a href="../../config/logout.php" class="profile-menu-item">Sair</a>
                            </div>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

   <!-- Menu lateral e overlay -->
<div class="menu-lateral">
    <span class="menu-close">&larr;</span> <!-- Ícone de seta para a esquerda -->
    <ul>
        <!-- Se o usuário estiver logado, mostra a foto e o texto "Perfil" -->
        <?php if ($loggedIn): ?>
            <li class="user-profile">
                <span class="user-profile-text"><?php echo $displayName; ?></span> <!-- Nome do usuário -->
                <img src="data:image/jpeg;base64,<?php echo $user_profile_image_base64; ?>" alt="Foto de Perfil" class="user-profile-img">
            </li>
            <li><a href="../client/meus-pedidos.php">Meus Pedidos</a></li>
        <?php else: ?>
            <li id="loginItem">
                <a href="../client/login.php" class="login-link">
                    <span class="login-icon"><i class="fas fa-user"></i></span> <!-- Ícone de login -->
                    <span class="login-text">Login</span>
                </a>
            </li>
        <?php endif; ?>
        <li><a href="../client/sobre.php">Sobre</a></li>
        <li><a href="../client/menu.php">Cardápio</a></li>
        <li><a href="../client/contato.php">Contato</a></li>
        <?php if ($loggedIn): ?><!-- Adiciona a opção de sair abaixo do contato, visível apenas se o usuário estiver logado -->
        <li><a href="../client/tela-config.php">Configurações</a></li>
            <li><a href="../../config/logout.php">Sair</a></li>
        <?php endif; ?>
    </ul>
</div>

<div class="menu-overlay"></div>

    <script src="../js/menu.js"></script> <!-- Adiciona o JavaScript para o menu lateral -->
</body>
</html>
