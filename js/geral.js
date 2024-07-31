// Inicializa o carrinho, quantidade do modal e chave do modal
let cart = [];
let modalQt = 1;
let modalKey = 0;
let pizzas;

// Verifica se há um carrinho salvo no 'localStorage'
// Se existir, carrega o carrinho salvo; caso contrário, inicializa um carrinho vazio
localStorage.getItem("pizza_cart")
  ? (cart = JSON.parse(localStorage.getItem("pizza_cart")))
  : (cart = []);

// Faz uma requisição à API para obter os dados das pizzas em formato JSON
// Atualiza o carrinho e lista as pizzas na interface
const api = fetch("js/apiData.php")
  .then(response => response.json())
  .then(data => {
    pizzas = data; // Armazena os dados das pizzas na variável 'pizzas'
    updateCart();  // Atualiza o carrinho

    // Mapeia todos os objetos do JSON (dados das pizzas)
    data.map((item, index) => {
      // Clona o modelo de item de pizza
      let pizzaItem = document.querySelector(".models .pizza-item").cloneNode(true);
      pizzaItem.setAttribute("data-key", index); // Define um atributo data-key com o índice da pizza

      // Define os atributos do item de pizza com os dados do JSON
      pizzaItem.querySelector(".pizza-item--img img").src = `data:image/jpeg;base64,${item.imagem}`;
      pizzaItem.querySelector(".pizza-item--price").innerHTML = `${item.preco.toLocaleString("pt-br", {
        style: "currency",
        currency: "BRL",
      })}`;
      pizzaItem.querySelector(".pizza-item--name").innerHTML = item.nome;
      pizzaItem.querySelector(".pizza-item--desc").innerHTML = item.descricao;

      // Adiciona um evento de clique para abrir o modal com as informações da pizza selecionada
      pizzaItem.querySelector("a").addEventListener("click", (e) => {
        e.preventDefault(); // Previne a ação padrão do link
        let key = e.target.closest(".pizza-item").getAttribute("data-key"); // Obtém o data-key do item clicado
        modalQt = 1; // Reseta a quantidade de pizzas no modal
        modalKey = key; // Define a chave da pizza no modal

        // Define as informações da pizza no modal
        document.querySelector(".pizzaBig img").src = `data:image/jpeg;base64,${pizzas[key].imagem}`;
        document.querySelector(".pizzaInfo h1").innerHTML = pizzas[key].nome;
        document.querySelector(".pizzaInfo--desc").innerHTML = pizzas[key].descricao;
        document.querySelector(".pizzaInfo--actualPrice").innerHTML = `${pizzas[key].preco.toLocaleString("pt-br", {
          style: "currency",
          currency: "BRL",
        })}`;
        //Area comentada devido a conteudo não utilizado ainda
        /*
        document.querySelector(".pizzaInfo--size.selected").classList.remove("selected"); // Reseta o tamanho selecionado
        document.querySelectorAll(".pizzaInfo--size").forEach((size, sizeIndex) => {
          // Define os tamanhos da pizza no modal
          if (sizeIndex == 2) {
            size.classList.add("selected"); // Seleciona o tamanho padrão (grande)
          }
          size.querySelector("span").innerHTML = pizzas[key].sizes[sizeIndex]; // Define o tamanho

          // Adiciona um evento de clique para alterar o tamanho da pizza
          size.addEventListener("click", () => {
            document.querySelector(".pizzaInfo--size.selected").classList.remove("selected");
            size.classList.add("selected");
            modalQt = 1;
            document.querySelector(".pizzaInfo--qt").innerHTML = modalQt;
            document.querySelector(".pizzaInfo--actualPrice").innerHTML = ` ${pizzas[key].price[sizeIndex].toLocaleString(
              "pt-br",
              { style: "currency", currency: "BRL" }
            )}`;
          });
        });
*/
        document.querySelector(".pizzaInfo--qt").innerHTML = modalQt;

        // Exibe o modal com animação de opacidade
        document.querySelector(".pizzaWindowArea").style.opacity = 0;
        document.querySelector(".pizzaWindowArea").style.display = "flex";
        setTimeout(() => {
          document.querySelector(".pizzaWindowArea").style.opacity = 1;
        }, 200);
      });

      // Adiciona o item de pizza clonado à área de pizzas
      document.querySelector(".pizza-area").append(pizzaItem);
    });
  });

// Função para fechar o modal
function closeModal() {
  document.querySelector(".pizzaWindowArea").style.opacity = 0;
  setTimeout(() => {
    document.querySelector(".pizzaWindowArea").style.display = "none";
  }, 600);
  window.scrollTo(0, 0); // Rola a página para o topo
}

// Fecha o modal ao pressionar a tecla 'Escape'
document.addEventListener("keydown", (event) => {
  const isEscKey = event.key === "Escape";
  if ((document.querySelector(".pizzaWindowArea").style.opacity = 1 && isEscKey)) {
    closeModal();
  }
});

// Fecha o modal ao clicar nos botões de cancelar
document
  .querySelectorAll(".pizzaInfo--cancelButton, .pizzaInfo--cancelMobileButton")
  .forEach((item) => {
    item.addEventListener("click", closeModal);
  });

// Controle de quantidade
// Evento para diminuir a quantidade de pizzas no modal
document.querySelector(".pizzaInfo--qtmenos").addEventListener("click", () => {
  if (modalQt > 1) {
    let preco = pizzas[modalKey].preco;
    modalQt--;
    document.querySelector(".pizzaInfo--qt").innerHTML = modalQt;
    let updatePreco = preco * modalQt;
    document.querySelector(".pizzaInfo--actualPrice").innerHTML = `${updatePreco.toLocaleString("pt-br", {
      style: "currency",
      currency: "BRL",
    })}`;
  }
});

// Evento para aumentar a quantidade de pizzas no modal
document.querySelector(".pizzaInfo--qtmais").addEventListener("click", () => {
  let preco = pizzas[modalKey].preco;
  modalQt++;
  document.querySelector(".pizzaInfo--qt").innerHTML = modalQt;
  let updatePreco = preco * modalQt;
  document.querySelector(".pizzaInfo--actualPrice").innerHTML = `${updatePreco.toLocaleString("pt-br", {
    style: "currency",
    currency: "BRL",
  })}`;
});

