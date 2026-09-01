import { defineStore } from 'pinia'
import { ref } from 'vue'
import apiLoja from '@/services/apiLoja'

export const useLojaStore = defineStore('loja', () => {
  // Tenta carregar do localStorage para exibir instantaneamente no reload
  const dadosLoja = ref(JSON.parse(localStorage.getItem('dados_loja')) || null)

  async function carregarLoja() {
    try {
      const { data } = await apiLoja.get('/loja')
      dadosLoja.value = data.data
      // Salva no localStorage para manter cacheado nas próximas recargas
      localStorage.setItem('dados_loja', JSON.stringify(data.data))
    } catch (e) {
      console.error("Erro ao carregar dados da loja", e)
    }
  }

  return { dadosLoja, carregarLoja }
})