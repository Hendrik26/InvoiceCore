<?php

namespace spec\Irvobmagturs\InvoiceCore\Model\ValueObject;

use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use PhpSpec\ObjectBehavior;

class MoneySpec extends ObjectBehavior
{
    function it_deserializes_from_an_array()
    {
        $this->beConstructedThroughDeserialize(['GBP', 2.34]);
        $this->amount->shouldBe(2.34);
        $this->currency->shouldBe('GBP');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Money::class);
    }

    function it_serializes_to_an_array()
    {
        $this->serialize()->shouldBe(['EUR', 1.23]);
    }

    function it_should_expose_its_amount()
    {
        $this->amount->shouldBe(1.23);
    }

    function it_should_expose_its_currency()
    {
        $this->currency->shouldBe('EUR');
    }

    function let()
    {
        $this->beConstructedWith(1.23, 'EUR');
    }
}
