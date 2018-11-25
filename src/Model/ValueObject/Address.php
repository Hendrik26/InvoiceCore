<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 05.11.18
 * Time: 11:33
 */

namespace Irvobmagturs\InvoiceCore\Model\ValueObject;

use InvalidArgumentException;
use Irvobmagturs\InvoiceCore\Infrastructure\AbstractValueObjectBase;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyCity;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyCountryCode;
use Irvobmagturs\InvoiceCore\Model\Exception\EmptyPostalCode;

/**
 * @property-read string $countryCode
 * @property-read string $postalCode
 * @property-read string $city
 * @property-read ?string $addressLine1
 * @property-read ?string $addressLine2
 * @property-read ?string $addressLine3
 * @method self withCountryCode(string $v)
 * @method self withPostalCode(string $v)
 * @method self withCity(string $v)
 * @method self withAddressLine1(string $v)
 * @method self withAddressLine2(string $v)
 * @method self withAddressLine3(string $v)
 */
class Address extends AbstractValueObjectBase
{
    /**
     * Address constructor.
     * @param string $countryCode
     * @param string $postalCode
     * @param string $city
     * @param null|string $addressLine1
     * @param null|string $addressLine2
     * @param null|string $addressLine3
     * @throws EmptyCountryCode
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $countryCode,
        string $postalCode,
        string $city,
        ?string $addressLine1 = null,
        ?string $addressLine2 = null,
        ?string $addressLine3 = null
    ) {
        $this->guardEmptyCountryCode($countryCode);
        $this->guardEmptyPostalCode($postalCode);
        $this->guardEmptyCity($city);
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
     * @throws EmptyCountryCode
     * @throws InvalidArgumentException
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
        return [
            $this->countryCode,
            $this->postalCode,
            $this->city,
            $this->addressLine1,
            $this->addressLine2,
            $this->addressLine3
        ];
    }

    /**
     * @param string $city
     * @throws EmptyCity
     */
    private function guardEmptyCity(string $city)
    {
        if (trim($city) === "") {
            throw new EmptyCity;
        }
    }

    /**
     * @param string $countryCode
     * @throws EmptyCountryCode
     */
    private function guardEmptyCountryCode(string $countryCode)
    {
        if (trim($countryCode) === "") {
            throw new EmptyCountryCode();
        }
    }

    /**
     * @param string $postalCode
     * @throws EmptyPostalCode
     */
    private function guardEmptyPostalCode(string $postalCode)
    {
        if (trim($postalCode) === "") {
            throw new EmptyPostalCode();
        }
    }
}