<?php
session_start();

// Inclui o arquivo de configuração
require_once '../../config/config.php';

// Verificar se o admin está logado
if (!isset($_SESSION['admin_id'])) {
    header('Location: adm-login.php');
    exit;
}

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Variáveis para armazenar mensagens
$msg = '';
$error = '';

// Se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta dados do formulário
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $website = $_POST['website'];
    $bairrosEntrega = $_POST['bairrosEntrega'] ?? []; // Array de bairros e valores
    $novoBairro = $_POST['novo_bairro'] ?? ''; // Novo bairro a ser adicionado
    $valorNovoBairro = $_POST['valor_novo_bairro'] ?? ''; // Valor para o novo bairro

    // Atualiza os dados no banco de dados se não houver erro
    if (!$error) {
        try {
            // Inicia uma transação para garantir a consistência dos dados
            $pdo->beginTransaction();

            // Atualiza os dados da empresa
            $sql = "UPDATE Empresa SET nome = :nome, descricao = :descricao, endereco = :endereco, telefone = :telefone, email = :email, website = :website WHERE id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':endereco' => $endereco,
                ':telefone' => $telefone,
                ':email' => $email,
                ':website' => $website,
            ]);

            // Atualiza os bairros de entrega existentes
            $stmt = $pdo->prepare("INSERT INTO BairrosEntrega (nome_bairros, valor_entrega) VALUES (:nome_bairros, :valor_entrega) ON DUPLICATE KEY UPDATE valor_entrega = VALUES(valor_entrega)");
            foreach ($bairrosEntrega as $bairro) {
                $stmt->execute([
                    ':nome_bairros' => $bairro['nome_bairros'],
                    ':valor_entrega' => $bairro['valor_entrega']
                ]);
            }

            // Adiciona um novo bairro, se fornecido
            if (!empty($novoBairro) && !empty($valorNovoBairro)) {
                $stmt->execute([
                    ':nome_bairros' => $novoBairro,
                    ':valor_entrega' => $valorNovoBairro
                ]);
            }

            // Confirma a transação
            $pdo->commit();

            $msg = 'Informações atualizadas com sucesso!';
        } catch (PDOException $e) {
            // Reverte a transação em caso de erro
            $pdo->rollBack();
            $error = "Erro ao atualizar dados da empresa: " . $e->getMessage();
        }
    }
}

// Consulta SQL para obter as informações da empresa
try {
    $stmt = $pdo->query("SELECT * FROM Empresa LIMIT 1");
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

    // Consulta os bairros de entrega
    $stmt = $pdo->query("SELECT nome_bairros, valor_entrega FROM BairrosEntrega");
    $bairrosEntrega = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="shortcut icon" href="../imagens/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/adm-sobre.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap" rel="stylesheet">
    <title>Editar Informações - <?php echo htmlspecialchars($empresa['nome']); ?></title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header>
        <h1>Sobre a Empresa</h1>
        <a href="adm-painel.php" class="button back-button">Voltar</a>
    </header>
    <!-- Mensagens -->
    <?php if ($msg): ?>
                        <div class="success-message"><?php echo htmlspecialchars($msg); ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
    <div class="container">
        <form action="adm-sobre.php" method="post">
            <div class="form-and-message">
                <div class="form-container">
                    <!-- Dados da Empresa -->
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($empresa['nome']); ?>" required>

                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($empresa['descricao']); ?></textarea>

                    <label for="endereco">Endereço:</label>
                    <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($empresa['endereco']); ?>" required>

                    <div class="form-group">
                        <label for="telefone">Telefone:</label>
                        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($empresa['telefone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($empresa['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="website">Website:</label>
                        <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($empresa['website']); ?>">
                    </div>

                    <!-- Adicionar Novo Bairro -->
                    <div class="form-group inline-fields">
                        <div class="field-group">
                            <label for="novo_bairro">Adicionar Novo Bairro:</label>
                            <input type="text" id="novo_bairro" placeholder="Digite o nome do novo bairro">
                        </div>
                        <div class="field-group">
                            <label for="valor_novo_bairro">Valor de Entrega:</label>
                            <input type="number" id="valor_novo_bairro" step="0.01" placeholder="Digite o valor de entrega">
                        </div>
                        <button type="button" id="add_bairro_button" class="button save-button">Adicionar Bairro</button>
                    </div>

                    <!-- Exibir Bairros como Tags -->
                    <div class="form-group tag-container" id="bairros_container">
                        <?php foreach ($bairrosEntrega as $bairro): ?>
                            <div class="tag">
                                <span class="tag-name"><?php echo htmlspecialchars($bairro['nome_bairros']); ?></span>
                                <span class="tag-value">R$ <?php echo htmlspecialchars(number_format($bairro['valor_entrega'], 2, ',', '.')); ?></span>
                                <span class="remove-tag" onclick="removeTag(this)">x</span>
                            </div>
                        <?php endforeach; ?>
                    </div>           

            <!-- Botões -->
            <div class="form-group button-group">
                <button type="submit" class="button save-button">Salvar</button>
            </div>
        </form>

    <script>
        $(document).ready(function() {
    $('#add_bairro_button').on('click', function() {
        var nomeBairro = $('#novo_bairro').val();
        var valorBairro = $('#valor_novo_bairro').val();

        if (nomeBairro && valorBairro) {
            $.ajax({
                type: 'POST',
                url: 'ajax-add-bairro.php',
                data: {
                    nome_bairro: nomeBairro,
                    valor_bairro: valorBairro
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        var newTag = '<div class="tag">' +
                                     '<span class="tag-name">' + nomeBairro + '</span>' +
                                     '<span class="tag-value">R$ ' + parseFloat(valorBairro).toFixed(2).replace('.', ',') + '</span>' +
                                     '<span class="remove-tag" onclick="removeTag(this)">x</span>' +
                                     '</div>';
                        $('#bairros_container').append(newTag);
                        $('#novo_bairro').val('');
                        $('#valor_novo_bairro').val('');
                    } else {
                        alert('Erro ao adicionar bairro.');
                    }
                }
            });
        } else {
            alert('Preencha o nome e valor do bairro.');
        }
    });
});

function removeTag(element) {
    $(element).parent('.tag').remove();
}
    </script>

</body>
</html>
