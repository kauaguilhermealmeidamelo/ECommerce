<template>
  <div class="pagina">
    <div class="pagina__cabecalho">
      <div>
        <h1 class="pagina__titulo">Configurações</h1>
        <p class="pagina__subtitulo">Preferências da conta e da loja</p>
      </div>
    </div>

    <div class="abas">
      <button v-for="t in ['loja', 'vendas', 'pagamento', 'entrega', 'seguranca']" :key="t" class="abas__item"
        :class="{ 'abas__item--ativa': aba === t }" @click="aba = t">
        <v-icon
          :icon="{ loja: 'mdi-store', vendas: 'mdi-credit-card-outline', pagamento: 'mdi-bank-outline', entrega: 'mdi-truck-delivery-outline', seguranca: 'mdi-shield-lock-outline' }[t]"
          size="small" class="mr-1"></v-icon>
        {{ { loja: 'Loja', vendas: 'Vendas', pagamento: 'Pagamento', entrega: 'Entregas', seguranca: 'Segurança' }[t] }}
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
        <v-icon icon="mdi-content-save" size="small" class="mr-1"></v-icon>
        {{ salvandoLoja ? 'Salvando...' : 'Salvar dados da loja' }}
      </button>
    </section>

    <!-- Vendas -->
    <section v-if="aba === 'vendas'" class="card secao">
      <h3 class="secao__titulo">Regras de Venda</h3>
      <div class="opcao-toggle">
        <div>
          <strong>Produto expira após a venda</strong>
          <p>Ao ativar, assim que um produto é vendido ele sai automaticamente de venda — ideal pra peças únicas de
            brechó.</p>
        </div>
        <button class="interruptor" :class="{ 'interruptor--ligado': configLoja.produto_expira_apos_venda }"
          @click="configLoja.produto_expira_apos_venda = !configLoja.produto_expira_apos_venda"></button>
      </div>
      <button class="btn btn--primario" style="margin-top:1rem" :disabled="salvandoConfigLoja"
        @click="salvarConfigLoja">
        <v-icon icon="mdi-content-save" size="small" class="mr-1"></v-icon>
        {{ salvandoConfigLoja ? 'Salvando...' : 'Salvar' }}
      </button>
    </section>

    <!-- Pagamento (Mercado Pago) -->
    <section v-if="aba === 'pagamento'" style="display:flex; flex-direction:column; gap:1rem">
      <div class="card secao">
        <h3 class="secao__titulo">Mercado Pago</h3>

        <details class="tutorial">
          <summary><v-icon icon="mdi-book-open-page-variant-outline" size="small" class="mr-1"></v-icon> Como pegar
            minhas credenciais (passo a passo)</summary>

          <ol class="tutorial__lista">
            <li>
              <strong>Entre no painel de desenvolvedores</strong>
              <p>Acesse <a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank"
                  rel="noopener">mercadopago.com.br/developers/panel/app</a> logado com a conta que recebe os pagamentos
                da loja.</p>
            </li>
            <li>
              <strong>Abra (ou crie) sua aplicação</strong>
              <p>Em "Suas integrações", clique na aplicação dessa loja. Se ainda não existe nenhuma, crie uma primeiro —
                é obrigatório pra pegar Access Token e chave do webhook.</p>
            </li>
            <li>
              <strong>Copie o Access Token</strong>
              <p>Em "Credenciais de produção" (ou "de teste", enquanto estiver testando), copie o campo "Access Token" e
                cole no campo abaixo.</p>
            </li>
            <li>
              <strong>Configure o Webhook e copie a Chave secreta</strong>
              <p>No menu "Webhooks", cole a URL abaixo, ative o evento <strong>Pagamentos</strong> e salve. Só depois de
                salvar aparece o campo "Chave secreta" — copie e cole no campo de webhook abaixo.</p>
            </li>
          </ol>

          <p class="tutorial__nota">
            ⚠️ Credenciais de teste e de produção têm cada uma sua própria Chave secreta de webhook. Use a de teste
            enquanto desenvolve, e troque pra de produção só quando for para o ar de verdade.
          </p>
        </details>

        <div class="url-webhook">
          <span class="campos__label">URL do Webhook (cole essa no painel do Mercado Pago)</span>
          <div class="url-webhook__linha">
            <code>{{ urlWebhook }}</code>
            <button class="btn btn--secundario" @click="copiarUrlWebhook">
              <v-icon icon="mdi-content-copy" size="small" class="mr-1"></v-icon> Copiar
            </button>
          </div>
        </div>

        <div class="campos" style="margin-top:1rem">
          <label>
            <span class="campos__label">
              Access Token
              <span v-if="pagamento.access_token_configurado" class="selo-conectado">✓ Configurado</span>
            </span>
            <input v-model="pagamento.access_token" type="password"
              :placeholder="pagamento.access_token_configurado ? '•••••••••••••••• (cole um novo pra trocar)' : 'APP_USR-...'" />
          </label>

          <label>
            <span class="campos__label">
              Chave secreta do Webhook
              <span v-if="pagamento.webhook_secret_configurado" class="selo-conectado">✓ Configurado</span>
            </span>
            <input v-model="pagamento.webhook_secret" type="password"
              :placeholder="pagamento.webhook_secret_configurado ? '•••••••••••••••• (cole uma nova pra trocar)' : 'Cole aqui a chave secreta'" />
          </label>
        </div>

        <p v-if="!pagamento.access_token_configurado" class="aviso-cadastro aviso-cadastro--alerta"
          style="margin-top:.9rem">
          ⚠️ Sem o Access Token configurado, o checkout da loja fica indisponível — os clientes não conseguem finalizar
          compras.
        </p>
      </div>

      <button class="btn btn--primario" :disabled="salvandoPagamento" @click="salvarPagamento">
        <v-icon icon="mdi-content-save" size="small" class="mr-1"></v-icon>
        {{ salvandoPagamento ? 'Salvando...' : 'Salvar Configurações de Pagamento' }}
      </button>
    </section>

    <!-- Entregas -->
    <section v-if="aba === 'entrega'" style="display:flex; flex-direction:column; gap:1rem">
      <div class="card secao">
        <h3 class="secao__titulo">Métodos de Entrega</h3>
        <div class="opcao-toggle">
          <div><strong>Retirada na loja</strong>
            <p>Cliente busca o pedido no endereço da loja, sem frete.</p>
          </div>
          <button class="interruptor" :class="{ 'interruptor--ligado': entrega.retirada_ativa }"
            @click="entrega.retirada_ativa = !entrega.retirada_ativa"></button>
        </div>
        <div class="opcao-toggle">
          <div><strong>Entrega local</strong>
            <p>Motoboy próprio, com valor fixo por faixa de CEP.</p>
          </div>
          <button class="interruptor" :class="{ 'interruptor--ligado': entrega.entrega_local_ativa }"
            @click="entrega.entrega_local_ativa = !entrega.entrega_local_ativa"></button>
        </div>
        <div class="opcao-toggle">
          <div><strong>Transportadora</strong>
            <p>Frete calculado por CEP via Melhor Envio.</p>
          </div>
          <button class="interruptor" :class="{ 'interruptor--ligado': entrega.transportadora_ativa }"
            @click="entrega.transportadora_ativa = !entrega.transportadora_ativa"></button>
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
        <p class="aviso-cadastro">Conecte sua conta do Melhor Envio pra habilitar cotação automática de frete. O token
          fica salvo com criptografia.</p>
        <label style="display:block; margin-top:.9rem">
          <span class="campos__label">Token de API</span>
          <input v-model="entrega.token_melhor_envio" type="password" placeholder="Cole aqui o token" />
        </label>
      </div>

      <button class="btn btn--primario" :disabled="salvandoEntrega" @click="salvarEntrega">
        <v-icon icon="mdi-content-save" size="small" class="mr-1"></v-icon>
        {{ salvandoEntrega ? 'Salvando...' : 'Salvar configurações de entrega' }}
      </button>
    </section>

    <!-- Segurança e Notificações -->
    <section v-if="aba === 'seguranca'" style="display:flex; flex-direction:column; gap:1rem">
      <div class="card secao">
        <h3 class="secao__titulo">🔒 Segurança e Notificações</h3>

        <div class="opcao-toggle">
          <div>
            <strong>Notificações por e-mail</strong>
            <p>Alertas de novos pedidos e estoque baixo</p>
          </div>
          <button class="interruptor" :class="{ 'interruptor--ligado': seguranca.notificacoes_email }"
            @click="seguranca.notificacoes_email = !seguranca.notificacoes_email"></button>
        </div>

        <div class="opcao-toggle">
          <div>
            <strong>Autenticação em 2 fatores</strong>
            <p>Camada extra de segurança no login — um código de 6 dígitos é enviado por e-mail a cada acesso do admin.
            </p>
          </div>
          <button class="interruptor" :class="{ 'interruptor--ligado': seguranca.autenticacao_dois_fatores }"
            @click="seguranca.autenticacao_dois_fatores = !seguranca.autenticacao_dois_fatores"></button>
        </div>

        <div class="opcao-toggle">
          <div>
            <strong>Modo manutenção</strong>
            <p>Exibe página de manutenção para visitantes — o painel administrativo continua acessível normalmente.</p>
          </div>
          <button class="interruptor" :class="{ 'interruptor--ligado': seguranca.modo_manutencao }"
            @click="seguranca.modo_manutencao = !seguranca.modo_manutencao"></button>
        </div>

        <p v-if="seguranca.modo_manutencao" class="aviso-cadastro aviso-cadastro--alerta">
          ⚠️ Com o modo manutenção ativo, a loja pública (storefront) para de responder pedidos e visitas até você
          desligar aqui.
        </p>
      </div>

      <button class="btn btn--primario" :disabled="salvandoSeguranca" @click="salvarSeguranca">
        <v-icon icon="mdi-content-save" size="small" class="mr-1"></v-icon>
        {{ salvandoSeguranca ? 'Salvando...' : 'Salvar Configurações' }}
      </button>
    </section>

    <Toast :mensagem="toastMsg" :tipo="toastTipo" @fechar="toastMsg = ''" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
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

