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
use Irvobmagturs\InvoiceCore\Model\Id\InvoiceId;
use Jubjubbird\Respects\Serializable;

class InvoiceWasOpened implements Serializable
{


    /**
     * @return CustomerId
     */
    public function getCustomerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    /**
     * @return DateTimeInterface
     */
    public function getInvoiceDate(): DateTimeInterface
    {
        return $this->invoiceDate;
    }

    /**
     * @var CustomerId
     */
    private $customerId;

    /**
     * @var string
     */
    private $invoiceNumber;

    /**
     * @var DateTimeInterface|null
     */
    private $invoiceDate;


    /**
     * InvoiceWasOpened constructor.
     * @param InvoiceId $invoiceId
     * @param CustomerId $customerId
     * @param string $invoiceNumber
     * @param DateTimeInterface $invoiceDate
     */
    public function __construct(CustomerId $customerId, string $invoiceNumber,
                                ?DateTimeInterface $invoiceDate = null)
    {
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
        return new self( CustomerId::fromString($data[1]), $data[2],
            $data[3] ? new DateTimeImmutable($data[3]) : null);
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [strval($this->customerId), $this->invoiceNumber,
            $this->invoiceDate->format(DATE_ATOM)];
    }

}