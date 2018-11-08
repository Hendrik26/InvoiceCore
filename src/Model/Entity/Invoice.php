<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 01.11.18
 * Time: 10:27
 */

namespace Irvobmagturs\InvoiceCore\Model\Entity;

use Buttercup\Protects\IdentifiesAggregate;
use Buttercup\Protects\RecordsEvents;
use DateTimeInterface;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateHistory;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateRoot;
use Irvobmagturs\InvoiceCore\Infrastructure\ApplyCallsWhenMethod;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordsEventsForBusinessMethods;
use Irvobmagturs\InvoiceCore\Model\Event\BecomeInternational;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceWasOpened;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasRemoved;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyCountryCode;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyInvoiceNumber;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerSalesTaxNumber;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;


class Invoice implements AggregateRoot
{
    use RecordsEventsForBusinessMethods;
    use ApplyCallsWhenMethod;

    /** @var InvoiceId */
    private $aggregateId;

    /**
     * @var
     */
    private $customerId;

    /**
     * @var
     */
    private $customerSalesTaxNumber;

    /**
     * @var
     */
    private $invoiceNumber;

    /**
     * @var
     */
    private $invoiceDate;

    /** @var LineItem[] */
    private $lineItems = [];

    /**
     * Invoice constructor.
     * @param InvoiceId $aggregateId
     */
    private function __construct(InvoiceId $aggregateId)
    {
        $this->aggregateId = $aggregateId;
    }

    /**
     * @param InvoiceId $invoiceId
     * @param CustomerId $customerId
     * @param string $invoiceNumber
     * @param DateTimeInterface $invoiceDate
     * @return Invoice
     * @throws EmptyInvoiceNumber
     */
    public static function chargeCustomer( // Factory-Method for Invoice
        InvoiceId $invoiceId,
        CustomerId $customerId,
        string $invoiceNumber,
        DateTimeInterface $invoiceDate
    ): self {
        $invoice = new self($invoiceId);
        $invoice->customerId = $customerId;
        $invoice->guardEmptyInvoiceNumber($invoiceNumber);
        $invoice->recordThat(new InvoiceWasOpened($customerId, $invoiceNumber, $invoiceDate));
        return $invoice;
    }

    /**
     * @param AggregateHistory $aggregateHistory
     * @return RecordsEvents
     * @throws InvalidInvoiceId
     */
    public static function reconstituteFrom(AggregateHistory $aggregateHistory)
    {
        $invoice = new self(InvoiceId::fromString(strval($aggregateHistory->getAggregateId())));
        foreach ($aggregateHistory as $event) {
            $invoice->apply($event);
        }
        return $invoice;
    }

    /**
     * @param LineItem $item
     * @throws InvalidLineItemTitle
     */
    public function appendLineItem(LineItem $item): void
    {
        $this->guardEmptyTitle($item);
        $this->recordThat(new LineItemWasAppended(count($this->lineItems), $item));
    }

    /**
     * @return IdentifiesAggregate
     */
    public function getAggregateId(): IdentifiesAggregate
    {
        return $this->aggregateId;
    }

    /**
     * @param int $position
     */
    public function removeLineItemByPosition(int $position)
    {
        $this->guardInvalidPosition($position);
        $this->recordThat(new LineItemWasRemoved($position));
    }

    /**
     * @param LineItem $item
     * @throws InvalidLineItemTitle
     */
    private function guardEmptyTitle(LineItem $item): void
    {
        if (trim($item->title) === "") {
            throw new InvalidLineItemTitle();
        }
    }

    /**
     * @param int $position
     */
    private function guardInvalidPosition(int $position): void
    {
        if (($position < 0) || $position >= count($this->lineItems)) {
            throw new InvalidLineItemPosition();
        }
    }

    /**
     * @param LineItemWasAppended $event
     */
    private function whenLineItemWasAppended(LineItemWasAppended $event)
    {
        $this->lineItems[] = $event->getItem();
    }

    /**
     * @param LineItemWasAppended $event
     */
    private function whenLineItemWasRemoved(LineItemWasRemoved $event)
    {
        array_splice($this->lineItems, $event->getPosition(), 1);
    }

    /**
     * @param string $invoiceNumber
     */
    private function guardEmptyInvoiceNumber(string $invoiceNumber)
    {
        if (trim($invoiceNumber) === "") {
            throw new EmptyInvoiceNumber();
        }
    }

    /**
     * @param InvoiceWasOpened $event
     */
    private function whenInvoiceWasOpened(InvoiceWasOpened $event)
    {
        $this->customerId = $event->getCustomerId();
        $this->invoiceNumber = $event->getInvoiceNumber();
        $this->invoiceDate = $event->getInvoiceDate();
    }

    /**
     * @param string $countryCode
     * @param string $customerSalesTaxNumber
     */
    public function becomeInternational(string $countryCode, string $customerSalesTaxNumber)
    {
        $this->guardEmptyCountryCode($countryCode);
        $this->guardEmptyCustomerSalesTaxNumber($customerSalesTaxNumber);
        $this->recordThat(new BecomeInternational($countryCode, $customerSalesTaxNumber));
    }

    /**
     * @param string $countryCode
     */
    private function guardEmptyCountryCode(string $countryCode)
    {
        if (trim($countryCode) === "") {
            throw new EmptyCountryCode();
        }
    }

    /**
     * @param string $salesTaxNumber
     */
    private function guardEmptyCustomerSalesTaxNumber(string $salesTaxNumber)
    {
        if (trim($salesTaxNumber) === "") {
            throw new InvalidCustomerSalesTaxNumber();
        }
    }

    private function whenBecameInternational(BecomeInternational $event)
    {
        $this->customerSalesTaxNumber = $this->customerSalesTaxNumber;
    }


}
