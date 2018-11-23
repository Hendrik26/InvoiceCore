<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Repository;

use Irvobmagturs\InvoiceCore\Infrastructure\EventBus;
use Irvobmagturs\InvoiceCore\Infrastructure\EventStore;
use Irvobmagturs\InvoiceCore\Repository\InvoiceRepository;
use Jubjubbird\Respects\AggregateRoot;
use Jubjubbird\Respects\DomainEvents;
use Jubjubbird\Respects\RecordedEvent;
use PhpSpec\ObjectBehavior;

class InvoiceRepositorySpec extends ObjectBehavior
{
    function it_can_save_an_aggregate(
        EventStore $eventStore,
        AggregateRoot $aggregateRoot,
        RecordedEvent $recordedEvent1,
        RecordedEvent $recordedEvent2
    ) {
        $void = null;
        $notMatterAtAll = function () {
        };
        $aggregateRoot->getRecordedEvents()->shouldBeCalledOnce();
        $aggregateRoot->clearRecordedEvents()->should($notMatterAtAll);
        $eventStore->append([$recordedEvent1, $recordedEvent2])->shouldBeCalledOnce();
        $this->save($aggregateRoot)->shouldReturn($void);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InvoiceRepository::class);
    }

    function it_prevents_saving_duplicate_changes(AggregateRoot $aggregateRoot)
    {
        $aggregateRoot->getRecordedEvents()->shouldBeCalledOnce();
        $aggregateRoot->clearRecordedEvents()->shouldBeCalledOnce();
        $this->save($aggregateRoot);
    }

    function let(
        EventStore $eventStore,
        EventBus $eventBus,
        AggregateRoot $aggregateRoot,
        DomainEvents $domainEvents,
        RecordedEvent $recordedEvent1,
        RecordedEvent $recordedEvent2
    ) {
        $this->beConstructedWith($eventStore, $eventBus);
        $aggregateRoot->getRecordedEvents()->willReturn($domainEvents);
        $domainEvents->toArray()->willReturn([$recordedEvent1, $recordedEvent2]);
    }
}
