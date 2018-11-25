<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;

/**
 * @property-read DateTimeInterface $startDate
 * @property-read DateTimeInterface $endDate
 * @method self withStartDate(DateTimeInterface $v)
 * @method self withEndDate(DateTimeInterface $v)
 */
class BillingPeriod extends AbstractValueObjectBase
{
    /**
     * BillingPeriod constructor.
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @throws InvalidArgumentException
     */
    public function __construct(DateTimeInterface $start, DateTimeInterface $end)
    {
        $this->init('startDate', $start);
        $this->init('endDate', $end);
    }

    /**
     * @param array $data
     * @return BillingPeriod
     * @throws Exception
     */
    public static function deserialize(array $data): self
    {
        return new self(new DateTimeImmutable($data[0]), new DateTimeImmutable($data[1]));
    }

    /**
     * @return DateInterval
     */
    public function getInterval(): DateInterval
    {
        return $this->startDate->diff($this->endDate);
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->startDate->format(DATE_ATOM), $this->endDate->format(DATE_ATOM)];
    }
}