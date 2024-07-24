<?php
// Iniciar a sessão
session_start();

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

// Inclui o arquivo de configuração
require_once 'config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

$user = null;
$email = null;

if ($loggedIn) {
    // Recuperar informações do usuário logado
    $userId = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Usuário encontrado, mostrar informações ou realizar outras operações
            $email = $user['email'];
        } else {
            // Não deveria acontecer se a sessão estiver corretamente configurada
            echo "Erro ao recuperar informações do usuário.";
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar dados do usuário: " . $e->getMessage();
    }
}

// Consulta SQL para obter os dados do usuário
if ($loggedIn) {
    try {
        $stmt = $pdo->prepare("SELECT fullname, email, address, phone_number, profile_image FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Recuperar dados do usuário
            $user_fullname = $user['fullname'];
            $user_email = $user['email'];
            $user_address = $user['address'];
            $user_phone_number = $user['phone_number'];
            $user_profile_image = $user['profile_image'];

            // Converter bytes para base64
            $user_profile_image_base64 = base64_encode($user_profile_image);

            // Definir o tipo MIME da imagem
            $image_type = $user_profile_image_type ? $user_profile_image_type : 'image/jpeg'; // Defina o tipo MIME adequado
        } else {
            // Usuário não encontrado
            header('Location: login.php');
            exit;
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar dados do usuário: " . $e->getMessage();
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
                    <?php if ($user_profile_image): ?>
                        <img id="profileImage" src="data:<?php echo $image_type; ?>;base64,<?php echo $user_profile_image_base64; ?>" alt="Foto de Perfil">
                    <?php else: ?>
                        <img id="profileImage" src="default-profile.png" alt="Foto de Perfil">
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <p><strong>Nome Completo:</strong> <?php echo htmlspecialchars($user_fullname); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                    <p><strong>Endereço:</strong> <?php echo htmlspecialchars($user_address); ?></p>
                    <p><strong>Número de Celular:</strong> <?php echo htmlspecialchars($user_phone_number); ?></p>
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
