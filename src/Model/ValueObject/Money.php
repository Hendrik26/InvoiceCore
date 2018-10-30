<?php declare(strict_types=1);
/**
 * @author hendrik
 */
namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;

/**
 * @property-read float $amount
 * @property-read string $currency
 * @method Money withAmount(float $v)
 * @method Money withCurrency(string $v)
 */
final class Money extends AbstractValueObjectBase
{

    public function __construct(float $amount, string $currency) {
        $this->init('amount', $amount);
        $this->init('currency', $currency);
    }

    /**
     * @param array $data
     * @return static The object instance
     */
    static function deserialize(array $data): Serializable
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
