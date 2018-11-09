<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 09.11.2018
 * Time: 09:02
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;

class InvoiceGotSepaDirectDebit
{
    /**
     * @var SepaDirectDebitMandate
     */
    private $mandate;

    /**
     * InvoiceGotSepaDirectDebit constructor.
     * @param SepaDirectDebitMandate $mandate
     */
    public function __construct(SepaDirectDebitMandate $mandate)
    {
        $this->mandate = $mandate;
    }
}