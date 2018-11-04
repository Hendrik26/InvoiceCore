<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 04.11.2018
 * Time: 20:48
 */

namespace Irvobmagturs\InvoiceCore\Model\Entity;


class Customer implements AggregateRoot
{
    /**
     * Invoice constructor.
     * @param InvoiceId $aggregateId
     */
    public function __construct(InvoiceId $aggregateId)
    {
        $this->aggregateId = $aggregateId;
    }

}