<?php
session_start();

// Inclui o arquivo de configuração
require_once '../../config/config.php';

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
    
    <section class="search-section">
        <form method="get" action="">
            <input type="text" name="search" placeholder="Buscar por nome ou ID" value="<?php echo htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES); ?>">
            <button type="submit">Pesquisar</button>
        </form>
        <a href="adm-painel.php"> Voltar </a>
    </section>

    <div id="status-container">
        <div id="recebido" class="status-column" data-status="Recebido">
            <h2>Recebido</h2>
            <ul class="sortable" id="recebido-list"></ul>
        </div>
        <div id="preparando" class="status-column" data-status="Preparando">
            <h2>Preparando</h2>
            <ul class="sortable" id="preparando-list"></ul>
        </div>
        <div id="entrega" class="status-column" data-status="Saiu para Entrega">
            <h2>Saiu para Entrega</h2>
            <ul class="sortable" id="entrega-list"></ul>
        </div>
    </div>

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

            var groupedOrders = {};

            orders.forEach(function(order) {
                var key = order.order_date + '-' + order.customer_email;

                if (!groupedOrders[key]) {
                    groupedOrders[key] = [];
                }

                groupedOrders[key].push(order);
            });

            for (var key in groupedOrders) {
                if (groupedOrders.hasOwnProperty(key)) {
                    var orderGroup = groupedOrders[key];

                    var listItem = "<li class='card' data-id='" + orderGroup[0].order_id + "' onclick='showDetails(" + orderGroup[0].order_id + ")'>" +
                                   "<div class='status-bolinha status-" + orderGroup[0].status.toLowerCase().replace(' ', '-') + "'></div>" +
                                   orderGroup[0].customer_name + " / " + orderGroup[0].items;

                    if (orderGroup.length > 1) {
                        listItem += " (Duplicado: " + orderGroup.length + " pedidos)";
                    }

                    if (orderGroup[0].status === 'Recebido') {
                        listItem += " <button onclick='confirmRejectOrder(" + orderGroup[0].order_id + ")'>Recusar</button>";
                    }

                    if (orderGroup[0].status === 'Saiu para Entrega') {
                        listItem += " <button onclick='confirmMarkAsDelivered(" + orderGroup[0].order_id + ")'>Entregue</button>";
                    }

                    listItem += "</li>";

                    if (orderGroup[0].status === 'Recebido') {
                        recebidoList.innerHTML += listItem;
                    } else if (orderGroup[0].status === 'Preparando') {
                        preparandoList.innerHTML += listItem;
                    } else if (orderGroup[0].status === 'Saiu para Entrega') {
                        entregaList.innerHTML += listItem;
                    }
                }
            }

            initializeSortable();
        }

        function initializeSortable() {
            var sortableLists = document.querySelectorAll('.sortable');

            sortableLists.forEach(function(list) {
                Sortable.create(list, {
                    group: 'shared',
                    animation: 150,
                    onStart: function(evt) {
                        evt.item.classList.add('dragging');
                    },
                    onEnd: function(evt) {
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
            if (confirm('Tem certeza que deseja marcar este pedido como entregue?')) {
                markAsDelivered(orderId);
            }
        }

        function rejectOrder(orderId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update-status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('id=' + encodeURIComponent(orderId) + '&status=Recusado');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    fetchOrders();
                }
            };
        }

        function markAsDelivered(orderId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update-status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('id=' + encodeURIComponent(orderId) + '&status=Entregue');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    fetchOrders();
                }
            };
        }

        fetchOrders();
    </script>
</body>
</html>
