import { computed } from 'vue'
import { useRoute } from 'vue-router'

export function usePedidoConfirmadoViewModel() {
  const route = useRoute()

  // Extrai o número do pedido da query string (ex: ?pedido=123)
  const numeroPedido = computed(() => {
    const pedido = route.query.pedido
    return pedido ? String(pedido) : null
  })

  return {
    numeroPedido,
  }
}