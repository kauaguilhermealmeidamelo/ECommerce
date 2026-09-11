export interface Produto {
    id: number
    nome: string
    preco: number
    categoriaId: number
    estoque: number
    ativo: boolean
}

export interface NovoProdutoPayload {
    nome: string
    preco: number
    categoriaId: number
    estoque: number
}