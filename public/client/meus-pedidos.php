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
    <link rel="stylesheet" href="../css/style-dark.css">
    <link rel="stylesheet" href="../css/meus-pedidos.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Link para Font Awesome -->
    <title>Meus Pedidos</title>
</head>
<body>
    <div class="container">
    <header class="header">
        <?php include '../../includes/nav.php'; ?>
    </header>

    <div class="content">
        <h1>Meus Pedidos</h1>
        <div id="ordersContainer"></div>
    </div>

    <!-- Modal -->
<div id="emailModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Enviar Mensagem</h2>
        <form id="emailForm">
            <input type="hidden" id="orderId" name="order_id">
            <label for="email">Seu Email:</label>
            <input type="email" id="email" name="email" readonly>
            <label for="subject">Assunto:</label>
            <input type="text" id="subject" name="subject" readonly>
            <label for="message">Mensagem:</label>
            <textarea id="message" name="message" rows="4" required></textarea>
            <button type="submit">Enviar</button>
        </form>
    </div>
</div>
</div>

<script src="../js/darkMode.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ordersContainer = document.getElementById('ordersContainer');
    const modal = document.getElementById('emailModal');
    const closeBtn = document.querySelector('.close-btn');
    const emailForm = document.getElementById('emailForm');
    const userEmail = "<?php echo $_SESSION['user_email']; ?>"; // E-mail da pessoa logada

    document.getElementById('email').value = userEmail;

    function updateOrders() {
        fetch('get-order-details.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    ordersContainer.innerHTML = `<p>${data.error}</p>`;
                    return;
                }

                const orders = data.orders;
                let htmlContent = '';

                if (orders.length > 0) {
                    orders.forEach(order => {
                        let statusTag = '';
                        let statusBar = '';
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
                                const stepClass = index <= currentStep ? 'complete' : '';
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
                                <button class="contact-btn" data-order-id="${order.order_id}" data-order-items="${order.items}">
                                    <i class="fas fa-comments"></i>
                                </button>
                            </div>
                        `;
                    });
                } else {
                    htmlContent = '<p>Nenhum pedido encontrado.</p>';
                }

                ordersContainer.innerHTML = htmlContent;

                document.querySelectorAll('.contact-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const orderId = this.getAttribute('data-order-id');
                        const orderItems = this.getAttribute('data-order-items');

                        document.getElementById('orderId').value = orderId;
                        document.getElementById('subject').value = `Pedido #${orderId}`;
                        modal.style.display = 'block';
                    });
                });
            })
            .catch(error => console.error('Erro ao atualizar pedidos:', error));
    }

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    emailForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(emailForm);

        fetch('send-email.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Email enviado com sucesso!');
                modal.style.display = 'none';
            } else {
                alert('Erro ao enviar o email: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro ao enviar o email:', error);
        });
    });

    updateOrders();
});

</script>

</body>
</html>
