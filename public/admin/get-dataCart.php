<?php
session_start();

require_once '../../config/config.php';

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
    // Consultar as informações do usuário
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado.']);
        exit;
    }

    // Receber os dados do carrinho enviados via POST
    $cartData = json_decode(file_get_contents('php://input'), true);
    if (!$cartData) {
        echo json_encode(['status' => 'error', 'message' => 'Dados do carrinho não fornecidos.']);
        exit;
    }

    foreach ($cartData as $item) {
        $orderId = $item['orderId'];
        $quantidade = $item['quantidade'];
        $observacao = $item['observacoes'];
        $observacoesGerais = $item['observacoesGerais'];
        $formaPagamento = $item['formaPagamento'];
        $valorTroco = $item['valorTroco'];
        $total = $item['valorTotal'];

        // Consultar as informações da pizza
        $stmt = $pdo->prepare('SELECT nome, descricao FROM Pizzas WHERE id = ?');
        $stmt->execute([$orderId]);
        $pizza = $stmt->fetch();

        if (!$pizza) {
            echo json_encode(['status' => 'error', 'message' => 'Pizza não encontrada.']);
            exit;
        }

        // Inserir os dados na tabela orders
        $stmt = $pdo->prepare('INSERT INTO orders (customer_name, items, quantidade, observacoesPedidos, ObservacoesGerais, formaPagamento, valorTroco, total, status, cep, city, neighborhood, street, number, complement, phone_number, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $user['fullname'],
            $pizza['nome'],
            $quantidade,
            $observacao,
            $observacoesGerais,
            $formaPagamento,
            $valorTroco,
            $total,
            'Recebido', 
            $user['cep'],
            $user['city'],
            $user['neighborhood'],
            $user['address'],
            $user['house_number'],
            $user['complement'],
            $user['phone_number'],
            $user['email'],
        ]);
    }

    echo json_encode(['status' => 'success', 'message' => 'Pedido finalizado com sucesso.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao consultar dados: ' . $e->getMessage()]);
}
?>
