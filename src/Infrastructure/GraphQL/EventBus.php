<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

use Jubjubbird\Respects\DomainEvents;

interface EventBus
{
    function dispatch(DomainEvents $domainEvents): void;
}
