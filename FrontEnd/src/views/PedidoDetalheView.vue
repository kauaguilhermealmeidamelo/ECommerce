<template>
  <div class="pagina">
    <router-link :to="{ name: 'meus-pedidos' }" class="pagina__voltar">← Meus pedidos</router-link>

    <div v-if="carregando" class="estado">Carregando pedido...</div>
    <p v-else-if="erro" class="estado estado--erro">{{ erro }}</p>

    <template v-else-if="pedido">
      <h1 class="font-display pagina__titulo">Pedido #{{ pedido.id }}</h1>
      <p class="pagina__subtitulo">Feito em {{ formatarData(pedido.created_at) }}</p>

      <!-- Rastreio -->
      <section class="card">
        <h2 class="card__titulo">Status da entrega</h2>
        <ol class="linha-tempo">
          <li v-for="etapa in etapasRastreio" :key="etapa.chave" class="linha-tempo__item" :class="{ 'linha-tempo__item--concluida': etapa.concluida, 'linha-tempo__item--atual': etapa.atual }">
            <span class="linha-tempo__ponto"></span>
            <div>
              <strong>{{ etapa.rotulo }}</strong>
              <p v-if="etapa.detalhe">{{ etapa.detalhe }}</p>
            </div>
          </li>
        </ol>

        <div v-if="pedido.codigo_rastreio" class="rastreio-codigo">
          <span>Código de rastreio ({{ pedido.transportadora }})</span>
          <strong>{{ pedido.codigo_rastreio }}</strong>
        </div>

        <p v-if="pedido.status === 'cancelado'" class="aviso-cancelado">Este pedido foi cancelado.</p>
      </section>

      <!-- Itens -->
      <section class="card">
        <h2 class="card__titulo">Itens</h2>
        <ul class="itens">
          <li v-for="item in pedido.itens" :key="item.id" class="itens__linha">
            <span>{{ item.quantidade }}x {{ item.produto?.nome }} <template v-if="item.tamanho">— {{ item.tamanho }}</template></span>
            <strong>{{ formatarMoeda(item.quantidade * item.preco_unitario) }}</strong>
          </li>
        </ul>
        <div class="itens__total">
          <span>Frete</span>
          <strong>{{ pedido.valor_frete > 0 ? formatarMoeda(pedido.valor_frete) : 'Grátis' }}</strong>
        </div>
        <div class="itens__total itens__total--principal">
          <span>Total</span>
          <strong>{{ formatarMoeda(pedido.total) }}</strong>
        </div>
      </section>

      <!-- Endereço -->
      <section v-if="pedido.metodo_entrega !== 'retirada'" class="card">
        <h2 class="card__titulo">Endereço de entrega</h2>
        <p class="endereco">
          {{ pedido.destinatario_nome }}<br />
          {{ pedido.destinatario_endereco }}, {{ pedido.destinatario_numero }}
          <template v-if="pedido.destinatario_complemento"> — {{ pedido.destinatario_complemento }}</template><br />
          {{ pedido.destinatario_bairro }} — {{ pedido.destinatario_cidade }}/{{ pedido.destinatario_uf }}<br />
          CEP {{ pedido.destinatario_cep }}
        </p>
      </section>
      <section v-else class="card">
        <h2 class="card__titulo">Retirada na loja</h2>
        <p class="endereco">Este pedido será retirado diretamente na loja.</p>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiLoja from '@/services/apiLoja'

const route = useRoute()
const pedido = ref(null)
const carregando = ref(true)
const erro = ref(null)

const formatarMoeda = (v) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
const formatarData = (d) => new Date(d).toLocaleDateString('pt-BR')

