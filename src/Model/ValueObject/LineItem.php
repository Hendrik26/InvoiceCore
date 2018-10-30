<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 30.10.18
 * Time: 12:31
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;

/**
 * @property-read int $position
 * @property-read Money $price
 * @property-read float $quantity
 * @property-read string $title
 * @property-read bool $timeBased
 * @property-read ?DateTimeInterface $date
 * @method self withPosition(int $v)
 * @method self withPrice(Money $v)
 * @method self withQuantity(float $v)
 * @method self withTitle(string $v)
 * @method self withTimeBased(bool $v)
 * @method self withDate(?DateTimeInterface $v)
 */
final class LineItem extends AbstractValueObjectBase
{
    public function __construct(
        int $position,
        Money $price,
        float $quantity,
        string $title,
        bool $timeBased,
        ?DateTimeInterface $date = null
    ) {
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
     * @throws Exception when the date string cannot be parsed
     */
    static function deserialize(array $data): Serializable
    {
        return new self(
            $data[0],
            Money::deserialize($data[1]),
            $data[2],
            $data[3],
            $data[4],
            $data[5] ? new DateTimeImmutable($data[5]) : null
        );
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [
            $this->position,
            $this->price->serialize(),
            $this->quantity,
            $this->title,
            $this->timeBased,
            $this->date ? $this->date->format(DATE_ATOM) : null
        ];
    }
}
