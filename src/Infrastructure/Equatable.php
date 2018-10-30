<?php declare(strict_types=1);
/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */
namespace Irvobmagturs\InvoiceCore\Infrastructure;

interface Equatable
{
    /**
     * @param mixed $other
     *
     * @return bool
     */
    function equals($other): bool;
}
