document.addEventListener("DOMContentLoaded", function() {
    const menuCheckbox = document.querySelector("#checkbox");
    const menuLateral = document.querySelector(".menu-lateral");
    const overlay = document.querySelector(".menu-overlay");
    const closeMenuBtn = document.querySelector(".menu-close");
    const menuHamburger = document.querySelector(".menu_hamburger");

    function openMenu() {
        menuLateral.classList.add("open");
        overlay.classList.add("open");
        menuHamburger.classList.add("active"); // Adiciona a classe 'active'
    }

    function closeMenu() {
        menuLateral.classList.remove("open");
        overlay.classList.remove("open");
        menuHamburger.classList.remove("active"); // Remove a classe 'active'
        menuCheckbox.checked = false; // Reseta o checkbox
    }

    menuCheckbox.addEventListener("change", function() {
        if (this.checked) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    overlay.addEventListener("click", closeMenu);

    closeMenuBtn.addEventListener("click", closeMenu);
});
