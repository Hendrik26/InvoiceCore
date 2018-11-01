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
     * LineItemWasAppended constructor.
     * @param LineItem $item
     */
    public function __construct(LineItem $item)
    {
        $this->item = $item;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [
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
        return new self(LineItem::deserialize($data[0]));
    }

    /**
     * @return LineItem
     */
    public function getItem(): LineItem
    {
        return $this->item;
    }
}
