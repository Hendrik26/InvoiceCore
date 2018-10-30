<?php
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace spec\Irvobmagturs\InvoiceCore\Model\Id;

use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class CustomerIdSpec extends ObjectBehavior
{
    function it_equals_another_instance_based_on_the_same_UUID()
    {
        $this->equals(new CustomerId(Uuid::fromString('c7f6ed80-6b74-4ece-b370-4b2528b26012')));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CustomerId::class);
    }

    function it_serializes_to_a_UUID_string()
    {
        $this->__toString()->shouldBe('c7f6ed80-6b74-4ece-b370-4b2528b26012');
    }

    function let()
    {
        $this->beConstructedWith(Uuid::fromString('c7f6ed80-6b74-4ece-b370-4b2528b26012'));
    }
}
