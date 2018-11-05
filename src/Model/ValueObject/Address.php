<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 05.11.18
 * Time: 11:33
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;

/**
 * Class Address
 * @package Irvobmagturs\InvoiceCore\Model\ValueObject
 *  * @property-read string $countryCode
 *  *  * @property-read string $postalCode
 *  *  * @property-read string $city
 *  *  * @property-read string $adressLine1
 *  *  * @property-read string $adressLine2
 *  *  * @property-read string $adressLine3
 */
class Address extends AbstractValueObjectBase
{

    /**
     * Address constructor.
     */
    public function __construct(string $countryCode, string $postalCode, string $city, string $adressLine1,
                                string $adressLine2, string $adressLine3)
    {
        $this->init('countryCode', $countryCode);
        $this->init('postalCode', $postalCode);
        $this->init('city', $city);
        $this->init('addressLine1', $addressLine1);
        $this->init('addressLine2', $addressLine2);
        $this->init('addressLine3', $addressLine3);
    }

    /**
     * @param array $data
     * @return Address
     */
    static function deserialize(array $data): self
    {
        return new self(
            $data[0],
            $data[1],
            $data[2],
            $data[3],
            $data[4],
            $data[5]
        );
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return[
            $this->countryCode,
            $this->postalCode,
            $this->city,
            $this->addressLine1,
            $this->addressLine2,
            $this->addressLine3
        ];
    }

}