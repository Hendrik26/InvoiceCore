<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 15.11.18
 * Time: 10:13
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;


use Buttercup\Protects\IdentifiesAggregate;
use Jubjubbird\Respects\RecordedEvent;
use Traversable;

interface EventStore
{
    /**
     * @param RecordedEvent[] $recordedEvents
     */
    public function append(array $recordedEvents): void;

    /**
     * @param IdentifiesAggregate $id
     * @return RecordedEvent[]
     * @throws NoEventsStored when there are no events for that ID.
     */
    public function listEventsForId(IdentifiesAggregate $id): Traversable;
}