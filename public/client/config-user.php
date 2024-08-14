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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <title>Configurações de Usuário</title>
    <link rel="stylesheet" href="../css/config.css">
    <link rel="stylesheet" href="../css/nav.css">
</head>
<body>
    <header class="header">
            <?php include '../../includes/nav.php'; ?>
    </header>
    <div class="container-configUser">
    
        <div class="cabeçalho-config-user">
            <button id="backButton">Voltar</button>
        </div>

        <form class="forms-config-user" action="">
        <div class="profile-section">
            <img src="path/to/profile.jpg" alt="Foto de Perfil">
            <input type="file" id="uploadPhoto">
        </div>

        <div class="form-group">
            <label for="userName">Nome:</label>
            <input type="text" id="userName" name="userName">
        </div>

        <div class="form-group">
            <label for="userZip">CEP:</label>
            <input type="text" id="userZip" name="userZip">
        </div>

        <div class="form-group">
            <label for="userCity">Cidade:</label>
            <input type="text" id="userCity" name="userCity">
        </div>

        <div class="form-group">
            <label for="userDistrict">Bairro:</label>
            <input type="text" id="userDistrict" name="userZip">
        </div>

        <div class="form-group">
            <label for="userStreet">Rua:</label>
            <input type="text" id="userStreet" name="userAddress">
        </div>

        <div class="form-group">
            <label for="userHouseNumber">Numero:</label>
            <input type="text" id="userHouseNumber" name="userAddress">
        </div>

        <div class="form-group">
            <label for="userAddress">Complemento:</label>
            <input type="text" id="userAddress" name="userAddress">
        </div>
        </form>

        <div class="botao-salvar">
            <button id="saveButton">Salvar</button>
        </div>
    </div>

    <script src="../js/edit-perfil-user.js"></script>
</body>
</html>
