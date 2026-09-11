<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Produtos\Repositories\ProdutoRepositoryInterface;
use App\Infrastructure\Persistence\EloquentProdutoRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProdutoRepositoryInterface::class,
            EloquentProdutoRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
