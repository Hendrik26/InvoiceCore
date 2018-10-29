<?php

namespace spec\Irvobmagturs\InvoiceCore;

use Irvobmagturs\InvoiceCore\ApplyCallsWhenMethod;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApplyCallsWhenMethodSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ApplyCallsWhenMethod::class);
    }
}
