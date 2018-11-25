<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure\Test;

use DateTime;
use InvalidArgumentException;
use Irvobmagturs\InvoiceCore\Infrastructure\ImmutableValue;

/**
 * @property-read string $string
 * @property-read array $array
 * @property-read DateTime $dateTime
 * @method self withString(string $v)
 * @method self withArray(array $v)
 * @method self withDateTime(DateTime $v)
 */
class ImmutableValueImpl extends ImmutableValue
{
    /**
     * ImmutableValueImpl constructor.
     * @param string $string
     * @param array $array
     * @param DateTime $dateTime
     * @throws InvalidArgumentException
     */
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
