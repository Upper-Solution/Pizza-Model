document.addEventListener('DOMContentLoaded', () => {
    const statusToPercentage = {
        'Recebido': '25%',
        'Em Preparação': '50%',
        'A Caminho': '75%',
        'Entregue': '100%'
    };

    const progressBars = document.querySelectorAll('.progress-bar');

    progressBars.forEach(bar => {
        const status = bar.getAttribute('data-status');
        bar.style.width = statusToPercentage[status] || '0%';
    });
});
