<?php declare(strict_types=1);

namespace Irvobmagturs\InvoiceCore\Infrastructure;

/**
 * A base class for value objects allowing read-only access to its protected member variables, equality testing and
 * [de]serialization.
 */
abstract class AbstractValueObjectBase extends ImmutableValue implements Serializable
{
}
