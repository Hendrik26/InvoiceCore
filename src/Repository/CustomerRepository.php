<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\Repository;

use Buttercup\Protects\IdentifiesAggregate;
use Irvobmagturs\InvoiceCore\Infrastructure\EventStore;
use Irvobmagturs\InvoiceCore\Infrastructure\Repository;
use Irvobmagturs\InvoiceCore\Model\Entity\Customer;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerId;
use Jubjubbird\Respects\AggregateHistory;
use Jubjubbird\Respects\AggregateRoot;
use Jubjubbird\Respects\CorruptAggregateHistory;

class CustomerRepository implements Repository
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
     * @throws InvalidCustomerId
     * @throws CorruptAggregateHistory
     */
    public function load(IdentifiesAggregate $id): AggregateRoot
    {
        return Customer::reconstituteFrom(new AggregateHistory($id, $this->eventStore->listEventsForId($id)));
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
