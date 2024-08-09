<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <style>

        .area-text textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            resize: vertical; /* Permite o redimensionamento vertical */
        }

        .area-text textarea:focus {
            border-color: #e0a225; /* Cor da borda ao focar no campo */
            box-shadow: inset 0 1px 3px rgba(255, 196, 0, 0.849);
            outline: none;
        }

        .area-text textarea::placeholder {
            color: #aaa; /* Cor do texto do placeholder */
            font-style: italic;
        }


        .contact-section {
            padding: 40px 0;
        }
        .contact-section h2 {
            margin-bottom: 40px;
            font-weight: bold;
            color: #343a40;
        }
        .form-control {
            margin-bottom: 20px;
            border-radius: 0;
        }
        .contact-info {
            font-size: 1.2rem;
            color: #495057;
        }
    </style>
</head>
<body>
    <section class="contact-section">
        <div class="container">
        <header class="header">
            <?php include '../../includes/nav.php'; ?>
        </header>
            <div class="row">
                <div class="col-md-6 text-center text-md-left area-text">
                    <h2>Entre em Contato</h2>
                    <form>
                        <div class="form-group">
                            <input type="text" class="form-control" id="name" placeholder="Seu Nome">
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-control" id="email" placeholder="Seu Email">
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" id="message" rows="5" placeholder="Sua Mensagem Aqui"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <h2>Informações de Contato</h2>
                    <p class="contact-info"><strong>Telefone:</strong> (11) 1234-5678</p>
                    <p class="contact-info"><strong>Email:</strong> contato@lanches.com</p>
                    <p class="contact-info"><strong>Endereço:</strong> Rua dos testes, 123 - Florianópolis, SC</p>
                </div>
            </div>
        </div>
    </section>
    <footer>
        <a href="#" target="_blank">© Developed by UpperResolution</a>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>