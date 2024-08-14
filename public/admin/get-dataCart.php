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

    // Iniciar uma transação
    $pdo->beginTransaction();

    // Inicializar variáveis para o pedido
    $totalPedido = 0;
    $observacaoPedidos = '';
    $observacoesGerais = '';
    $formaPagamento = '';
    $valorTroco = 0;
    $itensPedido = [];

    foreach ($cartData as $item) {
        $orderId = $item['orderId'];
        $quantidade = $item['quantidade'];
        $observacao = $item['observacoes'];
        $observacoesGerais = $item['observacoesGerais'];
        $formaPagamento = $item['formaPagamento'];
        $valorTroco = $item['valorTroco'];
        $total = $item['valorTotal'];

        // Consultar as informações da pizza
        $stmt = $pdo->prepare('SELECT nome FROM Pizzas WHERE id = ?');
        $stmt->execute([$orderId]);
        $pizza = $stmt->fetch();

        if (!$pizza) {
            // Reverter a transação se houver um erro
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Pizza não encontrada.']);
            exit;
        }

        // Acumular o total do pedido e informações dos itens
        $totalPedido += $total;
        $itensPedido[] = $pizza['nome'] . ' - Qtd: ' . $quantidade . ' |';
    }

    // Converter o array de itens em uma string separada por nova linha
    $itensPedidoStr = implode("\n", $itensPedido);

    // Inserir o pedido na tabela orders
    $stmt = $pdo->prepare('INSERT INTO orders (customer_name, quantidade, observacoesPedidos, ObservacoesGerais, formaPagamento, valorTroco, total, status, cep, city, neighborhood, street, number, complement, phone_number, email, items) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $user['fullname'], // customer_name
        count($cartData), // quantidade
        $observacaoPedidos, // observacoesPedidos
        $observacoesGerais, // ObservacoesGerais
        $formaPagamento, // formaPagamento
        $valorTroco, // valorTroco
        $totalPedido, // total
        'Recebido', // status
        $user['cep'], // cep
        $user['city'], // city
        $user['neighborhood'], // neighborhood
        $user['address'], // street
        $user['house_number'], // number
        $user['complement'], // complement
        $user['phone_number'], // phone_number
        $user['email'], // email
        $itensPedidoStr // items (formatado como 'nome, quantidade')
    ]);

    // Confirmar a transação
    $pdo->commit();

    echo json_encode(['status' => 'success', 'message' => 'Pedido finalizado com sucesso.']);
} catch (PDOException $e) {
    // Reverter a transação em caso de erro
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Erro ao consultar dados: ' . $e->getMessage()]);
}
?>
