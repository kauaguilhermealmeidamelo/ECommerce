/**
 * Identidade visual de UM cliente. Pra criar uma loja nova:
 * 1. Copie este arquivo pra src/theme/tema.js (ou mantenha e troque os valores)
 * 2. Ajuste cores, fontes e logo
 * 3. Troque assets em /public (logo.svg, favicon)
 * Nenhum componente deve ter cor/fonte fixa no <style> — tudo lê daqui.
 */
export const tema = {
  nome: 'Nome da Loja',

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

  logo: '/logo.svg',
}
