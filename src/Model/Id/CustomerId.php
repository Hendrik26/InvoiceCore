<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Model\Id;

use Buttercup\Protects\IdentifiesAggregate;
use Irvobmagturs\InvoiceCore\Infrastructure\UsesUuidForIdentification;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerId;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

final class CustomerId implements IdentifiesAggregate
{
    use UsesUuidForIdentification;

    /**
     * Creates an identifier object from a string representation
     * @param string $string
     * @return self
     * @throws InvalidCustomerId
     */
    public static function fromString($string): self
    {
        try {
            return new self(Uuid::fromString($string));
        } catch (InvalidUuidStringException $e) {
            throw new InvalidCustomerId($string);
        }
    }
}
