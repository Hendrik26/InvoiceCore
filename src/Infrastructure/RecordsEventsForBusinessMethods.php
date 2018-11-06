<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\DomainEvents;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

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
     * @return bool
     */
    public function hasChanges()
    {
        return count($this->recordedEvents) > 0;
    }

    /**
     * Records the first occurrence of this event from the method that caused it.
     * @param Serializable $event
     */
    protected function recordThat(Serializable $event): void
    {
        $now = null;
        try {
            $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        } catch (Exception $e) {
            // cannot happen
        }
        $recordedEvent = new RecordedEvent($event, $this->getAggregateId(), $now);
        $this->apply($recordedEvent);
        $this->recordedEvents[] = $recordedEvent;
    }
}
