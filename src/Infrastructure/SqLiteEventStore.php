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
use Jubjubbird\Respects\DomainEvent;
use Jubjubbird\Respects\RecordedEvent;
use PDO;
use PDOException;
use PDOStatement;
use stdClass;
use function Verraes\ClassFunctions\short;

class SqLiteEventStore implements EventStore
{
    const EVENT_NAMESPACE = 'Irvobmagturs\\InvoiceCore\\Model\\Event\\';
    const AGGREGATE_ID_NAMESPACE = 'Irvobmagturs\\InvoiceCore\\Model\\Id\\';
    const TABLENAME = '';
    const RESULT_COLUMN = '';
    const QUERY_COLUMN = '';
    const EXPRESSION = '';
    private $connection;
    /** @var SqLitePdo */
    private $database;
    private $dbStatement;
    private $dbWriteStatement;


    /**
     * SqLiteEventStore constructor.
     * @param SqLitePdo $database
     * @throws PDOException
     */
    public function __construct(SqLitePdo $database)
    {
        $this->connection = $database;
        $this->dbStatement = $this->createDbStatement($this->connection);
        $this->dbWriteStatement = $this->createDbWriteStatement($this->connection);
        $this->database = $database;
    }

    /**
     * @param PDO $connection
     * @return bool|PDOStatement
     * @throws PDOException
     */
    private function createDbStatement(PDO $connection)
    {
        $sql = <<<'SQL'
SELECT
  event_type,
  aggregate_id_type,
  aggregate_id_string,
  date_string,
  serialized_event_data
FROM event_table
WHERE aggregate_id_string = :aggregate_id_string
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
INSERT INTO event_table (
  event_type,
  aggregate_id_type,
  aggregate_id_string,
  date_string,
  serialized_event_data
)
VALUES (
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
     * @param DomainEvent[] $recordedEvents
     */
    public function append(array $recordedEvents): void
    {
        array_walk($recordedEvents, [$this, 'writeEvent']);
    }

    /**
     * @param IdentifiesAggregate $id
     * @return DomainEvent[]
     * @throws NoEventsStored when there are no events for that ID.
     */
    public function listEventsForId(IdentifiesAggregate $id): array
    {
        $dbResults = $this->getEventsFromStatement($this->dbStatement, $id);
        $this->guardAtLeastOneEvent($dbResults);
        return array_map([$this, 'restoreEventFromRecord'], $dbResults);
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
        $eventType = self::EVENT_NAMESPACE . $record->event_type;
        $idType = self::AGGREGATE_ID_NAMESPACE . $record->aggregate_id_type;
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
     * @return PDO
     * @throws PDOException
     */
    private function openDataBaseConnection(): PDO
    {
        $PDO = new PDO($this->createConnectionString());
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $PDO;
    }

    private function writeEvent(DomainEvent $recordedEvent): void
    {
        $eventType = short($recordedEvent->getPayload());
        $aggregateIdType = short($recordedEvent->getAggregateId());
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
}
