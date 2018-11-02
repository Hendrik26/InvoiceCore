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
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasRemoved;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;

class Invoice implements AggregateRoot
{
    use RecordsEventsForBusinessMethods;
    use ApplyCallsWhenMethod;
    /** @var InvoiceId */
    private $aggregateId;
    /** @var LineItem[] */
    private $lineItems = [];

    /**
     * Invoice constructor.
     * @param InvoiceId $aggregateId
     */
    public function __construct(InvoiceId $aggregateId)
    {
        $this->aggregateId = $aggregateId;
    }

    /**
     * @param LineItem $item
     * @throws InvalidLineItemTitle
     */
    public function appendLineItem(LineItem $item): void
    {
        $this->guardEmptyTitle($item);
        $this->recordThat(new LineItemWasAppended(count($this->lineItems), $item));
    }

    /**
     * @param int $position
     */
    public function removeLineItemByPosition(int $position){
        $this->guardInvalidPosition($position);
        $this->recordThat(new LineItemWasRemoved($position));
    }

    /**
     * @param LineItem $item
     * @throws InvalidLineItemTitle
     */
    private function guardEmptyTitle(LineItem $item): void
    {
        if (trim($item->title) === "") {
            throw new InvalidLineItemTitle();
        }
    }

    /**
     * @param int $position
     */
    private function guardInvalidPosition(int $position): void
    {
        if (($position < 0) || $position >= count($this->lineItems)) {
            throw new InvalidLineItemPosition();
        }
    }

    /**
     * @param LineItemWasAppended $event
     */
    private function whenLineItemWasAppended(LineItemWasAppended $event)
    {
        $this->lineItems[] = $event->getItem();
    }

    /**
     * @param LineItemWasAppended $event
     */
    private function whenLineItemWasRemoved(LineItemWasRemoved $event)
    {
       array_splice( $this->lineItems, $event->getPosition(), 1);
    }


    /**
     * @param AggregateHistory $aggregateHistory
     * @return RecordsEvents
     */
    public static function reconstituteFrom(AggregateHistory $aggregateHistory)
    {
        // TODO: Implement reconstituteFrom() method.
    }

    public function getAggregateId(): IdentifiesAggregate
    {
        return $this->aggregateId;
    }
}
