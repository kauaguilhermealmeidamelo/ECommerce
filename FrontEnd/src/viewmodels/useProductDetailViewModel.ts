import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import apiLoja from '@/services/apiLoja'

export function useProductDetailViewModel(propsId?: string | number) {
  const route = useRoute()
  const produto = ref<any>(null)
  const carregando = ref(true)
  const erro = ref<string | null>(null)

  async function carregar() {
    carregando.value = true
    erro.value = null
    try {
      const idParaBuscar = propsId ?? route.params.id
      const { data } = await apiLoja.get(`/produtos/${idParaBuscar}`)
      produto.value = data.data
    } catch {
      erro.value = 'Produto não encontrado.'
    } finally {
      carregando.value = false
    }
  }

  function onAdicionado() {
    // Ação ao adicionar no carrinho (ex: disparar toast ou notificação)
  }

  onMounted(carregar)

  return {
    produto,
    carregando,
    erro,
    onAdicionado
  }
}