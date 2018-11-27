<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 27.11.18
 * Time: 11:04
 */

namespace Irvobmagturs\InvoiceCore\Query;


use DateTime;
use GraphQL\Type\Definition\ResolveInfo;
use Irvobmagturs\InvoiceCore\Infrastructure\GraphQL\TypeResolver;

class InvoiceResolver extends TypeResolver
{
    public function __construct($base = null)
    {
        parent::__construct($base);
        $this->addResolverForField('CqrsQuery', 'invoices', function () {
            return ['Inv001', 'Inv002', 'Inv003'];
        });
        $this->addResolverForField('QInvoice', 'invoiceNumber', function (
            $typeValue, array $args, $context, ResolveInfo $info) {
            return $typeValue;
        });
        $this->addResolverForField('QInvoice', 'invoiceDate', function (
            $typeValue, array $args, $context, ResolveInfo $info) {
            return (new DateTime('now'))->format(DATE_ATOM).$typeValue;
        });
        $this->addResolverForField('QInvoice', 'mandate', function (
            $typeValue, array $args, $context, ResolveInfo $info) {
            return $typeValue;
        });
        $this->addResolverForField('QMandate', 'mandateReference', function (
            $typeValue, array $args, $context, ResolveInfo $info) {
            return 'MR-'.$typeValue;
        });
    }

}