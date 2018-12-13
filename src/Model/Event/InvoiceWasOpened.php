<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 06.11.18
 * Time: 12:58
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Irvobmagturs\InvoiceCore\Model\Id\CustomerId;
use Jubjubbird\Respects\Serializable;

class InvoiceWasOpened implements Serializable
{
    /**
     * @var CustomerId
     */
    private $customerId;
    /**
     * @var DateTimeInterface|null
     */
    private $invoiceDate;
    /**
     * @var string
     */
    private $invoiceNumber;

    /**
     * InvoiceWasOpened constructor.
     * @param CustomerId $customerId
     * @param string $invoiceNumber
     * @param DateTimeInterface|null $invoiceDate
     */
    public function __construct(
        CustomerId $customerId,
        string $invoiceNumber,
        ?DateTimeInterface $invoiceDate = null
    ) {
        $this->customerId = $customerId;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate = $invoiceDate;
    }

    /**
     * @param array $data
     * @return InvoiceWasOpened
     * @throws Exception
     */
    public static function deserialize(array $data): self
    {
        return new self(CustomerId::fromString($data[0]), $data[1],
            $data[2] ? new DateTimeImmutable($data[2]) : null);
    }

    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return DateTimeInterface
     */
    public function getInvoiceDate(): DateTimeInterface
    {
        return $this->invoiceDate;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            strval($this->customerId),
            $this->invoiceNumber,
            $this->invoiceDate->format(DATE_ATOM)
        ];
    }
}