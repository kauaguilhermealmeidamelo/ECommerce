<template>
  <div v-if="produto" class="produto">
    <img :src="produto.imagem_url" :alt="produto.nome" class="produto__imagem" />

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

      <p class="parcelamento">
        {{ produto.max_parcelas }}x de {{ formatarMoeda(produto.preco / produto.max_parcelas) }} sem juros
      </p>
      <p class="desconto-pix">{{ produto.desconto_pix_percentual }}% de desconto pagando com Pix</p>

      <!-- Seletor de tamanho -->
      <div v-if="produto.variacoes?.length" class="tamanhos">
        <span class="tamanhos__label">Tamanho: <strong>{{ tamanhoSelecionado || '—' }}</strong></span>
        <div class="tamanhos__opcoes">
          <button
            v-for="v in produto.variacoes"
            :key="v.tamanho"
            class="tamanho"
            :class="{ 'tamanho--selecionado': tamanhoSelecionado === v.tamanho, 'tamanho--esgotado': v.estoque === 0 }"
            :disabled="v.estoque === 0"
            @click="tamanhoSelecionado = v.tamanho"
          >
            {{ v.tamanho }}
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
        <button class="botao-comprar" :disabled="!podeComprar" @click="comprar">
          Comprar
        </button>
      </div>

      <!-- Meios de envio -->
      <section class="frete">
        <h2>Meios de envio</h2>
        <div class="frete__busca">
          <input v-model="cep" placeholder="Seu CEP" maxlength="9" @keyup.enter="buscarFrete" />
          <button @click="buscarFrete" :disabled="buscandoFrete">{{ buscandoFrete ? '...' : 'Calcular' }}</button>
        </div>
        <a href="https://buscacepinter.correios.com.br/" target="_blank" class="frete__sem-cep">Não sei meu CEP</a>

        <ul v-if="opcoesFrete.length" class="frete__opcoes">
          <li v-for="op in opcoesFrete" :key="op.metodo">
            <span>{{ op.label }}</span>
            <span>{{ op.valor === 0 ? 'Grátis' : op.valor ? formatarMoeda(op.valor) : 'Consultar' }}</span>
          </li>
        </ul>
        <p v-else-if="freteConsultado" class="frete__vazio">Nenhuma opção de entrega disponível pra esse CEP.</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import api from '@/api'

const props = defineProps({ produto: { type: Object, required: true } })

const tamanhoSelecionado = ref(props.produto.variacoes?.[0]?.tamanho ?? null)
const quantidade = ref(1)
const cep = ref('')
const opcoesFrete = ref([])
const buscandoFrete = ref(false)
const freteConsultado = ref(false)

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)

const precoPix = computed(() => props.produto.preco * (1 - props.produto.desconto_pix_percentual / 100))

const podeComprar = computed(() => {
  if (!props.produto.variacoes?.length) return true
  const variacao = props.produto.variacoes.find((v) => v.tamanho === tamanhoSelecionado.value)
  return variacao && variacao.estoque > 0
})

const emit = defineEmits(['adicionar-carrinho'])

async function comprar() {
  // POST /carrinho/itens — ajuste o payload conforme o CarrinhoController real.
  await api.post('/carrinho/itens', {
    produto_id: props.produto.id,
    tamanho: tamanhoSelecionado.value,
    quantidade: quantidade.value,
  })

  emit('adicionar-carrinho', { produto: props.produto, tamanho: tamanhoSelecionado.value, quantidade: quantidade.value })
}

async function buscarFrete() {
  if (!cep.value) return
  buscandoFrete.value = true
  freteConsultado.value = false

  try {
    const { data } = await api.get('/frete/opcoes', {
      params: { cep: cep.value, produto_id: props.produto.id, quantidade: quantidade.value },
    })
    opcoesFrete.value = data.data
  } catch (e) {
    opcoesFrete.value = []
  } finally {
    buscandoFrete.value = false
    freteConsultado.value = true
  }
}
</script>

<style scoped>
/* Cores/fontes vêm do tema — ver src/theme/aplicarTema.js */
.produto__imagem { width: 100%; aspect-ratio: 4/5; object-fit: cover; }
.produto__conteudo { padding: 1.25rem; max-width: var(--layout-largura-maxima, 640px); margin: 0 auto; }
.breadcrumb { font-size: .8rem; color: var(--cor-texto-suave); margin-bottom: .75rem; }
.produto__titulo { font-family: var(--fonte-display); font-size: 1.6rem; margin: 0 0 .75rem; text-transform: uppercase; }

.preco { display: flex; align-items: baseline; gap: .6rem; }
.preco__atual { font-size: 1.5rem; font-weight: 600; }
.preco__original { text-decoration: line-through; color: var(--cor-texto-suave); }
.preco__pix { margin-top: .2rem; font-size: 1rem; }
.parcelamento { margin: .75rem 0 .1rem; font-size: .9rem; }
.desconto-pix { margin: 0 0 1rem; font-size: .85rem; color: var(--cor-texto-suave); }

.tamanhos { margin-bottom: 1.25rem; }
.tamanhos__label { font-size: .9rem; display: block; margin-bottom: .5rem; }
.tamanhos__opcoes { display: flex; gap: .5rem; flex-wrap: wrap; }
.tamanho {
  border: 1px solid var(--cor-linha);
  background: var(--cor-superficie);
  padding: .5rem .9rem;
  border-radius: var(--raio-borda);
  cursor: pointer;
  font-size: .9rem;
}
.tamanho--selecionado { border-color: var(--cor-primaria); background: var(--cor-primaria); color: #fff; }
.tamanho--esgotado { text-decoration: line-through; color: var(--cor-texto-suave); cursor: not-allowed; opacity: .5; }

.acao-compra { display: flex; gap: .75rem; margin-bottom: 2rem; }
.quantidade { display: flex; align-items: center; border: 1px solid var(--cor-linha); border-radius: var(--raio-borda); }
.quantidade button { border: none; background: none; width: 36px; height: 44px; font-size: 1.1rem; cursor: pointer; }
.quantidade span { width: 30px; text-align: center; }
.botao-comprar {
  flex: 1;
  background: var(--cor-texto);
  color: #fff;
  border: none;
  border-radius: var(--raio-borda);
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
}
.botao-comprar:disabled { opacity: .5; cursor: not-allowed; }

.frete { border-top: 1px solid var(--cor-linha); padding-top: 1.25rem; }
.frete h2 { font-size: 1rem; margin: 0 0 .75rem; }
.frete__busca { display: flex; gap: .5rem; }
.frete__busca input { flex: 1; border: none; border-bottom: 1px solid var(--cor-linha); padding: .5rem 0; }
.frete__busca button { border: none; background: none; color: var(--cor-primaria); font-weight: 600; cursor: pointer; }
.frete__sem-cep { display: inline-block; margin-top: .5rem; font-size: .85rem; text-decoration: underline; color: var(--cor-texto-suave); }
.frete__opcoes { list-style: none; padding: 0; margin: 1rem 0 0; }
.frete__opcoes li { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid var(--cor-linha); font-size: .9rem; }
.frete__vazio { font-size: .85rem; color: var(--cor-texto-suave); margin-top: 1rem; }
</style>
