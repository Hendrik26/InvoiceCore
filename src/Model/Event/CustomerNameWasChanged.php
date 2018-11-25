<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 06.11.18
 * Time: 09:27
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Jubjubbird\Respects\Serializable;

class CustomerNameWasChanged implements Serializable
{
    /**
     * @var
     */
    private $customerName;

    /**
     * CustomerNameWasChanged constructor.
     * @param string $customerName
     */
    public function __construct(string $customerName)
    {
        $this->customerName = $customerName;
    }

    /**
     * @param array $data
     * @return CustomerNameWasChanged
     */
    public static function deserialize(array $data): self
    {
        return new self($data[0]);
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [$this->customerName];
    }
}