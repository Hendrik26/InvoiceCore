<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace spec\Irvobmagturs\InvoiceCore\Infrastructure;

use Irvobmagturs\InvoiceCore\Infrastructure\SqLiteEventStore;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceBecameInternational;
use Jubjubbird\Respects\RecordedEvent;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SqLiteEventStoreSpec extends ObjectBehavior
{
    private function createEventArray()
    {
        $payload = new InvoiceBecameInternational('testCountyCode',
            'testCustomerSalesTaxNumber');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SqLiteEventStore::class);
    }

    function it_appends_recorded_events(RecordedEvent $recordedEvent)
    {
        $this->append([$recordedEvent]);
    }

    function let()
    {
        $this->beConstructedWith(
            'member'
        );
    }
}
