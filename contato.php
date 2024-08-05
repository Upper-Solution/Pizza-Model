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
    <!-- Link para o arquivo de CSS (assumindo que criará um arquivo separado) -->
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

    <nav id="nav-container" ></nav>

    <!-- Cabeçalho da página -->
    <header>
        <h1>Contato</h1>
        <p>Entre em contato com a nossa hamburgueria!</p>
    </header>

    <!-- Seção de contato -->
    <section id="contact">
        <h2>Fale Conosco</h2>
        
        <!-- Formulário de contato -->
        <form action="process_contact.php" method="post">
            <!-- Campo para o nome do usuário -->
            <label for="name">Nome:</label>
            <input type="text" id="name" name="name" required>

            <!-- Campo para o email do usuário -->
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <!-- Campo para o telefone do usuário -->
            <label for="phone">Telefone:</label>
            <input type="tel" id="phone" name="phone" required>

            <!-- Campo para a mensagem do usuário -->
            <label for="message">Mensagem:</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <!-- Botão para enviar o formulário -->
            <button type="submit">Enviar</button>
        </form>
    </section>

    <!-- Rodapé da página -->
    <footer>
        <p>&copy; 2024 Hamburgueria. Todos os direitos reservados.</p>
    </footer>


</body>
</html>
