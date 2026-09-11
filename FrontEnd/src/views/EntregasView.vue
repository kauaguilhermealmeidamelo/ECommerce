<template>
  <section class="pagina">
    <h1 class="font-display pagina__titulo">Configurações</h1>
    <p class="pagina__subtitulo">Dados da loja, vendas e entregas.</p>

    <section class="painel">
      <h2 class="painel__titulo">Dados da loja</h2>
      <div class="campos">
        <label>Nome da loja<input v-model="loja.nome" /></label>
        <label>Telefone<input v-model="loja.telefone" /></label>
        <label>E-mail de contato<input v-model="loja.email_contato" type="email" /></label>
        <div class="campos__linha">
          <label>CEP<input v-model="loja.cep" /></label>
          <label>Número<input v-model="loja.numero" /></label>
        </div>
        <label>Endereço<input v-model="loja.endereco" /></label>
        <div class="campos__linha">
          <label>Bairro<input v-model="loja.bairro" /></label>
          <label>Cidade<input v-model="loja.cidade" /></label>
          <label>UF<input v-model="loja.uf" maxlength="2" /></label>
        </div>
      </div>
      <button class="btn btn--secundario" style="margin-top:1rem" @click="salvarLoja" :disabled="salvandoLoja">
        {{ salvandoLoja ? 'Salvando...' : 'Salvar dados da loja' }}
      </button>
    </section>

    <section class="painel" style="margin-top:1.25rem">
      <h2 class="painel__titulo">Vendas</h2>
      <label class="metodo">
        <div>
          <strong>Produto expira após a venda</strong>
          <p>Ao ativar, assim que um produto é vendido ele sai automaticamente de venda — ideal pra peças únicas de
            brechó.</p>
        </div>
        <input type="checkbox" v-model="configLoja.produto_expira_apos_venda" />
      </label>
      <button class="btn btn--secundario" style="margin-top:.75rem" @click="salvarConfigLoja"
        :disabled="salvandoConfigLoja">
        {{ salvandoConfigLoja ? 'Salvando...' : 'Salvar' }}
      </button>
    </section>

    <section class="painel metodos" style="margin-top:1.25rem">
      <label class="metodo">
        <div>
          <strong>Retirada na loja</strong>
          <p>Cliente busca o pedido no endereço da loja, sem frete.</p>
        </div>
        <input type="checkbox" v-model="config.retirada_ativa" />
      </label>

      <label class="metodo">
        <div>
          <strong>Entrega local</strong>
          <p>Motoboy próprio, com valor fixo por faixa de CEP.</p>
        </div>
        <input type="checkbox" v-model="config.entrega_local_ativa" />
      </label>

      <label class="metodo">
        <div>
          <strong>Transportadora</strong>
          <p>Frete calculado por CEP via Melhor Envio.</p>
        </div>
        <input type="checkbox" v-model="config.transportadora_ativa" />
      </label>
    </section>

    <section v-if="config.entrega_local_ativa" class="painel" style="margin-top:1.25rem">
      <div class="painel__cabecalho">
        <h2 class="painel__titulo">Zonas de entrega local</h2>
        <button class="btn btn--secundario" @click="novaZona">+ Zona</button>
      </div>

      <div v-for="(zona, i) in zonas" :key="i" class="zona">
        <div class="zona__campos">
          <label>CEP inicial<input v-model="zona.cep_inicial" placeholder="00000-000" /></label>
          <label>CEP final<input v-model="zona.cep_final" placeholder="00000-000" /></label>
        </div>
        <div class="zona__campos">
          <label>Valor (R$)<input v-model.number="zona.valor" type="number" step="0.01" /></label>
          <label>Prazo (dias)<input v-model.number="zona.prazo_dias" type="number" /></label>
        </div>
        <button class="btn btn--perigo zona__remover" @click="zonas.splice(i, 1)">Remover</button>
      </div>
    </section>

    <section v-if="config.transportadora_ativa" class="painel" style="margin-top:1.25rem">
      <h2 class="painel__titulo">Transportadora</h2>
      <p class="aviso">
        Conecte sua conta do Melhor Envio pra habilitar cotação automática de frete.
        O token fica salvo com criptografia, associado só a esta loja.
      </p>
      <label style="display:block; margin-top:.75rem">
        Token de API
        <input v-model="config.token_melhor_envio" type="password" placeholder="Cole aqui o token" />
      </label>
    </section>

    <button class="btn btn--primario" style="width:100%; margin-top:1.5rem" @click="salvar" :disabled="salvando">
      {{ salvando ? 'Salvando...' : 'Salvar configurações' }}
    </button>

    <p v-if="mensagem" class="mensagem">{{ mensagem }}</p>
  </section>
</template>

<script setup lang="ts">
import { useEntregasViewModel } from '@/viewmodels/useEntregasViewModel'

const {
  config,
  zonas,
  salvando,
  mensagem,
  loja,
  salvandoLoja,
  configLoja,
  salvandoConfigLoja,
  salvarLoja,
  salvarConfigLoja,
  novaZona,
  salvar,
} = useEntregasViewModel()
</script>

<style scoped>
.campos {
  display: flex;
  flex-direction: column;
  gap: .75rem;
}

.campos label {
  display: flex;
  flex-direction: column;
  gap: .3rem;
  font-size: .8rem;
  color: var(--ink-soft);
}

.campos__linha {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
  gap: .6rem;
}

.metodos {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.metodo {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.metodo p {
  margin: .2rem 0 0;
  font-size: .82rem;
  color: var(--ink-soft);
}

.metodo input[type="checkbox"] {
  width: auto;
  flex-shrink: 0;
  margin-top: .2rem;
  accent-color: var(--navy);
}

.painel__cabecalho {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.painel__titulo {
  font-size: .82rem;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--ink-soft);
  margin: 0;
  font-weight: 700;
}

.zona {
  border-top: 1px solid var(--line);
  padding-top: .9rem;
  margin-top: .9rem;
}

.zona__campos {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .6rem;
  margin-bottom: .6rem;
}

.zona__campos label {
  display: flex;
  flex-direction: column;
  gap: .25rem;
  font-size: .78rem;
  color: var(--ink-soft);
}

.zona__remover {
  font-size: .78rem;
  padding: .4rem .8rem;
}

.aviso {
  font-size: .82rem;
  color: var(--ink-soft);
  background: var(--icon-bg);
  padding: .75rem;
  border-radius: 10px;
}

.mensagem {
  text-align: center;
  font-size: .85rem;
  color: var(--ink-soft);
  margin-top: .75rem;
}
</style>