<template>
  <div class="pagina checkout-pagina">
    <div class="pagina__cabecalho">
      <h1 class="pagina__titulo">Finalizar Pedido</h1>
      <p class="pagina__subtitulo">Preencha os dados de entrega e pagamento</p>
    </div>

    <div v-if="carregando" class="estado-carregando">Carregando checkout...</div>
    <p v-else-if="erro" class="erro-mensagem">{{ erro }}</p>

    <div v-else class="checkout-grade">
      <!-- Coluna Formulário -->
      <div class="card checkout-formulario">
        <h3>1. Escolha a Opção de Entrega</h3>
        <div class="opcoes-entrega">
          <label class="radio-card">
            <input type="radio" v-model="tipoEntrega" value="retirada" />
            <span>Retirada na Loja</span>
          </label>
          <label class="radio-card">
            <input type="radio" v-model="tipoEntrega" value="local" />
            <span>Entrega Local</span>
          </label>
          <label class="radio-card">
            <input type="radio" v-model="tipoEntrega" value="transportadora" />
            <span>Transportadora</span>
          </label>
        </div>

        <h3>2. Endereço de Entrega</h3>
        <div class="campos">
          <div class="campos__linha">
            <label>CEP <input v-model="endereco.cep" @blur="calcularFrete" placeholder="00000-000"
                maxlength="9" /></label>
            <label>Número <input v-model="endereco.numero" placeholder="Ex: 123" /></label>
          </div>
          <label>Endereço <input v-model="endereco.logradouro" placeholder="Rua, Avenida, etc." /></label>
          <div class="campos__linha">
            <label>Bairro <input v-model="endereco.bairro" /></label>
            <label>Cidade <input v-model="endereco.cidade" /></label>
            <label>UF <input v-model="endereco.uf" maxlength="2" /></label>
          </div>
        </div>

        <button class="btn btn--primario btn--bloco" :disabled="salvando" @click="finalizarCheckout">
          {{ salvando ? 'Redirecionando ao Mercado Pago...' : 'Ir para o Pagamento' }}
        </button>
      </div>

      <!-- Coluna Resumo do Carrinho -->
      <div class="card checkout-resumo" v-if="carrinho">
        <h3>Resumo do Pedido</h3>
        <ul class="resumo-lista">
          <li v-for="item in carrinho.itens" :key="item.id" class="resumo-item">
            <span>{{ item.produto?.name ?? 'Produto' }} (x{{ item.quantidade }})</span>
            <strong>R$ {{ ((item.produto?.preco ?? 0) * item.quantidade).toFixed(2) }}</strong>
          </li>
        </ul>
        <div class="resumo-totais">
          <div class="linha-total"><span>Frete</span><strong>R$ {{ freteCalculado.toFixed(2) }}</strong></div>
          <div class="linha-total linha-total--destaque">
            <span>Total</span>
            <strong>R$ {{ ((carrinho.subtotal ?? 0) + freteCalculado).toFixed(2) }}</strong>
          </div>
        </div>
      </div>
    </div>

    <router-link :to="{ name: 'carrinho' }" class="btn-voltar"><v-icon icon="mdi-arrow-left" size="small" /> Voltar ao carrinho</router-link>
  </div>
</template>

<script setup lang="ts">
import { useCheckoutViewModel } from '@/viewmodels/useCheckoutViewModel'

const {
  carrinho,
  carregando,
  salvando,
  erro,
  tipoEntrega,
  endereco,
  freteCalculado,
  calcularFrete,
  finalizarCheckout,
} = useCheckoutViewModel()
</script>

<style scoped>
.checkout-pagina {
  max-width: 960px;
  margin: 2rem auto;
  padding: 0 1.25rem;
  color: var(--cor-texto);
}

.checkout-grade {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  margin-top: 1.5rem;
}

@media (min-width: 768px) {
  .checkout-grade {
    grid-template-columns: 1.6fr 1fr;
  }
}

.checkout-formulario,
.checkout-resumo {
  padding: 1.5rem;
}

.checkout-formulario h3,
.checkout-resumo h3 {
  font-size: 1rem;
  margin-bottom: 1rem;
  font-weight: 700;
}

.opcoes-entrega {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.radio-card {
  display: flex;
  align-items: center;
  gap: .4rem;
  font-size: .85rem;
  cursor: pointer;
}

.campos {
  display: flex;
  flex-direction: column;
  gap: .8rem;
  margin-bottom: 1.5rem;
}

.campos__linha {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .8rem;
}

.campos label {
  display: flex;
  flex-direction: column;
  gap: .3rem;
  font-size: .78rem;
  color: var(--cor-texto-suave);
  font-weight: 600;
}

.campos input {
  padding: .55rem;
  border: 1px solid var(--cor-linha);
  border-radius: var(--raio-borda);
  background: var(--cor-fundo);
  color: var(--cor-texto);
}

.btn--bloco {
  width: 100%;
  padding: .75rem;
  margin-top: 1rem;
}

.resumo-lista {
  list-style: none;
  padding: 0;
  margin: 0 0 1rem;
  display: flex;
  flex-direction: column;
  gap: .6rem;
}

.resumo-item {
  display: flex;
  justify-content: space-between;
  font-size: .82rem;
  color: var(--cor-texto-suave);
}

.resumo-totais {
  border-top: 1px solid var(--cor-linha);
  padding-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: .5rem;
}

.linha-total {
  display: flex;
  justify-content: space-between;
  font-size: .85rem;
}

.linha-total--destaque {
  font-size: 1.1rem;
  color: var(--cor-primaria);
  font-weight: 700;
  margin-top: .5rem;
}

.btn-voltar {
  display: inline-block;
  margin-top: 1.5rem;
  color: var(--cor-primaria);
  font-weight: 600;
  text-decoration: none;
}
</style>