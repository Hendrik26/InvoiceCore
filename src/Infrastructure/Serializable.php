<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\Infrastructure;

interface Serializable
{
    /**
     * @param array $data
     * @return static The object instance
     */
    static function deserialize(array $data): self;

    /**
     * @return array
     */
    function serialize(): array;
}
