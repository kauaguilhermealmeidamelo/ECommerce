import { reactive } from 'vue'
import apiLoja from '@/services/apiLoja'

export const temaAtual = {
  nome: 'default-admin',
  modo: 'light',
}

export const tema = reactive({
  nome: 'Nome da Loja',
  logo: '',
  banner: '',

  cores: {
    fundo: '#faf8f4',
    superficie: '#ffffff',
    texto: '#211d1a',
    textoSuave: '#7a7266',
    primaria: '#a4462f',   // botões, links, destaques
    primariaHover: '#8a3a27',
    linha: '#eae4d9',
  },

  tipografia: {
    display: "'Fraunces', serif",   // títulos
    corpo: "'Inter', sans-serif",   // texto e UI
  },

  layout: {
    raioBorda: '10px',
    larguraMaxima: '1100px',
  },

})

export async function carregarTemaDaLoja(): Promise<void> {
  const { data } = await apiLoja.get('/loja')
  const loja = data.data

  tema.nome = loja?.nome || 'Nome da Loja'
  tema.logo = loja?.logo_url || ''
  tema.banner = loja?.banner_url || ''
}
