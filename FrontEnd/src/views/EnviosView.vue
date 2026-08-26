<template>
  <div class="pagina">
    <h1 class="font-display pagina__titulo">Envios</h1>
    <p class="pagina__subtitulo">Pedidos pagos aguardando postagem.</p>

    <div v-if="carregando">Carregando...</div>
    <div v-else-if="erro" class="pagina__erro">{{ erro }}</div>
    <div v-else-if="pedidos.length === 0" class="vazio">Nenhum pedido aguardando envio no momento.</div>

    <ul v-else class="lista">
      <li v-for="pedido in pedidos" :key="pedido.id" class="pedido">
        <div class="pedido__cabecalho">
          <strong>Pedido #{{ pedido.id }}</strong>
          <span class="badge">{{ pedido.metodo_entrega === 'local' ? 'Entrega local' : 'Transportadora' }}</span>
        </div>

        <div class="pedido__itens">
          <span v-for="(item, i) in pedido.itens" :key="i">{{ item.quantidade }}x {{ item.produto }}</span>
        </div>

        <div class="endereco">
          <strong>{{ pedido.destinatario.nome }}</strong>
          <span>{{ pedido.destinatario.endereco }}, {{ pedido.destinatario.numero }}</span>
          <span v-if="pedido.destinatario.complemento">{{ pedido.destinatario.complemento }}</span>
          <span>{{ pedido.destinatario.bairro }} — {{ pedido.destinatario.cidade }}/{{ pedido.destinatario.uf }}</span>
          <span class="endereco__cep">CEP {{ pedido.destinatario.cep }}</span>
        </div>

        <button class="btn btn--secundario" @click="copiarEndereco(pedido)">Copiar endereço</button>

        <details class="marcar-enviado">
          <summary>Marcar como enviado</summary>
          <div class="marcar-enviado__form">
            <label>
              Transportadora
              <input v-model="formEnvio[pedido.id].transportadora" placeholder="Correios, Jadlog..." />
            </label>
            <label>
              Código de rastreio
              <input v-model="formEnvio[pedido.id].codigo_rastreio" placeholder="BR123456789" />
            </label>
            <button class="btn btn--primario" @click="marcarEnviado(pedido)" :disabled="enviando === pedido.id">
              {{ enviando === pedido.id ? 'Salvando...' : 'Confirmar envio' }}
            </button>
          </div>
        </details>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'

const pedidos = ref([])
const carregando = ref(true)
const erro = ref(null)
const enviando = ref(null)
const formEnvio = reactive({})

async function carregar() {
  try {
    const { data } = await api.get('/admin/envios/pendentes')
    pedidos.value = data.data
    pedidos.value.forEach((p) => {
      formEnvio[p.id] = { transportadora: '', codigo_rastreio: '' }
    })
  } catch (e) {
    erro.value = 'Não foi possível carregar os envios.'
  } finally {
    carregando.value = false
  }
}

function copiarEndereco(pedido) {
  const d = pedido.destinatario
  const texto = `${d.nome}\n${d.endereco}, ${d.numero} ${d.complemento ?? ''}\n${d.bairro} - ${d.cidade}/${d.uf}\nCEP: ${d.cep}`
  navigator.clipboard.writeText(texto)
}

async function marcarEnviado(pedido) {
  enviando.value = pedido.id
  try {
    await api.patch(`/admin/envios/${pedido.id}/marcar-enviado`, formEnvio[pedido.id])
    pedidos.value = pedidos.value.filter((p) => p.id !== pedido.id)
  } catch (e) {
    erro.value = 'Não foi possível confirmar o envio.'
  } finally {
    enviando.value = null
  }
}

onMounted(carregar)
</script>

<style scoped>
.lista { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 1rem; }
.pedido { border: 1px solid var(--line); border-radius: 14px; padding: 1rem; }
.pedido__cabecalho { display: flex; justify-content: space-between; align-items: center; margin-bottom: .5rem; }
.badge { font-size: .72rem; font-weight: 600; padding: .2rem .55rem; border-radius: 999px; background: var(--icon-bg); color: var(--navy); }
.pedido__itens { display: flex; flex-direction: column; font-size: .85rem; color: var(--ink-soft); margin-bottom: .75rem; }
.endereco { display: flex; flex-direction: column; font-size: .88rem; gap: .1rem; background: #fafafa; border-radius: 8px; padding: .7rem; margin-bottom: .75rem; }
.endereco__cep { font-weight: 600; margin-top: .2rem; }
.marcar-enviado { margin-top: .75rem; font-size: .85rem; }
.marcar-enviado summary { cursor: pointer; color: var(--navy); font-weight: 600; }
.marcar-enviado__form { display: flex; flex-direction: column; gap: .6rem; margin-top: .75rem; }
.marcar-enviado__form label { display: flex; flex-direction: column; gap: .3rem; font-size: .8rem; color: var(--ink-soft); }
.vazio { color: var(--ink-soft); text-align: center; padding: 2rem 0; }
.pagina__erro { color: var(--danger); }
</style>
