<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 09.11.2018
 * Time: 09:02
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;
use Jubjubbird\Respects\Serializable;

class InvoiceEmployedSepaDirectDebit implements Serializable
{
    /**
     * @var SepaDirectDebitMandate
     */
    private $mandate;

    /**
     * InvoiceEmployedSepaDirectDebit constructor.
     * @param SepaDirectDebitMandate $mandate
     */
    public function __construct(SepaDirectDebitMandate $mandate)
    {
        $this->mandate = $mandate;
    }

    /**
     * @param array $data
     * @return InvoiceEmployedSepaDirectDebit
     */
    static function deserialize(array $data): self
    {
        return new self(SepaDirectDebitMandate::deserialize($data[0]));
    }

    /**
     * @return SepaDirectDebitMandate
     */
    public function getMandate(): SepaDirectDebitMandate
    {
        return $this->mandate;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->mandate->serialize()];
    }
}