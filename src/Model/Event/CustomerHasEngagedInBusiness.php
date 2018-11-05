<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 05.11.2018
 * Time: 21:36
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Address;


class CustomerHasEngagedInBusiness implements Serializable
{
    private $billingAddress;
    private $customerName;



    /**
     * CustomerHasEngagedInBusiness constructor.
     * @param string $customerName
     * @param Address $billingAddress
     */
    public function __construct(string $customerName, Address $billingAddress)
    {
        $this->customerName = $customerName;
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return Address
     */
    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    /**
     * @return string
     */
    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    function serialize(): array
    {
        return [$this->customerName, $this->billingAddress->serialize()];
    }

    static function deserialize(array $data): self
    {
        return new self($data[0], Address::deserialize($data[1]));
    }


}