<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 22.11.18
 * Time: 10:11
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use DateTimeImmutable;
use DateTimeInterface;
use Jubjubbird\Respects\Serializable;

class InvoiceDueDateHasBeenSet implements Serializable
{
    private $dueDate;

    /**
     * InvoiceDueDateHasBeenSet constructor.
     * @param DateTimeInterface $dueDate
     */
    public function __construct(DateTimeInterface $dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @param array $data
     * @return InvoiceDueDateHasBeenSet
     * @throws \Exception
     */
    public static function deserialize(array $data): self
    {
        return new self($data[0] ? new DateTimeImmutable($data[0]) : null);
    }

    /**
     * @return DateTimeInterface
     */
    public function getInvoiceDueDate(): DateTimeInterface
    {
        return $this->dueDate;
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [$this->dueDate->format(DATE_ATOM)];
    }
}