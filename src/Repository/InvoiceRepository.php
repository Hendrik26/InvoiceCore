<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\Repository;

use Buttercup\Protects\IdentifiesAggregate;
use Irvobmagturs\InvoiceCore\Infrastructure\EventBus;
use Irvobmagturs\InvoiceCore\Infrastructure\EventStore;
use Irvobmagturs\InvoiceCore\Infrastructure\NoEventsStored;
use Irvobmagturs\InvoiceCore\Infrastructure\Repository;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Jubjubbird\Respects\AggregateHistory;
use Jubjubbird\Respects\AggregateRoot;
use Jubjubbird\Respects\CorruptAggregateHistory;

class InvoiceRepository implements Repository
{
    private $eventBus;
    private $eventStore;

    /**
     * @param EventStore $eventStore
     * @param EventBus $eventBus
     */
    public function __construct(EventStore $eventStore, EventBus $eventBus)
    {
        $this->eventStore = $eventStore;
        $this->eventBus = $eventBus;
    }

    /**
     * @param IdentifiesAggregate $id
     * @return AggregateRoot
     * @throws InvalidInvoiceId
     * @throws CorruptAggregateHistory
     * @throws InvoiceNotFound
     */
    public function load(IdentifiesAggregate $id): AggregateRoot
    {
        try {
            return Invoice::reconstituteFrom(new AggregateHistory($id, $this->eventStore->listEventsForId($id)));
        } catch (NoEventsStored $e) {
            throw new InvoiceNotFound();
        }
    }

    /**
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void
    {
        $domainEvents = $aggregateRoot->getRecordedEvents();
        $aggregateRoot->clearRecordedEvents();
        $this->eventStore->append($domainEvents->toArray());
        $this->eventBus->dispatch($domainEvents);
    }
}
