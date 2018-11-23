<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use Exception;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;
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

    public function __construct(CustomerRepository $repository, ?TypeResolver $base = null)
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

    /**
     * @param $aggregateId
     * @param array $args
     * @throws CorruptAggregateHistory
     * @throws Exception
     */
    public function relocate($aggregateId, array $args): void
    {
        /** @var Customer $customer */
        $customer = $this->repository->load(CustomerId::fromString($aggregateId));
        $billingAddress = $args['billingAddress'];
        $customer->relocate(
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
     * @param $aggregateId
     * @param array $args
     * @throws CorruptAggregateHistory
     * @throws Exception when one of the events is not a DomainEvent.
     */
    public function rename($aggregateId, array $args): void
    {
        /** @var Customer $customer */
        $customer = $this->repository->load(CustomerId::fromString($aggregateId));
        $customer->rename($args['name']);
        $this->repository->save($customer);
    }

    /**
     * @param $aggregateId
     * @param array $args
     * @throws CorruptAggregateHistory
     * @throws Exception when one of the events is not a DomainEvent.
     */
    public function assignTaxIdentification($aggregateId, array $args): void
    {
        /** @var Customer $customer */
        $customer = $this->repository->load(CustomerId::fromString($aggregateId));
        $customer->assignTaxIdentification($args['idNumber']);
        $this->repository->save($customer);
    }
}
