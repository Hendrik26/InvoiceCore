<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\IdentifiesAggregate;
use DateTimeImmutable;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordedEvent;
use Jubjubbird\Respects\Serializable;
use PhpSpec\ObjectBehavior;

class RecordedEventSpec extends ObjectBehavior
{
    function it_exposes_its_actual_payload(Serializable $payload)
    {
        $this->getPayload()->shouldBe($payload);
    }

    function it_exposes_the_ID_of_the_emitting_aggregate(IdentifiesAggregate $aggregateId)
    {
        $this->getAggregateId()->shouldBe($aggregateId);
    }

    function it_exposes_when_it_was_recorded(DateTimeImmutable $recordedOn)
    {
        $this->getRecordedOn()->shouldBe($recordedOn);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RecordedEvent::class);
    }

    function let(Serializable $payload, IdentifiesAggregate $aggregateId, DateTimeImmutable $recordedOn)
    {
        $this->beConstructedWith($payload, $aggregateId, $recordedOn);
    }
}
