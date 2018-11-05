<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

use Buttercup\Protects\DomainEvent;

interface EventBus
{
    function handle(DomainEvent $event): void;
}
