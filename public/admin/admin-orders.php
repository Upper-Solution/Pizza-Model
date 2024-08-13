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

// Consulta SQL para obter os dados dos pedidos
try {
    $stmt = $pdo->prepare("SELECT * FROM orders" . $searchQuery . " ORDER BY order_date DESC");
    $stmt->execute($searchValue);
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
        .clock {
            font-size: 14px;
            font-weight: bold;
            margin-left: 10px; /* Ajuste o espaçamento conforme necessário */
        }
    </style>
</head>
<body>
    <header class="header">
        <h1 class="title">Pedidos Realizados</h1>
    </header>

    <section class="search-section">
        <a href="adm-painel.php" class="back-link">Voltar</a>
    </section>

    <div id="status-container" class="status-container">
        <div id="recebido" class="status-column" data-status="Recebido">
            <h2 class="status-title">Recebido</h2>
            <ul class="sortable" id="recebido-list"></ul>
        </div>
        <div id="preparando" class="status-column" data-status="Preparando">
            <h2 class="status-title">Preparando</h2>
            <ul class="sortable" id="preparando-list"></ul>
        </div>
        <div id="entrega" class="status-column" data-status="Saiu para Entrega">
            <h2 class="status-title">Saiu para Entrega</h2>
            <ul class="sortable" id="entrega-list"></ul>
        </div>
    </div>

    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 class="modal-title">Detalhes do Pedido</h2>
            <div id="orderDetails" class="order-details"></div>
        </div>
    </div>

    <script>
        function updateClockInCards() {
            const clocks = document.querySelectorAll('.clock');
            clocks.forEach(clock => {
                const orderDate = new Date(clock.dataset.orderDate);
                const now = new Date();
                const elapsed = now - orderDate; // tempo decorrido em milissegundos

                // Converte milissegundos em horas, minutos e segundos
                const seconds = Math.floor(elapsed / 1000);
                const minutes = Math.floor(seconds / 60);
                const hours = Math.floor(minutes / 60);

                clock.textContent = `${hours}h ${minutes % 60}m ${seconds % 60}s`;
            });
        }

        // Atualiza o cronômetro a cada segundo
        setInterval(updateClockInCards, 1000);
        updateClockInCards(); // Atualiza imediatamente ao carregar a página

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

                    var itemsDetails = '';
                    orderGroup.forEach(function(order) {
                        itemsDetails += "<div class='order-item'>Item: " + order.items + "</div>";
                    });

                    var listItem = "<li class='card' data-id='" + orderGroup[0].order_id + "' data-order-date='" + orderGroup[0].order_date + "' onclick='showDetails(" + orderGroup[0].order_id + ")'>" +
                                    "<div class='order-id-container'>" +
                                        "<div class='order-id'>Pedido #" + orderGroup[0].order_id + "</div>" +
                                        "<div class='clock' data-order-date='" + orderGroup[0].order_date + "'>00:00</div>" + // Adicione o valor inicial do cronômetro se necessário
                                    "</div>" +
                                    "<div class='order-name'>Cliente: " + orderGroup[0].customer_name + "</div>" +
                                    itemsDetails +
                                    "<div class='order-observation'>Observação: " + (orderGroup[0].observation || "Nenhuma") + "</div>" +
                                    "<div class='buttons-container'>";

                                if (orderGroup[0].status === 'Recebido') {
                                    listItem += "<button onclick='moveToPreparing(" + orderGroup[0].order_id + ")' class='status-button'>Preparar</button>";
                                } else if (orderGroup[0].status === 'Preparando') {
                                    listItem += "<button onclick='moveToDelivery(" + orderGroup[0].order_id + ")' class='status-button'>Entregar</button>";
                                }

                                if (orderGroup[0].status === 'Recebido') {
                                    listItem += "<button onclick='confirmRejectOrder(" + orderGroup[0].order_id + ")' class='reject-button'>Recusar</button>";
                                } else if (orderGroup[0].status === 'Saiu para Entrega') {
                                    listItem += "<button onclick='confirmMarkAsDelivered(" + orderGroup[0].order_id + ")' class='deliver-button'>Finalizar Pedido</button>";
                                }

                                listItem += "</div></li>";


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
                        
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                fetchOrders(); // Recarrega os pedidos para garantir que os dados estão atualizados
                            } else {
                                console.error('Falha ao atualizar status: ' + xhr.statusText);
                            }
                        };
                    }
                });
            });
        }

        function moveToPreparing(orderId) {
            updateOrderStatus(orderId, 'Preparando');
        }

        function moveToDelivery(orderId) {
            updateOrderStatus(orderId, 'Saiu para Entrega');
        }

        function updateOrderStatus(orderId, newStatus) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'update-status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('id=' + encodeURIComponent(orderId) + '&status=' + encodeURIComponent(newStatus));
            xhr.onload = function() {
                if (xhr.status === 200) {
                    fetchOrders(); // Recarrega os pedidos para refletir as mudanças
                } else {
                    console.error('Falha ao atualizar status: ' + xhr.statusText);
                }
            };
        }

        function confirmRejectOrder(orderId) {
            if (confirm('Tem certeza de que deseja recusar este pedido?')) {
                updateOrderStatus(orderId, 'Recusado');
            }
        }

        function confirmMarkAsDelivered(orderId) {
            if (confirm('Tem certeza de que deseja marcar este pedido como entregue?')) {
                updateOrderStatus(orderId, 'Entregue');
            }
        }

        function showDetails(orderId) {
    console.log('Exibindo detalhes para o pedido:', orderId);
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'get-order-details.php?id=' + encodeURIComponent(orderId), true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var orderDetails = JSON.parse(xhr.responseText);
                console.log('Detalhes do pedido recebidos:', orderDetails);
                
                // Exemplo de exibição dos dados
                var detailsDiv = document.getElementById('orderDetails');
                detailsDiv.innerHTML = `
                    <div class="order-detail-container">
                        <div class="order-detail">
                            <h3>Detalhes do Usuário</h3>
                            <p><strong>Nome do Cliente:</strong> ${orderDetails.customer_name}</p>
                            <p><strong>Email:</strong> ${orderDetails.email}</p>
                            <p><strong>Data do Pedido:</strong> ${orderDetails.order_date}</p>
                            <p><strong>Cep:</strong> ${orderDetails.cep}</p>
                            <p><strong>Bairro:</strong> ${orderDetails.neighborhood}</p>
                            <p><strong>Endereço:</strong> ${orderDetails.street}, ${orderDetails.number}</p>
                            <p><strong>Telefone:</strong> ${orderDetails.phone_number}</p>
                            <p><strong>Observações:</strong> ${orderDetails.notes || "Nenhuma"}</p>
                        </div>
                        <div class="order-detail">
                            <h3>Detalhes do Pedido #${orderId}</h3>
                            ${Object.keys(orderDetails.merged_items).map(item => {
                                var details = orderDetails.merged_items[item];
                                return `
                                    <p><strong>Item:</strong> ${item}</p>
                                    <p><strong>Quantidade Total:</strong> ${details.quantity}</p>
                                    <p><strong>Preço Total:</strong> R$${details.price.toFixed(2).replace('.', ',')}</p>
                                    <hr>
                                `;
                            }).join('')}
                            <h3>Total Geral</h3>
                            <p><strong>Total Quantidade:</strong> ${orderDetails.total_quantity}</p>
                            <p><strong>Total Preço:</strong> R$${orderDetails.total_price.toFixed(2).replace('.', ',')}</p>
                        </div>
                    </div>
                `;

                document.getElementById('orderModal').style.display = 'block';
            } catch (e) {
                console.error('Erro ao analisar JSON:', e);
            }
        } else {
            console.error('Falha ao carregar detalhes do pedido:', xhr.statusText);
        }
    };
    xhr.send();
}



function closeModal() {
    document.getElementById('orderModal').style.display = 'none';
}


        fetchOrders(); // Carrega os pedidos ao iniciar a página
    </script>
</body>
</html>
