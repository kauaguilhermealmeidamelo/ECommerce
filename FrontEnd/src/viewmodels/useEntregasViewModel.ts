import { ref, onMounted } from 'vue'
import api from '@/services/api'

interface Zona {
  cep_inicial: string
  cep_final: string
  valor: number
  prazo_dias: number
}

interface Loja {
  nome: string
  telefone: string
  email_contato: string
  cep: string
  endereco: string
  numero: string
  bairro: string
  cidade: string
  uf: string
}

interface ConfigEntrega {
  retirada_ativa: boolean
  entrega_local_ativa: boolean
  transportadora_ativa: boolean
  token_melhor_envio: string
}

export function useEntregasViewModel() {
  const config = ref<ConfigEntrega>({
    retirada_ativa: true,
    entrega_local_ativa: false,
    transportadora_ativa: false,
    token_melhor_envio: '',
  })

  const zonas = ref<Zona[]>([])
  const salvando = ref(false)
  const mensagem = ref<string | null>(null)

  const loja = ref<Loja>({
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

  const configLoja = ref({ produto_expira_apos_venda: false })
  const salvandoConfigLoja = ref(false)

  async function carregarLoja() {
    try {
      const { data } = await api.get('/admin/loja')
      loja.value = { ...loja.value, ...data.data }
    } catch {
      // primeira execução, ainda sem registro
    }
  }

  async function salvarLoja() {
    salvandoLoja.value = true
    try {
      await api.put('/admin/loja', loja.value)
      mensagem.value = 'Dados da loja salvos.'
    } catch {
      mensagem.value = 'Não foi possível salvar os dados da loja.'
    } finally {
      salvandoLoja.value = false
    }
  }

  async function carregarConfigLoja() {
    try {
      const { data } = await api.get('/admin/configuracoes-loja')
      configLoja.value = { ...configLoja.value, ...data.data }
    } catch {
      // segue com o padrão
    }
  }

  async function salvarConfigLoja() {
    salvandoConfigLoja.value = true
    try {
      await api.put('/admin/configuracoes-loja', configLoja.value)
      mensagem.value = 'Configuração de vendas salva.'
    } catch {
      mensagem.value = 'Não foi possível salvar essa configuração.'
    } finally {
      salvandoConfigLoja.value = false
    }
  }

  function novaZona() {
    zonas.value.push({ cep_inicial: '', cep_final: '', valor: 0, prazo_dias: 1 })
  }

  async function carregar() {
    try {
      const { data } = await api.get('/admin/entregas/configuracao')
      config.value = { ...config.value, ...data.data.config }
      zonas.value = data.data.zonas ?? []
    } catch {
      // Endpoint ainda não implementado no backend
    }
  }

  async function salvar() {
    salvando.value = true
    mensagem.value = null

    try {
      await api.put('/admin/entregas/configuracao', { config: config.value, zonas: zonas.value })
      mensagem.value = 'Configurações salvas.'
    } catch {
      mensagem.value = 'Não foi possível salvar agora.'
    } finally {
      salvando.value = false
    }
  }

  onMounted(() => {
    carregar()
    carregarLoja()
    carregarConfigLoja()
  })

  return {
    config,
    zonas,
    salvando,
    mensagem,
    loja,
    salvandoLoja,
    configLoja,
    salvandoConfigLoja,
    salvarLoja,
    salvarConfigLoja,
    novaZona,
    salvar,
  }
}