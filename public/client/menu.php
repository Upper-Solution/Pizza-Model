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
    <div class="loader-content" id="loader">
        <div class="loader-circle"></div>
    </div>

    <header class="header" id="header">
        <?php include '../../includes/nav.php'; ?>
    </header>

    <div class="container">
        <div class="container-area">
            <div class="models">
                <!-- Pizza Item Template -->
                <div class="pizza-item" id="pizza-item-template">
                    <a href="#">
                        <div class="pizza-item--img"><img src="" alt="Pizza Image"/></div>
                        <div class="pizza-item--add">+</div>
                        <div class="pizza-item--price">R$ --</div>
                        <div class="pizza-item--name">--</div>
                        <div class="pizza-item--desc">--</div>
                    </a>
                </div>
                <!-- Cart Item Template -->
                <div class="cart--item" id="cart-item-template">
                    <img src="" alt="Cart Item Image" />
                    <div class="cart--item-nome">--</div>
                    <div class="cart--item--qtarea">
                        <button class="cart--item-qtmenos">-</button>
                        <div class="cart--item--qt">1</div>
                        <button class="cart--item-qtmais">+</button>
                    </div>
                    <div class="cart--obs">
                        <p></p>
                    </div>
                </div>
            </div>

            <main>
                <h1 class="titulo--h1">Cardápio</h1>
                <div class="pizza-area" id="pizza-area"></div>
            </main>

            <aside>
                <div class="cart--area" id="cart-area">
                    <div class="menu-closer">
                        <i class="fa-solid fa-arrow-left" id="menu-closer-icon"></i>
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
                    <div class="cart" id="cart"></div>
                    <div class="cart--details">
                        <div class="cart--totalitem pizzasValor">
                            <span>Pizzas</span>
                            <span id="pizzas-total">R$ --</span>
                        </div>
                        <div class="cart--totalitem entrega">
                            <span>Taxa de entrega</span>
                            <span id="delivery-fee">R$ --</span>
                        </div>
                        <div class="cart--totalitem subtotal">
                            <span>Subtotal</span>
                            <span id="subtotal">R$ --</span>
                        </div>
                        <div class="cart--totalitem desconto">
                            <span>Desconto (-10%)</span>
                            <span id="discount">R$ --</span>
                        </div>
                        <div class="cart--totalitem total big">
                            <span>Total</span>
                            <span id="total">R$ --</span>
                        </div>
                        
                        <div class="observacoesGerais">
                            <label for="observacoesGerais">Observações Gerais:</label>
                            <textarea id="observacoesGerais" rows="4" placeholder="Escreva suas observações aqui..."></textarea>
                        </div>

                        <div class="formaPagamento">
                            <label for="formaPagamento">Forma de Pagamento:</label>
                            <select id="formaPagamento" name="formaPagamento">
                                <option value="cartao">Cartão</option>
                                <option value="pix">Pix</option>
                                <option value="dinheiro">Dinheiro</option>
                            </select>

                            <!-- Input para Troco (escondido por padrão) -->
                            <div id="trocoContainer" class="hidden">
                                <label for="troco">Valor para Troco:</label>
                                <input type="text" id="troco" name="troco" placeholder="Insira o valor para troco">
                            </div>
                        </div>

                        <div id="finalizarPedidoBtn" class="cart--finalizar">Finalizar a compra</div>
                    </div>
                </div>
            </aside>

            <div class="pizzaWindowArea" id="pizza-window-area">
                <div class="pizzaWindowBody modal">
                    <div class="pizzaInfo--cancelMobileButton" id="pizza-info-cancel-mobile">
                        <i class="fa-solid fa-arrow-left"></i>
                    </div>
                    <div class="pizzaBig">
                        <img src="" alt="Pizza Image" id="pizza-image"/>
                    </div>
                    <div class="pizzaInfo">
                        <h1 id="pizza-name">--</h1>
                        <div class="pizzaInfo--desc" id="pizza-desc">--</div>
                        
                        <div class="pizzaInfo--pricearea">
                            <div class="pizzaInfo--sector">Preço</div>
                            <div class="pizzaInfo--price">
                                <div class="pizzaInfo--actualPrice" id="pizza-price">R$ --</div>
                                <div class="pizzaInfo--qtarea">
                                    <button class="pizzaInfo--qtmenos" id="pizza-qtmenos">-</button>
                                    <div class="pizzaInfo--qt" id="pizza-qt">1</div>
                                    <button class="pizzaInfo--qtmais" id="pizza-qtmais">+</button>
                                </div>
                            </div>
                        </div>
                        <div class="pizzaObservations">
                            <textarea id="pizza-observations" rows="4" placeholder="Escreva suas observações aqui..."></textarea>
                        </div>
                        <div class="pizzaInfo--addButton" id="add-to-cart">Adicionar ao carrinho</div>
                        <div class="pizzaInfo--cancelButton" id="cancel-button">Cancelar</div>
                    </div>
                </div>
            </div>

            <div class="success pizzaWindowArea" id="success-window">
                <div class="pizzaWindowBody success">
                    <div class="pedido-finalizado">
                        <i class="fa-solid fa-circle-check success-img"></i>
                        <h1>Seu pedido foi finalizado!</h1>
                    </div>
                    <span>Previsão de entrega: 45 minutos!</span>
                </div>
            </div>
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
            
            finalizarPedidoBtn.addEventListener('click', () => {
                window.location.href = "<?php echo $loggedIn ? 'finalizar-pedido.php' : 'login.php'; ?>";
            });

            loginButton.addEventListener('click', () => {
                window.location.href = "<?php echo $loggedIn ? 'logout.php' : 'login.php'; ?>";
            });
        });
    </script>
</body>
</html>
