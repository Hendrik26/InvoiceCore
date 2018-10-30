<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\DomainEvent;
use Verraes\ClassFunctions\ClassFunctions;

trait ApplyCallsWhenMethod
{
    /**
     * Delegate the application of the event to the appropriate when... method, e. g. a VisitorHasLeft event will be
     * processed by the (private) method whenVisitorHasLeft(VisitorHasLeft $event): void
     * @param DomainEvent $event
     */
    protected function apply(DomainEvent $event): void
    {
        $method = 'when' . ClassFunctions::short($event);
        $this->$method($event);
    }
}
