<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 27.11.18
 * Time: 10:30
 */

namespace Irvobmagturs\InvoiceCore\Query;


use DirectoryIterator;
use GraphQL\Type\Definition\ResolveInfo;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;

const FILE_EXTENSION = 'json';

class CustomerResolver extends TypeResolver
{
    private $dir;

    public function __construct($dir, $base = null)
    {
        parent::__construct($base);
        $this->addResolverForField('CqrsQuery', 'customers', function () use ($dir) {
            $customerFiles = array();
            $customers = array();
            foreach (new DirectoryIterator($dir) as $fileInfo) {
                if ($fileInfo->isDot()) continue;
                if ($fileInfo->getExtension() === FILE_EXTENSION) {
                    $customerFiles[] = $fileInfo->getRealPath();
                }
            }
            foreach ($customerFiles as $customerFile) {
                $customers[] = json_decode(file_get_contents($customerFile));
            }
            return $customers;
        });
        $this->addResolverForField(
            'QCustomer',
            'name',
            function ($customer, array $args, $context, ResolveInfo $info) {
                return $customer->name;
        }
        );
        $this->addResolverForField(
            'QCustomer',
            'billingAddress',
            function ($customer, array $args, $context, ResolveInfo $info) {
                return $customer->billingAddress;
            }
        );
        $this->addResolverForField(
            'QCustomer',
            'customerId',
            function ($customer, array $args, $context, ResolveInfo $info) {
                return $customer->vatid;
            }
        );
        $this->addResolverForField(
            'QAddress',
            'countryCode',
            function ($address, array $args, $context, ResolveInfo $info) {
                return $address->countryCode;
            }
        );
        $this->addResolverForType(
            'QAddress',
            function ($address, array $args, $context, ResolveInfo $info) {
                $fieldName = $info->fieldName;
                return $address->$fieldName;
            }
        );


        $this->dir = $dir;
    }

}