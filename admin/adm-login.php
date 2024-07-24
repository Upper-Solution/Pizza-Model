<?php
// Iniciar a sessão na página menu.php
session_start();

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['admin_id']);

// Inclui o arquivo de configuração
require_once '../config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

$admin = null;
$username = null;
$loginError = null;

if ($loggedIn) {
    // Recuperar informações do usuário logado
    $adminId = $_SESSION['admin_id'];
    try {
        $stmt = $pdo->prepare('SELECT id, username FROM Admin WHERE id = ?');
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Usuário encontrado, mostrar informações ou realizar outras operações
            $username = $admin['username'];
        } else {
            // Não deveria acontecer se a sessão estiver corretamente configurada
            echo "Erro ao recuperar informações do usuário.";
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar dados do usuário: " . $e->getMessage();
    }
}

// Verificar se o formulário de login foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpar e validar dados do formulário
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

    // Consulta SQL para verificar usuário
    try {
        $stmt = $pdo->prepare('SELECT id, username, password FROM Admin WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->rowCount() == 1) {
            // Usuário encontrado, verificar senha
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            $hashedPassword = $admin['password'];

            // Verificar se a senha digitada corresponde à senha hash no banco de dados
            if (password_verify($password, $hashedPassword)) {
                // Senha correta, iniciar sessão e redirecionar para index.html
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                header('Location: adm-painel.php');
                exit;
            } else {
                // Senha incorreta
                $loginError = "Senha incorreta. Por favor, tente novamente.";
            }
        } else {
            // Usuário não encontrado
            $loginError = "Usuário não encontrado. Por favor, verifique o nome de usuário.";
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar dados: " . $e->getMessage();
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
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login_styles.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>ADM - Login</title>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1 class="form-title">Login do Administrador</h1>
            <?php if ($loginError): ?>
                <p class="error-message"><?php echo $loginError; ?></p>
            <?php endif; ?>
            <form method="POST" action="adm-login.php">
                <div class="form-group">
                    <label for="username">Nome de Usuário:</label>
                    <input type="text" id="username" name="username" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="form-button">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
