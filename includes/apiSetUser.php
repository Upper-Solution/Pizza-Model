<?php
// Inclui o arquivo de configuração
require_once '../config/config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

session_start();

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

if (!$loggedIn) {
    echo json_encode(["error" => "Usuário não está logado."]);
    exit();
}

// Obter o ID do usuário da sessão
$userId = $_SESSION['user_id'];

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    echo json_encode(["error" => "Não foi possível conectar ao banco de dados."]);
    exit();
}

// Define o cabeçalho para JSON
header('Content-Type: application/json');

try {
    // Obtém os dados enviados no corpo da requisição
    $data = json_decode(file_get_contents('php://input'), true);

    // Verifica se todos os campos obrigatórios estão presentes
    if (!isset($data['fullname'], $data['cep'], $data['address'], $data['house_number'], $data['phone_number'], $data['city'], $data['neighborhood'])) {
        throw new Exception('Campos obrigatórios não preenchidos.');
    }

    // Prepare a consulta SQL para atualizar os dados do usuário
    $sql = "UPDATE users SET 
                fullname = :fullname, 
                email = :email,
                cep = :cep,
                address = :address,
                house_number = :house_number,
                phone_number = :phone_number,
                city = :city,
                neighborhood = :neighborhood,
                complement = :complement,
                profile_image = :profile_image
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    // Vincule os parâmetros
    $stmt->bindParam(':id', $userId); // Use o ID do usuário logado
    $stmt->bindParam(':fullname', $data['fullname']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':cep', $data['cep']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':house_number', $data['house_number']);
    $stmt->bindParam(':phone_number', $data['phone_number']);
    $stmt->bindParam(':city', $data['city']);
    $stmt->bindParam(':neighborhood', $data['neighborhood']);
    $stmt->bindParam(':complement', $data['complement']);
    
    // Perfil de imagem é opcional
    if (isset($data['profile_image']) && !empty($data['profile_image'])) {
        $stmt->bindParam(':profile_image', $data['profile_image'], PDO::PARAM_LOB);
    } else {
        $stmt->bindValue(':profile_image', null, PDO::PARAM_NULL);
    }
    


    // Execute a consulta
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Dados atualizados com sucesso.']);
    } else {
        throw new Exception('Erro ao atualizar os dados do usuário.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
