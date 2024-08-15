<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclui o arquivo de configuração
require_once __DIR__ . '/../config/config.php';

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

$user_profile_image_base64 = null;

if ($loggedIn) {
    // Recuperar informações do usuário logado
    $userId = $_SESSION['user_id'];
    try {
        // Conectar ao banco de dados
        $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
        
        // Preparar e executar a consulta
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Recuperar imagem de perfil do usuário
            $user_profile_image = $user['profile_image'];
            if ($user_profile_image) {
                // Converter bytes para base64
                $user_profile_image_base64 = base64_encode($user_profile_image);
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
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>icon</title>
</head>
<body>
    <div class="menu-area">
        <div class="logo">
            <a href="../client/index.php">
                <img src="../imagens/logo_pizza.png" alt="logo_pizza.png">
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
    <script src="../js/darkMode.js"></script>
</body>
</html>