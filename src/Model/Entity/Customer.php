<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 05.11.18
 * Time: 08:38
 */

namespace Irvobmagturs\InvoiceCore\Model\Entity;


use Buttercup\Protects\IdentifiesAggregate;
use Buttercup\Protects\RecordsEvents;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateHistory;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateRoot;
use Irvobmagturs\InvoiceCore\Infrastructure\ApplyCallsWhenMethod;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordsEventsForBusinessMethods;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerAddressWasChanged;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerHasEngagedInBusiness;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerSalesTaxNumberWasChanged;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerSalesTaxNumber;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Address;

class Customer implements AggregateRoot
{
    use ApplyCallsWhenMethod;
    use RecordsEventsForBusinessMethods;
    private $customerName;

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddress()
    {
        return $this->customerAddress;
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return mixed
     */
    public function getCustomerSalesTaxNumber()
    {
        return $this->customerSalesTaxNumber;
    }
    private $customerAddress;
    /**
     * @var CustomerId
     */
    private $customerId;
    private $customerSalesTaxNumber;


    /**
     * Customer constructor.
     */
    private function __construct(CustomerId $customerId)
    {
        $this->customerId = $customerId;
    }

    public static function engageInBusiness(CustomerId $customerId, string $customerName, Address $billingAddress): self
    {
        $customer = new self($customerId);
        $customer->recordThat(new CustomerHasEngagedInBusiness($customerName, $billingAddress));
        return $customer;
    }

    /**
     * @param AggregateHistory $aggregateHistory
     * @return RecordsEvents
     * @throws InvalidCustomerId
     */
    public static function reconstituteFrom(AggregateHistory $aggregateHistory)
    {
        $customer = new self(CustomerId::fromString(strval($aggregateHistory->getAggregateId())));
        foreach ($aggregateHistory as $event) {
            $customer->apply($event);
        }
        return $customer;
    }

    /**
     * @param Address $customerAddress
     * @throws InvalidCustomerAddress
     */
    public function changeCustomerAddress(Address $customerAddress)
    {
        $this->guardInvalidCustomerAddress($customerAddress);
        $this->recordThat(new CustomerAddressWasChanged($customerAddress));
    }

    /**
     * @param string $salesTaxNumber
     * @throws InvalidCustomerSalesTaxNumber
     */
    public function changeCustomerSalesTaxNumber(string $salesTaxNumber)
    {
        $this->guardEmptySalesTaxNumber($salesTaxNumber);
        $this->recordThat(new CustomerSalesTaxNumberWasChanged($salesTaxNumber));
    }

    /**
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        return $this->customerId;
    }

    /**
     * @param string $salesTaxNumber
     * @throws InvalidCustomerSalesTaxNumber
     */
    private function guardEmptySalesTaxNumber(string $salesTaxNumber)
    {
        if (trim($salesTaxNumber) === "") {
            throw new InvalidCustomerSalesTaxNumber();
        }

    }

    /**
     * @param string $customerAddress
     * @throws InvalidCustomerAddress
     */
    private function guardInvalidCustomerAddress(Address $customerAddress)
    {
        if (trim($customerAddress->countryCode) === "") {
            throw new InvalidCustomerAddress();
        }
    }

    /**
     * @param CustomerAddressWasChanged $event
     */
    private function whenCustomerAddressWasChanged(CustomerAddressWasChanged $event)
    {
        $this->customerAddress = $event->getCustomerAddress();
    }

    private function whenCustomerHasEngagedInBusiness(CustomerHasEngagedInBusiness $event)
    {
        // TODO
        $this->customerName = $event->getCustomerName();
        $this->customerAddress = $event->getBillingAddress();
    }

    /**
     * @param CustomerSalesTaxNumberWasChanged $event
     */
    private function whenCustomerSalesTaxNumberWasChanged(CustomerSalesTaxNumberWasChanged $event)
    {
        $this->customerSalesTaxNumber = $event->getCustomerSalesTaxNumber();
    }
}
