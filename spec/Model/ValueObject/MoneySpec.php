<?php
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace spec\Irvobmagturs\InvoiceCore\Model\ValueObject;

use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use PhpSpec\ObjectBehavior;

class MoneySpec extends ObjectBehavior
{
    function it_configures_a_new_instance()
    {
        $value = ['EUR', 2.34];
        $this->withAmount(2.34)->serialize()->shouldBe($value);
        $this->serialize()->shouldNotBe($value);
    }

    function it_deserializes_from_an_array()
    {
        $this->beConstructedThroughDeserialize(['GBP', 2.34]);
        $this->amount->shouldBe(2.34);
        $this->currency->shouldBe('GBP');
    }

    function it_exposes_the_amount()
    {
        $this->amount->shouldBe(1.23);
    }

    function it_exposes_the_currency()
    {
        $this->currency->shouldBe('EUR');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Money::class);
    }

    function it_serializes_to_an_array()
    {
        $this->serialize()->shouldBe(['EUR', 1.23]);
    }

    function let()
    {
        $this->beConstructedWith(1.23, 'EUR');
    }
}
