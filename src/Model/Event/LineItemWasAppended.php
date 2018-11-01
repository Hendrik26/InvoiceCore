<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 01.11.18
 * Time: 12:06
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


use Buttercup\Protects\DomainEvent;
use Buttercup\Protects\IdentifiesAggregate;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;

class LineItemWasAppended implements DomainEvent, Serializable
{
    private $item;
    private $timeStamp;

    /**
     * LineItemWasAppended constructor.
     * @param LineItem $item
     */
    public function __construct(LineItem $item)
    {
        $this->timeStamp = new \DateTime();
        $this->item = $item;

    }

    /**
     * The Aggregate this event belongs to.
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        // TODO: Implement getAggregateId() method.
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [
            $this->timeStamp->format(DATE_ATOM),
            $this->item->serialize(),
        ];
    }

    /**
     * @param array $data
     * @return static The object instance
     * @throws Exception when the date string cannot be parsed
     */
    static function deserialize(array $data): Serializable
    {
        $lineItemWasAppended = new self(
            $data[1]
        );
        $lineItemWasAppended->timeStamp = $data[0];
        return $lineItemWasAppended;
    }

    /**
     * @return LineItem
     */
    public function getItem(): LineItem
    {
        return $this->item;
    }


}