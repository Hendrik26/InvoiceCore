<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\IdentifiesAggregate;
use Ramsey\Uuid\UuidInterface;

trait UsesUuidForIdentification
{
    private $value;

    /**
     * @param UuidInterface $value
     */
    public function __construct(UuidInterface $value)
    {
        $this->value = $value;
    }

    /**
     * Returns a string that can be parsed by fromString()
     * @return string
     */
    public function __toString(): string
    {
        return $this->value->toString();
    }

    /**
     * Compares the object to another IdentifiesAggregate object. Returns true if both have the same type and value.
     * @param IdentifiesAggregate $other
     * @return boolean
     */
    public function equals(IdentifiesAggregate $other): bool
    {
        return $other instanceof self && $this->value->equals($other->value);
    }
}
