<?php
/**
 * Created by Fadymichel.
 * git: https://github.com/FadymichelR
 * 2018
 */

namespace Fad\Repository;


use Fad\Entity\Annotation;
use Fad\Entity\EntityInterface;
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
     * @param $id
     * @return mixed
     */
    public function find(int $id): ?object
    {

        $db = $this->pdo->prepare('SELECT * FROM ' . $this->getTableName() . ' WHERE id = ?');
        $db->execute([$id]);

        $db->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->getEntity());
        return $db->fetch() ?: null;
    }


    /**
     * @param array $arguments
     * @param bool $unique
     * @return array
     */
    public function findBy(array $arguments = [], bool $unique = false): array
    {
        $db = $this->pdo->prepare('SELECT * from ' . $this->getTableName() . $this->where($arguments));
        $db->execute(array_values($arguments));

        $db->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->getEntity());

        if ($unique) {
            return $db->fetch();
        }
        return $db->fetchAll();
    }

    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->findBy();
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

            return $db->execute(array_values(array_map([$this, 'formatter'], $propertiesAreMapped)));

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
                'UPDATE ' . $this->getTableName() . ' SET ' . implode(', ',
                    $cols) . ' WHERE id =' . $entity->getid() . ''
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

        return $db->execute([is_object($entity) ? $entity->getId() : (int)$entity]);
    }

    /**
     * @param array $arguments
     * @return int
     */
    public function count(array $arguments = []): int
    {
        $db = $this->pdo->prepare('SELECT count(*) from ' . $this->getTableName() . $this->where($arguments));
        $db->execute(array_values($arguments));

        return $db->fetchColumn();
    }



    /**
     * @param array $arguments
     * @return string
     */
    private function where(array $arguments = [])
    {

        $where = '';
        if (!empty($arguments)) {

            $where = array_map(function ($key) {
                return sprintf("%s = ?", $key);
            }, array_keys($arguments));

            $where = ' WHERE ' . implode(' AND ', $where);
        }
        return $where;
    }


    /**
     * @param $value
     * @return mixed
     */
    private function formatter($value)
    {

        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        }
        return $value;
    }

    /**
     * @param EntityInterface $entity
     * @return array
     * @throws \ReflectionException
     */
    private function mapped(EntityInterface $entity): array
    {

        $entity = $entity->toArray();

        $propertiesAreMapped = (new Annotation())->isMapped($this->getEntity());
        $entity = array_filter($entity, function ($key) use ($propertiesAreMapped) {

            return in_array($key, $propertiesAreMapped);
        }, ARRAY_FILTER_USE_KEY);

        return $entity;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
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