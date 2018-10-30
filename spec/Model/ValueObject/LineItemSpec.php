<?php
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace spec\Irvobmagturs\InvoiceCore\Model\ValueObject;

use DateTime;
use DateTimeInterface;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use PhpSpec\ObjectBehavior;

class LineItemSpec extends ObjectBehavior
{
    function it_configures_a_new_instance()
    {
        $value = [
            7, /*position*/
            ['EUR', 2.34], /*price*/
            5.6, /*quantity*/
            'title',
            true, /*timeBased*/
            '2018-10-30T23:04:42+01:00'
        ];
        $this->withPosition(7)->serialize()->shouldBe($value);
        $this->serialize()->shouldNotBe($value);
    }

    function it_deserializes_from_an_array()
    {
        $this->beConstructedThroughDeserialize([
            7, /*position*/
            ['GBP', 8.9],
            1.2, /*quantity*/
            'title*',
            false, /*timeBased*/
            null /*date*/
        ]);
        $this->position->shouldBe(7);
        $this->price->shouldBeLike(new Money(8.90, 'GBP'));
        $this->quantity->shouldBe(1.2);
        $this->title->shouldBe('title*');
        $this->timeBased->shouldBe(false);
        $this->date->shouldBe(null);
    }

    function it_exposes_the_date()
    {
        $this->date->shouldBeAnInstanceOf(DateTimeInterface::class);
    }

    function it_exposes_the_position()
    {
        $this->position->shouldBe(1);
    }

    function it_exposes_the_price()
    {
        $this->price->shouldBeLike(new Money(2.34, 'EUR'));
    }

    function it_exposes_the_quantity()
    {
        $this->quantity->shouldBe(5.6);
    }

    function it_exposes_the_title()
    {
        $this->title->shouldBe('title');
    }

    function it_exposes_whether_it_is_time_based()
    {
        $this->timeBased->shouldBe(true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LineItem::class);
    }

    function it_serializes_to_an_array()
    {
        $this->serialize()->shouldBe([
            1, /*position*/
            ['EUR', 2.34], /*price*/
            5.6, /*quantity*/
            'title',
            true, /*timeBased*/
            '2018-10-30T23:04:42+01:00'
        ]);
    }

    function let(DateTime $date)
    {
        $date->format(DATE_ATOM)->willReturn('2018-10-30T23:04:42+01:00');
        $this->beConstructedWith(
            1, /*position*/
            new Money(2.34, 'EUR'),
            5.6, /*quantity*/
            'title',
            true, /*timeBased*/
            $date
        );
    }
}
