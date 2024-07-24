<?php
// Configurações dos hosts
$hosts = ['127.0.0.1', '193.203.175.99'];
$port = '3306';             // Porta padrão do MySQL
$dbname = 'u778175734_PIzzaDB';
$username = 'u778175734_upper';
$password = '5pp2rr2s4l5t34N';

// Função para tentar conectar ao banco de dados
function connectToDatabase($hosts, $port, $dbname, $username, $password) {
    foreach ($hosts as $host) {
        try {
            // Adiciona a porta na string de conexão
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
            // Configura o PDO para lançar exceções
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;  // Conexão bem-sucedida, retorna o objeto PDO
        } catch (PDOException $e) {
        }
    }
    // Se todos os hosts falharem, exibe uma mensagem final
    die("Não foi possível conectar a nenhum dos hosts.");
}

// Chama a função para tentar a conexão
connectToDatabase($hosts, $port, $dbname, $username, $password);
?>
