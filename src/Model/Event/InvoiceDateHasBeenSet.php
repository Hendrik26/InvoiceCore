<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 12.11.18
 * Time: 12:56
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;


use DateTimeImmutable;
use DateTimeInterface;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;

class InvoiceDateHasBeenSet implements Serializable
{

    /**
     * @var DateTimeImmutable $invoiceDate
     */
    private $invoiceDate;

    /**
     * @return DateTimeImmutable
     */
    public function getInvoiceDate(): DateTimeInterface
    {
        return $this->invoiceDate;
    }

    /**
     * InvoiceDateHasBeenSet constructor.
     */
    public function __construct($date)
    {
        $this->invoiceDate = $date;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [$this->invoiceDate->format(DATE_ATOM)];
    }

    /**
     * @param array $data
     * @return InvoiceDateHasBeenSet
     * @throws \Exception
     */
    public static function deserialize(array $data): self
    {
        return new self($data[0] ? new DateTimeImmutable($data[0]) : null);
    }
}