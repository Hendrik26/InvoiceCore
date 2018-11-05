<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Model\ValueObject;

use Irvobmagturs\InvoiceCore\Model\ValueObject\Address;
use PhpSpec\ObjectBehavior;

class AddressSpec extends ObjectBehavior
{
    function it_allows_instantiation_without_any_address_lines()
    {
        $this->beConstructedWith('countryCode', 'city', 'postalCode');
        $this->addressLine1->shouldBe(null);
        $this->addressLine2->shouldBe(null);
        $this->addressLine3->shouldBe(null);
    }

    function it_deserializes_from_an_array()
    {
        $this->beConstructedThroughDeserialize([
            'countryCode',
            'city',
            'postalCode',
            'addressLine1',
            'addressLine2',
            'addressLine3'
        ]);
        $this->countryCode->shouldBe('countryCode');
        $this->city->shouldBe('city');
        $this->postalCode->shouldBe('postalCode');
        $this->addressLine1->shouldBe('addressLine1');
        $this->addressLine2->shouldBe('addressLine2');
        $this->addressLine3->shouldBe('addressLine3');
    }

    function it_exposes_the_address_line1()
    {
        $this->addressLine1->shouldBe('addressLine1');
    }

    function it_exposes_the_address_line2()
    {
        $this->addressLine2->shouldBe('addressLine2');
    }

    function it_exposes_the_address_line3()
    {
        $this->addressLine3->shouldBe('addressLine3');
    }

    function it_exposes_the_city()
    {
        $this->city->shouldBe('city');
    }

    function it_exposes_the_country_code()
    {
        $this->countryCode->shouldBe('countryCode');
    }

    function it_exposes_the_postal_code()
    {
        $this->postalCode->shouldBe('postalCode');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Address::class);
    }

    function it_serializes_to_an_array()
    {
        $this->serialize()->shouldBe([
            'countryCode',
            'city',
            'postalCode',
            'addressLine1',
            'addressLine2',
            'addressLine3'
        ]);
    }

    function let()
    {
        $this->beConstructedWith(
            'countryCode',
            'city',
            'postalCode',
            'addressLine1',
            'addressLine2',
            'addressLine3'
        );
    }
}
