<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Infrastructure\GraphQL;

use GraphQL\Language\AST\TypeDefinitionNode;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;
use PhpSpec\ObjectBehavior;

class TypeResolverSpec extends ObjectBehavior
{
    function it_generates_a_config_decorator_resolving_a_preconfigured_type(TypeDefinitionNode $typeDefNode)
    {
        $this->addResolverForType('SomeType', function () {
            return false;
        });
        $decorator = $this->generateTypeConfigDecorator();
        $decorator->shouldBeCallable();
        $typeConfig = $decorator(['name' => 'SomeType'], $typeDefNode, [$typeDefNode]);
        $typeConfig->shouldHaveKeyWithValue('name', 'SomeType');
        $typeConfig->shouldHaveKey('resolveField');
        $typeConfig['resolveField']->shouldBeCallable();
    }

    function it_generates_an_empty_type_config_decorator_for_a_GraphQL_schema(TypeDefinitionNode $typeDefNode)
    {
        $decorator = $this->generateTypeConfigDecorator();
        $decorator->shouldBeCallable();
        $decorator(['name' => 'SomeType'], $typeDefNode, [$typeDefNode])->shouldIterateAs(['name' => 'SomeType']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TypeResolver::class);
    }
}
