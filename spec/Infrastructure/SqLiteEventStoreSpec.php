<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Infrastructure;

use Buttercup\Protects\IdentifiesAggregate;
use DateTimeImmutable;
use Irvobmagturs\InvoiceCore\Infrastructure\SqLiteEventStore;
use Irvobmagturs\InvoiceCore\Infrastructure\SqLitePdo;
use Jubjubbird\Respects\RecordedEvent;
use Jubjubbird\Respects\Serializable;
use PDOStatement;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SqLiteEventStoreSpec extends ObjectBehavior
{
    function it_appends_recorded_events(
        RecordedEvent $recordedEvent,
        PDOStatement $insertStatement,
        PDOStatement $selectStatement,
        IdentifiesAggregate $identifier,
        Serializable $payload,
        DateTimeImmutable $timestamp
    ) {
        $recordedEvent->getAggregateId()->willReturn($identifier);
        $recordedEvent->getPayload()->willReturn($payload);
        $recordedEvent->getRecordedOn()->willReturn($timestamp);
        $identifier->__toString()->willReturn('71f95921-e4fb-4aed-89cd-4b34ab24e482');
        $payload->serialize()->willReturn([]);
        $this->append([$recordedEvent]);
        $insertStatement->execute(Argument::type('array'))->shouldHaveBeenCalledOnce();
        $selectStatement->execute(Argument::cetera())->shouldNotHaveBeenCalled();
    }

    function it_is_initializable(SqLitePdo $sqliteDatabase)
    {
        $this->shouldHaveType(SqLiteEventStore::class);
        $sqliteDatabase->prepare(Argument::containingString('SELECT'))->shouldHaveBeenCalledOnce();
        $sqliteDatabase->prepare(Argument::containingString('INSERT'))->shouldHaveBeenCalledOnce();
    }

    function it_loads_the_history_of_an_aggregate(

    )
    {
        throw new SkippingException(__METHOD__);
    }

    function let(
        SqLitePdo $sqliteDatabase,
        PDOStatement $selectStatement,
        PDOStatement $insertStatement
    ) {
        $this->beConstructedWith($sqliteDatabase);
        $sqliteDatabase->prepare(Argument::containingString('SELECT'))->willReturn($selectStatement);
        $sqliteDatabase->prepare(Argument::containingString('INSERT'))->willReturn($insertStatement);
    }
}
