<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\Infrastructure;

use PDO;

class SqLitePdo extends PDO
{
    /**
     * @param string $filePath The path to the *.sqlite file holding the database.
     */
    public function __construct(string $filePath)
    {
        parent::__construct($this->createDataSourceName($filePath));
    }

    /**
     * @param string $filePath
     * @return string The DSN.
     */
    private function createDataSourceName(string $filePath): string
    {
        return 'sqlite:' . realpath($filePath);
    }
}
