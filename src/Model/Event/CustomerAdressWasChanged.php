<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 05.11.18
 * Time: 09:24
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;

class CustomerAdressWasChanged implements Serializable
{
    /**
     * @var string
     */
    private $customerAdress;
    private $salesTaxNumber;

    /**
     * CustomerAdressWasChanged constructor.
     * @param string $customerAdress
     */
    public function __construct(string $customerAdress)
    {
        $this->customerAdress = $customerAdress;
    }

    /**
     * @return string
     */
    public function getCustomerAdress(): string
    {
        return $this->customerAdress;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->customerAdress];
    }

    /**
     * @param array $data
     * @return Serializable
     */
    static function deserialize(array $data): Serializable
    {
        return new self($data[0]);
    }
}