/* Esse código é responsável por adicionar pizzas ao 
carrinho, atualizar a interface do carrinho e 
finalizar a compra */

// Inicializa as variáveis globais
let entregaTaxa;
let valorDesconto;
let observacaoGeral;
let formaPagamento;
let valorTroco;


fetchOrders();

// Adicionar ao carrinho
// Cria um identificador único, combinando o ID da pizza e o tamanho
document.querySelector(".pizzaInfo--addButton").addEventListener("click", () => {

  // Obtém o identificador da pizza (você pode ajustar conforme sua lógica)
  let identifier = pizzas[modalKey].id;

  // Captura as observações do usuário
  let observacoes = document.getElementById("observations").value.trim();

  // Procura no carrinho se o identificador já existe
  let keyItem = cart.findIndex((item) => item.identifier == identifier);

  // Verifica se a pizza já está no carrinho
  if (keyItem > -1) {
    // Se já estiver, aumenta a quantidade e atualiza as observações
    cart[keyItem].qtd += modalQt;
    cart[keyItem].observacoes = observacoes;
  } else {
    // Se não estiver, adiciona um novo item ao carrinho
    cart.push({
      identifier,
      id: pizzas[modalKey].id,
      preco: pizzas[modalKey].preco,
      qtd: modalQt,
      imagem: pizzas[modalKey].imagem,
      observacoes,
      observacaoGeral,
      formaPagamento,
      valorTroco
    });
  }
  // Adiciona uma animação de pulso ao ícone do carrinho
  document.querySelector(".fa-cart-shopping").classList.add("pulse");

  // Atualiza o carrinho, fecha o modal e salva o carrinho no localStorage
  // Chame a função para garantir que `entregaTaxa` seja definida
  updateCart();
  closeModal();
  saveCart();
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
    let entrega = entregaTaxa || 0;
    let desconto = valorDesconto || 0;
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
      cartItem.querySelector("img").src = `data:image/jpeg;base64,${pizzaItem.imagem}`;
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
  capturarInformacoes();
  cart.forEach(item => {
    item.observacaoGeral = observacaoGeral;
    item.formaPagamento = formaPagamento;
    item.valorTroco = valorTroco;
  });
  retornaIdQT();
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
        window.location.href = '../client/meus-pedidos.php' // Direciona o usuario para a tela de meus-pedidos após a compra

      }, 200);
    }, 4000);
  }, 2100);
});

// Função para enviar os dados do carrinho para o arquivo PHP
function retornaIdQT() {
  // Obter os dados do carrinho
  const cartData = cart.map(item => ({
    orderId: item.id,
    quantidade: item.qtd,
    observacoes: item.observacoes,
    valorTotal: item.preco * item.qtd,
    observacoesGerais: item.observacaoGeral,
    formaPagamento: item.formaPagamento,
    valorTroco: item.valorTroco
  }));
  fetch('../admin/get-dataCart.php', {

    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(cartData)
  })
    .then(response => {
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        return response.json();
      } else {
        return response.text().then(text => { throw new Error(text); });
      }
    })
    .then(data => {
      if (data.status === 'success') {
        console.log('Pedido finalizado com sucesso:', data.message);
      } else {
        console.error('Erro ao finalizar o pedido:', data.message);
      }
    })
    .catch(error => {
      console.error('Erro ao enviar os dados do pedido:', error);
    });
}

// Faz a requisição a API apiGetDB_to_Js
async function fetchOrders() {
  try {
    const response = await fetch('../../includes/apiGetDB_to_Js.php');
    const data = await response.json();

    if (data.error || data.message) {
      console.log(data.error ?? data.message);
    } else {
      entregaTaxa = parseFloat(data[0].taxaEntrega); // Define a taxaEntrega
      valorDesconto = parseFloat(data[0].descontoPedido);
    }
  } catch (error) {
    console.error('Error fetching orders:', error);
  }
}

// Função para capturar e armazenar as informações
function capturarInformacoes() {
  // Seletores dos elementos
  const observacoesGeraisInput = document.getElementById('observacoesGerais');
  const formaPagamentoSelect = document.getElementById('formaPagamento');
  const trocoInput = document.getElementById('troco');

  // Armazenar observação geral
  observacaoGeral = observacoesGeraisInput.value;
  console.log('Observação Geral:', observacaoGeral);

  // Armazenar forma de pagamento
  formaPagamento = formaPagamentoSelect.value;
  console.log('Forma de Pagamento:', formaPagamento);

  // Armazenar valor do troco, se visível
  valorTroco = null;
  if (trocoInput && trocoInput.style.display !== 'none') {
    valorTroco = trocoInput.value;
  }
  console.log('Valor para Troco:', valorTroco);
}