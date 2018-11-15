<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Model\Entity\Customer;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Address;
use Irvobmagturs\InvoiceCore\Repository\CustomerNotFound;
use Irvobmagturs\InvoiceCore\Repository\CustomerRepository;
use Jubjubbird\Respects\CorruptAggregateHistory;

class CustomerHandler extends CqrsCommandHandler
{
    private $repository;

    public function __construct(?parent $base, CustomerRepository $repository)
    {
        parent::__construct($base);
        $this->repository = $repository;
    }

    /**
     * @param string $aggregateId
     * @param array $args
     * @throws CorruptAggregateHistory
     * @throws CustomerExists
     * @throws InvalidCustomerId
     */
    public function engageInBusiness(string $aggregateId, array $args): void
    {
        $this->guardUniqueCustomer($aggregateId);
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
        $this->repository->save($customer);
    }

    /**
     * @param string $aggregateId
     * @throws CorruptAggregateHistory
     * @throws CustomerExists
     * @throws InvalidCustomerId
     */
    private function guardUniqueCustomer(string $aggregateId): void
    {
        try {
            $this->repository->load(CustomerId::fromString($aggregateId));
        } catch (CustomerNotFound $e) {
            return;
        }
        throw new CustomerExists();
    }
}
