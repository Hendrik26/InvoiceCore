<?php
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\DomainEvents;

trait RecordsEventsForBusinessMethods
{
    /** @var DomainEvent[] */
    private $recordedEvents = [];

    /**
     * Clears the record of new Domain Events. This doesn't clear the history of the object.
     * @return void
     */
    public function clearRecordedEvents(): void
    {
        $this->recordedEvents = [];
    }

    /**
     * Get all the Domain Events that were recorded since the last time it was cleared, or since it was
     * restored from persistence. This does not include events that were recorded prior.
     * @return DomainEvents
     */
    public function getRecordedEvents(): DomainEvents
    {
        return new DomainEvents($this->recordedEvents);
    }

    /**
     * Records the first occurrence of this event from the method that caused it.
     * @param DomainEvent $event
     */
    protected function recordThat(DomainEvent $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
