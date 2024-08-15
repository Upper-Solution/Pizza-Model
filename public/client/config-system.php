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
    <title>Configurações do Sistema</title>
    <link rel="stylesheet" href="../css/config.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/darkMode.css">
</head>
<body>
    <header class="header">
        <?php include '../../includes/nav.php'; ?>
    </header>
    <div class="cabeçalho-config-user">
            <button id="backButton">Voltar</button>
        </div>
    <div class="container-system">
        <h1>Configurações do Sistema</h1>
        <main>
            <section class="config-section">
                <h2>Gerenciamento de Tema</h2>
                <label>
                    <input type="checkbox" id="dark-mode">
                    Modo Escuro
                </label>
            </section>
            <section class="config-section">
                <h2>Notificações</h2>
                <label>
                    <input type="checkbox" id="email-notifications">
                    Receber notificações por email
                </label>
            </section>
        </main>
        <div class="botao-salvar">
            <button   id="save-button">Salvar Configurações</button>
        </div>
    </div>
    <script>
        // Verifica se o modo escuro está ativo no localStorage
        const darkMode = localStorage.getItem('darkMode') === 'enabled';

        // Atualiza o estado do checkbox e o modo escuro no carregamento da página
        document.addEventListener('DOMContentLoaded', () => {
            const darkModeToggle = document.getElementById('dark-mode');
            if (darkMode) {
                document.body.classList.add('dark-mode');
                darkModeToggle.checked = true;
            }

            // Alterna o modo escuro quando o usuário altera o checkbox
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
        });
    </script>
</body>
</html>
