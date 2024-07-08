<?php
// Iniciar a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Conectar ao banco de dados
$conn = new mysqli('localhost', 'root', '', 'delivery_app');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Consulta SQL para obter os dados do usuário
$user_id = $_SESSION['user_id'];
$sql = "SELECT fullname, email, address, phone_number, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();

// Verificar se encontrou o usuário
if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_fullname, $user_email, $user_address, $user_phone_number, $user_profile_image);

    // Recuperar dados do usuário
    $stmt->fetch();
    
    // A imagem está armazenada como bytes no banco de dados
    // Converter bytes para base64
    $user_profile_image_base64 = base64_encode($user_profile_image);

    // Fechar statement
    $stmt->close();
} else {
    // Usuário não encontrado
    header('Location: login.php');
    exit;
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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile_styles.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Perfil - Pizzaria</title>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <h1 class="profile-title">Meu Perfil</h1>
            <div class="profile-card">
                <div class="profile-pic">
                    <img id="profileImage" src="data:image/png;base64, <?php echo $user_profile_image_base64; ?>" alt="Foto de Perfil">
                </div>
                <div class="profile-info">
                    <p><strong>Nome Completo:</strong> <?php echo $user_fullname; ?></p>
                    <p><strong>Email:</strong> <?php echo $user_email; ?></p>
                    <p><strong>Endereço:</strong> <?php echo $user_address; ?></p>
                    <p><strong>Número de Celular:</strong> <?php echo $user_phone_number; ?></p>
                </div>
            </div>
            <div class="profile-actions">
                <a href="edit_profile.php" class="btn-edit-profile">Editar Perfil</a>
                <a href="logout.php" class="btn-logout">Sair</a>
            </div>
        </div>
    </div>
</body>
</html>
