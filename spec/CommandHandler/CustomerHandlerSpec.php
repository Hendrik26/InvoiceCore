<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\CommandHandler;

use Irvobmagturs\InvoiceCore\CommandHandler\CustomerHandler;
use Irvobmagturs\InvoiceCore\Model\Entity\Customer;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Repository\CustomerNotFound;
use Irvobmagturs\InvoiceCore\Repository\CustomerRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CustomerHandlerSpec extends ObjectBehavior
{
    function it_handles_engageInBusiness(CustomerRepository $repository)
    {
        $uuidString = '813236ed-e509-4b2b-acad-741584f4032f';
        $repository->load(CustomerId::fromString($uuidString))->willThrow(CustomerNotFound::class);
        $repository->save(Argument::type(Customer::class))->shouldBeCalledOnce();
        $this->engageInBusiness(
            $uuidString,
            [
                'name' => 'Foobar Ltd.',
                'billingAddress' => [
                    'addressLine1' => '7 Deal Court',
                    'addressLine2' => 'Southmere Drive',
                    'city' => 'London',
                    'countryCode' => 'UK',
                    'postalCode' => 'SE2 9AS'
                ]
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CustomerHandler::class);
    }

    function let(CustomerRepository $repository)
    {
        $this->beConstructedWith($repository, null);
    }
}
