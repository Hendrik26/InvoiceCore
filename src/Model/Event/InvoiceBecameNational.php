<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 08.11.18
 * Time: 14:04
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Jubjubbird\Respects\Serializable;

class InvoiceBecameNational implements Serializable
{
    /**
     * BecomeNational constructor.
     */
    public function __construct()
    {
        // nothing to do
    }

    /**
     * @param array $data
     * @return InvoiceBecameNational
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