/* Esse código é responsável por adicionar pizzas ao 
carrinho, atualizar a interface do carrinho e 
finalizar a compra */

// Adicionar ao carrinho
// Cria um identificador único, combinando o ID da pizza e o tamanho
document.querySelector(".pizzaInfo--addButton").addEventListener("click", () => {
  // Obtém o tamanho selecionado
  // let size = parseInt(document.querySelector(".pizzaInfo--size.selected").getAttribute("data-key"));
  // Concatena o id da pizza com o tamanho para criar um identificador único
  let identifier = pizzas[modalKey].id; 
  // Procura no carrinho se o identificador já existe
  let keyItem = cart.findIndex((item) => item.identifier == identifier); 
  
  // Verifica se a pizza já está no carrinho
  if (keyItem > -1) {
    // Se já estiver, aumenta a quantidade
    cart[keyItem].qtd += modalQt; 
  } else {
    // Se não estiver, adiciona um novo item ao carrinho
    cart.push({ 
      identifier, 
      id: pizzas[modalKey].id,  
      preco: pizzas[modalKey].preco, 
      qtd: modalQt, 
      imagem: pizzas[modalKey].imagem
    });
  }

  // Adiciona uma animação de pulso ao ícone do carrinho
  document.querySelector(".fa-cart-shopping").classList.add("pulse");

  // Atualiza o carrinho, fecha o modal e salva o carrinho no localStorage
  updateCart();
  closeModal();
  saveCart();
  selecionaPedido();
});

// Função para salvar itens do carrinho no localStorage
const saveCart = () => {
  localStorage.setItem("pizza_cart", JSON.stringify(cart));
};

// Abre o menu do carrinho ao clicar no ícone
document.querySelector(".menu-openner").addEventListener("click", () => {
  if (cart.length > 0) {
    document.querySelector("aside").style.left = 0;
  }
});

// Fecha o menu do carrinho ao clicar no botão de fechar
document.querySelector(".menu-closer").addEventListener("click", () => {
  document.querySelector("aside").style.left = "100vw";
});

// Função para atualizar a interface do carrinho
function updateCart() {
  // Atualiza o número de itens no ícone do carrinho
  document.querySelector(".menu-openner span").innerHTML = cart.length;

  // Verifica se há itens no carrinho
  if (cart.length > 0) {
    document.querySelector("aside").classList.add("show");
    document.querySelector(".cart").innerHTML = ""; // Limpa o conteúdo do carrinho

    let pizzasValor = 0;
    let subtotal = 0;
    let entrega = 5;
    let desconto = 0;
    let total = 0;

    // Itera sobre os itens do carrinho
    for (let i in cart) {
      // Encontra a pizza correspondente no array de pizzas
      let pizzaItem = pizzas.find((item) => item.id == cart[i].id);
      // Calcula o valor total das pizzas
      pizzasValor += cart[i].preco * cart[i].qtd;
/*
      // Define o nome do tamanho da pizza
      let pizzaSizeName;
      switch (cart[i].size) {
        case 0:
          pizzaSizeName = "P";
          break;
        case 1:
          pizzaSizeName = "M";
          break;
        case 2:
          pizzaSizeName = "G";
          break;
      }
*/
      // Define o nome da pizza com o tamanho
      let pizzaName = `${pizzaItem.nome}`;
      // Clona o modelo de item do carrinho
      let cartItem = document.querySelector(".models .cart--item").cloneNode(true);

      // Define a imagem, nome e quantidade do item no carrinho
      cartItem.querySelector("img").src = pizzaItem.imagem;
      cartItem.querySelector(".cart--item-nome").innerHTML = pizzaName;
      cartItem.querySelector(".cart--item--qt").innerHTML = cart[i].qtd;

      // Evento para diminuir a quantidade do item no carrinho
      cartItem.querySelector(".cart--item-qtmenos").addEventListener("click", () => {
        if (cart[i].qtd > 1) {
          cart[i].qtd--;
        } else {
          cart.splice(i, 1);
        }
        updateCart();
      });

      // Evento para aumentar a quantidade do item no carrinho
      cartItem.querySelector(".cart--item-qtmais").addEventListener("click", () => {
        cart[i].qtd++;
        updateCart();
      });

      // Adiciona o item ao carrinho na interface
      document.querySelector(".cart").append(cartItem);
    }

    // Calcula os valores de subtotal, desconto e total
    subtotal = pizzasValor + entrega;
    desconto = subtotal * 0.1;
    total = subtotal - desconto;

    // Atualiza os valores na interface
    document.querySelector(".pizzasValor span:last-child").innerHTML = `${pizzasValor.toLocaleString("pt-br", {
      style: "currency",
      currency: "BRL",
    })}`;
    document.querySelector(".entrega span:last-child").innerHTML = `${entrega.toLocaleString("pt-br", {
      style: "currency",
      currency: "BRL",
    })}`;
    document.querySelector(".subtotal span:last-child").innerHTML = `${subtotal.toLocaleString("pt-br", {
      style: "currency",
      currency: "BRL",
    })}`;
    document.querySelector(".desconto span:last-child").innerHTML = `${desconto.toLocaleString("pt-br", {
      style: "currency",
      currency: "BRL",
    })}`;
    document.querySelector(".total span:last-child").innerHTML = `${total.toLocaleString("pt-br", {
      style: "currency",
      currency: "BRL",
    })}`;
  } else {
    // Se o carrinho estiver vazio, limpa o localStorage e fecha o carrinho
    localStorage.clear();
    document.querySelector("aside").classList.remove("show");
    document.querySelector("aside").style.left = "100vw";
  }
}

// Finaliza a compra ao clicar no botão de finalizar
document.querySelector(".cart--finalizar").addEventListener("click", () => {
  cart = []; // Limpa o carrinho
  localStorage.clear(); // Limpa o localStorage
  updateCart(); // Atualiza a interface do carrinho
  document.querySelector(".fa-cart-shopping").classList.remove("pulse"); // Remove a animação de pulso do ícone do carrinho
  document.querySelector(".loader-content").classList.add("display"); // Exibe o loader

  setTimeout(() => {
    document.querySelector(".loader-content").classList.remove("display"); // Esconde o loader

    // Exibe a mensagem de sucesso com animação de opacidade
    document.querySelector(".success.pizzaWindowArea").style.opacity = 0;
    document.querySelector(".success.pizzaWindowArea").style.display = "flex";
    setTimeout(() => {
      document.querySelector(".success.pizzaWindowArea").style.opacity = 1;
    }, 200);

    setTimeout(() => {
      document.querySelector(".success.pizzaWindowArea").style.opacity = 0;
      setTimeout(() => {
        document.querySelector(".success.pizzaWindowArea").style.display = "none";
        updateCart(); // Atualiza o carrinho
        closeModal(); // Fecha o modal
      }, 200);
    }, 4000);
  }, 2100);
});

function selecionaPedido(){
  console.log(identifier)
}