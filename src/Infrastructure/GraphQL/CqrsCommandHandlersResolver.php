<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

/**
 * Resolves mutations to fields on CqrsCommandHandlers, each representing an aggregate to be loaded from the specified
 * ID.
 */
class CqrsCommandHandlersResolver extends TypeResolver
{
    public function __construct($base = null)
    {
        parent::__construct($base);
        $this->addResolverForType(
            'CqrsCommandHandlers',
            function ($_, array $args) {
                return $args['id'];
            }
        );
    }
}