// Pagamento (Mercado Pago)
const pagamento = ref({ access_token: '', webhook_secret: '', access_token_configurado: false, webhook_secret_configurado: false })
const salvandoPagamento = ref(false)

const urlWebhook = computed(() => {
  const base = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  return base.replace(/\/api\/?$/, '') + '/api/webhooks/mercadopago'
})

function copiarUrlWebhook() {
  navigator.clipboard.writeText(urlWebhook.value)
  avisar('URL do webhook copiada.', 'info')
}

async function carregarPagamento() {
  try {
    const { data } = await api.get('/admin/configuracoes-pagamento')
    pagamento.value.access_token_configurado = data.data.access_token_configurado
    pagamento.value.webhook_secret_configurado = data.data.webhook_secret_configurado
  } catch (e) { /* segue com os padrões */ }
}
async function salvarPagamento() {
  salvandoPagamento.value = true
  try {
    const { data } = await api.put('/admin/configuracoes-pagamento', {
      access_token: pagamento.value.access_token || null,
      webhook_secret: pagamento.value.webhook_secret || null,
    })
    pagamento.value.access_token_configurado = data.data.access_token_configurado
    pagamento.value.webhook_secret_configurado = data.data.webhook_secret_configurado
    pagamento.value.access_token = ''
    pagamento.value.webhook_secret = ''
    avisar('Configurações de pagamento salvas.')
  } catch (e) {
    avisar('Não foi possível salvar agora.', 'error')
  } finally {
    salvandoPagamento.value = false
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
  } catch (e) { /* segue com os padrões */ }
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

// Segurança e Notificações
const seguranca = ref({ notificacoes_email: true, autenticacao_dois_fatores: false, modo_manutencao: false })
const salvandoSeguranca = ref(false)

async function carregarSeguranca() {
  try {
    const { data } = await api.get('/admin/configuracoes-seguranca')
    seguranca.value = { ...seguranca.value, ...data.data }
  } catch (e) { /* segue com os padrões */ }
}
async function salvarSeguranca() {
  salvandoSeguranca.value = true
  try {
    const { data } = await api.put('/admin/configuracoes-seguranca', seguranca.value)
    seguranca.value = { ...seguranca.value, ...data.data }
    avisar('Configurações de segurança salvas.')
  } catch (e) {
    avisar('Não foi possível salvar agora.', 'error')
  } finally {
    salvandoSeguranca.value = false
  }
}

onMounted(() => {
  carregarLoja()
  carregarConfigLoja()
  carregarPagamento()
  carregarEntrega()
  carregarSeguranca()
})
</script>

<style scoped>
.abas {
  display: flex;
  gap: .3rem;
  background: #fff;
  padding: .3rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--line);
  width: fit-content;
  overflow-x: auto;
}

