<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 09.11.2018
 * Time: 09:02
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;
use Irvobmagturs\InvoiceCore\Model\ValueObject\SepaDirectDebitMandate;

class InvoiceEmployedSepaDirectDebit implements Serializable
{
    /**
     * @var SepaDirectDebitMandate
     */
    private $mandate;

    /**
     * @return SepaDirectDebitMandate
     */
    public function getMandate(): SepaDirectDebitMandate
    {
        return $this->mandate;
    }

    /**
     * InvoiceEmployedSepaDirectDebit constructor.
     * @param SepaDirectDebitMandate $mandate
     */
    public function __construct(SepaDirectDebitMandate $mandate)
    {
        $this->mandate = $mandate;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->mandate->serialize()];
    }

    /**
     * @param array $data
     * @return InvoiceEmployedSepaDirectDebit
     */
    static function deserialize(array $data): self
    {
        return new self(SepaDirectDebitMandate::deserialize($data[0]));
    }


}