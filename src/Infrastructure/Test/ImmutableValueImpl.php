<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace Irvobmagturs\InvoiceCore\Infrastructure\Test;

use DateTime;
use Irvobmagturs\InvoiceCore\Infrastructure\ImmutableValue;

/**
 * @property-read string $string
 * @property-read array $array
 * @property-read DateTime $dateTime
 * @method self withString(string $v)
 * @method self withArray(array $v)
 * @method self withDateTime(DateTime $v)
 */
final class ImmutableValueImpl extends ImmutableValue
{
    public function __construct(string $string, array $array, DateTime $dateTime)
    {
        $this->init('string', $string);
        $this->init('array', $array);
        $this->init('dateTime', $dateTime);
    }

    public function revealArray()
    {
        return array_values((array)$this)[0];
    }
}
