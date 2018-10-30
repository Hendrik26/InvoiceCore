<?php
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace spec\Irvobmagturs\InvoiceCore\Model\Id;

use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class InvoiceIdSpec extends ObjectBehavior
{
    function it_equals_another_instance_based_on_the_same_UUID()
    {
        $this->equals(new InvoiceId(Uuid::fromString('faa6eeba-f231-496e-8054-fdddac90eba9')));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InvoiceId::class);
    }

    function it_serializes_to_a_UUID_string()
    {
        $this->__toString()->shouldBe('faa6eeba-f231-496e-8054-fdddac90eba9');
    }

    function let()
    {
        $this->beConstructedWith(Uuid::fromString('faa6eeba-f231-496e-8054-fdddac90eba9'));
    }
}
