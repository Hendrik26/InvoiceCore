<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use Buttercup\Protects\DomainEvents;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Model\Entity\Customer;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Address;

class CustomerHandler extends CqrsCommandHandler
{
    public function engageInBusiness(string $aggregateId, array $args): DomainEvents
    {
        $billingAddress = $args['billingAddress'];
        $customer = Customer::engageInBusiness(
            CustomerId::fromString($aggregateId),
            $args['name'],
            new Address(
                $billingAddress['countryCode'],
                $billingAddress['postalCode'],
                $billingAddress['city'],
                $billingAddress['addressLine1'] ?? null,
                $billingAddress['addressLine2'] ?? null,
                $billingAddress['addressLine3'] ?? null
            )
        );
        $domainEvents = $customer->getRecordedEvents();
        $customer->clearRecordedEvents();
        return $domainEvents;
    }
}
