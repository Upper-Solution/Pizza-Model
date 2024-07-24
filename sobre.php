<?php
// Inclui o arquivo de configuração
require_once 'config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Consulta SQL para obter as informações da empresa
try {
    $stmt = $pdo->query("SELECT * FROM Empresa LIMIT 1");
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao consultar dados da empresa: " . $e->getMessage();
}

// Fechar conexão
$pdo = null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/sobre.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Sobre Nós - <?php echo htmlspecialchars($empresa['nome']); ?></title>
</head>
<body>
    <header class="header"></header>
    <main class="main-content">
        <section class="info-section">
            <h2>Sobre Nós</h2>
            <p><?php echo htmlspecialchars($empresa['descricao']); ?></p>
        </section>
        <!-- <div class="logo-container">
            <img src="get_image.php?type=logo" alt="Logo da Empresa" class="logo-image">
        </div> -->
        <!-- <img src="get_image.php?type=banner" alt="Banner da Empresa" class="banner-image"> -->
        <section class="contact-section">
            <div class="contact-text">
                <h2>Contato</h2>
                <p><strong>Endereço:</strong> <?php echo htmlspecialchars($empresa['endereco']); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($empresa['telefone']); ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($empresa['email']); ?>"><?php echo htmlspecialchars($empresa['email']); ?></a></p>
                <p><strong>Website:</strong> <a href="<?php echo htmlspecialchars($empresa['website']); ?>" target="_blank"><?php echo htmlspecialchars($empresa['website']); ?></a></p>
            </div>
            <div class="social-media">
                <h2>Siga-nos</h2>
                <a href="https://facebook.com" target="_blank" class="social-icon"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://instagram.com" target="_blank" class="social-icon"><i class="fa-brands fa-instagram"></i></a>
                <a href="https://twitter.com" target="_blank" class="social-icon"><i class="fa-brands fa-twitter"></i></a>
                <a href="https://linkedin.com" target="_blank" class="social-icon"><i class="fa-brands fa-linkedin-in"></i></a>
            </div>
        </section>
    </main>
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($empresa['nome']); ?>. Todos os direitos reservados.</p>
    </footer>
    <script src="js/nav.js"></script>
</body>
</html>
