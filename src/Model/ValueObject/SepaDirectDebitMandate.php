<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use InvalidArgumentException;
use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;

/**
 * @property-read string $mandateReference
 * @property-read string $customerIban
 * @method withMandateReference(string $v)
 * @method withCustomerIban(string $v)
 */
class SepaDirectDebitMandate extends AbstractValueObjectBase
{
    /**
     * SepaDirectDebitMandate constructor.
     * @param string $mandateReference
     * @param string $customerIban
     * @throws InvalidArgumentException
     */
    public function __construct(string $mandateReference, string $customerIban)
    {
        $this->init('mandateReference', $mandateReference);
        $this->init('customerIban', $customerIban);
    }

    /**
     * @param array $data
     * @return static The object instance
     * @throws InvalidArgumentException
     */
    static function deserialize(array $data): self
    {
        return new self($data[0], $data[1]);
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->mandateReference, $this->customerIban];
    }
}
