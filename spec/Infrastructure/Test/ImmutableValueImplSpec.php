<?php
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace spec\Irvobmagturs\InvoiceCore\Infrastructure\Test;

use DateTime;
use Irvobmagturs\InvoiceCore\Infrastructure\Test\ImmutableValueImpl;
use PhpSpec\ObjectBehavior;

class ImmutableValueImplSpec extends ObjectBehavior
{
    function it_breaks_object_references()
    {
        $a = 'x';
        $b = &$a;
        $dateTime = new DateTime('2018-10-30T01:51:33Z');
        $array = [&$dateTime, $dateTime, &$b];
        $this->beConstructedWith($b, $array, $dateTime);
        $this->revealArray()->shouldBeLike([
            'array' => [new DateTime('2018-10-30T01:51:33Z'), new DateTime('2018-10-30T01:51:33Z'), 'x'],
            'dateTime' => new DateTime('2018-10-30T01:51:33Z'),
            'string' => 'x'
        ]);
        $b = 'z';
        $array[0]->setTime(2, 3, 4);
        $dateTime->setTime(1, 2, 3);
        $this->revealArray()->shouldBeLike([
            'array' => [new DateTime('2018-10-30T01:51:33Z'), new DateTime('2018-10-30T01:51:33Z'), 'x'],
            'dateTime' => new DateTime('2018-10-30T01:51:33Z'),
            'string' => 'x'
        ]);
    }

    function it_configures_a_new_instance()
    {
        $this->withString('x')->shouldBeAnInstanceOf(ImmutableValueImpl::class);
        $this->withString('x')->shouldNotBeLike($this);
        $this->withString('')->shouldBeLike($this);
    }

    function it_equals_an_instance_with_equal_data()
    {
        $this->beConstructedWith('', [], new DateTime('2018-10-30T01:51:33Z'));
        $this->withDateTime(new DateTime('2018-10-30T01:51:33Z'))->equals($this);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ImmutableValueImpl::class);
    }

    function let()
    {
        $this->beConstructedWith('', [], new DateTime());
    }
}
