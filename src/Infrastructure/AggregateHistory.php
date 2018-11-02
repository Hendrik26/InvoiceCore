<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\CorruptAggregateHistory;
use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\DomainEvents;
use Buttercup\Protects\IdentifiesAggregate;

/**
 * Duplicates the class AggregateHistory from Buttercup.Protects but without the final modifier to allow PhpSpec for
 * mocking it.
 */
class AggregateHistory extends DomainEvents
{
    /**
     * @var IdentifiesAggregate
     */
    private $aggregateId;

    /**
     * @param IdentifiesAggregate $aggregateId
     * @param array $events
     * @throws CorruptAggregateHistory
     */
    public function __construct(IdentifiesAggregate $aggregateId, array $events)
    {
        /** @var $event DomainEvent */
        foreach ($events as $event) {
            if (!$event->getAggregateId()->equals($aggregateId)) {
                throw new CorruptAggregateHistory;
            }
        }
        parent::__construct($events);
        $this->aggregateId = $aggregateId;
    }

    /**
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }
}
