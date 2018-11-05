<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\IdentifiesAggregate;
use DateTimeInterface;

class RecordedEvent implements DomainEvent
{
    /** @var IdentifiesAggregate */
    private $aggregateId;
    /** @var Serializable */
    private $payload;
    /** @var DateTimeInterface */
    private $recordedOn;

    /**
     * @param Serializable $payload
     * @param IdentifiesAggregate $aggregateId
     * @param DateTimeInterface $recordedOn
     */
    public function __construct(Serializable $payload, IdentifiesAggregate $aggregateId, DateTimeInterface $recordedOn)
    {
        $this->payload = $payload;
        $this->aggregateId = $aggregateId;
        $this->recordedOn = $recordedOn;
    }

    /**
     * The Aggregate this event belongs to.
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return Serializable
     */
    public function getPayload(): Serializable
    {
        return $this->payload;
    }

    /**
     * @return DateTimeInterface
     */
    public function getRecordedOn(): DateTimeInterface
    {
        return $this->recordedOn;
    }
}
