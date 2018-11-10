<?php declare(strict_types=1);
/**
 * @author Hendrik26
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use DateTimeImmutable;
use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;



class Period extends AbstractValueObjectBase implements Serializable
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

    function serialize(): array
    {
        return [$this->startDate->format(DATE_ATOM), $this->endDate->format(DATE_ATOM)];
    }

    public static function deserializeOld(array $data): self
    {
        return new self($data[0] ? new DateTimeImmutable($data[0]) : null,
            $data[1] ? new DateTimeImmutable($data[1]) : null);
    }

    public static function deserializeOld(array $data): self
    {
        return new self( CustomerId::fromString($data[1]), $data[2],
            $data[3] ? new DateTimeImmutable($data[3]) : null);
    }


}