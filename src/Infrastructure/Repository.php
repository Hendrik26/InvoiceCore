<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 15.11.18
 * Time: 10:04
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\IdentifiesAggregate;

interface Repository
{
    /**
     * @param IdentifiesAggregate $id
     * @return AggregateRoot
     */
    public function load(IdentifiesAggregate $id): AggregateRoot;

    /**
     * @param AggregateRoot $aggregateRoot
     */
    public function save(AggregateRoot $aggregateRoot): void;
}
