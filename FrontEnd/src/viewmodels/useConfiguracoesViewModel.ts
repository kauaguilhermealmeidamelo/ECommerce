import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

export type AbaType = 'loja' | 'vendas' | 'pagamento' | 'entrega' | 'seguranca'

export interface LojaData {
  nome: string
  telefone: string
  email_contato: string
  cep: string
  endereco: string
  numero: string
  bairro: string
  cidade: string
  uf: string
  [key: string]: any
}

export interface Zona {
  cep_inicial: string
  cep_final: string
  valor: number
  prazo_dias: number
}

export interface EntregaConfig {
  retirada_ativa: boolean
  entrega_local_ativa: boolean
  transportadora_ativa: boolean
  token_melhor_envio: string
  [key: string]: any
}

export function useConfiguracoesViewModel() {
  const aba = ref<AbaType>('loja')

  const toastMsg = ref('')
  const toastTipo = ref<'success' | 'error' | 'info'>('success')

  function avisar(msg: string, tipo: 'success' | 'error' | 'info' = 'success') {
    toastTipo.value = tipo
    toastMsg.value = msg
  }

  // Loja
  const loja = ref<LojaData>({
    nome: '',
    telefone: '',
    email_contato: '',
    cep: '',
    endereco: '',
    numero: '',
    bairro: '',
    cidade: '',
    uf: '',
  })
  const salvandoLoja = ref(false)

  async function carregarLoja() {
    try {
      const { data } = await api.get('/admin/loja')
      loja.value = { ...loja.value, ...data.data }
    } catch {
      /* primeira execução */
    }
  }

  async function salvarLoja() {
    salvandoLoja.value = true
    try {
      await api.put('/admin/loja', loja.value)
      avisar('Dados da loja salvos.')
    } catch {
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
    } catch {
      /* padrão */
    }
  }

  async function salvarConfigLoja() {
    salvandoConfigLoja.value = true
    try {
      await api.put('/admin/configuracoes-loja', configLoja.value)
      avisar('Configuração de vendas salva.')
    } catch {
      avisar('Não foi possível salvar essa configuração.', 'error')
    } finally {
      salvandoConfigLoja.value = false
    }
  }

  // Pagamento (Mercado Pago)
  const pagamento = ref({
    access_token: '',
    webhook_secret: '',
    access_token_configurado: false,
    webhook_secret_configurado: false,
  })
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
    } catch {
      /* padrão */
    }
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
    } catch {
      avisar('Não foi possível salvar agora.', 'error')
    } finally {
      salvandoPagamento.value = false
    }
  }

  // Entrega
  const entrega = ref<EntregaConfig>({
    retirada_ativa: true,
    entrega_local_ativa: false,
    transportadora_ativa: false,
    token_melhor_envio: '',
  })
  const zonas = ref<Zona[]>([])
  const salvandoEntrega = ref(false)

  function novaZona() {
    zonas.value.push({ cep_inicial: '', cep_final: '', valor: 0, prazo_dias: 1 })
  }

  async function carregarEntrega() {
    try {
      const { data } = await api.get('/admin/entregas/configuracao')
      entrega.value = { ...entrega.value, ...data.data.config }
      zonas.value = data.data.zonas ?? []
    } catch {
      /* padrão */
    }
  }

  async function salvarEntrega() {
    salvandoEntrega.value = true
    try {
      await api.put('/admin/entregas/configuracao', { config: entrega.value, zonas: zonas.value })
      avisar('Configurações de entrega salvas.')
    } catch {
      avisar('Não foi possível salvar agora.', 'error')
    } finally {
      salvandoEntrega.value = false
    }
  }

  // Segurança e Notificações
  const seguranca = ref({
    notificacoes_email: true,
    autenticacao_dois_fatores: false,
    modo_manutencao: false,
  })
  const salvandoSeguranca = ref(false)

  async function carregarSeguranca() {
    try {
      const { data } = await api.get('/admin/configuracoes-seguranca')
      seguranca.value = { ...seguranca.value, ...data.data }
    } catch {
      /* padrão */
    }
  }

  async function salvarSeguranca() {
    salvandoSeguranca.value = true
    try {
      const { data } = await api.put('/admin/configuracoes-seguranca', seguranca.value)
      seguranca.value = { ...seguranca.value, ...data.data }
      avisar('Configurações de segurança salvas.')
    } catch {
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

  return {
    aba,
    toastMsg,
    toastTipo,
    loja,
    salvandoLoja,
    configLoja,
    salvandoConfigLoja,
    pagamento,
    salvandoPagamento,
    urlWebhook,
    entrega,
    zonas,
    salvandoEntrega,
    seguranca,
    salvandoSeguranca,
    salvarLoja,
    salvarConfigLoja,
    copiarUrlWebhook,
    salvarPagamento,
    novaZona,
    salvarEntrega,
    salvarSeguranca,
  }
}