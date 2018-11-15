<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 09.11.2018
 * Time: 12:10
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Jubjubbird\Respects\Serializable;

class InvoiceRefrainedSepaDirectDebit implements Serializable
{

    /**
     * InvoiceRefrainedSepaDirectDebit constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [-1];
    }

    /**
     * @param array $data
     * @return InvoiceRefrainedSepaDirectDebit
     */
    public static function deserialize(array $data): self
    {
        return new self();
    }

}