<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\CommandHandler;

use Buttercup\Protects\DomainEvents;
use Irvobmagturs\InvoiceCore\CommandHandler\CustomerHandler;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordedEvent;
use Irvobmagturs\InvoiceCore\Model\Event\CustomerHasEngagedInBusiness;
use PhpSpec\ObjectBehavior;

class CustomerHandlerSpec extends ObjectBehavior
{
    function it_handles_engageInBusiness()
    {
        $domainEvents = $this->engageInBusiness(
            '813236ed-e509-4b2b-acad-741584f4032f',
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
        $domainEvents->shouldBeAnInstanceOf(DomainEvents::class);
        $domainEvents->shouldHaveCount(1);
        $domainEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        $domainEvents[0]->getPayload()->shouldBeAnInstanceOf(CustomerHasEngagedInBusiness::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CustomerHandler::class);
    }
}
