<?php

namespace Fad\Repository;


use Fad\Entity\Annotation;
use Fad\Entity\EntityInterface;
use Fad\Helper\Hydrate;
use Fad\Helper\ObjectToArray;
use Fad\QueryBuilder;
use PDO;

/**
 * Class Repository
 * @package Webby\Repository
 */
abstract class Repository implements RepositoryInterface
{

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * Repository constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo = $pdo;
    }

    /**
     * @param int $id
     * @return object|null
     * @throws \Exception
     */
    public function find(int $id): ?object
    {
        $query = $this->prepareQueryBuilder(['id' => $id]);
        $db = $this->query($query, [$id]);

        if ($response = $db->fetch()) {
            return Hydrate::lunch($this->getEntity(), $response);
        }
        return null;
    }

    /**
     * @param array $arguments
     * @return object|null
     * @throws \Exception
     */
    public function findOneBy(array $arguments = []): ?object
    {
        $query = $this->prepareQueryBuilder($arguments);

        $db = $this->query($query, array_values($arguments));
        if ($response = $db->fetch()) {
            return Hydrate::lunch($this->getEntity(), $response);
        }
        return null;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function findAll(): array
    {
        return $this->findBy();
    }

    /**
     * @param array $arguments
     * @return array
     * @throws \Exception
     */
    public function findBy(array $arguments = []): array
    {
        $query = $this->prepareQueryBuilder($arguments);
        $db = $this->query($query, array_values($arguments));

        $objects = [];
        foreach ($db->fetchAll() as $data) {
            $objects[] = Hydrate::lunch($this->getEntity(), $data);
        }
        return $objects;
    }

    /**
     * @param EntityInterface $entity
     * @return bool
     * @throws \Exception
     */
    public function save(EntityInterface $entity): bool
    {

        if ($entity->getid()) {
            return $this->update($entity);
        }

        $propertiesAreMapped = $this->mapped($entity);

        $columns = implode(', ', array_keys($propertiesAreMapped));
        $values = implode(',', array_fill(0, count($propertiesAreMapped), '?'));

        try {

            $db = $this->pdo->prepare('INSERT INTO ' . $this->getTableName() . ' (' . $columns . ') VALUES (' . $values . ')');
            $response = $db->execute(array_values($propertiesAreMapped));

            if ($response === true) {
                Hydrate::setId($entity, (int)$this->pdo->lastInsertId());
            }
            return $response;

        } catch (\Exception $e) {

            throw new \Exception($e->getMessage());
        }

    }

    /**
     * @param EntityInterface $entity
     * @return bool|mixed
     * @throws \Exception
     */
    public function update(EntityInterface $entity): bool
    {
        try {

            if (!$entity->getid()) {
                throw New \Exception('This entity haven\'t an ID');
            }

            $propertiesAreMapped = $this->mapped($entity);
            $cols = [];
            foreach ($propertiesAreMapped as $key => $value) {
                $cols[] = "$key = :$key";
            }

            $db = $this->pdo->prepare(
                'UPDATE ' . $this->getTableName()
                . ' SET ' . implode(', ', $cols)
                . ' WHERE id =' . $entity->getid() . ''
            );

            return $db->execute(array_map([$this, 'formatter'], $propertiesAreMapped));

        } catch (\Exception $e) {

            throw new \Exception($e->getMessage());
        }

    }

    /**
     * @param $entity
     * @return bool
     */
    public function remove(EntityInterface $entity): bool
    {
        $db = $this->pdo->prepare('DELETE FROM ' . $this->getTableName() . ' WHERE id = ?');

        return $db->execute([$entity->getId()]);
    }

    /**
     * @param array $arguments
     * @return int
     */
    public function count(array $arguments = []): int
    {
        $query = $this->prepareQueryBuilder($arguments)->select('count(*)');
        $db = $this->pdo->prepare($query);
        $db->execute(array_values($arguments));

        return $db->fetchColumn();
    }

    /**
     * @param array $arguments
     * @return QueryBuilder
     */
    public function prepareQueryBuilder(array $arguments): QueryBuilder
    {
        $query = (new QueryBuilder())->select('*')->from($this->getTableName());
        foreach ($arguments as $argument => $value) {
            $query->where(sprintf("%s = ?", $argument));
        }

        return $query;
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder(): QueryBuilder
    {
        return (new QueryBuilder())->from($this->getTableName());
    }

    /**
     * @param string $query
     * @param array $data
     * @return \PDOStatement
     */
    public function query(string $query, array $data = []): \PDOStatement
    {
        $db = $this->pdo->prepare($query);
        $db->execute($data);
        $db->setFetchMode(\PDO::FETCH_ASSOC);

        return $db;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }


    /**
     * @param EntityInterface $entity
     * @return array
     * @throws \ReflectionException
     */
    protected function mapped(EntityInterface $entity): array
    {

        $entityArray = ObjectToArray::convert($entity, true);

        $propertiesAreMapped = Annotation::isMapped($this->getEntity());
        $entityArray = array_filter($entityArray, function ($key) use ($propertiesAreMapped) {

            return in_array($key, $propertiesAreMapped);
        }, ARRAY_FILTER_USE_KEY);

        return $entityArray;
    }


    /**
     * @return string
     */
    abstract protected function getTableName(): string;

    /**
     * @return string
     */
    abstract protected function getEntity(): string;

}