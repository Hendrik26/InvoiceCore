<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: hendrik
 * Date: 15.11.18
 * Time: 12:08
 */

namespace Irvobmagturs\InvoiceCore\Infrastructure;


use Buttercup\Protects\IdentifiesAggregate;
use DateTimeImmutable;
use Jubjubbird\Respects\RecordedEvent;
use PDO;
use stdClass;
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
     * @return Traversable
     * @throws NoEventsStored when there are no events for that ID.
     */
    public function listEventsForId(IdentifiesAggregate $id): Traversable
    {
        $dbResults = $this->getEventsFromConnection($this->connection, $id);
        $this->guardAtLeastOneEvent($dbResults);
        return array_map([$this, 'restoreEventFromRecord'], $dbResults);
    }

    /**
     * @param array $dbResults
     * @throws NoEventsStored
     */
    private function guardAtLeastOneEvent(array $dbResults): void
    {
        if (empty($dbResults)) {
            throw new NoEventsStored();
        }
    }

    /**
     * @param stdClass $record
     * @return RecordedEvent
     * @throws \Exception
     */
    private function restoreEventFromRecord(stdClass $record): RecordedEvent
    {
        $eventType = $record->event_type; // SELECT event_type, id, foo FROM event_store....
        $idType = $record->id_type;
        $idString = $record->id_string;
        $dateString = $record->date_string;
        $serializedEventData = $record->serialized_event_data;
        new RecordedEvent(
            $eventType::deserialize($serializedEventData),
            $idType::fromString($idString),
            new DateTimeImmutable($dateString)
        );
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
        return new PDO($this->createConnectionString());
    }

    /**
     * @param PDO $connection
     * @param IdentifiesAggregate $aggregateId
     * @return array
     */
    private function getEventsFromConnection(PDO $connection, IdentifiesAggregate $aggregateId): array
    {
        $sql = 'select event_type, id_type, id_string, date_string, serialized_event_data from event_table
             where event where is_string = :id_string';
        $statement = $connection->prepare($sql);
        $statement->execute([':id_string' => $aggregateId]);
        return $statement->fetchAll();
        // return $connection->query($this->createQueryString());

    }

    private function createConnectionString()
    {
        return 'sqlite:' . $this->databasePath;
    }
}