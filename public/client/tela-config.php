<?php
session_start();

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

// Inclui o arquivo de configuração para conexão com o banco de dados
require_once '../../config/config.php';

// Obtém a conexão com o banco de dados
try {
    $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    exit();
}

// Inicialização das variáveis de usuário
$user = null;
$email = null;

if ($loggedIn) {
    // Recuperar informações do usuário logado
    $userId = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $email = $user['email'];
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar usuário: " . $e->getMessage();
    }
}

// Fechar conexão com o banco de dados
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <title>Configurações</title>
    <link rel="stylesheet" href="../css/config.css">
    <link rel="stylesheet" href="../css/nav.css">
</head>

<body>
        <header class="header">
            <?php include '../../includes/nav.php'; ?>
        </header>
        <div class="container-telaConfig">
            
            <section class="area-flex">
                <div class="config-area">
                <h2>Configurações</h2>

                    <div class="block user-settings" data-url="./config-user.php">
                        <div class="content">
                            <h3>Configurações de Usuário</h3>
                        </div>
                    </div>
                    <div class="block system-settings" data-url="./config-system.php">
                        <div class="content">
                            <h3>Configurações do Sistema</h3>
                        </div>
                    </div>
                </div>
                <img class="area-flex-img" src="../imagens/banner.jpg" alt="">
            </section>
        </div>

    <script>
        document.querySelectorAll('.block').forEach(block => {
            block.addEventListener('click', function() {
                const url = this.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            });
        });
    </script>
</body>
</html>
