<?php

namespace Fady\Entity;

interface EntityInterface {

    /**
     * @return int
     */
    public function getId();

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return void
     */
    public function hydrate();

}