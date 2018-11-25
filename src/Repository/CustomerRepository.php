<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Repository;

use Buttercup\Protects\IdentifiesAggregate;
use Irvobmagturs\InvoiceCore\Infrastructure\EventBus;
use Irvobmagturs\InvoiceCore\Infrastructure\EventStore;
use Irvobmagturs\InvoiceCore\Infrastructure\Exception\NoEventsStored;
use Irvobmagturs\InvoiceCore\Infrastructure\Repository;
use Irvobmagturs\InvoiceCore\Model\Entity\Customer;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerId;
use Jubjubbird\Respects\AggregateHistory;
use Jubjubbird\Respects\AggregateRoot;
use Jubjubbird\Respects\CorruptAggregateHistory;

class CustomerRepository implements Repository
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
     * @throws InvalidCustomerId
     * @throws CorruptAggregateHistory
     * @throws CustomerNotFound
     */
    public function load(IdentifiesAggregate $id): AggregateRoot
    {
        try {
            return Customer::reconstituteFrom(new AggregateHistory($id, $this->eventStore->listEventsForId($id)));
        } catch (NoEventsStored $e) {
            throw new CustomerNotFound();
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
