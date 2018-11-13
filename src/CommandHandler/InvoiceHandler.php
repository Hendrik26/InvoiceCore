<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use Buttercup\Protects\DomainEvents;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateHistory;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;
use Irvobmagturs\InvoiceCore\Model\ValueObject\BillingPeriod;



class InvoiceHandler extends CqrsCommandHandler
{
    /** @var Invoice[] */
    private $invoice = [];

    /**
     * @param $invoiceDate
     * @return DateTimeImmutable|null
     * @throws Exception
     */
    private function nullableStringToDate($invoiceDate)
    {
        return $invoiceDate ? new DateTimeImmutable($invoiceDate) : null;
    }


    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws Exception
     */
    public function chargeCustomer(string $aggregateId, array $args): DomainEvents
    {
        $invoice = Invoice::chargeCustomer(
            InvoiceId::fromString($aggregateId),
            CustomerId::fromString($args['customerId']),
            $args['invoiceNumber'],
            $this->nullableStringToDate($args['invoiceDate'] ?? null)
        );
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws InvalidInvoiceId
     * @throws InvalidLineItemTitle
     * @throws Exception
     */
    public function appendLineItem(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(new AggregateHistory(InvoiceId::fromString($aggregateId), []));
        $itemSpec = $args['item'];
        $this->invoice[$aggregateId]->appendLineItem(
            new LineItem(
                new Money($itemSpec['price']['amount'], $itemSpec['price']['currency']),
                $itemSpec['quantity'],
                $itemSpec['title'],
                $itemSpec['timeBased'],
                new DateTimeImmutable($itemSpec['date'], new DateTimeZone('UTC'))
            )
        );
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     */
    public function removeLineItemByPosition(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(
            new AggregateHistory(InvoiceId::fromString($aggregateId), [])
            );
        $this->invoice[$aggregateId]->removeLineItemByPosition($args['position']);
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     */
    public function becomeInternational(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(new AggregateHistory(InvoiceId::fromString($aggregateId), []));
        $this->invoice[$aggregateId]->becomeInternational($args['countryCode'], $args['customerSalesTaxNumber']);
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     */
    public function becomeNational(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(new AggregateHistory(InvoiceId::fromString($aggregateId), []));
        $this->invoice[$aggregateId]->becomeNational();
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     */
    public function employSepaDirectDebit(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(new AggregateHistory(InvoiceId::fromString($aggregateId), []));
        $mandateSpec = $args['mandate'];
        $this->invoice[$aggregateId]->employSepaDirectDebit(
            new SepaDirectDebitMandate($mandateSpec['mandateReference'], $mandateSpec['customerIban'])
        );
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     */
    public function refrainFromSepaDirectDebit(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(new AggregateHistory(InvoiceId::fromString($aggregateId), []));
        $this->invoice[$aggregateId]->refrainFromSepaDirectDebit();
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws Exception
     */
    public function coverBillingPeriod(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(
            new AggregateHistory(InvoiceId::fromString($aggregateId), [])
            );
        $periodSpec = $args['period'];
        $startDate = new DateTimeImmutable($periodSpec['startDate']['iso8601value']);
        $endDate = new DateTimeImmutable($periodSpec['endDate']['iso8601value']);
        $this->invoice[$aggregateId]->coverBillingPeriod(
            new BillingPeriod($startDate, $endDate)
        );
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     */
    public function dropBillingPeriod(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(
            new AggregateHistory(InvoiceId::fromString($aggregateId), [])
            );
        $this->invoice[$aggregateId]->dropBillingPeriod();
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws Exception
     */
    public function setInvoiceDate(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? Invoice::reconstituteFrom(
                new AggregateHistory(InvoiceId::fromString($aggregateId), [])
            );
        $invoiceDateSpec = $args['invoiceDate']['iso8601value'];
        $this->invoice[$aggregateId]->setInvoiceDate(
            new DateTimeImmutable($invoiceDateSpec)
        );
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }


}
