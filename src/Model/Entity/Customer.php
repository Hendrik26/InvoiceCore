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
use Irvobmagturs\InvoiceCore\Model\Event\CustomerAddressWasChanged;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerHasEngagedInBusiness;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerSalesTaxNumberWasChanged;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerName;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerSalesTaxNumber;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Address;
use Jubjubbird\Respects\AggregateHistory;
use Jubjubbird\Respects\AggregateRoot;
use Jubjubbird\Respects\ApplyCallsWhenMethod;
use Jubjubbird\Respects\RecordsEventsForBusinessMethods;

class Customer implements AggregateRoot
{
    use ApplyCallsWhenMethod;
    use RecordsEventsForBusinessMethods;

    /**
     * @var
     */
    private $customerAddress;
    /**
     * @var CustomerId
     */
    private $customerId;
    /**
     * @var
     */
    private $customerName;
    /**
     * @var
     */
    private $customerSalesTaxNumber;

    /**
     * Customer constructor.
     */
    private function __construct(CustomerId $customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @param CustomerId $customerId
     * @param string $customerName
     * @param Address $billingAddress
     * @return Customer
     */
    public static function engageInBusiness(CustomerId $customerId, string $customerName, Address $billingAddress): self
    {
        $customer = new self($customerId);
        $customer->guardEmptyCustomerName($customerName);
        $customer->recordThat(new CustomerHasEngagedInBusiness($customerName, $billingAddress));
        return $customer;
    }

    /**
     * @param AggregateHistory $aggregateHistory
     * @return Customer
     * @throws InvalidCustomerId
     */
    public static function reconstituteFrom(AggregateHistory $aggregateHistory): self
    {
        $customer = new self(CustomerId::fromString(strval($aggregateHistory->getAggregateId())));
        foreach ($aggregateHistory as $event) {
            $customer->apply($event);
        }
        return $customer;
    }

    /**
     * @param string $customerName
     */
    public function changeCustomerName(string $customerName)
    {
        $this->guardEmptyCustomerName($customerName);
        $this->recordThat(new CustomerNameWasChanged($customerName));
    }

    /**
     * @param string $customerName
     */
    private function guardEmptyCustomerName(string $customerName)
    {
        if (trim($customerName) === "") {
            throw new InvalidCustomerName();
        }
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
     * @param string $customerAddress
     * @throws InvalidCustomerAddress
     */
    private function guardInvalidCustomerAddress(Address $customerAddress)
    {
        // TODO
        if (trim($customerAddress->countryCode) === "") {
            throw new InvalidCustomerAddress();
        }
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
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        return $this->customerId;
    }

    /**
     * @param CustomerAddressWasChanged $event
     */
    private function whenCustomerAddressWasChanged(CustomerAddressWasChanged $event)
    {
        $this->customerAddress = $event->getCustomerAddress();
    }

    /**
     * @param CustomerHasEngagedInBusiness $event
     */
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

    /**
     * @param CustomerNameWasChanged $event
     */
    private function whenCustomerNameWasChanged(CustomerNameWasChanged $event)
    {
        $this->customerName = $event->getCustomerName();
    }
}
