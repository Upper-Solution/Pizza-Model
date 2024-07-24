document.addEventListener("DOMContentLoaded", function () {
    // Atualiza o cabeçalho
    let header = document.querySelector(".header");
    header.innerHTML = `<div class="menu-area">
        <div class="logo">
            <a href="index.html">
                <img src="images/logo_pizza.png" alt="logo_pizza.png">
            </a>
        </div>
        <nav>
            <div class="container-menu-mobile">
                <div class="menuMobile-area">
                    <div class="menu-openner"><span>0</span>
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                </div>
                <label for="checkbox" class="menu_hamburger">
                    <input type="checkbox" id="checkbox">
                    <span class="line line-main"></span>
                    <span class="line line-split"></span>
                </label>
            </div>
            <div class="menu">
                <ul>
                    <a href="index.html">
                        <li>Início</li>
                    </a>
                    <a href="menu.php">
                        <li>Pizzas</li>
                    </a>
                    <a href="sobre.php">
                        <li>Sobre</li>
                    </a>
                    <a href="">
                        <li>Contato</li>
                    </a>
                    <li id="loginItem">
                        <div class="login-icon" id="loginIcon">
                            <!-- O conteúdo será atualizado dinamicamente -->
                        </div>
                        <div class="profile-menu" id="profileMenu">
                            <a href="profile.php" class="profile-menu-item">Ver Perfil</a>
                            <a href="logout.php" class="profile-menu-item">Sair</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>`;

    // Requisição AJAX para verificar o estado de login
    fetch('check_login.php')
        .then(response => response.json())
        .then(data => {
            let loggedIn = data.loggedIn;

            let loginIcon = document.getElementById('loginIcon');
            let profileMenu = document.getElementById('profileMenu');
            let loginItem = document.getElementById('loginItem');

            if (loggedIn) {
                // Substitui o ícone de login pela imagem de perfil
                loginIcon.innerHTML = `<img src="path_to_profile_image.jpg" alt="Profile Image">`;
                loginIcon.style.cursor = 'pointer'; // Adiciona cursor pointer para a imagem

                // Mostra o menu de perfil e logout ao clicar na foto de perfil
                loginIcon.addEventListener('click', function () {
                    profileMenu.classList.toggle('profile-menu-open');
                });

                // Oculta o menu quando clicar fora
                document.addEventListener('click', function (event) {
                    if (!loginIcon.contains(event.target) && !profileMenu.contains(event.target)) {
                        profileMenu.classList.remove('profile-menu-open');
                    }
                });
            } else {
                loginIcon.innerHTML = `<i class="fa-solid fa-user"></i>`;
                loginIcon.style.cursor = 'pointer'; // Adiciona cursor pointer para o ícone

                // Redireciona para a página de login ao clicar no ícone
                loginIcon.addEventListener('click', function () {
                    window.location.href = 'login.php';
                });

                // Oculta o menu de perfil e logout se não estiver logado
                profileMenu.style.display = 'none';
            }

            // Atualiza a classe ativa do menu
            let activePage = window.location.pathname;
            document.querySelectorAll("nav .menu a").forEach((link) => {
                if (link.href.includes(`${activePage}`)) {
                    link.classList.add("active");
                }
            });

            // Adiciona funcionalidade para o menu hambúrguer
            let toggleMenu = document.querySelector("#checkbox");
            let openMenu = document.querySelector(".menu");

            toggleMenu.addEventListener("click", () => {
                openMenu.classList.toggle("menu-opened");
            });
        })
        .catch(error => console.error('Erro ao verificar o login:', error));
});
