<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 01.11.18
 * Time: 10:27
 */

namespace Irvobmagturs\InvoiceCore\Model\Entity;

use Buttercup\Protects\AggregateHistory;
use Buttercup\Protects\AggregateRoot;
use Buttercup\Protects\IdentifiesAggregate;
use Buttercup\Protects\RecordsEvents;
use Irvobmagturs\InvoiceCore\Infrastructure\ApplyCallsWhenMethod;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordsEventsForBusinessMethods;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;

final class Invoice implements AggregateRoot
{
    use RecordsEventsForBusinessMethods;
    use ApplyCallsWhenMethod;

    public function appendLineItem(LineItem $item): void
    {
        $this->guardEmptyTitle($item);
        $this->recordThat(new LineItemWasAppended($item));
        $this->whenLineItemWasAppended($this->recordedEvents[0]);
    }

    /**
     * @param LineItem $item
     */
    private function guardEmptyTitle(LineItem $item): void
    {
        if (trim($item->title) === "") {
            throw new InvalidLineItemTitle();
        }
    }

    private function whenLineItemWasAppended(LineItemWasAppended $event)
    {
        // TODO
    }

    /**
     * @param AggregateHistory $aggregateHistory
     * @return RecordsEvents
     */
    public static function reconstituteFrom(AggregateHistory $aggregateHistory)
    {
        // TODO: Implement reconstituteFrom() method.
    }

    /**
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        // TODO: Implement getAggregateId() method.
    }

    /**
     * @return bool
     */
    public function hasChanges()
    {
        // TODO: Implement hasChanges() method.
    }
}