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

    public function __construct(string $paymentReference)
    {
        $this->paymentReference = $paymentReference;
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

    /**
     * @return string
     */
    public function getPaymentReference(): string
    {
        return $this->paymentReference;
    }

    public function serialize(): array
    {
        return [$this->paymentReference];
    }
}