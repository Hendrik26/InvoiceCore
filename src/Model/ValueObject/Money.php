<?php declare(strict_types=1);
/**
 * @author Hendrik26
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use InvalidArgumentException;
use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;

/**
 * @property-read float $amount
 * @property-read string $currency
 * @method Money withAmount(float $v)
 * @method Money withCurrency(string $v)
 */
class Money extends AbstractValueObjectBase
{
    /**
     * Money constructor.
     * @param float $amount
     * @param string $currency
     * @throws InvalidArgumentException
     */
    public function __construct(float $amount, string $currency)
    {
        $this->init('amount', $amount);
        $this->init('currency', $currency);
    }

    /**
     * @param array $data
     * @return static The object instance
     * @throws InvalidArgumentException
     */
    static function deserialize(array $data): self
    {
        return new self($data[1], $data[0]);
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->currency, $this->amount];
    }
}
