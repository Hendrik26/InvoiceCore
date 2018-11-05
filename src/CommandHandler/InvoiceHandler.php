<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use Buttercup\Protects\DomainEvents;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;

class InvoiceHandler extends CqrsCommandHandler
{
    /** @var Invoice[] */
    private $invoice = [];

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
        $this->invoice[$aggregateId] = $this->invoice[$aggregateId] ?? new Invoice(InvoiceId::fromString($aggregateId));
        $itemSpec = $args['item'];
        $this->invoice[$aggregateId]->appendLineItem(
            new LineItem(
                0, // TODO remove explicit position
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
     */
    public function removeLineItemByPosition(string $aggregateId, array $args): DomainEvents
    {
        $this->invoice[$aggregateId]->removeLineItemByPosition($args['position']);
        $domainEvents = $this->invoice[$aggregateId]->getRecordedEvents();
        $this->invoice[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }
}
