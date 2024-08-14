<?php
// Inicia a sessão
session_start();

// Inclui o arquivo de configuração
require_once '../../config/config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit;
}

$userEmail = $_SESSION['user_email'];

try {
    // Conectar ao banco de dados
    $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
    
    // Preparar e executar a consulta para obter pedidos do usuário baseado no email
    $stmt = $pdo->prepare("SELECT order_id, order_date, status, items FROM orders WHERE email = ? ORDER BY order_date DESC");
    $stmt->execute([$userEmail]);

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar pedidos do usuário: " . $e->getMessage();
} finally {
    // Fechar conexão
    $pdo = null;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="../css/meus-pedidos.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <title>Meus Pedidos</title>
</head>
<body>

    <header class="header">
        <?php include '../../includes/nav.php'; ?>
    </header>

    <div class="content">
        <h1>Meus Pedidos</h1>
        <div id="ordersContainer"></div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ordersContainer = document.getElementById('ordersContainer');

        function updateOrders() {
            fetch('get-order-details.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    const orders = data.orders;
                    let htmlContent = '';

                    if (orders.length > 0) {
                        orders.forEach(order => {
                            let statusTag = '';
                            let statusBar = '';
                            let statusClass = '';
                            let statusLabel = '';

                            if (order.status === 'Recusado') {
                                statusTag = '<div class="status-tag status-recused">Recusado</div>';
                                statusBar = '<div class="status-bar cancelled-status"><div class="progress-bar"></div></div>';
                            } else if (order.status === 'Entregue') {
                                statusTag = '<div class="status-tag status-delivered">Entregue</div>';
                                statusBar = '<div class="status-bar delivered-status"><div class="progress-bar"></div></div>';
                            } else {
                                const steps = ['Recebido', 'Preparando', 'Saiu para Entrega'];
                                const currentStep = steps.indexOf(order.status);
                                statusBar = '<div class="status-bar">';
                                steps.forEach((step, index) => {
                                    const stepClass = index <= currentStep ? (order.status === 'Entregue' ? 'delivered' : 'complete') : '';
                                    statusBar += `<div class="progress-step ${stepClass}"><div class="progress-bar"></div></div>`;
                                });
                                statusBar += '</div>';
                                statusLabel = '<div class="status-labels"><span>Confirmação do restaurante</span><span>Preparando</span><span>Saiu para Entrega</span></div>';
                            }

                            htmlContent += `
                                <div class="order-card">
                                    <h2>Pedido #${order.order_id}</h2>
                                    <p class="order-date">Data: ${order.order_date}</p>
                                    ${statusTag}
                                    ${statusBar}
                                    ${statusLabel}
                                    <div class="order-items">
                                        <p><strong>Itens:</strong> ${order.items}</p>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        htmlContent += '<p>Nenhum pedido encontrado.</p>';
                    }

                    ordersContainer.innerHTML = htmlContent;
                    updateProgress();
                })
                .catch(error => console.error('Erro ao atualizar pedidos:', error));
        }

        function updateProgress() {
            const progressBars = document.querySelectorAll('.progress-bar');

            progressBars.forEach(bar => {
                const parent = bar.closest('.progress-step');
                if (parent) {
                    if (parent.classList.contains('delivered')) {
                        bar.style.width = '100%';
                        bar.style.backgroundColor = '#4caf50'; // Cor para status entregue
                    } else if (parent.classList.contains('complete')) {
                        bar.style.width = '100%';
                        bar.style.backgroundColor = '#66bb6a'; // Cor para status completo
                    } else {
                        bar.style.width = '0%';
                        bar.style.backgroundColor = '#e0e0e0'; // Cor para status inativo
                    }
                }
            });
        }

        // Atualiza a lista de pedidos automaticamente a cada 10 segundos
        setInterval(updateOrders, 10000); // 10000 milissegundos = 10 segundos

        // Atualiza imediatamente ao carregar
        updateOrders();
    });
    </script>

</body>
</html>
