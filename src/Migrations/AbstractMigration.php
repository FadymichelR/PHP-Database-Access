<?php

namespace Fad\Migrations;

/**
 * Class AbstractMigration
 * @package Fad\Migrations
 */
abstract class AbstractMigration
{
    /**
     * @var string[]
     */
    private $sql = [];

    /**
     * @return void
     */
    abstract public function up(): void;

    /**
     * @return void
     */
    abstract public function down(): void;

    /**
     * @param string $sql
     * @return self
     */
    protected function add(string $sql): self
    {
        $this->sql[] = $sql;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getSql(): array
    {
        return $this->sql;
    }
}