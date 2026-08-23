<?php

namespace App\Console\Commands;

use App\Models\Administrador;
use App\Models\Categoria;
use App\Models\Configuracao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ConfigurarLoja extends Command
{
    protected $signature = 'loja:configurar';

    protected $description = 'Assistente interativo pra configurar uma loja nova a partir do template: dados da loja, categorias iniciais e o primeiro administrador.';

    public function handle(): int
    {
        $this->info('=== Configuração inicial da loja ===');
        $this->newLine();

        $this->configurarDadosDaLoja();
        $this->newLine();

        $this->configurarCategorias();
        $this->newLine();

        $this->configurarAdmin();
        $this->newLine();

        $this->info('Loja configurada. Rode "php artisan serve" e confira /api/configuracoes e /api/categorias.');

        return self::SUCCESS;
    }

    private function configurarDadosDaLoja(): void
    {
        $this->comment('-- Dados da loja (chave/valor, usado pelo frontend em /api/configuracoes) --');

        $campos = [
            'nome_loja' => $this->ask('Nome da loja'),
            'cor_primaria' => $this->ask('Cor primária (hex, ex: #7c3aed)', '#7c3aed'),
            'whatsapp' => $this->ask('WhatsApp (com DDD, só números, ex: 5511999999999)'),
            'endereco' => $this->ask('Endereço (pode deixar em branco)', ''),
            'logo_url' => $this->ask('URL do logo (pode deixar em branco, ajusta depois)', ''),
            'instagram' => $this->ask('Instagram (usuário, sem @, pode deixar em branco)', ''),
        ];

        foreach ($campos as $chave => $valor) {
            if ($valor === '' && $chave !== 'nome_loja') {
                continue;
            }

            Configuracao::updateOrCreate(['chave' => $chave], ['valor' => $valor]);
            $this->line("  ✓ {$chave} = {$valor}");
        }
    }

    private function configurarCategorias(): void
    {
        $this->comment('-- Categorias iniciais --');
        $this->line('Digite os nomes separados por vírgula (ex: Camisetas, Calças, Acessórios).');

        $entrada = $this->ask('Categorias', 'Geral');

        $nomes = array_filter(array_map('trim', explode(',', $entrada)));

        foreach ($nomes as $ordem => $nome) {
            $categoria = Categoria::updateOrCreate(
                ['slug' => Str::slug($nome)],
                ['nome' => $nome, 'ordem' => $ordem]
            );
            $this->line("  ✓ Categoria criada: {$categoria->nome} ({$categoria->slug})");
        }
    }

    private function configurarAdmin(): void
    {
        $this->comment('-- Primeiro administrador da loja --');

        if (Administrador::count() > 0) {
            if (! $this->confirm('Já existe um administrador cadastrado. Quer criar outro mesmo assim?', false)) {
                return;
            }
        }

        $nome = $this->ask('Nome do administrador');
        $email = $this->ask('E-mail de login');
        $senha = $this->secret('Senha (não aparece na tela enquanto digita)');
        $senhaConfirmacao = $this->secret('Confirme a senha');

        if ($senha !== $senhaConfirmacao) {
            $this->error('As senhas não conferem. Administrador não foi criado — rode "php artisan loja:configurar" de novo.');

            return;
        }

        if (strlen($senha) < 8) {
            $this->error('Senha muito curta (mínimo 8 caracteres). Administrador não foi criado.');

            return;
        }

        Administrador::updateOrCreate(
            ['email' => $email],
            [
                'nome' => $nome,
                'senha_hash' => Hash::make($senha),
                'papel' => 'owner',
            ]
        );

        $this->line("  ✓ Administrador criado: {$nome} <{$email}>");
    }
}