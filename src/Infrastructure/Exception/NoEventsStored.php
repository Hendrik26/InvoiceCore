<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure\Exception;

use RuntimeException;

/** An event store has no events that belong to a given aggregate ID. */
class NoEventsStored extends RuntimeException
{
}
