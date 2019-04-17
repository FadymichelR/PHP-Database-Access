<?php

namespace Fad\Entity;

/**
 * Interface EntityInterface
 * @package Webby\Entity
 */
interface EntityInterface {

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return void
     */
    public function hydrate(): void;

}