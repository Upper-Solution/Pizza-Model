<?php
// Iniciar a sessão na página menu.php
session_start();

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

// Conectar ao banco de dados
$conn = new mysqli('localhost', 'root', '', 'delivery_app');

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$user = null;
$email = null;

if ($loggedIn) {
    // Recuperar informações do usuário logado
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT id, email FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Usuário encontrado, mostrar informações ou realizar outras operações
        $user = $result->fetch_assoc();
        $email = $user['email'];
    } else {
        // Não deveria acontecer se a sessão estiver corretamente configurada
        echo "Erro ao recuperar informações do usuário.";
    }

    // Fechar statement
    $stmt->close();
}

// Fechar conexão
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Pizzaria</title>
</head>

<body>
    <div class="loader-content">
        <div class="loader-circle"></div>
    </div>

    <header class="header"></header>

    <div class="container">
        <div class="container-area">
            <div class="models">
                <div class="pizza-item">
                    <a href="">
                        <div class="pizza-item--img"><img src="" /></div>
                        <div class="pizza-item--add">+</div>
                        <div class="pizza-item--price">R$ --</div>
                        <div class="pizza-item--name">--</div>
                        <div class="pizza-item--desc">--</div>
                    </a>
                </div>
                <div class="cart--item">
                    <img src="" />
                    <div class="cart--item-nome">--</div>
                    <div class="cart--item--qtarea">
                        <button class="cart--item-qtmenos">-</button>
                        <div class="cart--item--qt">1</div>
                        <button class="cart--item-qtmais">+</button>
                    </div>
                </div>
            </div>
            <main>
                <h1 class="titulo--h1">Pizzas</h1>
                <div class="pizza-area"></div>
            </main>
            
            <aside>
                <div class="cart--area">
                    <div class="menu-closer">
                        <i class="fa-solid fa-arrow-left"></i>
                        <?php
                        if ($loggedIn) {
                            echo '<div class="avatar-container">';
                            echo '<img src="path_to_avatar_image.jpg" alt="Avatar do Usuário">';
                            echo '</div>';
                        } else {
                            echo '<div class="avatar-container avatar-empty"></div>';
                        }
                        ?>
                        <button id="loginButton" class="login-button"><?php echo $loggedIn ? 'Logout' : 'Login'; ?></button>
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
                        <img src="" />
                    </div>
                    <div class="pizzaInfo">
                        <h1>--</h1>
                        <div class="pizzaInfo--desc">--</div>
                        <div class="pizzaInfo--sizearea">
                            <div class="pizzaInfo--sector">Tamanho</div>
                            <div class="pizzaInfo--sizes">
                                <div data-key="0" class="pizzaInfo--size">
                                    PEQUENA <span>--</span>
                                </div>
                                <div data-key="1" class="pizzaInfo--size">
                                    MÉDIO <span>--</span>
                                </div>
                                <div data-key="2" class="pizzaInfo--size selected">
                                    GRANDE <span>--</span>
                                </div>
                            </div>
                        </div>
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

    <footer>
        <a href="" target="_blank">© Developed by UpperResolution</a>
    </footer>

    <script src="js/nav.js"></script>
    <script src="js/geral.js"></script>
    <script src="js/cart.js"></script>
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
                    <?php } else { ?>
                        // Implementar a lógica para finalizar o pedido
                    <?php } ?>
                });
            }

            if (loginButton) {
                loginButton.addEventListener('click', function () {
                    console.log('Login button clicked');
                    <?php if ($loggedIn) { ?>
                        // Implementar a lógica para logout
                    <?php } else { ?>
                        window.location.href = 'login.php';
                    <?php } ?>
                });
            }
        });
    </script>
</body>
</html>
