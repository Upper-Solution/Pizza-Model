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
                    <a href="login.php" id="loginLink">
                        <li id="loginItem">
                            <div class="login-icon" id="loginIcon">
                                <!-- O conteúdo será atualizado dinamicamente -->
                            </div>
                            <div class="profile-menu" id="profileMenu">
                                <a href="profile.php">Ver Perfil</a>
                                <a href="logout.php">Sair</a>
                            </div>
                        </li>
                    </a>
                </ul>
            </div>
        </nav>
    </div>`;

    // Substitua isso por um valor real em produção
    let loggedIn = false; // Ou true se o usuário estiver logado
    // Se você estiver usando PHP para definir 'loggedIn', certifique-se de que ele é gerado corretamente no HTML:
    // let loggedIn = document.querySelector('meta[name="loggedIn"]').getAttribute('content') === 'true';

    let loginIcon = document.getElementById('loginIcon');
    let profileMenu = document.getElementById('profileMenu');
    let loginLink = document.getElementById('loginLink');

    // Atualiza o cabeçalho com base no estado de login
    if (loggedIn) {
        // Substitui o ícone de login pela imagem de perfil
        loginIcon.innerHTML = `<img src="path_to_profile_image.jpg" alt="Profile Image">`;
        loginIcon.style.cursor = 'pointer'; // Adiciona cursor pointer para a imagem
    } else {
        loginIcon.innerHTML = `<i class="fa-solid fa-user"></i>`;
    }

    // Adiciona a funcionalidade para mostrar o menu suspenso para perfil
    loginIcon.addEventListener('click', function () {
        profileMenu.classList.toggle('profile-menu-open');
    });

    // Adiciona a funcionalidade para ocultar o menu quando clicado fora
    document.addEventListener('click', function (event) {
        if (!loginIcon.contains(event.target) && !profileMenu.contains(event.target)) {
            profileMenu.classList.remove('profile-menu-open');
        }
    });

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
});
