import { defineStore } from "pinia";
import apiLoja from "@/services/apiLoja";

function safeJSONParse(key) {
  const item = localStorage.getItem(key);
  if (!item || item === "undefined" || item === "null") return null;
  try {
    return JSON.parse(item);
  } catch (e) {
    localStorage.removeItem(key);
    return null;
  }
}

export const useClienteAuthStore = defineStore("clienteAuth", {
  state: () => ({
    token:
      localStorage.getItem("cliente_token") !== "undefined"
        ? localStorage.getItem("cliente_token")
        : null,
    usuario: safeJSONParse("cliente_usuario"),
  }),

  getters: {
    autenticado: (state) => !!state.token && state.token !== "undefined",
    primeiroNome: (state) => state.usuario?.name?.split(" ")[0] ?? "",
  },

  actions: {
    async login(email, senha) {
      const { data } = await apiLoja.post("/auth/login", { email, senha });
      this.definirSessao(data);
    },

    async registrar({ name, email, senha, senha_confirmation, telefone }) {
      const { data } = await apiLoja.post("/auth/registro", {
        name,
        email,
        senha,
        senha_confirmation,
        telefone,
      });
      this.definirSessao(data);
    },

    definirSessao(data) {
      this.token = data.token;
      this.usuario = data.usuario;
      localStorage.setItem("cliente_token", data.token);
      localStorage.setItem("cliente_usuario", JSON.stringify(data.usuario));
    },

    logout() {
      this.token = null;
      this.usuario = null;
      localStorage.removeItem("cliente_token");
      localStorage.removeItem("cliente_usuario");
    },
  },
});
