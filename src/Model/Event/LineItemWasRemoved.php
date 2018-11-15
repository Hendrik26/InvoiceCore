<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 02.11.18
 * Time: 08:43
 */

namespace Irvobmagturs\InvoiceCore\Model\Event;

use Jubjubbird\Respects\Serializable;

class LineItemWasRemoved implements Serializable
{
    private $position;

    /**
     * LineItemWasRemoved constructor.
     * @param $position
     */
    public function __construct($position)
    {
        $this->position = $position;
    }

    /**
     * @param array $data
     * @return static
     * @throws Exception
     */
    static function deserialize(array $data): Serializable
    {
        return new self($data[0]);
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return array
     */
    function serialize(): array
    {
        return [
            $this->position,
        ];
    }

}