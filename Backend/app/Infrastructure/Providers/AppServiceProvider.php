<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Infrastructure\Persistence\EloquentProdutoRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind da Clean Architecture: Conecta a interface do domínio à implementação Eloquent da infraestrutura
        $this->app->bind(
            ProdutoRepositoryInterface::class,
            EloquentProdutoRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}