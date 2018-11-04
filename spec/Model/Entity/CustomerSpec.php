<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 04.11.2018
 * Time: 21:44
 */
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */


namespace spec\Irvobmagturs\InvoiceCore\Model\Entity;

use Irvobmagturs\InvoiceCore\Model\Entity\Customer;
use PhpSpec\ObjectBehavior;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateHistory;
use Irvobmagturs\InvoiceCore\Infrastructure\AggregateRoot;
use Irvobmagturs\InvoiceCore\Infrastructure\RecordedEvent;
use Irvobmagturs\InvoiceCore\Model\Entity\Invoice;
use Irvobmagturs\InvoiceCore\Model\Event\LineItemWasAppended;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemPosition;
use Irvobmagturs\InvoiceCore\Model\Exception\InvalidLineItemTitle;
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;
use Irvobmagturs\InvoiceCore\Model\ValueObject\Money;
use PhpSpec\ObjectBehavior;

class CustomerSpec extends ObjectBehavior
{

}
