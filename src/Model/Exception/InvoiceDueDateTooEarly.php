<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 22.11.18
 * Time: 10:03
 */

namespace Irvobmagturs\InvoiceCore\Model\Exception;

use InvalidArgumentException;

class InvoiceDueDateTooEarly extends InvalidArgumentException
{
}