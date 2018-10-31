<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace %namespace%;

use %subject%;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class %name% extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(%subject_class%::class);
    }

    function it_deserializes_from_an_array()
    {
        $this->beConstructedThroughDeserialize([
            'member'
        ]);
        $this->member->shouldBe('member');
    }

    function it_exposes_the_member()
    {
        $this->member->shouldBe('member');
    }

    function it_serializes_to_an_array()
    {
        $this->serialize()->shouldBe([
            'member'
        ]);
    }

    function let()
    {
        $this->beConstructedWith(
            'member'
        );
    }
}
