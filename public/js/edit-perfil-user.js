//Puxa os dados do usuario do banco de dados via JSON
    document.addEventListener('DOMContentLoaded', function() {
        // Função para buscar dados do usuário
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
                document.getElementById('userCity').value = userData.city || '';
                document.getElementById('userZip').value = userData.cep || '';
                document.getElementById('userDistrict').value = userData.neighborhood || '';
                document.getElementById('userStreet').value = userData.address || '';
                document.getElementById('userHouseNumber').value = userData.house_number || '';
    
            } catch (error) {
                console.error('Erro ao buscar dados do usuário:', error);
            }
        }
        fetchUserData();

        // Chamar a função para carregar os dados do usuário
    });
