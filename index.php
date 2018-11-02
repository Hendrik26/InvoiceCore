<?php declare(strict_types=1);

/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

use GraphQL\Server\StandardServer;
use Irvobmagturs\InvoiceCore\CommandHandler\InvoiceHandler;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandlersResolver;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CustomizedGraphqlServerConfig;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\SchemaFileCache;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;

if (!($_SERVER['REQUEST_METHOD'] ?? null)) {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    $_POST['query'] = /** @lang GraphQL */
        'mutation m($id: String! $item: LineItem!) {
            Invoice(id: $id) {
                appendLineItem(item: $item)
            }
        }';
    $_POST['variables'] = /** @lang JSON */
        '{
            "id": "2757f42e-6018-4719-b44b-39fa1338b477",
            "item": {
                "date": "now",
                "price": {
                    "amount": 1.23,
                    "currency": "EUR"
                },
                "quantity": 2.0,
                "timeBased": false,
                "title": "some item"
            }
        }';
}

$schemaCache = __DIR__ . '/data/cache/schema';
$schemaFile = __DIR__ . '/conf/cqrs.graphqls';
require_once __DIR__ . '/vendor/autoload.php';
$context = null;
$rootValue = null;
$serverConfig = null;
$typeResolver = new TypeResolver();
$typeResolver->addResolverForField('CqrsQuery', 'loadFoo', function () {
    return 'bar';
});
$typeResolver = new CqrsCommandHandlersResolver($typeResolver);
$typeResolver = new InvoiceHandler($typeResolver);
try {
    $schemaFileCache = new SchemaFileCache($schemaCache);
    $schema = $schemaFileCache->loadCacheForFile($schemaFile, $typeResolver->generateTypeConfigDecorator());
    $serverConfig = new CustomizedGraphqlServerConfig($schema, $context, $rootValue);
    $standardServer = new StandardServer($serverConfig);
    $standardServer->handleRequest();
} catch (Throwable $e) {
    StandardServer::send500Error(
        $serverConfig
            ? new Exception(json_encode($serverConfig->formatError($e), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
            : $e,
        true
    );
}
