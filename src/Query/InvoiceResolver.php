<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 27.11.18
 * Time: 11:04
 */

namespace Irvobmagturs\InvoiceCore\Query;

use DirectoryIterator;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;

class InvoiceResolver extends TypeResolver
{
    public function __construct(string $invoiceDir, TypeResolver $base = null)
    {
        parent::__construct($base);
        $this->addResolverForField('CqrsQuery', 'invoices', function () use ($invoiceDir) {
            foreach (new DirectoryIterator($invoiceDir) as $fileInfo) {
                if ($fileInfo->isFile() && $fileInfo->getExtension() === 'json') {
                    yield json_decode(file_get_contents($fileInfo->getRealPath()));
                }
            }
        });
    }
}
