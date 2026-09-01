<template>
  <footer class="rodape">
    <div class="rodape__conteudo">
      <div class="rodape__grade">
        <div class="rodape__marca">
          <span class="rodape__logo">{{ tema.nome }}</span>
          <p v-if="loja">
            {{
              loja.cidade ? `${loja.cidade}${loja.uf ? "/" + loja.uf : ""}` : ""
            }}
          </p>

          <div v-if="temRedeSocial" class="rodape__redes">
            <a
              v-if="loja.instagram_url"
              :href="loja.instagram_url"
              target="_blank"
              rel="noopener"
              aria-label="Instagram"
              class="rodape__rede"
              >IG</a
            >
            <a
              v-if="loja.facebook_url"
              :href="loja.facebook_url"
              target="_blank"
              rel="noopener"
              aria-label="Facebook"
              class="rodape__rede"
              >FB</a
            >
            <a
              v-if="loja.tiktok_url"
              :href="loja.tiktok_url"
              target="_blank"
              rel="noopener"
              aria-label="TikTok"
              class="rodape__rede"
              >TT</a
            >
            <a
              v-if="loja.whatsapp"
              :href="linkWhatsapp"
              target="_blank"
              rel="noopener"
              aria-label="WhatsApp"
              class="rodape__rede"
              >WA</a
            >
          </div>
        </div>

        <div class="rodape__coluna">
          <h4>Loja</h4>
          <ul>
            <li>
              <router-link :to="{ name: 'catalogo' }">Catálogo</router-link>
            </li>
            <li>
              <router-link :to="{ name: 'meus-pedidos' }"
                >Meus pedidos</router-link
              >
            </li>
            <li>
              <router-link :to="{ name: 'carrinho' }">Carrinho</router-link>
            </li>
          </ul>
        </div>

        <div class="rodape__coluna">
          <h4>Contato</h4>
          <ul>
            <li v-if="loja?.telefone">📞 {{ loja.telefone }}</li>
            <li v-if="loja?.email_contato">✉️ {{ loja.email_contato }}</li>
            <li v-if="loja?.whatsapp">
              <a :href="linkWhatsapp" target="_blank" rel="noopener"
                >💬 Fale no WhatsApp</a
              >
            </li>
            <li v-if="enderecoCompleto">📍 {{ enderecoCompleto }}</li>
          </ul>
        </div>
      </div>

      <div class="rodape__base">
        <p>© {{ anoAtual }} {{ tema.nome }}. Todos os direitos reservados.</p>
        <!-- Crédito do criador do sistema — mantido conforme contrato de
             licenciamento do template, não é editável pelo tema do cliente. -->
        <p class="rodape__credito">
          Loja virtual desenvolvida por
          <a href="https://github.com/kauag" target="_blank" rel="noopener"
            >Kauã G.</a
          >
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { tema } from "@/theme/tema";
import apiLoja from "@/services/apiLoja";

const loja = ref(null);
const anoAtual = new Date().getFullYear();

const temRedeSocial = computed(
  () =>
    !!(
      loja.value?.instagram_url ||
      loja.value?.facebook_url ||
      loja.value?.tiktok_url ||
      loja.value?.whatsapp
    ),
);

const linkWhatsapp = computed(() => {
  const numero = (loja.value?.whatsapp ?? "").replace(/\D/g, "");
  return numero ? `https://wa.me/55${numero}` : "#";
});

const enderecoCompleto = computed(() => {
  if (!loja.value?.endereco) return null;
  const { endereco, numero, bairro, cidade, uf } = loja.value;
  return [
    `${endereco}${numero ? ", " + numero : ""}`,
    bairro,
    cidade && uf ? `${cidade}/${uf}` : null,
  ]
    .filter(Boolean)
    .join(" — ");
});

async function carregarLoja() {
  try {
    const { data } = await apiLoja.get("/loja");
    loja.value = data.data;
  } catch (e) {
    loja.value = null;
  }
}

onMounted(carregarLoja);
</script>

<style scoped>
.rodape {
  background: var(--cor-texto);
  color: #fff;
  margin-top: 3rem;
}
.rodape__conteudo {
  max-width: 1100px;
  margin: 0 auto;
  padding: 2.5rem 1.25rem 6rem;
}

.rodape__grade {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}
.rodape__marca .rodape__logo {
  font-family: var(--fonte-display);
  font-size: 1.3rem;
  font-weight: 700;
}
.rodape__marca p {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.82rem;
  margin: 0.4rem 0 0;
}

.rodape__redes {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
}
.rodape__rede {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  font-weight: 700;
  text-decoration: none;
}
.rodape__rede:hover {
  background: var(--cor-primaria);
  color: #fff;
}

.rodape__coluna h4 {
  font-size: 0.85rem;
  font-weight: 700;
  margin: 0 0 0.8rem;
}
.rodape__coluna ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}
.rodape__coluna li,
.rodape__coluna a {
  color: rgba(255, 255, 255, 0.65);
  font-size: 0.8rem;
  text-decoration: none;
}
.rodape__coluna a:hover {
  color: #fff;
}

.rodape__base {
  border-top: 1px solid rgba(255, 255, 255, 0.12);
  padding-top: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.5);
}
.rodape__credito a {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: underline;
}
.rodape__credito a:hover {
  color: #fff;
}

@media (min-width: 700px) {
  .rodape__grade {
    grid-template-columns: 1.4fr 1fr 1fr;
  }
  .rodape__base {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}
</style>
