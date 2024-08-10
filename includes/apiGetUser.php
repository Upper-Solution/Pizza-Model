<?php
session_start();

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

header('Content-Type: application/json');

// Inclui o arquivo de configuração para conexão com o banco de dados
require_once '../config/config.php';

// Obtém a conexão com o banco de dados
try {
    $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
    exit();
}

// Inicialização das variáveis de usuário
$user = null;

if ($loggedIn) {
    // Recuperar informações do usuário logado
    $userId = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare('SELECT id, fullname, email, cep, address, house_number, city, neighborhood, complement FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['error' => 'Usuário não encontrado']);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao consultar usuário: ' . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(['error' => 'Usuário não está logado']);
    exit();
}

// Fechar conexão com o banco de dados
$pdo = null;

// Retorna os dados do usuário em JSON
echo json_encode($user);
?>
