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
use Irvobmagturs\InvoiceCore\Model\Event\CustomerAdressWasChanged;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;

class Customer implements AggregateRoot
{
   use ApplyCallsWhenMethod;
   use RecordsEventsForBusinessMethods;
    /**
     * @var CustomerId
     */
    private $customerId;
    private $customerAdress;
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
        // TODO: Implement reconstituteFrom() method.
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
     * @param string $customerAdress
     */
    public function changeCustomerAdress(string $customerAdress)
    {
      $this->guardEmptyCustomerAdress($customerAdress);
      $this->recordThat(new CustomerAdressWasChanged($customerAdress));
    }

    /**
     * @param string $customerAdress
     */
    private function guardEmptyCustomerAdress(string $customerAdress)
    {
        if ($customerAdress === "") {
            throw new InvalidCustomerAdress();
        }
    }

    private function whenCustomerAdressWasChanged(CustomerAdressWasChanged $event)
    {
        $this->customerAdress = $event->getCustomerAdress();
    }
}