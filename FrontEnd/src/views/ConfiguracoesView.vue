<template>
  <div class="pagina">
    <div class="pagina__cabecalho">
      <div>
        <h1 class="pagina__titulo">Configurações</h1>
        <p class="pagina__subtitulo">Preferências da conta e da loja</p>
      </div>
    </div>

    <div class="abas">
      <button v-for="t in ['loja', 'vendas', 'entrega']" :key="t" class="abas__item" :class="{ 'abas__item--ativa': aba === t }" @click="aba = t">
        {{ { loja: '🏬 Loja', vendas: '💳 Vendas', entrega: '🚚 Entregas' }[t] }}
      </button>
    </div>

    <!-- Loja -->
    <section v-if="aba === 'loja'" class="card secao">
      <h3 class="secao__titulo">Informações da Loja</h3>
      <div class="campos">
        <label>Nome da loja<input v-model="loja.nome" /></label>
        <label>E-mail de contato<input v-model="loja.email_contato" type="email" /></label>
        <label>Telefone<input v-model="loja.telefone" /></label>
        <div class="campos__linha">
          <label>CEP<input v-model="loja.cep" /></label>
          <label>Número<input v-model="loja.numero" /></label>
        </div>
        <label>Endereço<input v-model="loja.endereco" /></label>
        <div class="campos__linha">
          <label>Bairro<input v-model="loja.bairro" /></label>
          <label>Cidade<input v-model="loja.cidade" /></label>
          <label>UF<input v-model="loja.uf" maxlength="2" /></label>
        </div>
      </div>
      <button class="btn btn--primario" style="margin-top:1rem" :disabled="salvandoLoja" @click="salvarLoja">
        {{ salvandoLoja ? 'Salvando...' : '💾 Salvar dados da loja' }}
      </button>
    </section>

    <!-- Vendas -->
    <section v-if="aba === 'vendas'" class="card secao">
      <h3 class="secao__titulo">Regras de Venda</h3>
      <div class="opcao-toggle">
        <div>
          <strong>Produto expira após a venda</strong>
          <p>Ao ativar, assim que um produto é vendido ele sai automaticamente de venda — ideal pra peças únicas de brechó.</p>
        </div>
        <button class="interruptor" :class="{ 'interruptor--ligado': configLoja.produto_expira_apos_venda }" @click="configLoja.produto_expira_apos_venda = !configLoja.produto_expira_apos_venda"></button>
      </div>
      <button class="btn btn--primario" style="margin-top:1rem" :disabled="salvandoConfigLoja" @click="salvarConfigLoja">
        {{ salvandoConfigLoja ? 'Salvando...' : '💾 Salvar' }}
      </button>
    </section>

    <!-- Entregas -->
    <section v-if="aba === 'entrega'" style="display:flex; flex-direction:column; gap:1rem">
      <div class="card secao">
        <h3 class="secao__titulo">Métodos de Entrega</h3>
        <div class="opcao-toggle">
          <div><strong>Retirada na loja</strong><p>Cliente busca o pedido no endereço da loja, sem frete.</p></div>
          <button class="interruptor" :class="{ 'interruptor--ligado': entrega.retirada_ativa }" @click="entrega.retirada_ativa = !entrega.retirada_ativa"></button>
        </div>
        <div class="opcao-toggle">
          <div><strong>Entrega local</strong><p>Motoboy próprio, com valor fixo por faixa de CEP.</p></div>
          <button class="interruptor" :class="{ 'interruptor--ligado': entrega.entrega_local_ativa }" @click="entrega.entrega_local_ativa = !entrega.entrega_local_ativa"></button>
        </div>
        <div class="opcao-toggle">
          <div><strong>Transportadora</strong><p>Frete calculado por CEP via Melhor Envio.</p></div>
          <button class="interruptor" :class="{ 'interruptor--ligado': entrega.transportadora_ativa }" @click="entrega.transportadora_ativa = !entrega.transportadora_ativa"></button>
        </div>
      </div>

      <div v-if="entrega.entrega_local_ativa" class="card secao">
        <div class="secao__cabecalho">
          <h3 class="secao__titulo">Zonas de Entrega Local</h3>
          <button class="btn btn--secundario" @click="novaZona">+ Zona</button>
        </div>
        <div v-for="(zona, i) in zonas" :key="i" class="zona">
          <div class="campos__linha">
            <label>CEP inicial<input v-model="zona.cep_inicial" placeholder="00000-000" /></label>
            <label>CEP final<input v-model="zona.cep_final" placeholder="00000-000" /></label>
          </div>
          <div class="campos__linha">
            <label>Valor (R$)<input v-model.number="zona.valor" type="number" step="0.01" /></label>
            <label>Prazo (dias)<input v-model.number="zona.prazo_dias" type="number" /></label>
          </div>
          <button class="btn btn--perigo" style="margin-top:.5rem" @click="zonas.splice(i, 1)">Remover zona</button>
        </div>
        <p v-if="zonas.length === 0" class="estado-vazio" style="padding:1rem">Nenhuma zona cadastrada.</p>
      </div>

      <div v-if="entrega.transportadora_ativa" class="card secao">
        <h3 class="secao__titulo">Transportadora (Melhor Envio)</h3>
        <p class="aviso-cadastro">Conecte sua conta do Melhor Envio pra habilitar cotação automática de frete. O token fica salvo com criptografia.</p>
        <label style="display:block; margin-top:.9rem">
          <span class="campos__label">Token de API</span>
          <input v-model="entrega.token_melhor_envio" type="password" placeholder="Cole aqui o token" />
        </label>
      </div>

      <button class="btn btn--primario" :disabled="salvandoEntrega" @click="salvarEntrega">
        {{ salvandoEntrega ? 'Salvando...' : '💾 Salvar configurações de entrega' }}
      </button>
    </section>

    <Toast :mensagem="toastMsg" :tipo="toastTipo" @fechar="toastMsg = ''" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import Toast from '@/components/Toast.vue'

