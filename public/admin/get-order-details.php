<?php
require_once '../../config/config.php';

header('Content-Type: application/json'); // Definir o tipo de conteúdo para JSON

if (isset($_GET['id'])) {
    $orderId = $_GET['id'];

    try {
        // Conectando ao banco de dados
        $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

        // Buscando o pedido específico pelo ID
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
        if (!$stmt->execute([$orderId])) {
            throw new Exception("Erro ao executar a consulta SQL para o pedido com ID $orderId.");
        }

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Buscando todos os pedidos que compartilham a mesma chave (nome, e-mail, data)
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_name = ? AND email = ? AND order_date = ?");
            if (!$stmt->execute([$order['customer_name'], $order['email'], $order['order_date']])) {
                throw new Exception("Erro ao executar a consulta SQL para os pedidos duplicados.");
            }
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Inicializar variáveis para acumular a soma
            $totalQuantity = 0;
            $totalPrice = 0.0;
            $mergedItems = [];

            // Processar pedidos e mesclar itens
            foreach ($orders as $index => $order) {
                // Adicionar itens ao array para mesclar
                $items = explode(';', $order['items']); // Supondo que os itens são separados por ";"
                foreach ($items as $item) {
                    if (!empty($item)) {
                        $itemKey = $item . ' (Pedido ' . ($index + 1) . ')';
                        if (!isset($mergedItems[$itemKey])) {
                            $mergedItems[$itemKey] = ['quantity' => 0, 'price' => 0.0];
                        }
                        $mergedItems[$itemKey]['quantity'] += (int)$order['quantidade'];
                        $mergedItems[$itemKey]['price'] += (float)$order['total'];
                    }
                }

                // Verificar e acumular a quantidade e o preço total
                $quantity = isset($order['quantidade']) ? (int)$order['quantidade'] : 0;
                $price = isset($order['total']) ? (float)$order['total'] : 0.0;

                $totalQuantity += $quantity;
                $totalPrice += $price;
            }

            // Preparar os dados para resposta JSON
            $response = [
                'customer_name' => $order['customer_name'],
                'email' => $order['email'],
                'order_date' => $order['order_date'],
                'cep' => $order['cep'],
                'street' => $order['street'],
                'number' => $order['number'],
                'neighborhood' => $order['neighborhood'],
                'phone_number' => $order['phone_number'],
                'notes' => $order['notes'],
                'merged_items' => $mergedItems,
                'total_quantity' => $totalQuantity,
                'total_price' => $totalPrice
            ];

            // Enviar resposta JSON
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Detalhes do pedido não encontrados.']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID do pedido não fornecido.']);
}
?>
