import { defineStore } from 'pinia'
import apiLoja from '@/services/apiLoja'

interface ItemCarrinhoResumo {
  quantidade: number
  [key: string]: any
}

interface CarrinhoState {
  quantidadeItens: number
  carregandoQuantidade: boolean
  toastMsg: string
  toastTipo: 'success' | 'info' | 'error'
}

export const useCarrinhoStore = defineStore('carrinho', {
  state: (): CarrinhoState => ({
    quantidadeItens: 0,
    carregandoQuantidade: false,
    toastMsg: '',
    toastTipo: 'success',
  }),

  actions: {
    avisar(mensagem: string, tipo: 'success' | 'info' | 'error' = 'success') {
      this.toastTipo = tipo
      this.toastMsg = mensagem
    },

    limparAviso() {
      this.toastMsg = ''
    },

    async carregarQuantidade(): Promise<void> {
      this.carregandoQuantidade = true
      try {
        const { data } = await apiLoja.get('/carrinho')
        const itens: ItemCarrinhoResumo[] = data.data?.itens ?? []
        this.quantidadeItens = itens.reduce((soma, item) => soma + Number(item.quantidade ?? 0), 0)
      } catch {
        this.quantidadeItens = 0
      } finally {
        this.carregandoQuantidade = false
      }
    },

    /**
     * Único ponto de entrada pra adicionar item ao carrinho — sempre
     * atualiza o contador do cabeçalho e avisa o resultado (sucesso ou
     * erro) via toast. Nenhuma tela deve chamar apiLoja.post direto.
     */
    async adicionarItem(produtoId: number | string, quantidade: number = 1, tamanho: string | null = null): Promise<boolean> {
      try {
        await apiLoja.post('/carrinho/itens', {
          produto_id: produtoId,
          quantidade,
          tamanho,
        })
        await this.carregarQuantidade()
        this.avisar('Produto adicionado ao carrinho!', 'success')
        return true
      } catch (erro: any) {
        const mensagem = erro?.response?.data?.errors?.tamanho?.[0]
          ?? erro?.response?.data?.errors?.produto?.[0]
          ?? erro?.response?.data?.message
          ?? 'Não foi possível adicionar esse produto ao carrinho.'
        this.avisar(mensagem, 'error')
        return false
      }
    },
  },
})