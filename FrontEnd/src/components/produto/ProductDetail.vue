<template>
  <div v-if="produto" class="produto">
    <ProdutoCarrossel :imagens="produto.imagens" :imagem-url-fallback="produto.imagem_url" :alt="produto.nome"
      class="produto__imagem" />

    <div class="produto__conteudo">
      <nav class="breadcrumb">
        <span>Início</span> | <span>{{ produto.categoria?.nome }}</span>
      </nav>

      <h1 class="produto__titulo">{{ produto.nome }}</h1>

      <div class="preco">
        <span class="preco__atual">{{ formatarMoeda(produto.preco) }}</span>
        <span v-if="produto.preco_original" class="preco__original">{{ formatarMoeda(produto.preco_original) }}</span>
      </div>

      <div class="preco__pix">{{ formatarMoeda(precoPix) }} com Pix</div>

      <!-- Seletor de Variação Genérico (Anti-engessamento de nicho) -->
      <div v-if="produto.variacoes?.length" class="variacoes">
        <span class="variacoes__label">Opção Selecionada: <strong>{{ variacaoSelecionada?.nome || '—' }}</strong></span>
        <div class="variacoes__opcoes">
          <button v-for="v in produto.variacoes" :key="v.id ?? v.nome" class="variacao-btn"
            :class="{ 'variacao-btn--selecionado': variacaoSelecionada?.nome === v.nome, 'variacao-btn--esgotado': v.estoque === 0 }"
            :disabled="v.estoque === 0" @click="variacaoSelecionada = v">
            {{ v.nome }}
          </button>
        </div>
      </div>

      <!-- Quantidade + comprar -->
      <div class="acao-compra">
        <div class="quantidade">
          <button @click="quantidade = Math.max(1, quantidade - 1)">−</button>
          <span>{{ quantidade }}</span>
          <button @click="quantidade++">+</button>
        </div>
        <button class="botao-comprar" :disabled="!podeComprar || comprando" @click="comprar">
  {{ comprando ? 'Adicionando...' : 'Comprar' }}
</button>
      </div>

      <!-- Meios de envio -->
      <!-- <section class="frete">
        <h2>Meios de envio</h2>
        <div class="frete__busca">
          <input v-model="cep" placeholder="Seu CEP" maxlength="9" @keyup.enter="buscarFrete" />
          <button @click="buscarFrete" :disabled="buscandoFrete">{{ buscandoFrete ? '...' : 'Calcular' }}</button>
        </div>
        <ul v-if="opcoesFrete.length" class="frete__opcoes">
          <li v-for="op in opcoesFrete" :key="op.metodo">
            <span>{{ op.metodo }}</span>
            <span>{{ op.valor === 0 ? 'Grátis' : op.valor ? formatarMoeda(op.valor) : 'Consultar' }}</span>
          </li>
        </ul>
      </section> -->
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import apiLoja from '@/services/apiLoja'
import { useCarrinhoStore } from '@/stores/carrinho.store'
import ProdutoCarrossel from '@/components/ProdutoCarrossel.vue'

const props = defineProps({ produto: { type: Object, required: true } })
const carrinho = useCarrinhoStore()

const variacaoSelecionada = ref(props.produto.variacoes?.[0] ?? null)
const quantidade = ref(1)
const cep = ref('')
const opcoesFrete = ref([])
const buscandoFrete = ref(false)
const comprando = ref(false)


const formatarMoeda = (value: number) =>
  new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)

const precoPix = computed(() => props.produto.preco * (1 - (props.produto.desconto_pix_percentual || 0) / 100))

const podeComprar = computed(() => {
  if (!props.produto.variacoes?.length) return true
  return variacaoSelecionada.value && variacaoSelecionada.value.estoque > 0
})

const emit = defineEmits(['adicionar-carrinho'])

async function comprar() {
  if (comprando.value) return
  comprando.value = true
  try {
    const sucesso = await carrinho.adicionarItem(
      props.produto.id,
      quantidade.value,
      variacaoSelecionada.value?.nome ?? null,
    )

    if (sucesso) {
      emit('adicionar-carrinho', {
        produto: props.produto,
        variacao: variacaoSelecionada.value,
        quantidade: quantidade.value,
      })
    }
  } finally {
    comprando.value = false
  }
}

async function buscarFrete() {
  if (!cep.value) return
  buscandoFrete.value = true
  try {
    const { data } = await apiLoja.get('/frete/opcoes', {
      params: { cep: cep.value, produto_id: props.produto.id, quantidade: quantidade.value },
    })
    opcoesFrete.value = data.data
  } catch (e) {
    opcoesFrete.value = []
  } finally {
    buscandoFrete.value = false
  }
}
</script>