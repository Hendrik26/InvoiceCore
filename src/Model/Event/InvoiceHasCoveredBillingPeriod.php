<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 12.11.2018
 * Time: 00:22
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Irvobmagturs\InvoiceCore\Model\ValueObject\BillingPeriod;
use Jubjubbird\Respects\Serializable;

class InvoiceHasCoveredBillingPeriod implements Serializable
{
    /**
     * @var
     */
    private $period;

    /**
     * InvoiceBillingPeriodCovered constructor.
     * @param BillingPeriod $period
     */
    public function __construct(BillingPeriod $period)
    {
        $this->period = $period;;
    }

    /**
     * @param array $data
     * @return InvoiceHasCoveredBillingPeriod
     * @throws \Exception
     */
    public static function deserialize(array $data): self
    {
        return new self(BillingPeriod::deserialize($data[0]));
    }

    /**
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }

    function serialize(): array
    {
        return [$this->period->serialize()];
    }
}