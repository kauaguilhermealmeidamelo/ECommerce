<?php
namespace App\Domain\Shared\Exceptions;

use DomainException;

class CategoriaNaoEhFolhaException extends DomainException
{
    protected $message = 'Essa categoria possui subcategorias.';
}