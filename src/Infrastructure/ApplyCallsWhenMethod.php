<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Verraes\ClassFunctions\ClassFunctions;

trait ApplyCallsWhenMethod
{
    /**
     * Delegate the application of the event to the appropriate when... method, e. g. a VisitorHasLeft event will be
     * processed by the (private) method whenVisitorHasLeft(VisitorHasLeft $event): void
     * @param RecordedEvent $event
     */
    protected function apply(RecordedEvent $event): void
    {
        $method = 'when' . ClassFunctions::short($event->getPayload());
        $this->$method($event->getPayload(), $event);
    }
}
