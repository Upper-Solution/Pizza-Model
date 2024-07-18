<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar se todos os campos obrigatórios estão preenchidos
    $required_fields = ['password', 'fullname', 'email', 'cep', 'address', 'house_number', 'phone_number', 'city', 'neighborhood'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die('Por favor, preencha todos os campos obrigatórios.');
        }
    }

    // Conectar ao banco de dados
    $conn = new mysqli('127.0.0.1', 'u778175734_upper', '5pp2rr2s4l5t34N', 'u778175734_PIzzaDB', 3306);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }

    
    // Verificar se o e-mail já está registrado
    $check_email_query = 'SELECT * FROM users WHERE email = ? LIMIT 1';
    $stmt_check_email = $conn->prepare($check_email_query);
    $stmt_check_email->bind_param('s', $_POST['email']);
    $stmt_check_email->execute();
    $result = $stmt_check_email->get_result();
    if ($result->num_rows > 0) {
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

    // Salvar a imagem do perfil no servidor
    $profile_image_path = 'default-profile.png'; // Imagem padrão

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        // Nome da imagem única
        $file_name = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $uploadDir = 'uploads/';
        $profile_image_path = $uploadDir . $file_name;

        // Move o arquivo para o diretório de uploads
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_image_path)) {
            die('Erro ao salvar a imagem do perfil.');
        }
    }

    // Inserir os dados do usuário no banco de dados
    $stmt = $conn->prepare('INSERT INTO users (password, fullname, email, cep, address, house_number, phone_number, city, neighborhood, complement, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt->bind_param('sssssssssss', $hashed_password, $_POST['fullname'], $_POST['email'], $_POST['cep'], $address_data['address'], $_POST['house_number'], $_POST['phone_number'], $address_data['city'], $address_data['neighborhood'], $_POST['complement'], $profile_image_path);

    if (!$stmt->execute()) {
        die('Erro ao registrar usuário: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

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
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/register_styles.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Register - Pizzaria</title>
</head>
<body>
    <h1 class="form-title">Cadastro de Usuário</h1>
    <div class="container">
        <div class="form-container">
            <div class="profile-pic">
                <div class="profile-circle">
                    <img id="profileImage" src="default-profile.png" alt="Foto de Perfil">
                </div>
                <input type="file" id="profileImageInput" name="profile_image" accept="image/*" style="display: none;">
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
                        <input type="text" id="cep" name="cep" class="form-input" required>
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
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                </div>
                <button type="submit" class="form-button">Register</button>
            </form>
            <div class="form-footer">
                <p>Já tem uma conta? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('profileImage').addEventListener('click', function() {
            document.getElementById('profileImageInput').click();
        });

        document.getElementById('profileImageInput').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileImage').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('cep').addEventListener('blur', function() {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length !== 8) {
                return;
            }
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        console.log('CEP não encontrado.');
                        return;
                    }
                    document.getElementById('address').value = data.logradouro;
                    document.getElementById('neighborhood').value = data.bairro;
                    document.getElementById('city').value = data.localidade;
                })
                .catch(error => console.error('Erro ao consultar o CEP:', error));
        });
    </script>
</body>
</html>
