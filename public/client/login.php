<?php

// Iniciar a sessão
session_start();

// Inclui o arquivo de configuração e a função de conexão
require_once '../../config/config.php'; // Certifique-se de que este arquivo define a função connectToDatabase()

// Configuração do banco de dados
$hosts = ['127.0.0.1', '193.203.175.99'];
$port = '3306';             // Porta padrão do MySQL
$dbname = 'u778175734_PIzzaDB';
$username = 'u778175734_upper';
$password = '5pp2rr2s4l5t34N';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

$user = null;
$email = null;
$error = null; // Inicializa a variável de erro

// Verificar se o formulário de login foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpar e validar dados do formulário
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : null;
    $password = isset($_POST['password']) ? trim($_POST['password']) : null;

    // Consulta SQL para verificar usuário
    try {
        $stmt = $pdo->prepare('SELECT id, fullname, email, address, phone_number, profile_image, password FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 1) {
            // Usuário encontrado, verificar senha
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $hashedPassword = $user['password'];

            // Verificar se a senha digitada corresponde à senha hash no banco de dados
            if (password_verify($password, $hashedPassword)) {
                // Senha correta, iniciar sessão e redirecionar para menu.php
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_fullname'] = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_address'] = $user['address'];
                $_SESSION['user_phone_number'] = $user['phone_number'];
                $_SESSION['user_profile_image'] = $user['profile_image'];

                header('Location: menu.php');
                exit;
            } else {
                // Senha incorreta
                $error = "Senha incorreta. Por favor, tente novamente.";
            }
        } else {
            // Usuário não encontrado
            $error = "Usuário não encontrado. Por favor, verifique o email.";
        }
    } catch (PDOException $e) {
        $error = "Erro ao consultar dados: " . $e->getMessage();
    }
}

// Fechar conexão
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="../imagens/favicon.ico" type="image/x-icon">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/login_styles.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Login - Pizzaria</title>
</head>
<body>
<header class="header">
        <?php include '../../includes/nav.php'; ?>
    </header>
    <div class="container">

        <div class="form-container">
            <h1 class="form-title">Login</h1>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="form-button">Login</button>
                <br>
                <a href="register.php" class="form-register-link">Cadastre-se</a>
                <?php if ($error) { echo "<p class='form-error'>$error</p>"; } ?>
            </form>
        </div>
    </div>
</body>
</html>
