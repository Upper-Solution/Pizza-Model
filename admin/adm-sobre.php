<?php
// Inclui o arquivo de configuração
require_once '../config.php';

// Obtém a conexão com o banco de dados
$pdo = connectToDatabase($hosts, $port, $dbname, $username, $password);

// Verifica se a conexão foi bem-sucedida
if (!$pdo) {
    die("Não foi possível conectar ao banco de dados.");
}

// Variáveis para armazenar mensagens
$msg = '';
$error = '';

// Define o tamanho máximo permitido para as imagens (em bytes)
$maxFileSize = 16 * 1024 * 1024; // 16MB

// Define os tipos MIME permitidos
$allowedMimeTypes = ['image/jpeg', 'image/png'];

function isValidImage($file, $allowedMimeTypes, $maxFileSize) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($file['size'] > $maxFileSize) {
        return false;
    }

    $fileMimeType = mime_content_type($file['tmp_name']);
    if (!in_array($fileMimeType, $allowedMimeTypes)) {
        return false;
    }

    return true;
}

// Se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Coleta dados do formulário
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $website = $_POST['website'];

    // Coleta e armazena as imagens em variáveis binárias, se fornecidas
    $imagem_logo = null;
    $imagem_banner = null;
    
    if (isset($_FILES['imagem_logo'])) {
        if (isValidImage($_FILES['imagem_logo'], $allowedMimeTypes, $maxFileSize)) {
            $imagem_logo = file_get_contents($_FILES['imagem_logo']['tmp_name']);
        } else {
            $error = 'A imagem do logo é inválida ou muito grande. O tamanho máximo permitido é de 16MB e o tipo permitido é JPEG ou PNG.';
        }
    }
    
    if (isset($_FILES['imagem_banner'])) {
        if (isValidImage($_FILES['imagem_banner'], $allowedMimeTypes, $maxFileSize)) {
            $imagem_banner = file_get_contents($_FILES['imagem_banner']['tmp_name']);
        } else {
            $error = 'A imagem do banner é inválida ou muito grande. O tamanho máximo permitido é de 16MB e o tipo permitido é JPEG ou PNG.';
        }
    }

    // Atualiza os dados no banco de dados se não houver erro
    if (!$error) {
        try {
            $sql = "UPDATE Empresa SET nome = :nome, descricao = :descricao, endereco = :endereco, telefone = :telefone, email = :email, website = :website, imagem_logo = :imagem_logo, imagem_banner = :imagem_banner WHERE id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':endereco' => $endereco,
                ':telefone' => $telefone,
                ':email' => $email,
                ':website' => $website,
                ':imagem_logo' => $imagem_logo,
                ':imagem_banner' => $imagem_banner,
            ]);
            $msg = 'Informações atualizadas com sucesso!';
        } catch (PDOException $e) {
            $error = "Erro ao atualizar dados da empresa: " . $e->getMessage();
        }
    }
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
    <link rel="stylesheet" href="../css/adm-sobre.css">
    <link href="https://fonts.googleapis.com/css?family=Hepta+Slab:400,700|Lato:400,700&display=swap" rel="stylesheet">
    <title>Editar Informações - <?php echo htmlspecialchars($empresa['nome']); ?></title>
    <style>
        .button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 10px 0;
            cursor: pointer;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .preview-image {
            width: 200px; /* Define a largura fixa */
            height: 150px; /* Define a altura fixa */
            object-fit: cover; /* Garante que a imagem se ajuste ao tamanho sem distorção */
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 10px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Editar Informações da Empresa</h1>
        <?php if ($msg): ?>
            <p class="message success"><?php echo htmlspecialchars($msg); ?></p>
        <?php elseif ($error): ?>
            <p class="message error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="adm-sobre.php" method="post" enctype="multipart/form-data">
            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($empresa['nome']); ?>" required>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($empresa['descricao']); ?></textarea>

            <label for="endereco">Endereço:</label>
            <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($empresa['endereco']); ?>" required>

            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($empresa['telefone']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($empresa['email']); ?>" required>

            <label for="website">Website:</label>
            <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($empresa['website']); ?>" required>

            <label for="imagem_logo">Imagem Logo:</label>
            <input type="file" id="imagem_logo" name="imagem_logo" accept="image/png, image/jpeg">
            <?php if ($empresa['imagem_logo']): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($empresa['imagem_logo']); ?>" alt="Logo Atual" class="preview-image">
            <?php endif; ?>

            <label for="imagem_banner">Imagem Banner:</label>
            <input type="file" id="imagem_banner" name="imagem_banner" accept="image/png, image/jpeg">
            <?php if ($empresa['imagem_banner']): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($empresa['imagem_banner']); ?>" alt="Banner Atual" class="preview-image">
            <?php endif; ?>

            <button type="submit">Atualizar</button>
        </form>
        <a href="adm-painel.php" class="button">Voltar para o Painel</a>
    </div>
</body>
</html>