.abas__item {
  padding: .5rem 1rem;
  border-radius: var(--radius-sm);
  border: none;
  background: transparent;
  font-size: .8rem;
  font-weight: 700;
  color: var(--ink-soft);
  white-space: nowrap;
  cursor: pointer;
  display: flex;
  align-items: center;
}

.abas__item--ativa {
  background: var(--blue-600);
  color: #fff;
}

.secao {
  padding: 1.1rem 1.2rem;
  max-width: 620px;
}

.secao__titulo {
  font-size: .92rem;
  font-weight: 700;
  margin-bottom: .9rem;
}

.secao__cabecalho {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: .9rem;
}

.campos {
  display: flex;
  flex-direction: column;
  gap: .8rem;
}

.campos__linha {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
  gap: .6rem;
}

.campos label,
.campos__label {
  display: flex;
  flex-direction: column;
  gap: .3rem;
  font-size: .78rem;
  color: var(--ink-soft);
  font-weight: 600;
}

.campos__label {
  flex-direction: row !important;
  align-items: center;
  gap: .5rem !important;
}

.selo-conectado {
  font-size: .68rem;
  font-weight: 700;
  color: var(--success);
  background: var(--success-bg);
  padding: .1rem .5rem;
  border-radius: 999px;
}

.opcao-toggle {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding: .7rem 0;
  border-bottom: 1px solid var(--line);
}

