<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\RecordsEvents;
use Buttercup\Protects\TracksChanges;

interface AggregateRoot extends RecordsEvents, IsEventSourced, TracksChanges
{
}
