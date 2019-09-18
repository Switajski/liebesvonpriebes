<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class MigrationHelper
 */
class MigrationHelper
{
    /**
     * @var string
     */
    const DATE_FORMAT = 'YmdHis';

    /**
     * @var string
     */
    const MIGRATION_CLASS_NAME_PATTERN = '/^Migration_(\d+)$/i';

    /**
     * @var string
     */
    const MIGRATION_FILE_NAME_PATTERN = '/^(\d+)_([\w_]+).php$/i';

    /**
     * Gets the migration path.
     *
     * @return string
     */
    public static function getMigrationPath()
    {
        return PFAD_ROOT . PFAD_UPDATE . 'migrations' . DIRECTORY_SEPARATOR;
    }

    /**
     * Gets an array of all the existing migration class names.
     * @return string
     */
    public static function getExistingMigrationClassNames()
    {
        $classNames = [];
        $path       = static::getMigrationPath();

        $phpFiles = glob($path . '*.php');
        foreach ($phpFiles as $filePath) {
            if (preg_match(static::MIGRATION_FILE_NAME_PATTERN, basename($filePath))) {
                $classNames[] = static::mapFileNameToClassName(basename($filePath));
            }
        }

        return $classNames;
    }

    /**
     * Get the id from a file name.
     *
     * @param string $fileName File Name
     * @return string
     */
    public static function getIdFromFileName($fileName)
    {
        $matches = [];
        if (preg_match(static::MIGRATION_FILE_NAME_PATTERN, basename($fileName), $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Get the info from a file name.
     *
     * @param string $fileName File Name
     * @return string
     */
    public static function getInfoFromFileName($fileName)
    {
        $matches = [];
        if (preg_match(static::MIGRATION_FILE_NAME_PATTERN, basename($fileName), $matches)) {
            return preg_replace_callback(
                '/(^|_)([a-z])/',
                function ($m) { return (strlen($m[1]) ? ' ' : '') . strtoupper($m[2]); },
                $matches[2]
            );
        }

        return null;
    }

    /**
     * Returns names like 'Migration_12345678901234'.
     *
     * @param string $fileName File Name
     * @return string
     */
    public static function mapFileNameToClassName($fileName)
    {
        return 'Migration_' . static::getIdFromFileName($fileName);
    }

    /**
     * Returns names like '12345678901234'.
     *
     * @param string $className File Name
     * @return string
     */
    public static function mapClassNameToId($className)
    {
        $matches = [];
        if (preg_match(static::MIGRATION_CLASS_NAME_PATTERN, $className, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if a migration file name is valid.
     *
     * @param string $fileName File Name
     * @return boolean
     */
    public static function isValidMigrationFileName($fileName)
    {
        $matches = [];

        return preg_match(static::MIGRATION_FILE_NAME_PATTERN, $fileName, $matches);
    }

    /**
     * Check database integrity
     */
    public static function verifyIntegrity()
    {
        Shop::DB()->query("
            CREATE TABLE IF NOT EXISTS tmigration 
            (
                kMigration bigint(14) NOT NULL, 
                nVersion int(3) NOT NULL, 
                dExecuted datetime NOT NULL,
                PRIMARY KEY (kMigration)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1", 3
        );
        Shop::DB()->query("
            CREATE TABLE IF NOT EXISTS tmigrationlog 
            (
                kMigrationlog int(10) NOT NULL AUTO_INCREMENT, 
                kMigration bigint(20) NOT NULL, 
                cDir enum('up','down') NOT NULL, 
                cState varchar(6) NOT NULL, 
                cLog text NOT NULL, 
                dCreated datetime NOT NULL, 
                PRIMARY KEY (kMigrationlog)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1", 3
        );
    }
}
