<?php
require_once '../config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Obtém o ID do pedido da solicitação
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId > 0) {
    // Consulta SQL para obter os detalhes do pedido
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            echo "<div class='order-details'>";
            echo "<div class='column'>";
            echo "<p><strong>ID:</strong> " . htmlspecialchars($order['order_id']) . "</p>";
            echo "<p><strong>Nome do Cliente:</strong> " . htmlspecialchars($order['customer_name']) . "</p>";
            echo "<p><strong>Itens:</strong> " . htmlspecialchars($order['items']) . "</p>";
            echo "<p><strong>Total:</strong> R$ " . number_format($order['total'], 2, ',', '.') . "</p>";
            echo "<p><strong>Data do Pedido:</strong> " . htmlspecialchars($order['order_date']) . "</p>";
            echo "<p><strong>Status:</strong> " . htmlspecialchars($order['status']) . "</p>";
            echo "</div>";
            echo "<div class='column'>";
            echo "<p><strong>Cidade:</strong> " . htmlspecialchars($order['city']) . "</p>";
            echo "<p><strong>Bairro:</strong> " . htmlspecialchars($order['neighborhood']) . "</p>";
            echo "<p><strong>Rua:</strong> " . htmlspecialchars($order['street']) . "</p>";
            echo "<p><strong>Número:</strong> " . htmlspecialchars($order['number']) . "</p>";
            echo "<p><strong>Complemento:</strong> " . htmlspecialchars($order['complement']) . "</p>";
            echo "<p><strong>Observação:</strong> " . htmlspecialchars($order['observation']) . "</p>";
            echo "<p><strong>Telefone:</strong> " . htmlspecialchars($order['phone_number']) . "</p>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<p>Pedido não encontrado.</p>";
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar detalhes do pedido: " . $e->getMessage();
    }
}

// Fechar conexão
$pdo = null;
?>
