<?php

namespace spec\Irvobmagturs\InvoiceCore;

use Irvobmagturs\InvoiceCore\ImmutableValue;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ImmutableValueSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ImmutableValue::class);
    }
}
