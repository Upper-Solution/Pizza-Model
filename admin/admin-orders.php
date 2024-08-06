<?php
session_start();

// Inclui o arquivo de configuração
require_once '../config.php';

// Verificar se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: adm-login.php');
    exit;
}

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
        <a href="adm-painel.php"> Voltar </a>
    </section>

    <!-- Contêineres para os status -->
    <div id="status-container">
        <div id="recebido" class="status-column" data-status="Recebido">
            <h2>Recebido</h2>
            <ul class="sortable" id="recebido-list">
                <!-- Itens serão inseridos aqui via JavaScript -->
            </ul>
        </div>
        <div id="preparando" class="status-column" data-status="Preparando">
            <h2>Preparando</h2>
            <ul class="sortable" id="preparando-list">
                <!-- Itens serão inseridos aqui via JavaScript -->
            </ul>
        </div>
        <div id="entrega" class="status-column" data-status="Saiu para Entrega">
            <h2>Saiu para Entrega</h2>
            <ul class="sortable" id="entrega-list">
                <!-- Itens serão inseridos aqui via JavaScript -->
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
        function fetchOrders() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch-orders.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var orders = JSON.parse(xhr.responseText);
                    updateOrderLists(orders);
                }
            };
            xhr.send();
        }

        function updateOrderLists(orders) {
            var recebidoList = document.getElementById('recebido-list');
            var preparandoList = document.getElementById('preparando-list');
            var entregaList = document.getElementById('entrega-list');

            recebidoList.innerHTML = '';
            preparandoList.innerHTML = '';
            entregaList.innerHTML = '';

            orders.forEach(function(order) {
                var listItem = "<li class='card' data-id='" + order.order_id + "' onclick='showDetails(" + order.order_id + ")'>" +
                               "<div class='status-bolinha status-" + order.status.toLowerCase().replace(' ', '-') + "'></div>" +
                               order.customer_name + " / " + order.items;

                // Adiciona o botão 'Recusar' na coluna 'Recebido'
                if (order.status === 'Recebido') {
                    listItem += " <button onclick='confirmRejectOrder(" + order.order_id + ")'>Recusar</button>";
                }

                // Adiciona o botão 'Entregue' na coluna 'Saiu para Entrega'
                if (order.status === 'Saiu para Entrega') {
                    listItem += " <button onclick='confirmMarkAsDelivered(" + order.order_id + ")'>Entregue</button>";
                }

                listItem += "</li>";

                if (order.status === 'Recebido') {
                    recebidoList.innerHTML += listItem;
                } else if (order.status === 'Preparando') {
                    preparandoList.innerHTML += listItem;
                } else if (order.status === 'Saiu para Entrega') {
                    entregaList.innerHTML += listItem;
                }
            });

            // Re-initialize sortable functionality
            initializeSortable();
        }

        function initializeSortable() {
            var sortableLists = document.querySelectorAll('.sortable');

            sortableLists.forEach(function (list) {
                Sortable.create(list, {
                    group: 'shared',
                    animation: 150,
                    onStart: function (evt) {
                        evt.item.classList.add('dragging');
                    },
                    onEnd: function (evt) {
                        evt.item.classList.remove('dragging');
                        
                        var itemId = evt.item.dataset.id;
                        var newStatus = evt.to.parentNode.dataset.status;
                        
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', 'update-status.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send('id=' + encodeURIComponent(itemId) + '&status=' + encodeURIComponent(newStatus));
                    }
                });
            });
        }

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

        function confirmRejectOrder(orderId) {
            if (confirm('Tem certeza que deseja recusar este pedido?')) {
                rejectOrder(orderId);
            }
        }

        function confirmMarkAsDelivered(orderId) {
            if (confirm('Tem certeza que este pedido foi entregue?')) {
                markAsDelivered(orderId);
            }
        }

        function rejectOrder(orderId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update-status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    fetchOrders(); // Atualiza a lista de pedidos
                }
            };
            xhr.send('id=' + encodeURIComponent(orderId) + '&status=Recusado');
        }

        function markAsDelivered(orderId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update-status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    fetchOrders(); // Atualiza a lista de pedidos
                }
            };
            xhr.send('id=' + encodeURIComponent(orderId) + '&status=Entregue');
        }

        document.addEventListener('DOMContentLoaded', function () {
            fetchOrders();
            setInterval(fetchOrders, 5000); // Atualiza a cada 5 segundos
        });
    </script>
</body>
</html>
