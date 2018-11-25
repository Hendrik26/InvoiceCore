<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

/**
 * The class name of an implementation matches one of the result types defined in CqrsCommandHandlers from the GraphQL
 * schema and provides a method for every field of that type:
 *
 *     public function ...(string $aggregateId, array $args, $context, ResolveInfo $info): void
 *
 * $args is an associative array containing the arguments specified in the GraphQL mutation.
 */
interface CqrsCommandHandler
{
}
