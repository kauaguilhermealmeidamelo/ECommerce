<template>
    <header class="topo">
        <div class="topo__marca">
            <span class="topo__logo">{{ nomeExibido.slice(0, -1) }}<span class="topo__logo-destaque">{{
                nomeExibido.slice(-1) }}</span></span>
            <span class="topo__selo">Admin</span>
        </div>

        <div class="topo__titulo">
            <h1>{{ titulo }}</h1>
            <p>{{ subtitulo }}</p>
        </div>

        <div class="busca topo__busca">
            <v-icon icon="mdi-magnify" size="small" class="ml-2"></v-icon>
            <input type="text" placeholder="Buscar..." />
        </div>

        <div class="topo__acoes">
            <div class="topo__notificacoes">
                <button class="topo__icone-botao" @click="notifAberta = !notifAberta" aria-label="Notificações">
                    <v-icon icon="mdi-bell-outline" size="small"></v-icon>
                    <span v-if="notificacoes.length" class="topo__ponto-alerta"></span>
                </button>
                <div v-if="notifAberta" class="topo__dropdown">
                    <div class="topo__dropdown-cabecalho">
                        <strong>Notificações</strong>
                        <button @click="notifAberta = false">✕</button>
                    </div>
                    <div v-if="notificacoes.length === 0" class="topo__dropdown-vazio">Nenhuma novidade por aqui.</div>
                    <div v-for="(n, i) in notificacoes" :key="i" class="topo__dropdown-item">
                        <span class="topo__dropdown-ponto" :class="`topo__dropdown-ponto--${n.cor || 'azul'}`"></span>
                        <div>
                            <p>{{ n.texto }}</p>
                            <span>{{ n.tempo }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="topo__notificacoes">
                <button class="topo__usuario" @click="menuAberto = !menuAberto">
                    <span class="avatar">{{ iniciaisUsuario }}</span>
                    <span class="topo__usuario-nome">{{ nomeUsuario }}</span>
                    <v-icon icon="mdi-chevron-down" size="small" class="topo__chevron"></v-icon>
                </button>
                <div v-if="menuAberto" class="topo__dropdown topo__dropdown--menor">
                    <button class="topo__dropdown-sair" @click="sair">
                        <v-icon icon="mdi-logout" size="small" class="mr-1"></v-icon> Sair
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

defineProps({
    titulo: { type: String, default: '' },
    subtitulo: { type: String, default: '' },
    notificacoes: { type: Array, default: () => [] },
})

const router = useRouter()
const auth = useAuthStore()
const notifAberta = ref(false)
const menuAberto = ref(false)

function sair() {
    menuAberto.value = false
    auth.logout()
    router.push({ name: 'login' })
}

const nomeUsuario = computed(() => auth.usuario?.name?.split(' ')[0] ?? 'Você')
const iniciaisUsuario = computed(() => {
    const nome = auth.usuario?.name ?? '?'
    return nome.trim().split(/\s+/).slice(0, 2).map((p) => p[0]).join('').toUpperCase()
})

const nomeLoja = ref('Loja')

async function carregarNomeLoja() {
    try {
        const { data } = await api.get('/admin/loja')
        if (data.data?.nome) nomeLoja.value = data.data.nome
    } catch (e) {
        // mantém o padrão "Loja"
    }
}

const nomeExibido = computed(() => (nomeLoja.value.split(/\s+/)[0] || 'Loja').toUpperCase())

onMounted(carregarNomeLoja)
</script>

<style scoped>
.topo {
    background: var(--surface);
    border-bottom: 1px solid var(--line);
    padding: .8rem 1rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-shrink: 0;
    position: sticky;
    top: 0;
    z-index: 30;
}

.topo__marca {
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-shrink: 0;
}

.topo__logo {
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--ink);
    white-space: nowrap;
}

.topo__logo-destaque {
    color: var(--blue-600);
}

.topo__selo {
    font-size: .6rem;
    font-weight: 600;
    color: var(--ink-faint);
    background: #f3f4f6;
    padding: .15rem .45rem;
    border-radius: 999px;
    display: none;
}

.topo__titulo {
    display: none;
}

.topo__titulo h1 {
    font-size: .85rem;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.1;
}

.topo__titulo p {
    font-size: .65rem;
    color: var(--ink-faint);
    margin-top: .1rem;
}

.topo__busca {
    flex: 1;
    max-width: 380px;
    margin: 0 auto;
    display: flex;
    align-items: center;
}

.topo__busca input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    padding: .4rem;
}

.topo__acoes {
    display: flex;
    align-items: center;
    gap: .4rem;
    margin-left: auto;
    flex-shrink: 0;
}

.topo__icone-botao {
    position: relative;
    background: none;
    border: none;
    padding: .55rem;
    border-radius: var(--radius-md);
    font-size: 1rem;
    cursor: pointer;
}

.topo__icone-botao:hover {
    background: #fafafa;
}

.topo__ponto-alerta {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: var(--danger);
}

.topo__notificacoes {
    position: relative;
}

.topo__dropdown {
    position: absolute;
    right: 0;
    top: 3rem;
    width: 280px;
    background: #fff;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: 1px solid var(--line);
    overflow: hidden;
    z-index: 50;
}

.topo__dropdown-cabecalho {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .8rem 1rem;
    border-bottom: 1px solid var(--line);
    font-size: .85rem;
}

.topo__dropdown-cabecalho button {
    background: none;
    border: none;
    color: var(--ink-faint);
    cursor: pointer;
}

.topo__dropdown-vazio {
    padding: 1.2rem;
    text-align: center;
    font-size: .78rem;
    color: var(--ink-faint);
}

.topo__dropdown-item {
    display: flex;
    gap: .6rem;
    padding: .7rem 1rem;
    border-bottom: 1px solid #fafafa;
}

.topo__dropdown-item:last-child {
    border-bottom: none;
}

.topo__dropdown-item p {
    font-size: .76rem;
    font-weight: 600;
    color: var(--ink);
    margin: 0;
}

.topo__dropdown-item span {
    font-size: .65rem;
    color: var(--ink-faint);
}

.topo__dropdown-ponto {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    margin-top: .3rem;
    flex-shrink: 0;
    background: var(--blue-600);
}

.topo__dropdown-ponto--ambar {
    background: var(--warning);
}

.topo__dropdown-ponto--verde {
    background: var(--success);
}

.topo__usuario {
    display: flex;
    align-items: center;
    gap: .5rem;
    background: none;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-md);
    padding: .3rem .6rem .3rem .3rem;
    cursor: pointer;
}

.topo__usuario:hover {
    background: #fafafa;
}

.topo__usuario-nome {
    display: none;
    font-size: .82rem;
    font-weight: 600;
    color: var(--ink);
}

.topo__chevron {
    display: none;
    color: var(--ink-faint);
    flex-shrink: 0;
}

.topo__dropdown--menor {
    width: 140px;
    top: 3.2rem;
}

.topo__dropdown-sair {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    padding: .75rem 1rem;
    font-size: .82rem;
    font-weight: 600;
    color: var(--danger);
    display: flex;
    align-items: center;
    cursor: pointer;
}

.topo__dropdown-sair:hover {
    background: var(--danger-bg);
}

@media (min-width: 640px) {
    .topo {
        padding: .9rem 1.5rem;
    }

    .topo__titulo {
        display: block;
    }

    .topo__selo {
        display: inline-block;
    }

    .topo__usuario-nome {
        display: block;
    }

    .topo__chevron {
        display: block;
    }
}
</style>