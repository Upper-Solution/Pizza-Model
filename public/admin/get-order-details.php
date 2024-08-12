<?php
require_once '../../config/config.php';

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

            echo "<h3>Cliente: " . htmlspecialchars($order['customer_name']) . "</h3>";
            echo "<p>Email: " . htmlspecialchars($order['email']) . "</p>";
            echo "<p>Data do Pedido: " . htmlspecialchars($order['order_date']) . "</p>";
            echo "<p>Endereço: " . htmlspecialchars($order['address']) . "</p>";
            echo "<p>Telefone: " . htmlspecialchars($order['phone_number']) . "</p>";
            echo "<p>Observações: " . htmlspecialchars($order['notes']) . "</p>";

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

            // Exibindo itens mesclados
            echo "<h3>Itens Mesclados</h3>";
            foreach ($mergedItems as $item => $details) {
                echo "<p>Item: " . htmlspecialchars($item) . "</p>";
                echo "<p>Quantidade Total: " . $details['quantity'] . "</p>";
                echo "<p>Preço Total: R$" . number_format($details['price'], 2, ',', '.') . "</p>";
                echo "<hr>";
            }

            // Exibindo total acumulado
            echo "<h3>Total Geral</h3>";
            echo "<p>Total Quantidade: " . $totalQuantity . "</p>";
            echo "<p>Total Preço: R$" . number_format($totalPrice, 2, ',', '.') . "</p>";
        } else {
            echo "<p>Detalhes do pedido não encontrados.</p>";
        }
    } catch (Exception $e) {
        echo "<p>Erro: " . $e->getMessage() . "</p>";
    }
}
?>
