<?php


namespace Fad\Migrations;


class MigrateService
{


    /**
     * @var \PDO
     */
    private $pdo;
    /**
     * @var array
     */
    private $params;

    /**
     * MigrateService constructor.
     * @param \PDO $pdo
     * @param array $params
     */
    public function __construct(\PDO $pdo, array $params = [])
    {
        $this->pdo = $pdo;
        $defaultParams = [
            'table_name' => 'migration_versions',
            'column_name' => 'version',
        ];
        $this->params = $defaultParams + $params;
    }


    /**
     * @return void
     */
    public function generateMigration() :void
    {
        $migration = file_get_contents(__DIR__.'migration.txt');
        $version = 'Version'.date('YmdHis');
        $migration = str_replace(
            ['%migrations_namespace%', '%version%'],
            [$this->params['migrations_namespace'], $version],
            $migration
        );
        file_put_contents($this->params['migrations_directory'].DIRECTORY_SEPARATOR.$version.'.php', $migration);
    }
}