<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidInvoiceId;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;

class InvoiceHandler extends CqrsCommandHandler
{
    /**
     * @param string $aggregateId
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @return bool
     * @throws InvalidInvoiceId
     * @throws InvalidLineItemTitle
     * @throws Exception
     */
    public function appendLineItem(string $aggregateId, array $args, $context, ResolveInfo $info): bool
    {
        $invoice = new Invoice(InvoiceId::fromString($aggregateId));
        $itemSpec = $args['item'];
        $invoice->appendLineItem(
            new LineItem(
                0, // TODO remove explicit position
                new Money($itemSpec['price']['amount'], $itemSpec['price']['currency']),
                $itemSpec['quantity'],
                $itemSpec['title'],
                $itemSpec['timeBased'],
                new DateTimeImmutable($itemSpec['date'], new DateTimeZone('UTC'))
            )
        );
        return true;
    }
}
