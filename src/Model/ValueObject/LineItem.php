<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 30.10.18
 * Time: 12:31
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use DateTimeInterface;
use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;

/**
 * @property-read int $position
 * @property-read Money $price
 * @property-read float $quantity
 * @property-read string $title
 * @property-read bool $timeBased
 * @property-read ?DateTime $date
 */
final class LineItem extends AbstractValueObjectBase
{
    public function __construct(int $position, Money $price, float $quantity, string $title,
                                bool $timeBased, ?DateTimeInterface $date = null)
    {
        $this->init('position', $position);
        $this->init('price', $price);
        $this->init('quantity', $quantity);
        $this->init('title', $title);
        $this->init('timeBased', $timeBased);
        $this->init('date', $date);
    }

    /**
     * @param array $data
     * @return static The object instance
     */
    static function deserialize(array $data): Serializable
    {
        return new self($data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->position, $this->price, $this->quantity, $this->title, $this->timeBased, $this->date];
    }
}
