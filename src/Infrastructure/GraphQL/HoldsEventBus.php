<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

interface HoldsEventBus
{
    function getEventBus(): EventBus;
}
