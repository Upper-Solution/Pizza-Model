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
    <div class="container">

        <header class="header">
            <?php include '../../includes/nav.php'; ?>
        </header>
        <section class="area-flex">
        

        <div class="block user-settings">
            <a>Configurações de Usuário</a>
            <!-- Conteúdo das configurações de usuário -->
        </div>
        <div class="block system-settings">
            <a>Configurações do Sistema</a>
            <!-- Conteúdo das configurações do sistema -->
        </div>
        </section>
    </div>
</body>
</html>
