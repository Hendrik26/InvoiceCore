<?php declare(strict_types=1);

/**
 * @author I. R. Vobmagturs <i+r+vobmagturs@commodea.com>
 */

use GraphQL\Server\StandardServer;
use Irvobmagturs\InvoiceCore\CommandHandler\CustomerHandler;
use Irvobmagturs\InvoiceCore\CommandHandler\InvoiceHandler;
use Irvobmagturs\InvoiceCore\Infrastructure\EventBus;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CqrsCommandHandlersResolver;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\CustomizedGraphqlServerConfig;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\SchemaFileCache;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;
use Irvobmagturs\InvoiceCore\Infrastructure\SqLiteEventStore;
use Irvobmagturs\InvoiceCore\Infrastructure\SqLitePdo;
use Irvobmagturs\InvoiceCore\Repository\CustomerRepository;
use Irvobmagturs\InvoiceCore\Repository\InvoiceRepository;
use Jubjubbird\Respects\DomainEvents;

require_once __DIR__ . '/vendor/autoload.php';
$schemaCache = __DIR__ . '/data/cache/schema';
$eventStoreFile = __DIR__ . '/data/eventstore.sqlite';
$schemaFile = __DIR__ . '/cqrs.graphqls';
$context = null;
$rootValue = null;
$serverConfig = null;
$eventStore = new SqLiteEventStore(new SqLitePdo($eventStoreFile));
$eventBus = new class implements EventBus
{
    function dispatch(DomainEvents $domainEvents): void
    {
    }
};
$typeResolver = new TypeResolver();
$typeResolver->addResolverForField('CqrsQuery', 'loadFoo', function () {
    return 'bar';
});
$typeResolver = new CqrsCommandHandlersResolver($typeResolver);
$typeResolver = new InvoiceHandler(new InvoiceRepository($eventStore, $eventBus), $typeResolver);
$typeResolver = new CustomerHandler(new CustomerRepository($eventStore, $eventBus), $typeResolver);
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
