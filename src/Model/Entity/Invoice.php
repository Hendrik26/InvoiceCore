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
use DateTimeImmutable;
use DateTimeInterface;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateHistory;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateRoot;
use Irvobmagturs\InvoiceCore\Infrastructure\ApplyCallsWhenMethod;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordsEventsForBusinessMethods;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceBecameInternational;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceBecameNational;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceDateHasBeenSet;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceEmployedSepaDirectDebit;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceHasCoveredBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceHasDroppedBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceRefrainedSepaDirectDebit;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceWasOpened;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasRemoved;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyCountryCode;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyInvoiceNumber;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerIban;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerSalesTaxNumber;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidSepaDirectDebitMandateReference;
use Irvobmagturs\InvoiceCore\Model\Exception\toEarlyInvoiceDate;
use Irvobmagturs\InvoiceCore\Model\Exception\toLateInvoiceDate;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\BillingPeriod;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;


class Invoice implements AggregateRoot
{
    use RecordsEventsForBusinessMethods;
    use ApplyCallsWhenMethod;

    /** @var InvoiceId */
    private $aggregateId; // done???

    /**
     * @var
     */
    private $customerId; // done

    /**
     * @var
     */
    private $customerSalesTaxNumber; // done

    /**
     * @var
     */
    private $invoiceNumber; // done

    /**
     * @var
     */
    private $invoiceDate; // done

    /**
     * @var
     */
    private $mandate; // done

    /**
     * @var
     */
    private $period; // done

    /** @var LineItem[] */
    private $lineItems = []; // done

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
     * @throws \Exception
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
        $invoice->guardInvoiceDate($invoiceDate);
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
        $this->recordThat(new InvoiceBecameInternational($countryCode, $customerSalesTaxNumber));
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

    /**
     * @param InvoiceBecameInternational $event
     */
    private function whenInvoiceBecameInternational(InvoiceBecameInternational $event)
    {
        $this->customerSalesTaxNumber = $event->getCustomerSalesTaxNumber();
    }

    /**
     *
     */
    public function becomeNational()
    {
        $this->recordThat(new InvoiceBecameNational());
    }

    /**
     * @param InvoiceBecameNational $event
     */
    private function whenInvoiceBecameNational(InvoiceBecameNational $event)
    {
        // nothing to do
    }

    /**
     * @param SepaDirectDebitMandate $mandate
     */
    public function employSepaDirectDebit(SepaDirectDebitMandate $mandate): void
    {
        // TODO: write logic here // primary TODO
        $this->guardInvalidSepaDirectDebitMandate($mandate);
        $this->recordThat(new InvoiceEmployedSepaDirectDebit($mandate));
    }

    /**
     * @param SepaDirectDebitMandate $mandate
     */
    private function guardInvalidSepaDirectDebitMandate(SepaDirectDebitMandate $mandate)
    {
        if (trim($mandate->getCustomerIban()) === "") {
            throw new InvalidCustomerIban();
        }
        if (trim($mandate->getMandateReference()) === "") {
            throw new InvalidSepaDirectDebitMandateReference();
        }

    }

    /**
     * @param InvoiceEmployedSepaDirectDebit $event
     */
    private function whenInvoiceEmployedSepaDirectDebit(InvoiceEmployedSepaDirectDebit $event)
    {
        $this->mandate = $event->getMandate();

    }

    /**
     *
     */
    public function refrainFromSepaDirectDebit(): void
    {
        // TODO: write logic here
        $this->recordThat(new InvoiceRefrainedSepaDirectDebit());
    }

    /**
     * @param InvoiceRefrainedSepaDirectDebit $event
     */
    private function whenInvoiceRefrainedSepaDirectDebit(InvoiceRefrainedSepaDirectDebit $event)
    {
        // nothing to do
    }

    /**
     * @param BillingPeriod $period
     */
    public function coverBillingPeriod(BillingPeriod $period)
    {
        // TODO: write logic here
        $this->guardBillingPeriod($period);
        $this->recordThat(new InvoiceHasCoveredBillingPeriod($period));
    }

    /**
     * @param BillingPeriod $period
     */
    private function guardBillingPeriod(BillingPeriod $period)
    {
        if ($period->getInterval()->d < 0){
            throw new InvalidBillingPeriod();
        }
    }

    /**
     * @param InvoiceHasCoveredBillingPeriod $event
     */
    private function whenInvoiceHasCoveredBillingPeriod(InvoiceHasCoveredBillingPeriod $event)
    {
        $this->period = $event->getPeriod();
    }

    /**
     *
     */
    public function dropBillingPeriod(): void
    {
        // TODO: write logic here
        $this->recordThat(new InvoiceHasDroppedBillingPeriod());
    }

    /**
     *
     */
    private function whenInvoiceHasDroppedBillingPeriod(InvoiceHasDroppedBillingPeriod $event)// nothing to do
    {
        // nothing to do
    }

    /**
     * @param DateTimeInterface $date
     * @throws \Exception
     */
    public function setInvoiceDate(DateTimeInterface $date)
    {
        $this->guardInvoiceDate($date);
        $this->recordThat(new InvoiceDateHasBeenSet($date));
    }

    /**
     * @param DateTimeInterface $date
     * @throws \Exception
     */
    private function guardInvoiceDate(DateTimeInterface $date)
    {
        $minDate = new DateTimeImmutable('1949-05-23');
        $maxDate = new DateTimeImmutable('2100-01-01');
        $interval = $minDate->diff($date); // DateInterval
        $interval2 = $date->diff($maxDate); // DateInterval
        if ($interval->d < 0)
        {
            throw new toEarlyInvoiceDate;
        }
        if ($interval2->d < 0)
        {
            throw new toLateInvoiceDate;
        }
    }

    /**
     * @param InvoiceDateHasBeenSet $event
     */
    private function whenInvoiceDateHasBeenSet(InvoiceDateHasBeenSet $event)
    {
        $this->invoiceDate = $event->getInvoiceDate();
    }
}
