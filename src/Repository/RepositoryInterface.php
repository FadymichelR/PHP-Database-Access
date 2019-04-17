<?php
/**
 * Created by Fadymichel.
 * git: https://github.com/FadymichelR
 * 2018
 */

namespace Fad\Repository;

use Fad\Entity\EntityInterface;

/**
 * Interface RepositoryInterface
 * @package Fad\Repository
 */
interface RepositoryInterface
{

    /**
     * @param $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * @param array $arguments
     * @return mixed
     */
    public function findBy(array $arguments = []);

    /**
     * @return mixed
     */
    public function findAll(): array;


    /**
     * @param $entity
     * @return bool
     */
    public function save(EntityInterface $entity): bool;

    /**
     * @param $entity
     * @return bool
     */
    public function update(EntityInterface $entity): bool;

    /**
     * @param $entity
     * @return bool
     */
    public function remove(EntityInterface $entity): bool;

    /**
     * @param array $arguments
     * @return mixed
     */
    public function count(array $arguments = []);

}