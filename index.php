<?php declare(strict_types=1);

/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

use GraphQL\Server\StandardServer;
use GraphQL\Type\Definition\ResolveInfo;
use Irvobmagturs\InvoiceCore\CommandHandler\CustomerHandler;
use Irvobmagturs\InvoiceCore\CommandHandler\InvoiceHandler;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandBus;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CustomizedGraphqlServerConfig;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\SchemaFileCache;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;
use Irvobmagturs\InvoiceCore\Infrastructure\SqLiteEventStore;
use Irvobmagturs\InvoiceCore\Infrastructure\SqLitePdo;
use Irvobmagturs\InvoiceCore\Projector\SimpleProjector;
use Irvobmagturs\InvoiceCore\Query\CustomerResolver;
use Irvobmagturs\InvoiceCore\Query\InvoiceResolver;
use Irvobmagturs\InvoiceCore\Repository\CustomerRepository;
use Irvobmagturs\InvoiceCore\Repository\InvoiceRepository;

if (PHP_SAPI !== 'cli') {
    header('Access-Control-Allow-Origin: *');
    if (($_SERVER['REQUEST_METHOD'] ?? null) == 'OPTIONS') {
        header('Access-Control-Allow-Headers: content-type');
        header('Access-Control-Allow-Methods: POST');
        exit;
    }
}

require_once __DIR__ . '/vendor/autoload.php';
$schemaCache = __DIR__ . '/data/cache/schema';
$eventStoreFile = __DIR__ . '/data/eventstore.sqlite';
$schemaFile = __DIR__ . '/cqrs.graphqls';
$customerDir = __DIR__ . '/data/projections/customer';
$invoiceDir = __DIR__ . '/data/projections/invoice';
$context = null;
$rootValue = null;
$serverConfig = null;
$eventStore = new SqLiteEventStore(new SqLitePdo($eventStoreFile));
$eventBus = new SimpleProjector($invoiceDir, $customerDir);
$typeResolver = new TypeResolver();
$typeResolver->addResolverForField('CqrsQuery', 'loadFoo', function () {
    return 'bar';
});
$typeResolver = new InvoiceResolver($invoiceDir, $typeResolver);
$typeResolver = new CustomerResolver($customerDir, $typeResolver);
$commandBus = new CqrsCommandBus($typeResolver);
$typeResolver = $commandBus;
$commandBus->append(new InvoiceHandler(new InvoiceRepository($eventStore, $eventBus)));
$commandBus->append(new CustomerHandler(new CustomerRepository($eventStore, $eventBus)));
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
