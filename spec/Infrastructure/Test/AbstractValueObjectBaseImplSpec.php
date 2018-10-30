<?php
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Infrastructure\Test;

use Irvobmagturs\InvoiceCore\Infrastructure\Test\AbstractValueObjectBaseImpl;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AbstractValueObjectBaseImplSpec extends ObjectBehavior
{
    function it_deserializes_from_an_array()
    {
        $this->beConstructedThroughDeserialize([]);
        $this->shouldBeAnInstanceOf(AbstractValueObjectBaseImpl::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AbstractValueObjectBaseImpl::class);
    }

    function it_serializes_to_an_array()
    {
        $this->serialize()->shouldBeLike([]);
    }
}
