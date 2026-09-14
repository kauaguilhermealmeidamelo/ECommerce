<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Produto\Repositories\ProdutoRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProdutoRepository;

class AppServiceProvider extends ServiceProvider
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