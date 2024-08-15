document.addEventListener('DOMContentLoaded', function() {
    async function fetchUserData() {
        try {
            const response = await fetch('../../includes/apiGetUser.php');
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const userData = await response.json();
            if (userData.error) {
                console.error('Erro:', userData.error);
                return;
            }

            // Preencher os campos do formulário com os dados do usuário
            document.getElementById('userName').value = userData.fullname || '';
            document.getElementById('userPhone-number').value = userData.phone_number || '';
            document.getElementById('userCity').value = userData.city || '';
            document.getElementById('userZip').value = userData.cep || '';
            document.getElementById('userDistrict').value = userData.neighborhood || '';
            document.getElementById('userStreet').value = userData.address || '';
            document.getElementById('userHouseNumber').value = userData.house_number || '';
            document.getElementById('userAddress').value = userData.complement || '';

            // Atualizar o ícone de perfil com a foto do usuário, se disponível
            const profileIcon = document.getElementById('userImage-profile');
            if (userData.profile_image) {
                profileIcon.style.backgroundImage = `url('data:image/jpeg;base64,${userData.profile_image}')`;
                profileIcon.className = ""; // Limpar a classe do ícone
            } else {
                profileIcon.className = "fas fa-user"; // Ícone padrão
                profileIcon.style.backgroundImage = ""; // Remover imagem de fundo
            }
        } catch (error) {
            console.error('Erro ao buscar dados do usuário:', error);
        }
    }

    const backButton = document.getElementById("backButton");
    if (backButton) {
        backButton.addEventListener('click', function() {
            window.location.href = '../client/tela-config.php'; // Direciona o usuário para a tela de configurações
        });
    }

    const saveButton = document.getElementById("saveButton");
    if (saveButton) {
        saveButton.addEventListener("click", saveUser);
    }

    fetchUserData();
});

// Função para converter a imagem em base64
function convertImageToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result.split(',')[1]); // Remove a parte "data:image/jpeg;base64,"
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}
// Função para salvar alterações do usuário
async function saveUser() {
    const userData = {
        fullname: document.getElementById('userName').value,
        phone_number: document.getElementById('userPhone-number').value,
        city: document.getElementById('userCity').value,
        cep: document.getElementById('userZip').value,
        neighborhood: document.getElementById('userDistrict').value,
        address: document.getElementById('userStreet').value,
        house_number: document.getElementById('userHouseNumber').value,
        complement: document.getElementById('userAddress').value,
    };

    const imageFile = document.getElementById('uploadPhoto').files[0];
    if (imageFile) {
        // Verificar o tipo da imagem
        const allowedTypes = ['image/jpeg', 'image/png'];
        if (!allowedTypes.includes(imageFile.type)) {
            alert('Formato de imagem inválido. Apenas arquivos PNG e JPEG são permitidos.');
            return;
        }

        // Verificar o tamanho da imagem
        const maxSize = 16 * 1024 * 1024; // 16 MB
        if (imageFile.size > maxSize) {
            alert('A imagem é muito grande. O tamanho máximo permitido é de 16MB.');
            return;
        }

        try {
            const base64Image = await convertImageToBase64(imageFile);
            userData.profile_image = base64Image;
        } catch (error) {
            console.error('Erro ao converter a imagem:', error);
            alert('Erro ao converter a imagem. Por favor, tente novamente.');
            return;
        }
    }

    try {
        const response = await fetch('../../includes/apiSetUser.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();
        if (result.success) {
            alert('Dados salvos com sucesso!');
        } else {
            alert('Erro ao salvar dados: ' + result.message);
        }
    } catch (error) {
        console.error('Erro ao salvar os dados do usuário:', error);
        alert('Erro ao salvar os dados. Por favor, tente novamente.');
    }
}
