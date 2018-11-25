<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use Irvobmagturs\InvoiceCore\CommandHandler\Exception\InvoiceExists;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyCountryCode;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerIban;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerSalesTaxNumber;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidSepaDirectDebitMandateReference;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\BillingPeriod;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;
use Irvobmagturs\InvoiceCore\Repository\InvoiceNotFound;
use Irvobmagturs\InvoiceCore\Repository\InvoiceRepository;
use Jubjubbird\Respects\CorruptAggregateHistory;

class InvoiceHandler implements CqrsCommandHandler
{
    private $repository;

    public function __construct(InvoiceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws CorruptAggregateHistory
     * @throws InvalidArgumentException
     * @throws InvalidInvoiceId
     * @throws InvalidLineItemTitle
     * @throws InvoiceNotFound
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
     * @throws CorruptAggregateHistory
     * @throws InvalidInvoiceId
     * @throws InvoiceNotFound
     * @throws EmptyCountryCode
     * @throws InvalidCustomerSalesTaxNumber
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
     * @throws CorruptAggregateHistory
     * @throws InvalidInvoiceId
     * @throws InvoiceNotFound
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
     * @throws CorruptAggregateHistory
     * @throws InvalidInvoiceId
     * @throws InvoiceExists
     * @throws InvalidCustomerId
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
     * @param array $args
     * @throws CorruptAggregateHistory
     * @throws InvalidArgumentException
     * @throws InvalidBillingPeriod
     * @throws InvalidInvoiceId
     * @throws InvoiceNotFound
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
     * @throws CorruptAggregateHistory
     * @throws InvalidInvoiceId
     * @throws InvoiceNotFound
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
     * @throws CorruptAggregateHistory
     * @throws InvalidArgumentException
     * @throws InvalidInvoiceId
     * @throws InvoiceNotFound
     * @throws InvalidCustomerIban
     * @throws InvalidSepaDirectDebitMandateReference
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
     * @throws CorruptAggregateHistory
     * @throws InvalidInvoiceId
     * @throws InvoiceNotFound
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
     * @throws CorruptAggregateHistory
     * @throws InvalidInvoiceId
     * @throws InvoiceNotFound
     * @throws InvalidLineItemPosition
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
     * @throws CorruptAggregateHistory
     * @throws InvalidInvoiceId
     * @throws InvoiceNotFound
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
     * @param string $aggregateId
     * @throws CorruptAggregateHistory
     * @throws InvalidInvoiceId
     * @throws InvoiceExists
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
     * @param $invoiceDate
     * @return DateTimeImmutable|null
     * @throws Exception
     */
    private function nullableStringToDate($invoiceDate)
    {
        return $invoiceDate ? new DateTimeImmutable($invoiceDate) : null;
    }
}
