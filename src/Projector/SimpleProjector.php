<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Projector;

use Irvobmagturs\InvoiceCore\Infrastructure\EventBus;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerAddressWasChanged;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerHasEngagedInBusiness;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerNameWasChanged;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerSalesTaxNumberWasChanged;
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
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Jubjubbird\Respects\DomainEvent;
use Jubjubbird\Respects\DomainEvents;
use stdClass;
use function Verraes\ClassFunctions\short;

class SimpleProjector implements EventBus
{
    /** @var string[] */
    private $directoryMap;
    private $versionMap = [];

    public function __construct(string $invoiceDir, string $customerDir)
    {
        $this->directoryMap[InvoiceId::class] = $invoiceDir;
        $this->directoryMap[CustomerId::class] = $customerDir;
    }

    function dispatch(DomainEvents $domainEvents): void
    {
        /** @var DomainEvent $domainEvent */
        foreach ($domainEvents as $domainEvent) {
            $this->apply($domainEvent);
        }
    }

    /**
     * Delegate the application of the event to the appropriate when... method, e. g. a VisitorHasLeft event will be
     * processed by the (private) method whenVisitorHasLeft(VisitorHasLeft $event): void
     * @param DomainEvent $event
     */
    protected function apply(DomainEvent $event): void
    {
        $id = $event->getAggregateId();
        $version = $this->versionMap[strval($id)] ?? 0;
        $dir = $this->directoryMap[get_class($id)];
        $inFile = sprintf('%s/%s.json', rtrim($dir, '/'), strval($id), $version);
        $outFile = sprintf('%s/%s.json', rtrim($dir, '/'), strval($id), ++$version);
        if (is_file($inFile)) {
            $aggregate = json_decode(file_get_contents($inFile));
        } else {
            $aggregate = new stdClass();
        }
        $method = 'when' . short($event->getPayload());
        $this->$method($event->getPayload(), $aggregate, $event);
        file_put_contents(
            $outFile,
            json_encode($aggregate, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        $this->versionMap[strval($id)] = $version;
    }

    private function whenCustomerAddressWasChanged(
        CustomerAddressWasChanged $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $address = [];
        $address['addressLine1'] = $event->getCustomerAddress()->addressLine1;
        $address['addressLine2'] = $event->getCustomerAddress()->addressLine2;
        $address['addressLine3'] = $event->getCustomerAddress()->addressLine3;
        $address['city'] = $event->getCustomerAddress()->city;
        $address['countryCode'] = $event->getCustomerAddress()->countryCode;
        $address['postalCode'] = $event->getCustomerAddress()->postalCode;
        $aggregate->billingAddress = (object)array_filter($address, function ($x) {
            return $x !== null;
        });
    }

    private function whenCustomerHasEngagedInBusiness(
        CustomerHasEngagedInBusiness $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->name = $event->getCustomerName();
        $address = [];
        $address['addressLine1'] = $event->getBillingAddress()->addressLine1;
        $address['addressLine2'] = $event->getBillingAddress()->addressLine2;
        $address['addressLine3'] = $event->getBillingAddress()->addressLine3;
        $address['city'] = $event->getBillingAddress()->city;
        $address['countryCode'] = $event->getBillingAddress()->countryCode;
        $address['postalCode'] = $event->getBillingAddress()->postalCode;
        $aggregate->billingAddress = (object)array_filter($address, function ($x) {
            return $x !== null;
        });
    }

    private function whenCustomerNameWasChanged(
        CustomerNameWasChanged $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->name = $event->getCustomerName();
    }

    private function whenCustomerSalesTaxNumberWasChanged(
        CustomerSalesTaxNumberWasChanged $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->vatid = $event->getCustomerSalesTaxNumber();
    }

    private function whenInvoiceBecameInternational(
        InvoiceBecameInternational $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->countryCode = $event->getCountryCode();
        $aggregate->vatid = $event->getCustomerSalesTaxNumber();
    }

    private function whenInvoiceBecameNational(
        InvoiceBecameNational $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        unset($aggregate->countryCode);
        unset($aggregate->vatid);
    }

    private function whenInvoiceDateHasBeenSet(
        InvoiceDateHasBeenSet $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->date = $event->getInvoiceDate()->format(DATE_ATOM);
    }

    private function whenInvoiceDueDateHasBeenSet(
        InvoiceDueDateHasBeenSet $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->dueDate = $event->getInvoiceDueDate()->format(DATE_ATOM);
    }

    private function whenInvoiceEmployedSepaDirectDebit(
        InvoiceEmployedSepaDirectDebit $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->mandate = new stdClass();
        $aggregate->mandate->customerIban = $event->getMandate()->customerIban;
        $aggregate->mandate->mandateReference = $event->getMandate()->mandateReference;
    }

    private function whenInvoiceHasCoveredBillingPeriod(
        InvoiceHasCoveredBillingPeriod $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->billingPeriod = new stdClass();
        $aggregate->billingPeriod->startDate = $event->getPeriod()->startDate->format(DATE_ATOM);
        $aggregate->billingPeriod->endDate = $event->getPeriod()->endDate->format(DATE_ATOM);
    }

    private function whenInvoiceHasDroppedBillingPeriod(
        InvoiceHasDroppedBillingPeriod $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        unset($aggregate->billingPeriod);
    }

    private function whenInvoiceRefrainedSepaDirectDebit(
        InvoiceRefrainedSepaDirectDebit $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        unset($aggregate->mandate);
    }

    private function whenInvoiceWasOpened(
        InvoiceWasOpened $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->customerId = strval($event->getCustomerId());
        $aggregate->invoiceDate = $event->getInvoiceDate()->format(DATE_ATOM);
        $aggregate->lineItems = [];
        $aggregate->invoiceNumber = $event->getInvoiceNumber();
    }

    private function whenLineItemWasAppended(
        LineItemWasAppended $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $item = [];
        $item['price']['amount'] = $event->getItem()->price->amount;
        $item['price']['currency'] = $event->getItem()->price->currency;
        $item['date'] = $event->getItem()->date->format(DATE_ATOM);
        $item['quantity'] = $event->getItem()->quantity;
        $item['timeBased'] = $event->getItem()->timeBased;
        $item['title'] = $event->getItem()->title;
        array_splice(
            $aggregate->lineItems,
            $event->getPosition(),
            0,
            [
                (object)array_filter($item, function ($x) {
                    return $x !== null;
                })
            ]
        );
    }

    private function whenLineItemWasRemoved(
        LineItemWasRemoved $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        array_splice($aggregate->lineItems, $event->getPosition(), 1);
    }

    private function whenPaymentReferenceHasBeenRequested(
        PaymentReferenceHasBeenRequested $event,
        stdClass $aggregate,
        DomainEvent $recordedEvent
    ): void {
        $aggregate->paymentReference = $event->getPaymentReference();
    }
}
