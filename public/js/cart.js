/* Esse código é responsável por adicionar pizzas ao 
carrinho, atualizar a interface do carrinho e 
finalizar a compra */

// Inicializa as variáveis globais
let entregaTaxa;
let valorDesconto;
let observacaoGeral;
let formaPagamento;
let valorTroco;
let adicionais = [];

getInfoDB();
// Adicionar ao carrinho
// Cria um identificador único, combinando o ID da pizza e o tamanho
// Adicionar ao carrinho
document.querySelector(".pizzaInfo--addButton").addEventListener("click", () => {
  let identifier = pizzas[modalKey].id;

  // Captura as observações do usuário
  let observacoes = document.getElementById("observations").value.trim();

  // Captura os adicionais selecionados no modal
  let adicionaisSelecionados = capturarAdicionais();

  // Procura no carrinho se o identificador já existe
  let keyItem = cart.findIndex((item) => item.identifier == identifier);

  // Verifica se a pizza já está no carrinho
  if (keyItem > -1) {
      // Se já estiver, aumenta a quantidade e atualiza as observações
      cart[keyItem].qtd += modalQt;
      cart[keyItem].adicionais = adicionaisSelecionados;
  } else {
      // Se não estiver, adiciona um novo item ao carrinho
      cart.push({
          identifier,
          id: pizzas[modalKey].id,
          preco: pizzas[modalKey].preco,
          qtd: modalQt,
          imagem: pizzas[modalKey].imagem,
          observacoes: observacoes,
          observacaoGeral: observacaoGeral,
          formaPagamento: formaPagamento,
          adicionais: adicionaisSelecionados, // Adicionais selecionados
          valorTroco: valorTroco
      });
  }

  //Limpa a area de observações
  document.getElementById("observations").value = '';

  // Atualiza o carrinho
  updateCart();
  closeModal();
  saveCart();
});

// Função para salvar itens do carrinho no localStorage
const saveCart = () => {
  localStorage.setItem("pizza_cart", JSON.stringify(cart));
};

document.querySelector(".barra-carrinho").addEventListener("click", () => {
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
  // Atualiza o número de itens na barra do carrinho
  document.querySelector(".barra-carrinho-total-itens").innerHTML = `${cart.length} itens`;

  // Calcula o valor total
  let pizzasValor = 0;
  for (let i in cart) {
    pizzasValor += cart[i].preco * cart[i].qtd;
  }

  // Atualiza o valor total na barra do carrinho
  document.querySelector(".barra-carrinho-valor-total").innerHTML = `Total: R$ ${pizzasValor.toLocaleString("pt-br", {
    style: "currency",
    currency: "BRL",
  })}`;

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

      // Calcula o valor dos adicionais
      let adicionaisValor = 0;
      if (cart[i].adicionais && cart[i].adicionais.length > 0) {
        cart[i].adicionais.forEach(adicional => {
          adicionaisValor += adicional.preco;
        });
      }

      // Calcula o valor total das pizzas (incluindo adicionais)
      pizzasValor += (cart[i].preco * cart[i].qtd) + (adicionaisValor * cart[i].qtd);

      // Define o nome da pizza com o tamanho
      let pizzaName = `${pizzaItem.nome}`;

      // Clona o modelo de item do carrinho
      let cartItem = document.querySelector(".models .cart--item").cloneNode(true);

      // Define a imagem, nome e quantidade do item no carrinho
      cartItem.querySelector("img").src = `data:image/jpeg;base64,${pizzaItem.imagem}`;
      cartItem.querySelector(".cart--item-nome").innerHTML = pizzaName;
      cartItem.querySelector(".cart--item--qt").innerHTML = cart[i].qtd;

      // Define o elemento de observações
      const observacoesElement = cartItem.querySelector(".cart--obs");
      observacoesElement.innerHTML = cart[i].observacoes || '';

      // Exibe ou oculta o elemento de observações com base na sua presença
      if (!cart[i].observacoes) {
        observacoesElement.style.display = 'none'; // Oculta o elemento se não houver observações
      } else {
        observacoesElement.style.display = 'block'; // Garante que o elemento esteja visível se houver observações
      }

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

// Faz a requisição a API para pegar entrega e desconto
async function getInfoDB() {
  try {
    const response = await fetch('../../includes/getEntretaDesconto.php');
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
