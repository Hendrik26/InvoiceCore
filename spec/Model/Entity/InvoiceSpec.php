<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Model\Entity;

use Buttercup\Protects\AggregateRoot;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use PhpSpec\ObjectBehavior;

class InvoiceSpec extends ObjectBehavior
{
    function it_appends_an_item_with_a_proper_title(LineItem $item)
    {
        $item->beConstructedWith($this->itemConstructorArgsFromTitle('some proper title'));
        $this->appendLineItem($item);
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(LineItemWasAppended::class);
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
        $this->shouldThrow()->duringAppendLineItem($item);
        $this->getRecordedEvents()->shouldHaveCount(0);
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
