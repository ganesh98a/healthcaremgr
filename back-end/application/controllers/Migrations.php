<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migrations extends CI_Controller
{
    /**
     * PHP date() format used for timestamp generation
     *
     * @var string
     */
    private static $TIMESTAMP_FORMAT = 'YmdHis';

    /**
     * Path to config file for migrations
     *
     * @var string
     */
    private static $CONFIG_PATH = APPPATH . 'config/migration.php';

    /**
     * Template to use for new migrations, replaces %name% and %ucname%
     * with the $name and ucfirst($name) specified.
     *
     * @var string
     */
    private static $MIGRATION_TEMPLATE = "<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_%ucname% extends CI_Migration {
    public function up()
    {
        \$this->dbforge->add_field('`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');
        \$this->dbforge->add_field('`field` VARCHAR(32) NOT NULL');
        \$this->dbforge->create_table('%name%');
    }

    public function down()
    {
        \$this->dbforge->drop_table('%name%');
    }
}
";


    public function __construct()
    {
        parent::__construct();

        $this->config->load('migration');
        $this->load->library('migration');
    }

    /**
     * Sets the migration version to the timestamp specified in both
     * loaded config and the config file.
     *
     * @param int  Timestamp to set version to
     * @return bool
     */
    private function set_migration_version($timestamp)
    {
        // set realtime config for execution's sake
        $this->config->set_item('migration_version', $timestamp);

        // read in existing config
        $contents = '';
        $fh = fopen(self::$CONFIG_PATH, 'r');
        if ($fh === false) {
            return false;
        }

        $found = false;
        while (!feof($fh)) {
            $line = fgets($fh);

            if (!$found) {
                // replace the migration version while reading in
                if (strpos($line, "\$config['migration_version'] = ") === 0) {
                    $line = "\$config['migration_version'] = {$timestamp};\n";
                    $found = true;
                }
            }

            $contents .= $line;
        }
        fclose($fh);

        // write out modified config
        $fh = fopen(self::$CONFIG_PATH, 'w');
        if ($fh === false) {
            return false;
        }

        return fwrite($fh, $contents) !== false && fclose($fh);
    }

    /**
     * Creates a new migration with the name specified and updates the latest version.
     *
     * @param string  Name of new migration
     * @return bool
     */
    public function new($name)
    {
        $timestamp = date(self::$TIMESTAMP_FORMAT);
        $filename = "{$timestamp}_{$name}.php";

        $success = $this->set_migration_version($timestamp) &&
            file_put_contents(
                $this->config->item('migration_path') . $filename,
                strtr(self::$MIGRATION_TEMPLATE, [
                    '%name%' => $name,
                    '%ucname%' => ucfirst($name),
                ])
            );

        echo $success ? 'Created' : 'Failed to create';
        echo ' migration ' . $filename . PHP_EOL;
        return $success;
    }

    /**
     * Fetches the current and latest migration versions, presents a list of
     * pending migrations to run, then runs them.
     *
     * @return void
     */
    public function run()
    {
        // get current loaded migration version frmo database
        $query = $this->db->query('SELECT `version` FROM ' . $this->config->item('migration_table'));
        $current_version = (int) $query->row()->version;

        // get latest migration version from config
        $latest_version = (int) $this->config->item('migration_version');

        echo 'Current version : ' . $current_version . PHP_EOL;
        echo 'Latest version  : ' . $latest_version . PHP_EOL;
        echo PHP_EOL;

        // convert migrations to basename and filter migrations by those within (current_version, latest_version]
        $migrations = array_filter(
            array_map('basename', $this->migration->find_migrations()),
            function ($version) use ($current_version, $latest_version) {
                return ($version > $current_version && $version <= $latest_version);
            },
            ARRAY_FILTER_USE_KEY
        );

        if (empty($migrations)) {
            echo 'No migrations to run!' . PHP_EOL;
            return;
        }

        // sort migrations into execution order before printing them
        asort($migrations);
        $amt = count($migrations);
        echo 'Running ' . $amt . ' migration' . ($amt != 1 ? 's' : '') . ' :' . PHP_EOL;
        echo '- ' . implode(PHP_EOL . '- ', $migrations) . PHP_EOL;
        echo PHP_EOL;

        echo $this->migration->current() ? 'Successfully ran migrations.' : 'Failed to run migrations.' . PHP_EOL . $this->migration->error_string();
        echo PHP_EOL;
    }

    /**
     * Rolls back/forward the current migration version to the timestamp specified.
     *
     * @param int  Timestamp to roll version to
     * @return bool
     */
    public function version($timestamp = 0)
    {
        return $this->migration->version($timestamp) !== false;
    }

    /**
     * Rolls back all migrations.
     *
     * @return bool
     */
    public function reset()
    {
        return $this->version();
    }

    /**
     * Finds the latest migration version and sets it in the config.
     * Useful for deleting migrations during testing and needing the latest version reset.
     * Should not be used in production, as migrations should never be deleted.
     *
     * @return void
     */
    public function fix_version()
    {
        $versions = array_keys($this->migration->find_migrations());
        if (empty($versions)) {
            echo 'No migrations found!' . PHP_EOL;
            $latest_version = 0;
        } else {
            sort($versions);
            $latest_version = array_pop($versions);
        }

        echo 'Setting latest version to ' . $latest_version . PHP_EOL;
        return $this->set_migration_version($latest_version);
    }
}
