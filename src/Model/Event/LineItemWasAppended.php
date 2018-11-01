<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 01.11.18
 * Time: 12:06
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Exception;
use Irvobmagturs\InvoiceCore\Infrastructure\Serializable;
use Irvobmagturs\InvoiceCore\Model\ValueObject\LineItem;

class LineItemWasAppended implements Serializable
{
    private $item;
    /**
     * @var int
     */
    private $position;

    /**
     * LineItemWasAppended constructor.
     * @param int $position
     * @param LineItem $item
     */
    public function __construct(int $position, LineItem $item)
    {
        $this->item = $item;
        $this->position = $position;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [
            $this->position,
            $this->item->serialize()
        ];
    }

    /**
     * @param array $data
     * @return static
     * @throws Exception
     */
    static function deserialize(array $data): Serializable
    {
        return new self($data[0], LineItem::deserialize($data[1]));
    }

    /**
     * @return LineItem
     */
    public function getItem(): LineItem
    {
        return $this->item;
    }
}
