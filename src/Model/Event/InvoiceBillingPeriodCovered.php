<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 12.11.2018
 * Time: 00:22
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


use Irvobmagturs\InvoiceCore\Model\ValueObject\Period;

class InvoiceBillingPeriodCovered implements Serializable
{

    /**
     * @var
     */
    private $period;

    /**
     * InvoiceBillingPeriodCovered constructor.
     * @param Period $period
     */
    public function __construct(Period $period)
    {
        $this->period = $period;;
    }

    function serialize(): array
    {
        return [$this->period->serialize()];
    }

    public static function deserialize(array $data): self
    {
        return new self(Period::deserialize(data[0]));
    }
}