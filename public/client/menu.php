<?php
session_start();

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

// Inclui o arquivo de configuração para conexão com o banco de dados
require_once '../../config/config.php';

// Obtém a conexão com o banco de dados
try {
    $pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    exit();
}

// Inicialização das variáveis de usuário
$user = null;
$email = null;

if ($loggedIn) {
    // Recuperar informações do usuário logado
    $userId = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare('SELECT id, email FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $email = $user['email'];
        }
    } catch (PDOException $e) {
        echo "Erro ao consultar usuário: " . $e->getMessage();
    }
}

// Fechar conexão com o banco de dados
$pdo = null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="../imagens/favicon.ico" type="image/x-icon">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Cardápio</title>
</head>
<body>
    <div class="loader-content">
        <div class="loader-circle"></div>
    </div>

    <header class="header">
        <?php include '../../includes/nav.php'; ?>
    </header>

    <div class="container">
        <div class="container-area">
            <div class="models">
                <div class="pizza-item">
                    <a href="">
                        <div class="pizza-item--img"><img/></div>
                        <div class="pizza-item--add">+</div>
                        <div class="pizza-item--price">R$ --</div>
                        <div class="pizza-item--name">--</div>
                        <div class="pizza-item--desc">--</div>
                    </a>
                </div>
                <div class="cart--item">
                    <img src="" alt="Cart Item Image" />
                    <div class="cart--item-nome">--</div>
                    <div class="cart--item--qtarea">
                        <button class="cart--item-qtmenos">-</button>
                        <div class="cart--item--qt">1</div>
                        <button class="cart--item-qtmais">+</button>
                    </div>
                </div>
            </div>
            <main>
                <h1 class="titulo--h1">Cardápio</h1>
                <div class="pizza-area"></div>
            </main>

            <aside>
                <div class="cart--area">
                    <div class="menu-closer">
                        <i class="fa-solid fa-arrow-left"></i>
                        <?php if ($loggedIn) { ?>
                            <div class="avatar-container">
                                <img src="path_to_avatar_image.jpg" alt="Avatar do Usuário">
                            </div>
                        <?php } else { ?>
                            <div class="avatar-container avatar-empty"></div>
                        <?php } ?>
                        <button id="loginButton" class="login-button">
                            <?php echo $loggedIn ? 'Logout' : 'Login'; ?>
                        </button>
                    </div>
                    <h1>Suas Pizzas</h1>
                    <div class="cart"></div>
                    <div class="cart--details">
                        <div class="cart--totalitem pizzasValor">
                            <span>Pizzas</span>
                            <span>R$ --</span>
                        </div>
                        <div class="cart--totalitem entrega">
                            <span>Taxa de entrega</span>
                            <span>R$ --</span>
                        </div>
                        <div class="cart--totalitem subtotal">
                            <span>Subtotal</span>
                            <span>R$ --</span>
                        </div>
                        <div class="cart--totalitem desconto">
                            <span>Desconto (-10%)</span>
                            <span>R$ --</span>
                        </div>
                        <div class="cart--totalitem total big">
                            <span>Total</span>
                            <span>R$ --</span>
                        </div>
                        <div id="finalizarPedidoBtn" class="cart--finalizar">Finalizar a compra</div>
                    </div>
                </div>
            </aside>
            <div class="pizzaWindowArea">
                <div class="pizzaWindowBody modal">
                    <div class="pizzaInfo--cancelMobileButton">
                        <i class="fa-solid fa-arrow-left"></i>
                    </div>
                    <div class="pizzaBig">
                        <img src="" alt="Pizza Image" />
                    </div>
                    <div class="pizzaInfo">
                        <h1>--</h1>
                        <div class="pizzaInfo--desc">--</div>
                        
                        <div class="pizzaInfo--pricearea">
                            <div class="pizzaInfo--sector">Preço</div>
                            <div class="pizzaInfo--price">
                                <div class="pizzaInfo--actualPrice">R$ --</div>
                                <div class="pizzaInfo--qtarea">
                                    <button class="pizzaInfo--qtmenos">-</button>
                                    <div class="pizzaInfo--qt">1</div>
                                    <button class="pizzaInfo--qtmais">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="pizzaInfo--addButton">Adicionar ao carrinho</div>
                        <div class="pizzaInfo--cancelButton">Cancelar</div>
                    </div>
                </div>
            </div>
            <div class="success pizzaWindowArea">
                <div class="pizzaWindowBody success">
                    <div class="pedido-finalizado">
                        <i class="fa-solid fa-circle-check success-img"></i>
                        <h1>Seu pedido foi finalizado!</h1>
                    </div>
                    <span>Previsão de entrega: 30 minutos!</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de pagamento via Pix -->
    <div id="pixModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Pagamento via Pix</h2>
            <p>Valor a ser pago: <span id="pixAmount"></span></p>
            <div id="pixQRCode"></div>
            <button id="confirmPaymentBtn">Confirmar Pagamento</button>
        </div>
    </div>

    <footer>
        <a href="#" target="_blank">© Developed by UpperResolution</a>
    </footer>

    <script src="../js/geral.js"></script>
    <script src="../js/cart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            console.log('DOM fully loaded and parsed');
            const finalizarPedidoBtn = document.getElementById('finalizarPedidoBtn');
            const loginButton = document.getElementById('loginButton');

            if (finalizarPedidoBtn) {
                finalizarPedidoBtn.addEventListener('click', function (event) {
                    event.preventDefault();
                    console.log('Finalizar Pedido button clicked');
                    <?php if (!$loggedIn) { ?>
                        window.location.href = 'login.php';
                    <?php } ?>
                });
            }

            if (loginButton) {
                loginButton.addEventListener('click', function () {
                    console.log('Login button clicked');
                    <?php if ($loggedIn) { ?>
                        window.location.href = '../../config/logout.php'; 
                    <?php } else { ?>
                        window.location.href = 'login.php';
                    <?php } ?>
                });
            }
        });
    </script>
</body>
</html>
