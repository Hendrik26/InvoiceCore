<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 15.11.18
 * Time: 10:13
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;


use Buttercup\Protects\IdentifiesAggregate;

interface EventStore
{
    /**
     * @param IdentifiesAggregate $id
     * @return RecordedEvent[]
     */
    public function listEventsForId(IdentifiesAggregate $id): array;

    /**
     * @param RecordedEvent $event
     */
    public function append(RecordedEvent $event): void;
}