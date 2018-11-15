<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\Repository;

use Buttercup\Protects\IdentifiesAggregate;
use Irvobmagturs\InvoiceCore\Infrastructure\EventStore;
use Irvobmagturs\InvoiceCore\Infrastructure\Repository;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Jubjubbird\Respects\AggregateHistory;
use Jubjubbird\Respects\AggregateRoot;
use Jubjubbird\Respects\CorruptAggregateHistory;

class InvoiceRepository implements Repository
{
    private $eventStore;

    /**
     * @param EventStore $eventStore
     */
    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @param IdentifiesAggregate $id
     * @return AggregateRoot
     * @throws InvalidInvoiceId
     * @throws CorruptAggregateHistory
     */
    public function load(IdentifiesAggregate $id): AggregateRoot
    {
        return Invoice::reconstituteFrom(new AggregateHistory($id, $this->eventStore->listEventsForId($id)));
    }

    /**
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void
    {
        $domainEvents = $aggregateRoot->getRecordedEvents();
        $aggregateRoot->clearRecordedEvents();
        $this->eventStore->append($domainEvents->toArray());
    }
}
