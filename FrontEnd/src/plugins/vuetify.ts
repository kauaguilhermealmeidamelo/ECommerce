// src/plugins/vuetify.ts
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify, type ThemeDefinition } from 'vuetify'

/**
 * Paleta pensada pro contexto do produto (brechós e lojas de roupa):
 * verde-pinho como cor de ação principal — remete a segunda mão/reuso
 * sem cair no indigo padrão de qualquer dashboard — e terracota queimado
 * como acento secundário pra estoque/atenção. Fundo "papel" levemente
 * quente, não o cinza-azulado frio de UI corporativa genérica.
 *
 * Tipografia: reaproveita as fontes já carregadas no index.html do painel
 * (Playfair Display pra títulos, Inter pro resto) — ver src/style.css.
 */
const brechoLight: ThemeDefinition = {
  dark: false,
  colors: {
    background: '#F6F4EF',
    surface: '#FFFFFF',
    'surface-variant': '#EFEBE3',
    'surface-bright': '#FFFFFF',
    primary: '#1F6F5C',
    'primary-darken-1': '#15503F',
    secondary: '#B4532A',
    error: '#B3261E',
    warning: '#A8791C',
    success: '#1F6F5C',
    info: '#3E6B8A',
    'on-background': '#1C1B1A',
    'on-surface': '#1C1B1A',
    'on-primary': '#FFFFFF',
  },
  variables: {
    'border-color': '#E7E3DC',
    'medium-emphasis-opacity': 0.72,
    'disabled-opacity': 0.42,
  },
}

export default createVuetify({
  theme: {
    defaultTheme: 'brechoLight',
    themes: { brechoLight },
  },
  defaults: {
    VBtn: { rounded: 'lg', style: 'letter-spacing: normal;' },
    VCard: { rounded: 'lg' },
    VTextField: { rounded: 'lg' },
    VChip: { rounded: 'lg' },
    VDialog: { rounded: 'lg' },
    VTooltip: { openDelay: 300 },
  },
})