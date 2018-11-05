<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 05.11.18
 * Time: 13:12
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;

class CustomerSalesTaxNumberWasChanged implements Serializable
{
    /**
     * @var
     */
    private $customerSalesTaxNumber;

    /**
     * CustomerSalesTaxNumberWasChanged constructor.
     * @param $customerAdress
     */
    public function __construct(string $salesTaxNumber)
    {
        $this->customerSalesTaxNumber = $salesTaxNumber;
    }

    /**
     * @param array $data
     * @return static The object instance
     */
    static function deserialize(array $data): Serializable
    {
        // TODO: Implement deserialize() method.
        return new self(data[0]);
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        // TODO: Implement serialize() method.
        return[
            $this->customerSalesTaxNumber
        ];
    }
}