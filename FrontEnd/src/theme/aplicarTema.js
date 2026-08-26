import { tema } from './tema'

/**
 * Converte o objeto de tema em CSS vars no :root. Chamado uma vez no
 * main.js. Componentes usam var(--cor-primaria) etc — nunca hexadecimal
 * direto — pra reskinar virar só uma questão de editar tema.js.
 */
export function aplicarTema() {
  const raiz = document.documentElement.style

  raiz.setProperty('--cor-fundo', tema.cores.fundo)
  raiz.setProperty('--cor-superficie', tema.cores.superficie)
  raiz.setProperty('--cor-texto', tema.cores.texto)
  raiz.setProperty('--cor-texto-suave', tema.cores.textoSuave)
  raiz.setProperty('--cor-primaria', tema.cores.primaria)
  raiz.setProperty('--cor-primaria-hover', tema.cores.primariaHover)
  raiz.setProperty('--cor-linha', tema.cores.linha)

  raiz.setProperty('--fonte-display', tema.tipografia.display)
  raiz.setProperty('--fonte-corpo', tema.tipografia.corpo)

  raiz.setProperty('--raio-borda', tema.layout.raioBorda)

  document.title = tema.nome
}
