<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

use Buttercup\Protects\DomainEvent;

interface EventBus
{
    function handle(DomainEvent $event): void;
}
