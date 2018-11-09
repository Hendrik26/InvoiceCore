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
        return [$this->s, $this->amount];
    }


}