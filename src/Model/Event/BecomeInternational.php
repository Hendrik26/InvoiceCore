<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 08.11.18
 * Time: 12:58
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;

class BecomeInternational implements Serializable
{
    /**
     * @var
     */
    private $countryCode;

    /**
     * @var
     */
    private $customerSalesTaxNumber;

    /**
     * @return mixed
     */
    public function getCustomerSalesTaxNumber()
    {
        return $this->customerSalesTaxNumber;
    }

    /**
     * BecomeInternational constructor.
     * @param string $countryCode
     * @param string $customerSalesTaxNumber
     */
    public function __construct(string $countryCode, string $customerSalesTaxNumber)
    {
        $this->countryCode = $countryCode;
        $this->customerSalesTaxNumber = $customerSalesTaxNumber;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->countryCode, $this->customerSalesTaxNumber];
    }

    /**
     * @param array $data
     * @return BecomeInternational
     */
    static function deserialize(array $data): self
    {
        return new self($data[0], $data[1]);
    }


}