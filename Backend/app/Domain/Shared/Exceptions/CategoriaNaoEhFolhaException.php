<?php
namespace App\Domain\Produto\Exceptions;

use DomainException;

class CategoriaNaoEhFolhaException extends DomainException
{
    protected $message = 'Essa categoria possui subcategorias.';
}