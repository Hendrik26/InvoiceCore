<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 04.11.2018
 * Time: 20:48
 */

namespace Irvobmagturs\InvoiceCore\Model\Entity;

use Buttercup\Protects\IdentifiesAggregate;
use Buttercup\Protects\RecordsEvents;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateHistory;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateRoot;
use Irvobmagturs\InvoiceCore\Infrastructure\ApplyCallsWhenMethod;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordsEventsForBusinessMethods;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasRemoved;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;



class Customer implements AggregateRoot
{
    use RecordsEventsForBusinessMethods;
    use ApplyCallsWhenMethod;
    /** @var InvoiceId */
    private $aggregateId;
    /** @var LineItem[] */
    private $lineItems = [];


    /**
     * Invoice constructor.
     * @param InvoiceId $aggregateId
     */
    public function __construct(InvoiceId $aggregateId)
    {
        $this->aggregateId = $aggregateId;
    }

    /**
     * @param string $
     */
    public function changeCustomerAdress(String $customerAdress): void
    {
        $this->guardEmptyAddress($customerAdress);
        $this->recordThat(new CustomerAdressWasChanged($customerAdress));
    }



    /**
     * @param LineItem $item
     * @throws InvalidLineItemTitle
     */
    private function guardEmptyAdress(String $customerAdress): void
    {
        if (trim($customerAdress) === "") {
            throw new InvalidCustomerAdress();
        }
    }

}