import { defineStore } from "pinia";
import { ref, computed } from "vue";
import axios from "axios"; // ou sua instância configurada do axios

export const useClienteAuthStore = defineStore("clienteAuth", () => {
  // 1. Inicializa lendo direto do localStorage para não perder a sessão
  const token = ref(localStorage.getItem("auth_token") || null);
  const usuario = ref(null);

  const autenticado = computed(() => !!token.value);

  // Função centralizada para definir o token e atualizar o Axios
  function definirToken(novoToken) {
    token.value = novoToken;
    if (novoToken) {
      localStorage.setItem("auth_token", novoToken);
      axios.defaults.headers.common["Authorization"] = `Bearer ${novoToken}`;
    } else {
      localStorage.removeItem("auth_token");
      delete axios.defaults.headers.common["Authorization"];
    }
  }

  // Se já houver token salvo ao carregar a página, já injeta no Axios
  if (token.value) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token.value}`;
  }

  function logout() {
    definirToken(null);
    usuario.value = null;
  }

  return {
    token,
    usuario,
    autenticado,
    definirToken,
    logout,
  };
});
