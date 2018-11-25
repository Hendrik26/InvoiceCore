<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use function Verraes\ClassFunctions\short;

class CqrsCommandBus extends TypeResolver
{
    /** @var CqrsCommandHandlerInterface[] */
    private $handlers = [];

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

    public function append(CqrsCommandHandlerInterface $handler)
    {
        $this->handlers[] = $handler;
    }

    public function generateTypeConfigDecorator(): Closure
    {
        foreach ($this->handlers as $handler) {
            $this->registerHandler($handler);
        }
        return parent::generateTypeConfigDecorator();
    }

    /**
     * @param CqrsCommandHandlerInterface $handler
     */
    private function registerHandler(CqrsCommandHandlerInterface $handler): void
    {
        foreach (get_class_methods($handler) as $method) {
            $this->addResolverForField(
                short($handler),
                $method,
                function ($typeValue, array $args, $context, ResolveInfo $info) use ($handler, $method) {
                    $handler->$method($typeValue, $args, $context, $info);
                    return true;
                }
            );
        }
    }
}
