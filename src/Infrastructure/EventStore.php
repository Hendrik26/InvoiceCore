<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 15.11.18
 * Time: 10:13
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\IdentifiesAggregate;
use Irvobmagturs\InvoiceCore\Infrastructure\Exception\NoEventsStored;
use Jubjubbird\Respects\DomainEvent;

interface EventStore
{
    /**
     * @param DomainEvent[] $recordedEvents
     */
    public function append(array $recordedEvents): void;

    /**
     * @param IdentifiesAggregate $id
     * @return DomainEvent[]|iterable
     * @throws NoEventsStored when there are no events for that ID.
     */
    public function listEventsForId(IdentifiesAggregate $id): iterable;
}
