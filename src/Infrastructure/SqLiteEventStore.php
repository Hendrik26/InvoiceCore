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
use Exception;
use Jubjubbird\Respects\RecordedEvent;
use PDO;
use PDOException;
use PDOStatement;
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
    private $dbStatement;
    private $dbWriteStatement;


    /**
     * SqLiteEventStore constructor.
     * @param $databasePath
     */
    public function __construct(string $databasePath)
    {
        $this->databasePath = $databasePath;
        $this->connection = $this->openDataBaseConnection();
        $this->dbStatement = $this->createDbStatement($this->connection);
        $this->dbWriteStatement = $this->createDbWriteStatement($this->connection);
    }

    /**
     * @param PDO $connection
     * @return bool|PDOStatement
     * @throws PDOException
     */
    private function createDbStatement(PDO $connection)
    {
        $sql = <<<'SQL'
select
  event_type,
  aggregate_id_type,
  aggregate_id_string,
  date_string,
  serialized_event_data
from event_table
where aggregate_id_string = :aggregate_id_string
SQL;
        $statement = $connection->prepare($sql);
        return $statement;
    }

    /**
     * @param PDO $connection
     * @return bool|PDOStatement
     * @throws PDOException
     */
    private function createDbWriteStatement(PDO $connection)
    {
        $sql = <<<SQL
insert into event_table (
  event_type,
  aggregate_id_type,
  aggregate_id_string,
  date_string,
  serialized_event_data
)
values (
  :eventType,
  :aggregateIdType,
  :aggregateIdString,
  :dateString,
  :serializedEventData
);
SQL;
        $statement = $connection->prepare($sql);
        return $statement;
    }



    /**
     * @param IdentifiesAggregate $id
     * @return Traversable
     * @throws NoEventsStored when there are no events for that ID.
     */
    public function listEventsForId(IdentifiesAggregate $id): Traversable
    {
        $dbResults = $this->getEventsFromStatement($this->dbStatement, $id);
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
     * @throws Exception
     */
    private function restoreEventFromRecord(stdClass $record): RecordedEvent
    {
        $eventType = $record->event_type; // SELECT event_type, id, foo FROM event_store....
        $idType = $record->aggregate_id_type;
        $idString = $record->aggregate_id_string;
        $dateString = $record->date_string;
        $serializedEventData = $record->serialized_event_data;
        return new RecordedEvent(
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
        array_walk($recordedEvents, [$this, 'writeEvent']);
    }

    /**
     * @return PDO
     * @throws PDOException
     */
    private function openDataBaseConnection(): PDO
    {
        $PDO = new PDO($this->createConnectionString());
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $PDO;
    }

    /**
     * @param PDOStatement $statement
     * @param IdentifiesAggregate $aggregateId
     * @return array
     */
    private function getEventsFromStatement(PDOStatement $statement, IdentifiesAggregate $aggregateId): array
    {
        $statement->execute([':aggregate_id_string' => $aggregateId]);
        return $statement->fetchAll();
        // return $connection->query($this->createQueryString());

    }

    private function writeEvent(RecordedEvent $recordedEvent): void
    {
        $eventType = get_class($recordedEvent->getPayload());
        $aggregateIdType = get_class($recordedEvent->getAggregateId());
        $aggregateIdString = strval($recordedEvent->getAggregateId());
        $dateString = $recordedEvent->getRecordedOn()->format(DATE_ATOM);
        $serializedEventData = $recordedEvent->getPayload()->serialize();
        $this->dbWriteStatement->execute([
            ':eventType' => $eventType,
            ':aggregateIdType' => $aggregateIdType,
            ':aggregateIdString' => $aggregateIdString,
            ':dateString' => $dateString,
            ':serializedEventData' => $serializedEventData
        ]);
    }

    private function createConnectionString()
    {
        return 'sqlite:' . $this->databasePath;
    }

}