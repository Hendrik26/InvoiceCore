<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Model\ValueObject;

use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;
use PhpSpec\ObjectBehavior;

class SepaDirectDebitMandateSpec extends ObjectBehavior
{
    function it_deserializes_from_an_array()
    {
        $this->beConstructedThroughDeserialize(['m1234567', 'DE12345678901234567890']);
        $this->mandateReference->shouldBe('m1234567');
        $this->customerIBAN->shouldBe('DE12345678901234567890');
    }

    function it_exposes_the_IBAN_of_the_customer()
    {
        $this->customerIBAN->shouldBe('DE12345678901234567890');
    }

    function it_exposes_the_mandate_reference()
    {
        $this->mandateReference->shouldBe('m1234567');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SepaDirectDebitMandate::class);
    }

    function it_serializes_to_an_array()
    {
        $this->serialize()->shouldBe(['m1234567', 'DE12345678901234567890']);
    }

    function let()
    {
        $this->beConstructedWith('m1234567', 'DE12345678901234567890');
    }
}
