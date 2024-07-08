document.addEventListener("DOMContentLoaded", function () {
    const finalizarPedidoBtn = document.getElementById('finalizarPedidoBtn');
    const loginButton = document.getElementById('loginButton');

    finalizarPedidoBtn.addEventListener('click', function (event) {
        event.preventDefault();

        fetch('check_login.php')
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    // Usuário está logado, finalizar pedido
                    fetch('finalizar_pedido.php', { method: 'POST' })
                        .then(response => response.text())
                        .then(data => {
                            alert(data);
                            // Redirecionar ou atualizar a página após finalizar o pedido
                            window.location.href = 'success_page.html';
                        })
                        .catch(error => {
                            console.error('Erro ao finalizar o pedido:', error);
                            alert('Ocorreu um erro ao finalizar o pedido. Por favor, tente novamente.');
                        });
                } else {
                    // Usuário não está logado, redirecionar para a página de login
                    window.location.href = 'login.php';
                }
            })
            .catch(error => {
                console.error('Erro ao verificar login:', error);
                alert('Ocorreu um erro ao verificar o login. Por favor, tente novamente.');
            });
    });

    loginButton.addEventListener('click', function () {
        window.location.href = 'login.php';
    });
});
