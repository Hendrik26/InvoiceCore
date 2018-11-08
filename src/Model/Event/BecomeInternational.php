<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 08.11.18
 * Time: 12:58
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


class BecomeInternational
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
     * @return Self
     */
    static function deserialize(array $data): Self
    {
        return new self(data[0], data[1]);
    }


}