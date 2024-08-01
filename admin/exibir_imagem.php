<?php
// Inclui o arquivo de configuração
require_once '../config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Verifica se o ID da pizza foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("SELECT imagem FROM Pizzas WHERE id = ?");
        $stmt->execute([$id]);
        $pizza = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pizza && $pizza['imagem']) {
            header("Content-Type: image/jpeg"); // Ajustar para o tipo de imagem adequado se necessário
            echo $pizza['imagem'];
        } else {
            // Imagem não encontrada, mostrar imagem padrão ou erro
            header("Content-Type: image/jpeg");
            readfile('path/to/default-image.jpg');
        }
    } catch (PDOException $e) {
        echo "Erro ao recuperar a imagem: " . $e->getMessage();
    }
} else {
    echo "ID não fornecido.";
}

// Fechar conexão
$pdo = null;
?>
