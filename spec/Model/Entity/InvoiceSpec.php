<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Model\Entity;

use Buttercup\Protects\AggregateRoot;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordedEvent;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use PhpSpec\ObjectBehavior;

class InvoiceSpec extends ObjectBehavior
{
    function it_appends_an_item_with_a_proper_title(LineItem $item)
    {
        $item->beConstructedWith($this->itemConstructorArgsFromTitle('some proper title'));
        $this->appendLineItem($item);
        /** @var RecordedEvent[] $recordedEvents */
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        /** @var LineItemWasAppended $payload */
        $payload = $recordedEvents[0]->getPayload();
        $payload->shouldBeAnInstanceOf(LineItemWasAppended::class);
        $payload->getPosition()->shouldBe(0);
    }

    function it_increments_the_position_when_appending_items(LineItem $item, LineItem $secondItem)
    {
        $item->beConstructedWith($this->itemConstructorArgsFromTitle('some proper title'));
        $secondItem->beConstructedWith($this->itemConstructorArgsFromTitle('something else'));
        $this->appendLineItem($item);
        $this->appendLineItem($secondItem);
        /** @var RecordedEvent[] $recordedEvents */
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(2);
        /** @var LineItemWasAppended $payload */
        $payload = $recordedEvents[0]->getPayload();
        $payload->getPosition()->shouldBe(0);
        /** @var LineItemWasAppended $payload */
        $payload = $recordedEvents[1]->getPayload();
        $payload->getPosition()->shouldBe(1);
    }

    function it_is_an_aggregate_root()
    {
        $this->shouldImplement(AggregateRoot::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Invoice::class);
    }

    function it_rejects_an_item_with_an_empty_title(LineItem $item, Money $money)
    {
        $item->beConstructedWith($this->itemConstructorArgsFromTitle(''));
        $this->shouldThrow(InvalidLineItemTitle::class)->duringAppendLineItem($item);
        $this->getRecordedEvents()->shouldHaveCount(0);
    }

    function it_rejects_an_item_with_a_blank_title(LineItem $item, Money $money)
    {
        $item->beConstructedWith($this->itemConstructorArgsFromTitle(' '));
        $this->shouldThrow(InvalidLineItemTitle::class)->duringAppendLineItem($item);
        $this->getRecordedEvents()->shouldHaveCount(0);
    }

    function it_rejects_a_deleting_position_smaller_than_zero ()
    {
        $this->shouldThrow(InvalidLineItemPosition::class)->duringRemoveLineItemByPosition(-1);
        $this->getRecordedEvents()->shouldHaveCount(0);
    }

    function it_rejects_a_deleting_position_greater_than_max (LineItem $item, LineItem $secondItem)
    {
        $item->beConstructedWith($this->itemConstructorArgsFromTitle('some proper title'));
        $secondItem->beConstructedWith($this->itemConstructorArgsFromTitle('something else'));
        $this->appendLineItem($item);
        $this->appendLineItem($secondItem);
        $this->appendLineItem($item);
        $this->appendLineItem($secondItem);
        $this->clearRecordedEvents();
        $this->shouldThrow(InvalidLineItemPosition::class)->duringRemoveLineItemByPosition(4);
        $this->getRecordedEvents()->shouldHaveCount(0);
    }

    function it_deletes_item_by_valid_position (LineItem $item, LineItem $secondItem)
    {
        $item->beConstructedWith($this->itemConstructorArgsFromTitle('some proper title'));
        $secondItem->beConstructedWith($this->itemConstructorArgsFromTitle('something else'));
        $this->appendLineItem($item);
        $this->appendLineItem($secondItem);
        $this->appendLineItem($item);
        $this->appendLineItem($secondItem);
        $this->clearRecordedEvents();
        $this->removeLineItemByPosition(0);
        $this->removeLineItemByPosition(1);
        $this->shouldThrow(InvalidLineItemPosition::class)->duringRemoveLineItemByPosition(2);
        $this->getRecordedEvents()->shouldHaveCount(2);
    }



    function let()
    {
        $this->beConstructedWith(InvoiceId::fromString('afc788eb-d60b-4de6-b409-3aab54d46945'));
    }

    /**
     * @param string $title
     * @return array
     */
    private function itemConstructorArgsFromTitle($title): array
    {
        return [0, new Money(0, ''), .0, $title, false];
    }
}
