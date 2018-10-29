<?php

namespace spec\Irvobmagturs\InvoiceCore;

use Irvobmagturs\InvoiceCore\AbstractValueObjectBase;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AbstractValueObjectBaseSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AbstractValueObjectBase::class);
    }
}
