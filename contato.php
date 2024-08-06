<?php
session_start();

// Verificar se o usuário está logado
$loggedIn = isset($_SESSION['user_id']);

// Inclui o arquivo de configuração para conexão com o banco de dados
require_once 'config.php';

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
    <title>Contato - Hamburgueria</title>
    <!-- Link para o arquivo de CSS -->
    <link rel="stylesheet" href="css/contato.css">
    <script defer src="js/geral.js"></script>
    <script src="https://kit.fontawesome.com/8b4042ccf0.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="header">
        <?php include 'nav.php'; ?>
    </header>

    <nav id="nav-container"></nav>

    <!-- Cabeçalho da página -->
    <header>
        <h1>Contato</h1>
        <p>Entre em contato com a nossa hamburgueria!</p>
    </header>

    <!-- Seção de contato -->
    <section id="contact">
        <div class="contact-container">
            <!-- Informações da Hamburgueria -->
            <div class="hamburgueria-info">
                <h2>Informações da Hamburgueria</h2>
                <p>Siga-nos nas mídias sociais:</p>
                <p><a href="https://www.facebook.com/hamburgueria" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></p>
                <p><a href="https://www.instagram.com/hamburgueria" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></p>
                <p>Envie um email para: <a href="mailto:contato@hamburgueria.com"><i class="fas fa-envelope"></i> contato@hamburgueria.com</a></p>
            </div>
            
            <!-- Formulário de contato -->
            <div class="contact-form">
                <h2>Fale Conosco</h2>
                <form action="process_contact.php" method="post">
                    <label for="name">Nome:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="phone">Telefone:</label>
                    <input type="tel" id="phone" name="phone" required>

                    <label for="message">Mensagem:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>

                    <button type="submit">Enviar</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Rodapé da página -->
    <footer>
        <p>&copy; 2024 Hamburgueria. Todos os direitos reservados.</p>
    </footer>

</body>
</html>
