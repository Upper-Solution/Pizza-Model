<?php
session_start();

require_once 'config.php';

function connectToDatabase($hosts, $port, $dbname, $username, $password) {
    $dsn = "mysql:host=$hosts;port=$port;dbname=$dbname;charset=utf8mb4";
    try {
        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => false,
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()]);
        exit;
    }
}

$loggedIn = isset($_SESSION['user_id']);
if (!$loggedIn) {
    echo json_encode(['status' => 'error', 'message' => 'Usuário não está logado.']);
    exit;
}

$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Não foi possível conectar ao banco de dados.']);
    exit;
}

$userId = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado.']);
        exit;
    }

    $email = $user['email'];

    $cartData = json_decode(file_get_contents('php://input'), true);
    if (!$cartData) {
        echo json_encode(['status' => 'error', 'message' => 'Dados do carrinho não fornecidos.']);
        exit;
    }

    // Aqui você pode processar os dados do carrinho e salvar no banco de dados
    // Exemplos de dados do carrinho:
    $orderId = $cartData['id'];
    $quantidade = $cartData['qtd'];

    // Exemplo de salvamento no banco de dados (ajuste conforme necessário)
    $stmt = $pdo->prepare('INSERT INTO orders (user_email, order_id, quantidade) VALUES (?, ?, ?)');
    $stmt->execute([$email, $orderId, $quantidade]);

    echo json_encode(['status' => 'success', 'message' => 'Pedido finalizado com sucesso.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao consultar dados do usuário: ' . $e->getMessage()]);
}
?>
