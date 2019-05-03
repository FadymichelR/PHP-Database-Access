<?php


namespace Fad\Migrations;

/**
 * Class MigrateService
 * @package Fad\Migrations
 */
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
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        $this->pdo = $params['connection'];
        $defaultParams = [
            'table_name' => 'migration_versions',
        ];
        $this->params = $defaultParams + $params;
    }

    /**
     * @return void
     */
    /**
     * @return void
     */
    public function generateMigration(): void
    {
        $file = date('YmdHis') . '.sql';
        file_put_contents($this->params['migrations_directory'] . DIRECTORY_SEPARATOR . $file, '');
        echo 'Migration ' . $file . ' generate' . PHP_EOL;
    }

    /**
     * @param string|null $version
     */
    public function migrate(): void
    {
        $this->createVersion();

        $stmt = $this->pdo->prepare('SELECT version FROM ' . $this->params['table_name']);
        $stmt->execute();
        $versions = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($this->getMigrations() as $version => $migration) {

            if (in_array($version, $versions)) {
                continue;
            }
            echo 'Migrating ' . $version . PHP_EOL;
            try {
                $this->pdo->query(file_get_contents($migration));
                $status = $this->pdo->prepare('INSERT INTO ' . $this->params['table_name'] . ' (`version`) VALUES (:version)')
                    ->execute(['version' => $version]);
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }

        }

    }

    /**
     * @return void
     */
    public function createVersion(): void
    {
        $this->pdo->query('CREATE TABLE IF NOT EXISTS ' . $this->params['table_name'] . ' (id INT PRIMARY KEY NOT NULL AUTO_INCREMENT, version varchar(255) NOT NULL)');
    }

    /**
     * @param string $folder
     * @return array
     */
    private function getMigrations(): array
    {
        $migrations = [];
        foreach (new \DirectoryIterator($this->params['migrations_directory']) as $file) {
            if ($file->getExtension() !== 'sql') {
                continue;
            }
            $version = pathinfo($file->getBasename(), PATHINFO_FILENAME);
            $migrations[$version] = $file->getPathname();
        }
        ksort($migrations);
        return $migrations;
    }
}