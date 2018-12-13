<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 12.11.18
 * Time: 11:28
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Jubjubbird\Respects\Serializable;

/**
 * Class InvoiceHasDroppedBillingPeriod
 * @package Irvobmagturs\InvoiceCore\Model\Event
 */
class InvoiceHasDroppedBillingPeriod implements Serializable
{
    /**
     * InvoiceHasDroppedBillingPeriod constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param array $data
     * @return InvoiceHasDroppedBillingPeriod
     */
    public static function deserialize(array $data): self
    {
        return new self();
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [-1];
    }
}