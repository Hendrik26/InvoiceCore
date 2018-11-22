<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 22.11.18
 * Time: 13:28
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Jubjubbird\Respects\Serializable;


class PaymentReferenceHasBeenRequested implements Serializable
{
    private $paymentReference;

    /**
     * @return String
     */
    public function getPaymentReference(): String
    {
        return $this->paymentReference;
    }

    /**
     * PaymentReferenceHasBeenRequested constructor.
     */
    public function __construct(String $paymentReference)
    {
        $this->paymentReference = $paymentReference;
    }

    public function serialize(): array
    {
        return [$this->paymentReference];
    }

    /**
     * @param array $data
     * @return static The object instance
     */
    static function deserialize(array $data)
    {
        // TODO: Implement deserialize() method.
        return new self($data[0]);
    }
}