<?php
/**
 * Created by Fadymichel.
 * git: https://github.com/FadymichelR
 * 2018
 */

namespace Fady\Repository;


use Fady\Entity\Annotation;
use Fady\Entity\Entity;
use Fady\Entity\EntityInterface;
use PDO;

/**
 * Class Repository
 * @package App\Repository
 */
abstract class Repository implements RepositoryInterface
{

    /**
     * @var PDO
     */
    protected $pdo;


    /**
     * Repository constructor.
     */
    abstract function __construct(PDO $pdo);


    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {

        $db = $this->pdo->prepare('SELECT * FROM ' . $this->getTableName() . ' WHERE id = ?');
        $db->execute([$id]);

        return $db->fetchObject($this->getEntity());
    }


    /**
     * @param array $arguments
     * @param bool $unique
     * @return array|mixed
     */
    public function findBy(array $arguments = [], $unique = false)
    {
        $where = $this->where($arguments);
        try {

            $db = $this->pdo->prepare('SELECT * from ' . $this->getTableName() . $where);
            $db->execute(array_values($arguments));

            if ($unique) {
                return $db->fetchObject($this->getEntity());
            }

            $db->setFetchMode(\PDO::FETCH_CLASS, $this->getEntity());
            return $db->fetchAll();

        } catch (\Exception $e) {

            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @return array|bool|mixed
     */
    public function findAll()
    {
        return $this->findBy();
    }

    /**
     * @param EntityInterface $entity
     * @return bool|mixed
     */
    public function save(EntityInterface $entity)
    {

        if ($entity->getid()) {
            return $this->update($entity);
        }

        $propertiesAreMapped = $this->mapped($entity);

        $columns = implode(', ', array_keys($propertiesAreMapped));
        $values = implode(',', array_fill(0, count($propertiesAreMapped), '?'));

        try {

            $db = $this->pdo->prepare('INSERT INTO ' . $this->getTableName() . ' (' . $columns . ') VALUES (' . $values . ')');
            $db->execute(array_values(array_map([$this, 'formatter'], $propertiesAreMapped)));

            return true;

        } catch (\Exception $e) {

            throw new \Exception($e->getMessage());
        }

    }

    /**
     * @param EntityInterface $entity
     * @return bool|mixed
     */
    public function update(EntityInterface $entity)
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

            $db = $this->pdo->prepare('UPDATE ' . $this->getTableName() . ' SET ' . implode(', ', $cols) . ' WHERE id =' . $entity->getid() . '');
            $db->execute(array_map([$this, 'formatter'], $propertiesAreMapped));

            return true;

        } catch (\Exception $e) {

            throw new \Exception($e->getMessage());
        }

    }


    /**
     * @param $entity
     * @return bool|mixed
     */
    public function remove(EntityInterface $entity)
    {
        $db = $this->pdo->prepare('DELETE FROM ' . $this->getTableName() . ' WHERE id = ?');
        $db->execute([is_object($entity) ? $entity->getId() : (int)$entity]);

        return true;
    }

    /**
     * @param array $arguments
     * @return bool|mixed
     */
    public function count(array $arguments = [])
    {
        $where = $this->where($arguments);

        try {

            $db = $this->pdo->prepare('SELECT count(*) from ' . $this->getTableName() . $where);
            $db->execute(array_values($arguments));

            return $db->fetchColumn();

        } catch (\Exception $e) {

            throw new \Exception($e->getMessage());
        }
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
     * @return string
     */
    private function formatter($value)
    {

        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        }
        return $value;
    }

    /**
     * @param $entity
     * @return array
     */
    private function mapped(EntityInterface $entity)
    {

        $entity = is_array($entity) ? $entity : $entity->toArray();

        $propertiesAreMapped = (new Annotation())->isMapped($this->getEntity());
        $entity = array_filter($entity, function ($key) use ($propertiesAreMapped) {

            return in_array($key, $propertiesAreMapped);
        }, ARRAY_FILTER_USE_KEY);

        return $entity;
    }

    abstract protected function getTableName();

    abstract protected function getEntity();

}