const aba = ref('loja')

const toastMsg = ref('')
const toastTipo = ref('success')
function avisar(msg, tipo = 'success') { toastTipo.value = tipo; toastMsg.value = msg }

// Loja
const loja = ref({ nome: '', telefone: '', email_contato: '', cep: '', endereco: '', numero: '', bairro: '', cidade: '', uf: '' })
const salvandoLoja = ref(false)

async function carregarLoja() {
  try {
    const { data } = await api.get('/admin/loja')
    loja.value = { ...loja.value, ...data.data }
  } catch (e) { /* primeira execução, ainda sem registro */ }
}
async function salvarLoja() {
  salvandoLoja.value = true
  try {
    await api.put('/admin/loja', loja.value)
    avisar('Dados da loja salvos.')
  } catch (e) {
    avisar('Não foi possível salvar os dados da loja.', 'error')
  } finally {
    salvandoLoja.value = false
  }
}

// Vendas
const configLoja = ref({ produto_expira_apos_venda: false })
const salvandoConfigLoja = ref(false)

async function carregarConfigLoja() {
  try {
    const { data } = await api.get('/admin/configuracoes-loja')
    configLoja.value = { ...configLoja.value, ...data.data }
  } catch (e) { /* segue com o padrão */ }
}
async function salvarConfigLoja() {
  salvandoConfigLoja.value = true
  try {
    await api.put('/admin/configuracoes-loja', configLoja.value)
    avisar('Configuração de vendas salva.')
  } catch (e) {
    avisar('Não foi possível salvar essa configuração.', 'error')
  } finally {
    salvandoConfigLoja.value = false
  }
}

// Entrega
const entrega = ref({ retirada_ativa: true, entrega_local_ativa: false, transportadora_ativa: false, token_melhor_envio: '' })
const zonas = ref([])
const salvandoEntrega = ref(false)

function novaZona() { zonas.value.push({ cep_inicial: '', cep_final: '', valor: 0, prazo_dias: 1 }) }

async function carregarEntrega() {
  try {
    const { data } = await api.get('/admin/entregas/configuracao')
    entrega.value = { ...entrega.value, ...data.data.config }
    zonas.value = data.data.zonas ?? []
  } catch (e) { /* endpoint ainda sem dados — segue com os padrões */ }
}
async function salvarEntrega() {
  salvandoEntrega.value = true
  try {
    await api.put('/admin/entregas/configuracao', { config: entrega.value, zonas: zonas.value })
    avisar('Configurações de entrega salvas.')
  } catch (e) {
    avisar('Não foi possível salvar agora.', 'error')
  } finally {
    salvandoEntrega.value = false
  }
}

onMounted(() => {
  carregarLoja()
  carregarConfigLoja()
  carregarEntrega()
})
</script>

<style scoped>
.abas { display: flex; gap: .3rem; background: #fff; padding: .3rem; border-radius: var(--radius-md); border: 1px solid var(--line); width: fit-content; overflow-x: auto; }
.abas__item { padding: .5rem 1rem; border-radius: var(--radius-sm); border: none; background: transparent; font-size: .8rem; font-weight: 700; color: var(--ink-soft); white-space: nowrap; }
.abas__item--ativa { background: var(--blue-600); color: #fff; }

.secao { padding: 1.1rem 1.2rem; max-width: 620px; }
.secao__titulo { font-size: .92rem; font-weight: 700; margin-bottom: .9rem; }
.secao__cabecalho { display: flex; justify-content: space-between; align-items: center; margin-bottom: .9rem; }

.campos { display: flex; flex-direction: column; gap: .8rem; }
.campos__linha { display: grid; grid-template-columns: repeat(auto-fit, minmax(90px, 1fr)); gap: .6rem; }
.campos label, .campos__label { display: flex; flex-direction: column; gap: .3rem; font-size: .78rem; color: var(--ink-soft); font-weight: 600; }

.opcao-toggle { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; padding: .7rem 0; border-bottom: 1px solid var(--line); }
.opcao-toggle:last-of-type { border-bottom: none; }
.opcao-toggle strong { font-size: .85rem; color: var(--ink); }
.opcao-toggle p { font-size: .75rem; color: var(--ink-soft); margin: .2rem 0 0; }

.zona { border-top: 1px solid var(--line); padding-top: .9rem; margin-top: .9rem; }
.aviso-cadastro { background: var(--blue-50); color: var(--blue-700); font-size: .78rem; padding: .75rem 1rem; border-radius: var(--radius-md); margin: 0; }
</style>