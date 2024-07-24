<?php
// Inclui o arquivo de configuração
require_once 'config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Verifica se o parâmetro 'type' e 'id' estão definidos
if (isset($_GET['type'])) {
    $type = $_GET['type'];

    // Define o campo da imagem de acordo com o tipo
    if ($type === 'logo') {
        $column = 'imagem_logo';
    } elseif ($type === 'banner') {
        $column = 'imagem_banner';
    } else {
        die("Tipo de imagem inválido.");
    }

    // Consulta SQL para obter a imagem
    try {
        $stmt = $pdo->query("SELECT $column FROM Empresa LIMIT 1");
        $imagem = $stmt->fetchColumn();

        // Verifica se a imagem foi encontrada
        if ($imagem !== false) {
            header("Content-Type: image/jpeg");
            echo $imagem;
        } else {
            die("Imagem não encontrada.");
        }
    } catch (PDOException $e) {
        die("Erro ao consultar imagem: " . $e->getMessage());
    }
} else {
    die("Tipo de imagem não especificado.");
}

// Fechar conexão
$pdo = null;
?>
