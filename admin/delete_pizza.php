<?php
// Inclui o arquivo de configuração
require_once '../config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Verifica se o ID da pizza foi fornecido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Prepara e executa a consulta de exclusão
        $stmt = $pdo->prepare("DELETE FROM Pizzas WHERE id = ?");
        $stmt->execute([$id]);
        
        // Redireciona de volta para a página principal
        header('Location: adm-item.php');
        exit;
    } catch (PDOException $e) {
        echo "Erro ao excluir pizza: " . $e->getMessage();
    }
} else {
    echo "ID da pizza não fornecido.";
}

// Fechar conexão
$pdo = null;
?>
