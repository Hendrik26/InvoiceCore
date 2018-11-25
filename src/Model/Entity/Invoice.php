<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 01.11.18
 * Time: 10:27
 */

namespace Irvobmagturs\InvoiceCore\Model\Entity;

use Buttercup\Protects\IdentifiesAggregate;
use DateTimeImmutable;
use DateTimeInterface;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceBecameInternational;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceBecameNational;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceDateHasBeenSet;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceDueDateHasBeenSet;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceEmployedSepaDirectDebit;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceHasCoveredBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceHasDroppedBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceRefrainedSepaDirectDebit;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceWasOpened;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasRemoved;
use Irvobmagturs\InvoiceCore\Model\Event\PaymentReferenceHasBeenRequested;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyCountryCode;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyInvoiceNumber;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyPaymentReference;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerIban;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerSalesTaxNumber;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidSepaDirectDebitMandateReference;
use Irvobmagturs\InvoiceCore\Model\Exception\InvoiceDateTooEarly;
use Irvobmagturs\InvoiceCore\Model\Exception\InvoiceDateTooLate;
use Irvobmagturs\InvoiceCore\Model\Exception\InvoiceDueDateTooEarly;
use Irvobmagturs\InvoiceCore\Model\Exception\InvoiceDueDateTooLate;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\BillingPeriod;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;
use Jubjubbird\Respects\AggregateHistory;
use Jubjubbird\Respects\AggregateRoot;
use Jubjubbird\Respects\ApplyCallsWhenMethod;
use Jubjubbird\Respects\RecordsEvents;
use Jubjubbird\Respects\RecordsEventsForBusinessMethods;

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
    private $invoiceDate; // done
    private $invoiceDueDate; // === $intendedUse;
    /**
     * @var
     */
    private $invoiceNumber; // done
    /** @var LineItem[] */
    private $lineItems = []; // done
    /**
     * @var
     */
    private $mandate; // done
    private $paymentReference; // done
    /**
     * @var
     */
    private $period; // done

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
     * @return self
     * @throws InvalidInvoiceId
     */
    public static function reconstituteFrom(AggregateHistory $aggregateHistory): RecordsEvents
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
     *
     */
    public function becomeNational()
    {
        $this->recordThat(new InvoiceBecameNational());
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
     *
     */
    public function dropBillingPeriod(): void
    {
        // TODO: write logic here
        $this->recordThat(new InvoiceHasDroppedBillingPeriod());
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
     * @return IdentifiesAggregate
     */
    public function getAggregateId(): IdentifiesAggregate
    {
        return $this->aggregateId;
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
     * @param int $position
     */
    public function removeLineItemByPosition(int $position)
    {
        $this->guardInvalidPosition($position);
        $this->recordThat(new LineItemWasRemoved($position));
    }

    /**
     * @param String $paymentReference
     */
    public function requestPaymentReference(String $paymentReference)
    {
        $this->guardPaymentReference($paymentReference);
        $this->recordThat(new PaymentReferenceHasBeenRequested($paymentReference));
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
    public function setInvoiceDueDate(DateTimeInterface $date)
    {
        $this->guardInvoiceDueDate($date);
        $this->recordThat(new InvoiceDueDateHasBeenSet($date));
    }

    /**
     * @param BillingPeriod $period
     */
    private function guardBillingPeriod(BillingPeriod $period)
    {
        if ($period->getInterval()->d < 0) {
            throw new InvalidBillingPeriod();
        }
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
     * @param string $invoiceNumber
     */
    private function guardEmptyInvoiceNumber(string $invoiceNumber)
    {
        if (trim($invoiceNumber) === "") {
            throw new EmptyInvoiceNumber();
        }
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
     * @param DateTimeInterface $date
     * @throws \Exception
     */
    private function guardInvoiceDate(DateTimeInterface $date)
    {
        $minDate = new DateTimeImmutable('1949-05-23');
        $maxDate = new DateTimeImmutable('2100-01-01');
        $interval = $minDate->diff($date); // DateInterval
        $interval2 = $date->diff($maxDate); // DateInterval
        if ($interval->d < 0) {
            throw new InvoiceDateTooEarly;
        }
        if ($interval2->d < 0) {
            throw new InvoiceDateTooLate;
        }
    }

    /**
     * @param DateTimeInterface $date
     * @throws \Exception
     */
    private function guardInvoiceDueDate(DateTimeInterface $date)
    {
        $minDate = new DateTimeImmutable('1949-05-23');
        $maxDate = new DateTimeImmutable('2100-01-01');
        $interval = $minDate->diff($date); // DateInterval
        $interval2 = $date->diff($maxDate); // DateInterval
        if ($interval->d < 0) {
            throw new InvoiceDueDateTooEarly;
        }
        if ($interval2->d < 0) {
            throw new InvoiceDueDateTooLate;
        }
    }

    /**
     * @param String $paymentReference
     */
    private function guardPaymentReference(String $paymentReference)
    {
        if (trim($paymentReference) === "") {
            throw new EmptyPaymentReference;
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
     * @param InvoiceBecameNational $event
     */
    private function whenInvoiceBecameNational(InvoiceBecameNational $event)
    {
        // nothing to do
    }

    /**
     * @param InvoiceDateHasBeenSet $event
     */
    private function whenInvoiceDateHasBeenSet(InvoiceDateHasBeenSet $event)
    {
        $this->invoiceDate = $event->getInvoiceDate();
    }

    /**
     * @param InvoiceDueDateHasBeenSet $event
     */
    private function whenInvoiceDueDateHasBeenSet(InvoiceDueDateHasBeenSet $event)
    {
        $this->invoiceDueDate = $event->getInvoiceDueDate();
    }

    /**
     * @param InvoiceEmployedSepaDirectDebit $event
     */
    private function whenInvoiceEmployedSepaDirectDebit(InvoiceEmployedSepaDirectDebit $event)
    {
        $this->mandate = $event->getMandate();
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
    private function whenInvoiceHasDroppedBillingPeriod(InvoiceHasDroppedBillingPeriod $event)// nothing to do
    {
        // nothing to do
    }

    /**
     * @param InvoiceRefrainedSepaDirectDebit $event
     */
    private function whenInvoiceRefrainedSepaDirectDebit(InvoiceRefrainedSepaDirectDebit $event)
    {
        // nothing to do
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
     * @param LineItemWasAppended $event
     */
    private function whenLineItemWasAppended(LineItemWasAppended $event)
    {
        $this->lineItems[] = $event->getItem();
    }

    /**
     * @param LineItemWasRemoved $event
     */
    private function whenLineItemWasRemoved(LineItemWasRemoved $event)
    {
        array_splice($this->lineItems, $event->getPosition(), 1);
    }

    /**
     * @param PaymentReferenceHasBeenRequested $event
     */
    private function whenPaymentReferenceHasBeenRequested(PaymentReferenceHasBeenRequested $event)
    {
        $this->paymentReference = $event->getPaymentReference();
    }
}
