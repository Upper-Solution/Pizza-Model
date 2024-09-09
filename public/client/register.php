<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Inclui o arquivo de configuração
require_once '../../config/config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar se todos os campos obrigatórios estão preenchidos
    $required_fields = ['password', 'confirm_password', 'fullname', 'email', 'cep', 'address', 'house_number', 'phone_number', 'city', 'neighborhood'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die('Por favor, preencha todos os campos obrigatórios.');
        }
    }

    // Verificar se as senhas coincidem
    if ($_POST['password'] !== $_POST['confirm_password']) {
        die('As senhas não coincidem. Por favor, tente novamente.');
    }

    // Verificar se o e-mail já está registrado
    $check_email_query = 'SELECT * FROM users WHERE email = ? LIMIT 1';
    $stmt_check_email = $pdo->prepare($check_email_query);
    $stmt_check_email->execute([$_POST['email']]);
    if ($stmt_check_email->rowCount() > 0) {
        die('Este e-mail já está registrado. Por favor, escolha outro e-mail.');
    }

    // Função para validar e consultar o CEP usando a API do ViaCEP
    function validateAndFetchAddress($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep); // Remove caracteres não numéricos do CEP

        // Consultar a API do ViaCEP
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Verificar se houve erro na consulta
        $data = json_decode($response, true);
        if (isset($data['erro'])) {
            return false;
        }

        // Retornar os dados do endereço
        return [
            'address' => $data['logradouro'],
            'neighborhood' => $data['bairro'],
            'city' => $data['localidade'],
            'state' => $data['uf']
        ];
    }

    // Validar e obter os dados do endereço pelo CEP
    $address_data = validateAndFetchAddress($_POST['cep']);
    if (!$address_data) {
        die('CEP inválido. Por favor, verifique e tente novamente.');
    }

    // Verificar e processar o upload da imagem do perfil
    $profile_image = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        // Verificar o tipo do arquivo
        $allowed_types = ['image/png', 'image/jpeg'];
        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            die('O arquivo deve ser uma imagem PNG, JPG ou JPEG.');
        }

        // Verificar o tamanho do arquivo (máximo de 16 MB)
        if ($_FILES['profile_image']['size'] > 16 * 1024 * 1024) {
            die('O tamanho da imagem não pode exceder 16 MB.');
        }

        // Ler o conteúdo do arquivo
        $profile_image = file_get_contents($_FILES['profile_image']['tmp_name']);
    }

    // Inserir os dados do usuário no banco de dados
    $insert_user_query = 'INSERT INTO users (password, fullname, email, cep, address, house_number, phone_number, city, neighborhood, complement, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = $pdo->prepare($insert_user_query);
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt->execute([
        $hashed_password, 
        $_POST['fullname'], 
        $_POST['email'], 
        $_POST['cep'], 
        $address_data['address'], 
        $_POST['house_number'], 
        $_POST['phone_number'], 
        $address_data['city'], 
        $address_data['neighborhood'], 
        $_POST['complement'], 
        $profile_image
    ]);

    // Fechar a conexão
    $pdo = null;

    // Redirecionar para a página de login após o registro
    header('Location: login.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="../imagens/favicon.ico" type="image/x-icon">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/register_styles.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Register - Pizzaria</title>
    <script>
        async function buscarEndereco(cep) {
            if (!cep) {
                return;
            }

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();

                if (data.erro) {
                    alert('CEP não encontrado.');
                    return;
                }

                document.getElementById('address').value = data.logradouro;
                document.getElementById('neighborhood').value = data.bairro;
                document.getElementById('city').value = data.localidade;
                document.getElementById('state').value = data.uf;
            } catch (error) {
            }
        }

        function validarCep() {
            const cep = document.getElementById('cep').value.replace(/\D/g, '');
            if (cep.length === 8) {
                buscarEndereco(cep);
            } else {
                alert('CEP inválido. Deve conter 8 dígitos.');
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="profile-pic">
                <label for="profileImageInput" class="profile-circle">
                    <img id="profileImage" src="../imagens/default-profile.png" alt="">
                </label>
                <input type="file" id="profileImageInput" name="profile_image" accept="image/png, image/jpeg" style="display: none;" onchange="previewImage()">
            </div>
            <form method="POST" action="register.php" enctype="multipart/form-data">
                <div class="form-group-half">
                    <div class="form-group">
                        <label for="fullname">Nome Completo:</label>
                        <input type="text" id="fullname" name="fullname" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="cep">CEP:</label>
                        <input type="text" id="cep" name="cep" class="form-input" required onblur="validarCep()">
                    </div>
                </div>
                <div class="form-group-half">
                    <div class="form-group">
                        <label for="address">Endereço:</label>
                        <input type="text" id="address" name="address" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="house_number">Número da Casa:</label>
                        <input type="text" id="house_number" name="house_number" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Número de Celular:</label>
                        <input type="text" id="phone_number" name="phone_number" class="form-input" required>
                    </div>
                </div>
                <div class="form-group-half">
                    <div class="form-group">
                        <label for="city">Cidade:</label>
                        <input type="text" id="city" name="city" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="neighborhood">Bairro:</label>
                        <input type="text" id="neighborhood" name="neighborhood" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="complement">Complemento:</label>
                        <input type="text" id="complement" name="complement" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Senha:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                </div>
                <div class="form-group">
                    <input type="submit" value="Cadastrar" class="form-button">
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage() {
    const fileInput = document.getElementById('profileImageInput');
    const file = fileInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const profileImage = document.getElementById('profileImage');
            profileImage.src = e.target.result;
            profileImage.classList.add('updated'); // Adiciona a classe 'updated'
        };
        reader.readAsDataURL(file);
    }
}

    </script>
</body>
</html>
