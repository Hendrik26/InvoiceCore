<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 08.11.18
 * Time: 14:04
 */

namespace Irvobmagturs\InvoiceCore\Model\Entity;


use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;

class BecomeNational implements Serializable
{

    /**
     * BecomeNational constructor.
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
     * @return BecomeNational
     */
    public static function deserialize(array $data): self
    {
        return new self();
    }


}