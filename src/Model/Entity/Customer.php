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
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerSalesTaxNumber;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Address;

class Customer implements AggregateRoot
{
   use ApplyCallsWhenMethod;
   use RecordsEventsForBusinessMethods;
    /**
     * @var CustomerId
     */
    private $customerId;
    private $customerAddress;
    private $salesTaxNumber;


    /**
     * Customer constructor.
     */
    public function __construct(CustomerId $customerId)
    {
        $this->customerId = $customerId;
    }

    /**
     * @param AggregateHistory $aggregateHistory
     * @return RecordsEvents
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
     * @return IdentifiesAggregate
     */
    public function getAggregateId()
    {
        return $this->customerId;
    }


    /**
     * @param Address $customerAddress
     */
    public function changeCustomerAddress(Address $customerAddress)
    {
      $this->guardInvalidCustomerAddress($customerAddress);
      $this->recordThat(new CustomerAddressWasChanged($customerAddress));
    }

    /**
     * @param string $customerAddress
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

    /**
     * @param string $salesTaxNumber
     */
    public function changeCustomerSalesTaxNumber(string $salesTaxNumber)
    {
        $this->guardEmptySalesTaxNumber($salesTaxNumber);
        $this->recordThat(new CustomerSalesTaxNumberWasChanged($salesTaxNumber));
    }

    /**
     * @param string $salesTaxNumber
     */
    private function guardEmptySalesTaxNumber(string $salesTaxNumber)
    {
        if (trim($salesTaxNumber) === "") {
            throw new InvalidCustomerSalesTaxNumber();
        }

    }

}