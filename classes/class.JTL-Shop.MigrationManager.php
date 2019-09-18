<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Migration
 */
class MigrationManager
{
    /**
     * @var array
     */
    protected static $migrations;

    /**
     * @var array
     */
    protected $executedMigrations;

    /**
     * Construct
     */
    public function __construct()
    {
        static::$migrations = [];
    }

    /**
     * Migrate the specified identifier.
     *
     * @param int $identifier
     * @return array
     * @throws Exception
     */
    public function migrate($identifier = null)
    {
        $migrations         = $this->getMigrations();
        $executedMigrations = $this->getExecutedMigrations();
        $currentId          = $this->getCurrentId();

        if (empty($executedMigrations) && empty($migrations)) {
            return [];
        }

        if ($identifier === null) {
            $identifier = max(array_merge($executedMigrations, array_keys($migrations)));
        }

        $direction = $identifier > $currentId ?
            IMigration::UP : IMigration::DOWN;

        $executed = [];

        try {
            if ($direction === IMigration::DOWN) {
                krsort($migrations);
                foreach ($migrations as $migration) {
                    if ($migration->getId() <= $identifier) {
                        break;
                    }
                    if (in_array($migration->getId(), $executedMigrations)) {
                        $executed[] = $migration;
                        $this->executeMigration($migration, IMigration::DOWN);
                    }
                }
            }
            ksort($migrations);
            foreach ($migrations as $migration) {
                if ($migration->getId() > $identifier) {
                    break;
                }
                if (!in_array($migration->getId(), $executedMigrations)) {
                    $executed[] = $migration;
                    $this->executeMigration($migration, IMigration::UP);
                }
            }
        } catch (PDOException $e) {
            @list($code, $state, $message) = $e->errorInfo;
            $this->log($migration, $direction, $code, $message);
            throw $e;
        } catch (Exception $e) {
            $this->log($migration, $direction, 'JTL01', $e->getMessage());
            throw $e;
        }

        return $executed;
    }

    /**
     * Get a migration by Id.
     *
     * @param int $id MigrationId
     * @return IMigration
     */
    public function getMigrationById($id)
    {
        $migrations = $this->getMigrations();

        if (!array_key_exists($id, $migrations)) {
            throw new \InvalidArgumentException(sprintf(
                'Migration "%s" not found', $id
            ));
        }

        return $migrations[$id];
    }

    /**
     * Execute a migration.
     *
     * @param int $id MigrationId
     * @param string $direction Direction
     * @return void
     */
    public function executeMigrationById($id, $direction = IMigration::UP)
    {
        $migration = $this->getMigrationById($id);
        $this->executeMigration($migration, $direction);
    }

    /**
     * Execute a migration.
     *
     * @param IMigration $migration Migration
     * @param string $direction Direction
     * @return void
     * @throws Exception
     */
    public function executeMigration(IMigration $migration, $direction = IMigration::UP)
    {
        // reset cached executed migrations
        $this->executedMigrations = null;

        $start   = new DateTime('now');
        $id      = $migration->getId();

        try {
            Shop::DB()->beginTransaction();
            call_user_func([&$migration, $direction]);
            Shop::DB()->commit();
            $this->migrated($migration, $direction, $start);
        } catch (Exception $e) {
            Shop::DB()->rollback();
            throw $e;
        }
    }

    /**
     * Sets the database migrations.
     *
     * @param array $migrations Migrations
     * @return $this
     */
    public function setMigrations(array $migrations)
    {
        static::$migrations = $migrations;

        return $this;
    }

    /**
     * Has valid migrations.
     *
     * @return boolean
     */
    public function hasMigrations()
    {
        return count($this->getMigrations()) > 0;
    }

