<?php
/**
 * Created by Fadymichel.
 * git: https://github.com/FadymichelR
 * 2018
 */

namespace Fady\Repository;


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
    abstract function __construct(PDO $pdo = null);


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
     * @return array|bool|mixed
     */
    public function findBy(array $arguments = [])
    {
        $where = $this->where($arguments);
        try {

            $db = $this->pdo->prepare('SELECT * from ' . $this->getTableName() . $where);
            $db->execute(array_values($arguments));
            $db->setFetchMode(\PDO::FETCH_CLASS, $this->getEntity());

            return $db->fetchAll();

        } catch (\Exception $e) {

            echo 'Error : ', $e->getMessage(), "\n";
            return false;
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
     * @param $entity
     * @return bool|mixed
     */
    public function save($entity)
    {

        $entity = is_array($entity) ? $entity : $entity->toArray();
        if (array_key_exists('id', $entity)) {
            unset($entity['id']);
        }

        $columns = implode(', ', array_keys($entity));
        $values = implode(',', array_fill(0, count($entity), '?'));

        try {

            $db = $this->pdo->prepare('INSERT INTO ' . $this->getTableName() . ' (' . $columns . ') VALUES (' . $values . ')');
            $db->execute(array_values(array_map([$this, 'formatter'], $entity)));

            return true;

        } catch (\Exception $e) {

            echo 'Error : ', $e->getMessage(), "\n";

            return false;
        }

    }


    /**
     * @param $entity
     * @return bool|mixed
     */
    public function remove($entity)
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

            echo 'Error : ', $e->getMessage(), "\n";
            return false;
        }
    }


    /**
     * @param array $arguments
     * @return string
     */
    private function where(array $arguments = []) {

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

    abstract protected function getTableName();

    abstract protected function getEntity();

}