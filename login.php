<?php
// Iniciar a sessão
session_start();

// Conectar ao banco de dados
$conn = new mysqli('localhost', 'root', '', 'delivery_app');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Verificar se o formulário de login foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpar e validar dados do formulário
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Consulta SQL para verificar usuário
    $stmt = $conn->prepare('SELECT id, fullname, email, address, phone_number, profile_image, password FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        // Usuário encontrado, verificar senha
        $stmt->bind_result($userId, $userFullname, $userEmail, $userAddress, $userPhoneNumber, $userProfileImage, $hashedPassword);
        $stmt->fetch();

        // Verificar se a senha digitada corresponde à senha hash no banco de dados
        if (password_verify($password, $hashedPassword)) {
            // Senha correta, iniciar sessão e redirecionar para menu.php
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_fullname'] = $userFullname;
            $_SESSION['user_email'] = $userEmail;
            $_SESSION['user_address'] = $userAddress;
            $_SESSION['user_phone_number'] = $userPhoneNumber;
            $_SESSION['user_profile_image'] = $userProfileImage;

            header('Location: profile.php');
            exit;
        } else {
            // Senha incorreta
            echo "Senha incorreta. Por favor, tente novamente.";
        }
    } else {
        // Usuário não encontrado
        echo "Usuário não encontrado. Por favor, verifique o email.";
    }

    // Fechar statement
    $stmt->close();
}

// Fechar conexão
$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login_styles.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Login - Pizzaria</title>
</head>
<body>
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
            </form>
        </div>
    </div>
</body>
</html>
