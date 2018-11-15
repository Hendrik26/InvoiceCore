<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 15.11.18
 * Time: 12:08
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;


use Buttercup\Protects\IdentifiesAggregate;
use PDO;
use Traversable;

class SqLiteEventStore implements EventStore
{
    private $connection;
    const TABLENAME = '';
    const RESULT_COLUMN = '';
    const QUERY_COLUMN = '';
    const EXPRESSION = '';
    private $databasePath;

    /**
     * SqLiteEventStore constructor.
     * @param $databasePath
     */
    public function __construct(string $databasePath)
    {
        $this->databasePath = $databasePath;
        $this->connection = $this->openDataBaseConnection();
    }

    /**
     * @param IdentifiesAggregate $id
     * @return RecordedEvent[]
     */
    public function listEventsForId(IdentifiesAggregate $id): Traversable
    {
        $dbResults = $this->getEventsFromConnection($this->connection);
        return array_map([$this, 'restoreEventFromRecord'], $dbResults);
    }

    private function restoreEventFromRecord(stdClass $record): RecordedEvent
    {
      // TODO new RecordedEvent();
    }

    /**
     * @param RecordedEvent[] $recordedEvents
     */
    public function append(array $recordedEvents): void
    {
        // TODO: Implement append() method.
    }

    private function openDataBaseConnection(): PDO
    {
        return new PDO($this->createConnectionString(), self::USERNAME, self::PASSWORD);
    }


    private function createQueryString(): string
    {
        return 'select ' . self::RESULT_COLUMN . ' from ' . self::TABLENAME . ' where ' .  self::QUERY_COLUMN  . ' = '
            . self::EXPRESSION;
    }
    
    private function getEventsFromConnection(PDO $connection): array 
    {
        return $connection->query($this->createQueryString());
            
    }

    private function createConnectionString()
    {
        return 'sqlite:' . $this->databasePath;
    }
}