<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use Jubjubbird\Respects\DomainEvent;
use Verraes\ClassFunctions\ClassFunctions;

/**
 * The class name of a subclass matches one of the result types defined in CqrsCommandHandlers from the GraphQL schema
 * and provides a method for every field of that type:
 *
 *     public function ...(string $aggregateId, array $args, $context, ResolveInfo $info): DomainEvents
 *
 * $args is an associative array containing the arguments specified in the GraphQL mutation.
 * The return value must be the events recorded during the operation. These will be passed to the event bus that has to
 * be available from the context.
 */
abstract class CqrsCommandHandler extends TypeResolver
{
    /**
     * {@inheritDoc}
     */
    public function __construct(parent $base = null)
    {
        parent::__construct($base);
        foreach (get_class_methods($this) as $method) {
            $this->addResolverForField(
                ClassFunctions::short($this),
                $method,
                function ($typeValue, array $args, HoldsEventBus $context, ResolveInfo $info) use ($method) {
                    /** @var DomainEvent $event */
                    foreach ($this->$method($typeValue, $args, $context, $info) as $event) {
                        $context->getEventBus()->handle($event);
                    }
                    return true;
                }
            );
        }
    }
}
