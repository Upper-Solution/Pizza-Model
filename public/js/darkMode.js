// Verifica se o modo escuro está ativo no localStorage
const darkMode = localStorage.getItem('darkMode') === 'enabled';

// Atualiza o estado do checkbox e o modo escuro no carregamento da página
document.addEventListener('DOMContentLoaded', () => {
    const darkModeToggle = document.getElementById('dark-mode');
    if (darkMode) {
        document.body.classList.add('dark-mode');
        document.header.classList.add('dark-mode');
        darkModeToggle.checked = true;
    }
});
