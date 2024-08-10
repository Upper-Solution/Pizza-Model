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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5" style="max-width: 70%;">
        <h2>Configurações</h2>
        <form>
            <!-- Configurações de Perfil -->
            <div class="mb-3">
                <label for="profileSettings" class="form-label">Alterar Perfil</label>
                <select class="form-select" id="profileSettings">
                    <option value="no">Não alterar</option>
                    <option value="yes">Alterar</option>
                </select>
            </div>

            <div id="profileDetails" class="d-none">
                <!-- Trocar Foto de Perfil -->
                <div class="mb-3">
                    <label for="profilePhoto" class="form-label">Foto de Perfil</label>
                    <input type="file" class="form-control" id="profilePhoto">
                </div>

                <!-- Editar Nome e Senha -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="userName" class="form-label">Nome</label>
                        <input type="text" class="form-control" id="userName" placeholder="Digite seu nome">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="userPassword" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="userPassword" placeholder="Digite sua nova senha">
                    </div>
                </div>

                <!-- Editar Endereço -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="userCity" class="form-label">Cidade</label>
                        <input type="text" class="form-control" id="userCity" placeholder="Digite sua cidade">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="userZip" class="form-label">CEP</label>
                        <input type="text" class="form-control" id="userZip" placeholder="Digite seu CEP">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="userDistrict" class="form-label">Bairro</label>
                        <input type="text" class="form-control" id="userDistrict" placeholder="Digite seu bairro">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="userStreet" class="form-label">Rua</label>
                        <input type="text" class="form-control" id="userStreet" placeholder="Digite sua rua">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="userHouseNumber" class="form-label">Número</label>
                        <input type="text" class="form-control" id="userHouseNumber" placeholder="Número da casa">
                    </div>
                </div>
            </div>

            <!-- Configurações de Modo -->
            <div class="mb-3">
                <label for="systemMode" class="form-label">Modo do Sistema</label>
                <select class="form-select" id="systemMode">
                    <option value="light">Claro</option>
                    <option value="dark">Escuro</option>
                </select>
            </div>

            <!-- Configurações de Notificações -->
            <div class="mb-3">
                <label for="emailNotifications" class="form-label">Receber Notificações por Email</label>
                <select class="form-select" id="emailNotifications">
                    <option value="yes">Sim</option>
                    <option value="no">Não</option>
                </select>
            </div>

            <!-- Botão de Salvar -->
            <button type="submit" class="btn btn-primary">Salvar Configurações</button>
        </form>
    </div>

    <script src="../js/edit-perfil-user.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Manipula a exibição dos detalhes do perfil
        document.getElementById('profileSettings').addEventListener('change', function() {
            const profileDetails = document.getElementById('profileDetails');
            if (this.value === 'yes') {
                profileDetails.classList.remove('d-none');
            } else {
                profileDetails.classList.add('d-none');
            }
        });

        // Troca entre modo claro e escuro
        document.getElementById('systemMode').addEventListener('change', function() {
            if (this.value === 'dark') {
                document.body.classList.add('bg-dark', 'text-white');
            } else {
                document.body.classList.remove('bg-dark', 'text-white');
            }
        });
    </script>
</body>
</html>
