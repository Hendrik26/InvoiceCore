<?php declare(strict_types=1);
/**
 * @author Hendrik26
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use DateTimeImmutable;
use DateTimeInterface;
use DateInterval;
use Exception;
use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;



class BillingPeriod extends AbstractValueObjectBase implements Serializable
{
    /**
     * Period constructor.
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     */
    public function __construct(DateTimeImmutable $start, DateTimeImmutable $end)
    {
        $this->init('startDate', $start);
        $this->init('endDate', $end);
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @return mixed
     */
    public function getInterval(): DateInterval
    {
        $interval = $this->startDate->diff($this->endDate); // DateInterval
        return $interval;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->startDate->format(DATE_ATOM), $this->endDate->format(DATE_ATOM)];
    }

    /**
     * @param array $data
     * @return BillingPeriod
     * @throws Exception
     */
    public static function deserialize(array $data): self
    {
        return new self($data[0] ? new DateTimeImmutable($data[0]) : null,
            $data[1] ? new DateTimeImmutable($data[1]) : null);
    }
}