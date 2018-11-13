<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

namespace spec\Irvobmagturs\InvoiceCore\Model\Entity;

use Buttercup\Protects\DomainEvents;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateHistory;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateRoot;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordedEvent;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceDateHasBeenSet;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceBecameNational;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceBecameInternational;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceEmployedSepaDirectDebit;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceHasCoveredBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceHasDroppedBillingPeriod;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceRefrainedSepaDirectDebit;
use Irvobmagturs\InvoiceCore\Model\Event\InvoiceWasOpened;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\BillingPeriod;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;
use PhpSpec\ObjectBehavior;

class InvoiceSpec extends ObjectBehavior
{
    function it_appends_an_item_with_a_proper_title(LineItem $item)
    {
        $this->clearRecordedEvents();
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
        $this->clearRecordedEvents();
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
        $this->clearRecordedEvents();
        $this->shouldImplement(AggregateRoot::class);
    }

    function it_is_initializable_by_charging_a_customer()
    {
        $this->shouldHaveType(Invoice::class);
        /** @var DomainEvents $domainEvents */
        $domainEvents = $this->getRecordedEvents();
        $domainEvents->shouldHaveCount(1);
        $domainEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        $domainEvents[0]->getPayload()->shouldBeAnInstanceOf(InvoiceWasOpened::class);
    }

    function it_rejects_an_item_with_an_empty_title(LineItem $item, Money $money)
    {
        $this->clearRecordedEvents();
        $item->beConstructedWith($this->itemConstructorArgsFromTitle(''));
        $this->shouldThrow(InvalidLineItemTitle::class)->duringAppendLineItem($item);
        $this->getRecordedEvents()->shouldHaveCount(0);
    }

    function it_rejects_an_item_with_a_blank_title(LineItem $item, Money $money)
    {
        $this->clearRecordedEvents();
        $item->beConstructedWith($this->itemConstructorArgsFromTitle(' '));
        $this->shouldThrow(InvalidLineItemTitle::class)->duringAppendLineItem($item);
        $this->getRecordedEvents()->shouldHaveCount(0);
    }

    function it_rejects_a_deleting_position_smaller_than_zero ()
    {
        $this->clearRecordedEvents();
        $this->shouldThrow(InvalidLineItemPosition::class)->duringRemoveLineItemByPosition(-1);
        $this->getRecordedEvents()->shouldHaveCount(0);
    }

    function it_rejects_a_deleting_position_greater_than_max (LineItem $item, LineItem $secondItem)
    {
        $this->clearRecordedEvents();
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
        $this->clearRecordedEvents();
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

    function it_reconstitutes_from_aggregate_history(AggregateHistory $aggregateHistory)
    {
        $invoiceId = InvoiceId::fromString('4cd97851-15ed-4e7e-87f8-0fa84ac76c5d');
        $aggregateHistory->getAggregateId()->willReturn($invoiceId);
        $this->beConstructedThroughReconstituteFrom($aggregateHistory);
        $this->shouldBeAnInstanceOf(Invoice::class);
        $this->getAggregateId()->shouldBeLike($invoiceId);
    }

    /**
     * @throws \Exception
     */
    function let()
    {
        $this->beConstructedThroughChargeCustomer(InvoiceId::fromString('afc788eb-d60b-4de6-b409-3aab54d46945'),
            CustomerId::fromString('da23f23f-57b7-47fe-b5b7-b9929f6b1aea'), 'Inv12345',
            new \DateTimeImmutable('now'));
    }

    /**
     * @param string $title
     * @return array
     */
    private function itemConstructorArgsFromTitle($title): array
    {
        return [new Money(0, ''), .0, $title, false];
    }

    /**
     * @param \PhpSpec\Wrapper\Collaborator $country
     * @param \PhpSpec\Wrapper\Collaborator $customerSalesTaxNumber
     */
    function it_becomes_international()
    {
        $this->clearRecordedEvents();
        $this->becomeInternational('testCountry', 'testCustomerSalesTaxNumber');
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        $payload = $recordedEvents[0]->getPayload();
        $payload->shouldBeAnInstanceOf(InvoiceBecameInternational::class);
        $payload->getCountryCode()->shouldBe('testCountry');
        $payload->getCustomerSalesTaxNumber()->shouldBe('testCustomerSalesTaxNumber');
    }

    /**
     *
     */
    function it_becomes_national()
    {
        $this->clearRecordedEvents();
        $this->becomeNational();
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        $payload = $recordedEvents[0]->getPayload();
        $payload->shouldBeAnInstanceOf(InvoiceBecameNational::class);
        // $payload->getPosition()->shouldBe(0); // no equivalent test possible
    }

    /**
     * @param SepaDirectDebitMandate|\PhpSpec\Wrapper\Collaborator $mandate
     */
    function it_employs_direct_debit(SepaDirectDebitMandate $mandate)
    {
        $this->clearRecordedEvents();
        $mandate = new SepaDirectDebitMandate( 'testMandateReference',
            'testCustomerIban');
        $this->employSepaDirectDebit($mandate);
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        /** @var InvoiceEmployedSepaDirectDebit $payload */
        $payload = $recordedEvents[0]->getPayload();
        $payload->shouldBeAnInstanceOf(InvoiceEmployedSepaDirectDebit::class);
        $payload->getMandate()->shouldBeLike($mandate);
    }


    /**
     *
     */
    function it_refrains_from_direct_debit()
    {
        $this->clearRecordedEvents();
        $this->refrainFromSepaDirectDebit();
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        $payload = $recordedEvents[0]->getPayload();
        $payload->shouldBeAnInstanceOf(InvoiceRefrainedSepaDirectDebit::class);
    }

    /**
     * @param \PhpSpec\Wrapper\Collaborator|BillingPeriod $period
     * @throws \Exception
     */
    function it_coveres_billing_period(BillingPeriod $period)
    {
        $this->clearRecordedEvents();
        $period = new BillingPeriod(new DateTimeImmutable('2018-09-03'), new DateTimeImmutable('2018-11-28'));
        $this->coverBillingPeriod($period);
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        /** @var InvoiceHasCoveredBillingPeriod $payload */
        $payload = $recordedEvents[0]->getPayload();
        $payload->shouldBeAnInstanceOf(InvoiceHasCoveredBillingPeriod::class);
        $payload->getPeriod()->shouldBeLike($period);
    }

    /**
     *
     */
    function it_drops_billing_period()
    {
        $this->clearRecordedEvents();
        $this->dropBillingPeriod();
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        $payload = $recordedEvents[0]->getPayload();
        $payload->shouldBeAnInstanceOf(InvoiceHasDroppedBillingPeriod::class);
        // $payload->getPosition()->shouldBe(0); // no equivalent test
    }

    /**
     * @param DateTimeInterface|\PhpSpec\Wrapper\Collaborator $date
     * @throws \Exception
     */
    function it_sets_invoice_date()
    {
        $this->clearRecordedEvents();
        $date = new DateTimeImmutable('2018-09-03');
        $this->setInvoiceDate($date);
        $recordedEvents = $this->getRecordedEvents();
        $recordedEvents->shouldHaveCount(1);
        $recordedEvents[0]->shouldBeAnInstanceOf(RecordedEvent::class);
        $payload = $recordedEvents[0]->getPayload();
        $payload->shouldBeAnInstanceOf(InvoiceDateHasBeenSet::class);
        $payload->getInvoiceDate()->shouldBeLike($date); // no equivalent test
    }
}
