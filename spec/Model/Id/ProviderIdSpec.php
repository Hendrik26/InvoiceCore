<?php
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace spec\Irvobmagturs\InvoiceCore\Model\Id;

use Irvobmagturs\InvoiceCore\Model\Id\ProviderId;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class ProviderIdSpec extends ObjectBehavior
{
    function it_equals_another_instance_based_on_the_same_UUID()
    {
        $this->equals(new ProviderId(Uuid::fromString('84ab53d7-1d28-48d1-8bf7-89ec12ae7c45')));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProviderId::class);
    }

    function it_serializes_to_a_UUID_string()
    {
        $this->__toString()->shouldBe('84ab53d7-1d28-48d1-8bf7-89ec12ae7c45');
    }

    function let()
    {
        $this->beConstructedWith(Uuid::fromString('84ab53d7-1d28-48d1-8bf7-89ec12ae7c45'));
    }
}