// Linha do tempo simples baseada no status + enviado_em — não depende de
// nenhuma integração de rastreio externa, só do que o admin já registra.
const etapasRastreio = computed(() => {
  if (!pedido.value) return []

  const status = pedido.value.status
  const ordem = ['pendente', 'pago', 'enviado', 'concluido']
  const indiceAtual = ordem.indexOf(status)

  return [
    { chave: 'pendente', rotulo: 'Pedido realizado' },
    { chave: 'pago', rotulo: 'Pagamento confirmado' },
    { chave: 'enviado', rotulo: 'Enviado', detalhe: pedido.value.enviado_em ? formatarData(pedido.value.enviado_em) : null },
    { chave: 'concluido', rotulo: 'Entregue' },
  ].map((etapa, i) => ({
    ...etapa,
    concluida: status !== 'cancelado' && i <= indiceAtual,
    atual: status !== 'cancelado' && i === indiceAtual,
  }))
})

async function carregar() {
  carregando.value = true
  try {
    const { data } = await apiLoja.get(`/minha-conta/pedidos/${route.params.id}`)
    pedido.value = data.data
  } catch (e) {
    erro.value = e.response?.status === 403
      ? 'Você não tem acesso a este pedido.'
      : 'Não foi possível carregar este pedido.'
  } finally {
    carregando.value = false
  }
}

onMounted(carregar)
</script>

<style scoped>
.pagina { max-width: 640px; margin: 0 auto; padding: 1.5rem 1.25rem 6rem; }
.pagina__voltar { display: inline-block; margin-bottom: 1rem; font-size: .82rem; color: var(--cor-texto-suave); text-decoration: none; }
.pagina__titulo { font-size: 1.3rem; margin: 0 0 .2rem; color: var(--cor-texto); }
.pagina__subtitulo { color: var(--cor-texto-suave); font-size: .85rem; margin: 0 0 1.5rem; }

.estado, .estado--erro { text-align: center; padding: 2.5rem 1rem; color: var(--cor-texto-suave); font-size: .88rem; }
.estado--erro { color: #dc2626; }

.card { background: var(--cor-superficie); border: 1px solid var(--cor-linha); border-radius: var(--raio-borda); padding: 1.1rem 1.15rem; margin-bottom: 1rem; }
.card__titulo { font-size: .85rem; text-transform: uppercase; letter-spacing: .04em; color: var(--cor-texto-suave); margin: 0 0 1rem; font-weight: 700; }

.linha-tempo { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 1.1rem; }
.linha-tempo__item { display: flex; gap: .8rem; align-items: flex-start; opacity: .45; }
.linha-tempo__item--concluida, .linha-tempo__item--atual { opacity: 1; }
.linha-tempo__ponto { width: 10px; height: 10px; border-radius: 50%; background: var(--cor-linha); margin-top: .3rem; flex-shrink: 0; }
.linha-tempo__item--concluida .linha-tempo__ponto { background: var(--cor-primaria); }
.linha-tempo__item strong { display: block; font-size: .88rem; color: var(--cor-texto); }
.linha-tempo__item p { margin: .15rem 0 0; font-size: .76rem; color: var(--cor-texto-suave); }

.rastreio-codigo { margin-top: 1.1rem; padding-top: 1rem; border-top: 1px solid var(--cor-linha); display: flex; flex-direction: column; gap: .2rem; }
.rastreio-codigo span { font-size: .76rem; color: var(--cor-texto-suave); }
.rastreio-codigo strong { font-size: .95rem; color: var(--cor-texto); letter-spacing: .03em; }
.aviso-cancelado { color: #dc2626; font-size: .85rem; margin: 1rem 0 0; }

.itens { list-style: none; margin: 0 0 .8rem; padding: 0; display: flex; flex-direction: column; gap: .6rem; }
.itens__linha { display: flex; justify-content: space-between; gap: 1rem; font-size: .85rem; color: var(--cor-texto); }
.itens__total { display: flex; justify-content: space-between; font-size: .82rem; color: var(--cor-texto-suave); padding-top: .6rem; border-top: 1px solid var(--cor-linha); }
.itens__total--principal { font-size: 1rem; color: var(--cor-texto); font-weight: 700; margin-top: .3rem; }

.endereco { font-size: .88rem; color: var(--cor-texto); line-height: 1.6; margin: 0; }
</style>