.opcao-toggle:last-of-type {
  border-bottom: none;
}

.opcao-toggle strong {
  font-size: .85rem;
  color: var(--ink);
}

.opcao-toggle p {
  font-size: .75rem;
  color: var(--ink-soft);
  margin: .2rem 0 0;
}

.zona {
  border-top: 1px solid var(--line);
  padding-top: .9rem;
  margin-top: .9rem;
}

.aviso-cadastro {
  background: var(--blue-50);
  color: var(--blue-700);
  font-size: .78rem;
  padding: .75rem 1rem;
  border-radius: var(--radius-md);
  margin: 0;
}

.aviso-cadastro--alerta {
  background: var(--warning-bg);
  color: var(--warning);
  margin-top: .9rem;
}

.tutorial {
  border: 1px solid var(--line);
  border-radius: var(--radius-md);
  padding: .75rem 1rem;
  background: #fafbfc;
}

.tutorial summary {
  cursor: pointer;
  font-size: .82rem;
  font-weight: 700;
  color: var(--ink);
  display: flex;
  align-items: center;
}

.tutorial__lista {
  margin: .9rem 0 0;
  padding-left: 1.2rem;
  display: flex;
  flex-direction: column;
  gap: .7rem;
}

.tutorial__lista li strong {
  font-size: .8rem;
  color: var(--ink);
}

.tutorial__lista li p {
  font-size: .78rem;
  color: var(--ink-soft);
  margin: .2rem 0 0;
}

.tutorial__lista a {
  color: var(--blue-600);
  text-decoration: underline;
}

.tutorial__nota {
  font-size: .74rem;
  color: var(--warning);
  background: var(--warning-bg);
  padding: .6rem .75rem;
  border-radius: var(--radius-sm);
  margin: .9rem 0 0;
}

.url-webhook {
  margin-top: .9rem;
}

.url-webhook__linha {
  display: flex;
  gap: .5rem;
  align-items: center;
  margin-top: .35rem;
}

.url-webhook__linha code {
  flex: 1;
  background: #f3f4f6;
  border: 1px solid var(--line-strong);
  border-radius: var(--radius-sm);
  padding: .55rem .7rem;
  font-size: .78rem;
  color: var(--ink);
  overflow-x: auto;
  white-space: nowrap;
}
</style>