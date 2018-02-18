<?php
/**
 * Created by Fadymichel.
 * git: https://github.com/FadymichelR
 * 2018
 */

namespace Fady\Repository;


interface RepositoryInterface
{

    /**
     * @param $id
     * @return mixed
     */
    public function find($id);


    /**
     * @param array $options
     * @return mixed
     */
    public function findBy(array $arguments = []);

    /**
     * @return mixed
     */
    public function findAll();


    /**
     * @param $entity
     * @return mixed
     */
    public function save($entity);

    /**
     * @param $entity
     * @return mixed
     */
    public function remove($entity);

    /**
     * @param $by
     * @return mixed
     */
    public function count(array $arguments = []);



}