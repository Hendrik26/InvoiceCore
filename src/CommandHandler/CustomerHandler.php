<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\CommandHandler;

use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandler;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;
use Irvobmagturs\InvoiceCore\Model\Entity\Customer;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidCustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Address;
use Irvobmagturs\InvoiceCore\Repository\CustomerNotFound;
use Irvobmagturs\InvoiceCore\Repository\CustomerRepository;
use Jubjubbird\Respects\CorruptAggregateHistory;
use Jubjubbird\Respects\AggregateHistory;
use Jubjubbird\Respects\DomainEvents;


class CustomerHandler extends CqrsCommandHandler
{
    /** @var Customer[] */
    private $customer = [];
    private $repository;

    public function __construct(?TypeResolver $base, CustomerRepository $repository)
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
     * @return mixed
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     */
    public function relocate($aggregateId, array $args)
    {
        //      * @throws Exception
        $this->customer[$aggregateId] = $this->customer[$aggregateId] ?? Customer::reconstituteFrom(new AggregateHistory(CustomerId::fromString($aggregateId), []));
        // $this->customer[$aggregateId]->changeCustomerAddress($args['customerAddress']);
        $billingAddress = $args['billingAddress'];
        $this->customer[$aggregateId]->relocate(
            new Address(
                $billingAddress['countryCode'],
                $billingAddress['postalCode'],
                $billingAddress['city'],
                $billingAddress['addressLine1'] ?? null,
                $billingAddress['addressLine2'] ?? null,
                $billingAddress['addressLine3'] ?? null
            )        );
        $domainEvents = $this->customer[$aggregateId]->getRecordedEvents();
        $this->customer[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param $aggregateId
     * @param array $args
     * @return mixed
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     */
    public function rename($aggregateId, array $args)
    {
        $this->customer[$aggregateId] = $this->customer[$aggregateId] ?? Customer::reconstituteFrom(new
            AggregateHistory(CustomerId::fromString($aggregateId), []));
        $this->customer[$aggregateId]->rename($args['idNumber']);
        $domainEvents = $this->customer[$aggregateId]->getRecordedEvents();
        $this->customer[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

    /**
     * @param $aggregateId
     * @param array $args
     * @return mixed
     * @throws \Jubjubbird\Respects\CorruptAggregateHistory
     */
    public function assignTaxIdentification($aggregateId, array $args) // changeCustomerSalesTaxNumber(string $salesTaxNumber)
    {
        $this->customer[$aggregateId] = $this->customer[$aggregateId] ?? Customer::reconstituteFrom(new
            AggregateHistory(CustomerId::fromString($aggregateId), []));
        $this->customer[$aggregateId]->assignTaxIdentification($args['idNumber']);
        $domainEvents = $this->customer[$aggregateId]->getRecordedEvents();
        $this->customer[$aggregateId]->clearRecordedEvents();
        return $domainEvents;
    }

}
