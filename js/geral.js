let cart = [];
let modalQt = 1;
let modalKey = 0;
let pizzas = [];

// GET CART BY SESSION STORAGE
localStorage.getItem("pizza_cart")
  ? (cart = JSON.parse(localStorage.getItem("pizza_cart")))
  : (cart = []);

fetch('apiGetPizzas.php')
  .then(response => response.json())
  .then(data => {
    pizzas = data;

    //##LIST PIZZAS
    data.forEach((item, index) => {
      let pizzaItem = document.querySelector(".models .pizza-item").cloneNode(true);
      pizzaItem.setAttribute("data-key", index);

      pizzaItem.querySelector(".pizza-item--img img").src = item.img;
      pizzaItem.querySelector(".pizza-item--price").innerHTML = item.tamanhos[0].preco.toLocaleString("pt-br", {
        style: "currency",
        currency: "BRL",
      });
      pizzaItem.querySelector(".pizza-item--name").innerHTML = item.nome;
      pizzaItem.querySelector(".pizza-item--desc").innerHTML = item.descricao;

      //### MODAL
      pizzaItem.querySelector("a").addEventListener("click", (e) => {
        e.preventDefault();
        let key = e.target.closest(".pizza-item").getAttribute("data-key");
        modalQt = 1;
        modalKey = key;

        document.querySelector(".pizzaBig img").src = pizzas[key].img;
        document.querySelector(".pizzaInfo h1").innerHTML = pizzas[key].nome;
        document.querySelector(".pizzaInfo--desc").innerHTML = pizzas[key].descricao;
        document.querySelector(".pizzaInfo--actualPrice").innerHTML = pizzas[key].tamanhos[0].preco.toLocaleString("pt-br", {
          style: "currency",
          currency: "BRL",
        });

        document.querySelector(".pizzaInfo--size.selected").classList.remove("selected");
        document.querySelectorAll(".pizzaInfo--size").forEach((size, sizeIndex) => {
          if (sizeIndex == 0) {
            size.classList.add("selected");
          }
          size.querySelector("span").innerHTML = pizzas[key].tamanhos[sizeIndex].tamanho;
          size.querySelector("span").setAttribute("data-key", sizeIndex);

          size.addEventListener("click", () => {
            document.querySelector(".pizzaInfo--size.selected").classList.remove("selected");
            size.classList.add("selected");

            let selectedSizeIndex = parseInt(size.querySelector("span").getAttribute("data-key"));
            let selectedPrice = pizzas[key].tamanhos[selectedSizeIndex].preco;
            modalQt = 1;
            document.querySelector(".pizzaInfo--qt").innerHTML = modalQt;
            document.querySelector(".pizzaInfo--actualPrice").innerHTML = selectedPrice.toLocaleString("pt-br", {
              style: "currency",
              currency: "BRL",
            });
          });
        });

        document.querySelector(".pizzaInfo--qt").innerHTML = modalQt;
        document.querySelector(".pizzaWindowArea").style.opacity = 0;
        document.querySelector(".pizzaWindowArea").style.display = "flex";
        setTimeout(() => {
          document.querySelector(".pizzaWindowArea").style.opacity = 1;
        }, 200);
      });

      document.querySelector(".pizza-area").append(pizzaItem);
    });
  })
  .catch(error => console.error('Erro ao buscar pizzas:', error));

//##MODAL EVENTS
function closeModal() {
  document.querySelector(".pizzaWindowArea").style.opacity = 0;
  setTimeout(() => {
    document.querySelector(".pizzaWindowArea").style.display = "none";
  }, 600);
  window.scrollTo(0, 0);
}
//Fechar modal com Esc
document.addEventListener("keydown", (event) => {
  const isEscKey = event.key === "Escape";

  if (document.querySelector(".pizzaWindowArea").style.opacity == 1 && isEscKey) {
    closeModal();
  }
});
//Fechar modal com click no 'cancelar'
document.querySelectorAll(".pizzaInfo--cancelButton, .pizzaInfo--cancelMobileButton")
  .forEach((item) => {
    item.addEventListener("click", closeModal);
  });

//##CONTROLS
document.querySelector(".pizzaInfo--qtmenos").addEventListener("click", () => {
  if (modalQt > 1) {
    let sizeIndex = parseInt(document.querySelector(".pizzaInfo--size.selected span").getAttribute("data-key"));
    let preco = pizzas[modalKey].tamanhos[sizeIndex].preco;

    modalQt--;
    document.querySelector(".pizzaInfo--qt").innerHTML = modalQt;

    let updatePreco = preco * modalQt;
    document.querySelector(".pizzaInfo--actualPrice").innerHTML = updatePreco.toLocaleString("pt-br", {
      style: "currency",
      currency: "BRL",
    });
  }
});

document.querySelector(".pizzaInfo--qtmais").addEventListener("click", () => {
  let sizeIndex = parseInt(document.querySelector(".pizzaInfo--size.selected span").getAttribute("data-key"));
  let preco = pizzas[modalKey].tamanhos[sizeIndex].preco;

  modalQt++;
  document.querySelector(".pizzaInfo--qt").innerHTML = modalQt;

  let updatePreco = preco * modalQt;
  document.querySelector(".pizzaInfo--actualPrice").innerHTML = updatePreco.toLocaleString("pt-br", {
    style: "currency",
    currency: "BRL",
  });
});
