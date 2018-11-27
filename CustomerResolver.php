<?php
/**
 * Created by PhpStorm.
 * User: hendr
 * Date: 27.11.2018
 * Time: 09:04
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
use Irvobmagturs\InvoiceCore\Repository\CustomerRepository;
use Irvobmagturs\InvoiceCore\Repository\InvoiceRepository;
require_once __DIR__ . '/vendor/autoload.php';

class CustomerResolver
{
    private $schemaCache;
    private $eventStoreFile;
    private $schemaFile;
    private$customerDir;
    private $invoiceDir;
    private $context;
    private $rootValue;
    private $serverConfig;
    private $eventStore;
    private $eventBus;

    public function initialize()
    {
        $this->schemaCache = __DIR__ . '/data/cache/schema';
        $this->eventStoreFile = __DIR__ . '/data/eventstore.sqlite';
        $this->schemaFile = __DIR__ . '/cqrs.graphqls';
        $this->customerDir = __DIR__ . '/data/projections/customer';
        $this->invoiceDir = __DIR__ . '/data/projections/invoice';
        $this->context = null;
        $this->rootValue = null;
        $this->serverConfig = null;
        $this->eventStore = new SqLiteEventStore(new SqLitePdo($eventStoreFile));
        $this->eventBus = new SimpleProjector($invoiceDir, $customerDir);
    }

    public function createResolver(){
        $typeResolver = new TypeResolver($typeResolver);

        $typeResolver->addResolverForField('CqrsQuery', 'customers', function () {
            $customerFiles = array();
            $customers = array();
            foreach (new DirectoryIterator('data/projections/customer') as $fileInfo) {
                if($fileInfo->isDot()) continue;
                if($fileInfo->getExtension() === 'json'){
                    $customerFiles[] = $fileInfo->getFilename();
                }
                // echo $fileInfo->getFilename() . "<br>\n";
            }
            foreach ($customerFiles as $customerFile){
                $customers[] =  json_decode(file_get_contents($customerFile));
            }
            return $customers;
        });
        $typeResolver->addResolverForField('QCustomer', 'name', function (
            $typeValue, array $args, $context, ResolveInfo $info) {

            return $customer->billingAdress;
        });

        return $typeResolver;
    }

}