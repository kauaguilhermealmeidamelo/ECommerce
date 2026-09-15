import api from './api'
import type { Produto, NovoProdutoPayload } from '@/types/produto.types'

export const produtoService = {
  async listar(categoriaId?: number): Promise<Produto[]> {
    const { data } = await api.get('/admin/produtos', { params: { categoria_id: categoriaId } })
    return data.data
  },

  async criar(payload: NovoProdutoPayload): Promise<Produto> {
    const { data } = await api.post('/admin/produtos', payload)
    return data.data
  },

  async remover(id: number): Promise<void> {
    await api.delete(`/admin/produtos/${id}`)
  },
}