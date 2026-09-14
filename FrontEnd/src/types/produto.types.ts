export interface Produto {
    id: number
    nome: string
    preco: number
    categoriaId?: number | null
    categoria?: string | null
    sku?: string | null
    estoque: number
    ativo?: boolean
}

export interface NovoProdutoPayload {
    nome: string
    preco: number
    categoria_id?: number | null
    estoque: number
    categoria?: string
}