    /**
     * Gets an array of the database migrations.
     *
     * @throws \InvalidArgumentException
     * @return IMigration[]
     */
    public function getMigrations()
    {
        if (!is_array(static::$migrations) || count(static::$migrations) === 0) {
            $migrations = [];
            $executed   = $this->_getExecutedMigrations();
            $path       = MigrationHelper::getMigrationPath();

            foreach (glob($path . '*.php') as $filePath) {
                $baseName = basename($filePath);
                if (MigrationHelper::isValidMigrationFileName($baseName)) {
                    $id    = MigrationHelper::getIdFromFileName($baseName);
                    $info  = MigrationHelper::getInfoFromFileName($baseName);
                    $class = MigrationHelper::mapFileNameToClassName($baseName);
                    $date  = isset($executed[(int)$id]) ? $executed[(int)$id] : null;

                    require_once $filePath;

                    if (!class_exists($class)) {
                        throw new \InvalidArgumentException(sprintf(
                            'Could not find class "%s" in file "%s"',
                            $class,
                            $filePath
                        ));
                    }

                    $migration = new $class($info, $date);

                    if (!is_subclass_of($migration, 'IMigration')) {
                        throw new \InvalidArgumentException(sprintf(
                            'The class "%s" in file "%s" must implement IMigration interface',
                            $class,
                            $filePath
                        ));
                    }

                    $migrations[$id] = $migration;
                }
            }
            ksort($migrations);
            $this->setMigrations($migrations);
        }

        return static::$migrations;
    }

    /**
     * Get lastest executed migration id.
     *
     * @return int
     */
    public function getCurrentId()
    {
        $oVersion = Shop::DB()->executeQuery("SELECT kMigration FROM tmigration ORDER BY kMigration DESC", 1);
        if ($oVersion) {
            return $oVersion->kMigration;
        }

        return 0;
    }

    /**
     * @return array
     */
    public function getExecutedMigrations()
    {
        $migrations = $this->_getExecutedMigrations();
        if (!is_array($migrations)) {
            $migrations = [];
        }

        return array_keys($migrations);
    }

    /**
     * @return array
     */
    public function getPendingMigrations()
    {
        $executed   = $this->getExecutedMigrations();
        $migrations = array_keys($this->getMigrations());

        return array_udiff($migrations, $executed, function ($a, $b) {
            return strcmp($a, $b);
        });
    }

    /**
     * @return array|int
     */
    protected function _getExecutedMigrations()
    {
        if ($this->executedMigrations === null) {
            $migrations = Shop::DB()->executeQuery("SELECT * FROM tmigration ORDER BY kMigration ASC", 2);
            foreach ($migrations as $m) {
                $this->executedMigrations[$m->kMigration] = new DateTime($m->dExecuted);
            }
        }

        return $this->executedMigrations;
    }

    /**
     * @param IMigration $migration
     * @param string     $direction
     * @param string     $state
     * @param string     $message
     */
    public function log(IMigration $migration, $direction, $state, $message)
    {
        $sql = sprintf(
            "INSERT INTO tmigrationlog (kMigration, cDir, cState, cLog, dCreated) VALUES ('%s', %s, %s, %s, '%s');",
            $migration->getId(),
            Shop::DB()->pdoEscape($direction),
            Shop::DB()->pdoEscape($state),
            Shop::DB()->pdoEscape($message),
            (new DateTime('now'))->format('Y-m-d H:i:s')
        );
        Shop::DB()->executeQuery($sql, 3);
    }

    /**
     * @param IMigration $migration
     * @param string     $direction
     * @param DateTime   $executed
     * @return $this
     */
    public function migrated(IMigration $migration, $direction, $executed)
    {
        if (strcasecmp($direction, IMigration::UP) === 0) {
            $sql = sprintf(
                "INSERT INTO tmigration (kMigration, dExecuted) VALUES ('%s', '%s');",
                $migration->getId(), $executed->format('Y-m-d H:i:s')
            );
            Shop::DB()->executeQuery($sql, 3);
        } else {
            $sql = sprintf("DELETE FROM tmigration WHERE kMigration = '%s'", $migration->getId());
            Shop::DB()->executeQuery($sql, 3);
        }

        return $this;
    }
}
