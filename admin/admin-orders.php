<?php
session_start();

// Verifica se o usuário é administrador
if (!isset($_SESSION['admin_id'])) {
    header('Location: adm-login.php');
    exit;
}

// Inclui o arquivo de configuração
require_once '../config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Verifica se foi feito um filtro de pesquisa
$searchQuery = '';
$searchValue = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $searchQuery = " WHERE customer_name LIKE ? OR order_id = ?";
    $searchValue = ["%$search%", $search];
}

// Consulta SQL para obter os dados dos pedidos
try {
    $stmt = $pdo->prepare("SELECT * FROM orders" . $searchQuery . " ORDER BY order_date DESC");
    if ($searchValue) {
        $stmt->execute($searchValue);
    } else {
        $stmt->execute();
    }
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar dados dos pedidos: " . $e->getMessage();
}

// Fechar conexão
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos Realizados</title>
    <link rel="stylesheet" href="../css/status.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <style>
        /* Estilos para o modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>Pedidos Realizados</h1>
    </header>
    
    <!-- Formulário de pesquisa -->
    <section class="search-section">
        <form method="get" action="">
            <input type="text" name="search" placeholder="Buscar por nome ou ID" value="<?php echo htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES); ?>">
            <button type="submit">Pesquisar</button>
        </form>
    </section>

    <!-- Contêineres para os status -->
    <div id="status-container">
        <div id="recebido" class="status-column">
            <h2>Recebido</h2>
            <ul class="sortable" data-status="Recebido">
                <?php
                foreach ($orders as $order) {
                    if ($order['status'] === 'Recebido') {
                        echo "<li data-id='" . htmlspecialchars($order['order_id']) . "' onclick='showDetails(" . htmlspecialchars($order['order_id']) . ")'>" . htmlspecialchars($order['customer_name']) ." / ". htmlspecialchars($order['items']) . "</li>";
                    }
                }
                ?>
            </ul>
        </div>
        <div id="preparando" class="status-column">
            <h2>Preparando</h2>
            <ul class="sortable" data-status="Preparando">
                <?php
                foreach ($orders as $order) {
                    if ($order['status'] === 'Preparando') {
                        echo "<li data-id='" . htmlspecialchars($order['order_id']) . "' onclick='showDetails(" . htmlspecialchars($order['order_id']) . ")'>" . htmlspecialchars($order['customer_name']) ." / ". htmlspecialchars($order['items']) . "</li>";
                    }
                }
                ?>
            </ul>
        </div>
        <div id="entrega" class="status-column">
            <h2>Saiu para Entrega</h2>
            <ul class="sortable" data-status="Saiu para Entrega">
                <?php
                foreach ($orders as $order) {
                    if ($order['status'] === 'Saiu para Entrega') {
                        echo "<li data-id='" . htmlspecialchars($order['order_id']) . "' onclick='showDetails(" . htmlspecialchars($order['order_id']) . ")'>" . htmlspecialchars($order['customer_name']) ." / ". htmlspecialchars($order['items']) . "</li>";
                    }
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- Modal para mostrar detalhes do pedido -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Detalhes do Pedido</h2>
            <div id="orderDetails"></div>
        </div>
    </div>

    <script>
        function showDetails(orderId) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get-order-details.php?id=' + encodeURIComponent(orderId), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('orderDetails').innerHTML = xhr.responseText;
                    document.getElementById('orderModal').style.display = 'block';
                }
            };
            xhr.send();
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Seleciona todas as listas com a classe 'sortable'
            var sortableLists = document.querySelectorAll('.sortable');
            
            // Inicializa o SortableJS para cada lista
            sortableLists.forEach(function (list) {
                Sortable.create(list, {
                    group: 'shared', // Permite arrastar entre colunas
                    animation: 150,
                    onStart: function (evt) {
                        evt.item.classList.add('dragging');
                    },
                    onEnd: function (evt) {
                        evt.item.classList.remove('dragging');
                        
                        var itemId = evt.item.dataset.id;
                        var newStatus = evt.from.dataset.status;
                        
                        console.log('Item moved:', itemId);
                        console.log('New status:', newStatus);
                        
                        // Envia a atualização para o servidor
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'update-status.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send('id=' + encodeURIComponent(itemId) + '&status=' + encodeURIComponent(newStatus));
                    }
                });
            });
        });
    </script>
</body>
</html>
