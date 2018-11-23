<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\BillingPeriod;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;
use Irvobmagturs\InvoiceCore\Repository\InvoiceRepository;
use Jubjubbird\Respects\DomainEvents;


class InvoiceHandler extends CqrsCommandHandler
{
    private $repository;

    public function __construct(InvoiceRepository $repository, ?TypeResolver $base = null)
    {
        parent::__construct($base);
        $this->repository = $repository;
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
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $itemSpec = $args['item'];
        $invoice->appendLineItem(
            new LineItem(
                new Money($itemSpec['price']['amount'], $itemSpec['price']['currency']),
                $itemSpec['quantity'],
                $itemSpec['title'],
                $itemSpec['timeBased'],
                new DateTimeImmutable($itemSpec['date'], new DateTimeZone('UTC'))
            )
        );
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * * @throws Exception
     */
    public function becomeInternational(string $aggregateId, array $args): DomainEvents
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->becomeInternational($args['countryCode'], $args['customerSalesTaxNumber']);
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function becomeNational(string $aggregateId, array $args): DomainEvents
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->becomeNational();
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
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
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws Exception
     */
    public function coverBillingPeriod(string $aggregateId, array $args): DomainEvents
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $periodSpec = $args['period'];
        $startDate = new DateTimeImmutable($periodSpec['startDate']['iso8601value']);
        $endDate = new DateTimeImmutable($periodSpec['endDate']['iso8601value']);
        $invoice->coverBillingPeriod(
            new BillingPeriod($startDate, $endDate)
        );
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function dropBillingPeriod(string $aggregateId, array $args): DomainEvents
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->dropBillingPeriod();
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function employSepaDirectDebit(string $aggregateId, array $args): DomainEvents
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $mandateSpec = $args['mandate'];
        $invoice->employSepaDirectDebit(
            new SepaDirectDebitMandate($mandateSpec['mandateReference'], $mandateSpec['customerIban'])
        );
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function refrainFromSepaDirectDebit(string $aggregateId, array $args): DomainEvents
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->refrainFromSepaDirectDebit();
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function removeLineItemByPosition(string $aggregateId, array $args): DomainEvents
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->removeLineItemByPosition($args['position']);
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @return DomainEvents
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function setInvoiceDate(string $aggregateId, array $args): DomainEvents
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoiceDateSpec = $args['invoiceDate']['iso8601value'];
        $invoice->setInvoiceDate(
            new DateTimeImmutable($invoiceDateSpec)
        );
        $domainEvents = $invoice->getRecordedEvents();
        $invoice->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param $invoiceDate
     * @return DateTimeImmutable|null
     * @throws Exception
     */
    private function nullableStringToDate($invoiceDate)
    {
        return $invoiceDate ? new DateTimeImmutable($invoiceDate) : null;
    }
}
