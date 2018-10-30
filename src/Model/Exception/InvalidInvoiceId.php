<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace Irvobmagturs\InvoiceCore\Model\Exception;

use Ramsey\Uuid\Exception\InvalidUuidStringException;

final class InvalidInvoiceId extends InvalidUuidStringException
{
}
