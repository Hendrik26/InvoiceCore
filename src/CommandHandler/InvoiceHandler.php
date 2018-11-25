<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandlerInterface;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\BillingPeriod;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;
use Irvobmagturs\InvoiceCore\Repository\InvoiceNotFound;
use Irvobmagturs\InvoiceCore\Repository\InvoiceRepository;
use Jubjubbird\Respects\CorruptAggregateHistory;
use Jubjubbird\Respects\DomainEvents;


class InvoiceHandler implements CqrsCommandHandlerInterface
{
    private $repository;

    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws InvalidInvoiceId
     * @throws InvalidLineItemTitle
     * @throws Exception
     */
    public function appendLineItem(string $aggregateId, array $args): void
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
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * * @throws Exception
     */
    public function becomeInternational(string $aggregateId, array $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->becomeInternational($args['countryCode'], $args['customerSalesTaxNumber']);
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function becomeNational(string $aggregateId, array $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->becomeNational();
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws Exception
     */
    public function chargeCustomer(string $aggregateId, array $args): void
    {
        $this->guardUniqueInvoice($aggregateId);
        $invoice = Invoice::chargeCustomer(
            InvoiceId::fromString($aggregateId),
            CustomerId::fromString($args['customerId']),
            $args['invoiceNumber'],
            $this->nullableStringToDate($args['invoiceDate'] ?? null)
        );
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @throws CorruptAggregateHistory
     * @throws InvoiceExists
     * @throws InvalidInvoiceId
     */
    private function guardUniqueInvoice(string $aggregateId): void
    {
        try {
            $this->repository->load(InvoiceId::fromString($aggregateId));
        } catch (InvoiceNotFound $e) {
            return;
        }
        throw new InvoiceExists();
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws Exception
     */
    public function coverBillingPeriod(string $aggregateId, array $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $periodSpec = $args['period'];
        $startDate = new DateTimeImmutable($periodSpec['startDate']['iso8601value']);
        $endDate = new DateTimeImmutable($periodSpec['endDate']['iso8601value']);
        $invoice->coverBillingPeriod(
            new BillingPeriod($startDate, $endDate)
        );
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function dropBillingPeriod(string $aggregateId, array $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->dropBillingPeriod();
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function employSepaDirectDebit(string $aggregateId, array $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $mandateSpec = $args['mandate'];
        $invoice->employSepaDirectDebit(
            new SepaDirectDebitMandate($mandateSpec['mandateReference'], $mandateSpec['customerIban'])
        );
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function refrainFromSepaDirectDebit(string $aggregateId, array $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->refrainFromSepaDirectDebit();
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function removeLineItemByPosition(string $aggregateId, array $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoice->removeLineItemByPosition($args['position']);
        $this->repository->save($invoice);
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws \Buttercup\Protects\CorruptAggregateHistory
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     * @throws Exception
     */
    public function setInvoiceDate(string $aggregateId, array $args): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->repository->load(InvoiceId::fromString($aggregateId));
        $invoiceDateSpec = $args['invoiceDate']['iso8601value'];
        $invoice->setInvoiceDate(
            new DateTimeImmutable($invoiceDateSpec)
        );
        $this->repository->save($invoice);
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
