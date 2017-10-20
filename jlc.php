<?php namespace JobLion;

/**
 * [J]ob [L]ion [C]ommandline interface, to migrate the database
 */

require_once __DIR__.'/src/autoload.php';

use JobLion\Database\Config;
use JobLion\Database\Backend;
use JobLion\Database\Backend\Migration\Migrator;

$task = !empty($argv[1]) ? $argv[1] : "help";

// show help
if ($task == "help") {
    echo "available commands:\n
    upgrade: Migrate the database according to config.php
    help: Display this help text";
}
// apply all migrations to database
elseif ($task == "upgrade") {
    $config = new Config();
    $config->load();

    $db = new Backend\Mysql(
      $config->get("dbHost"),
      $config->get("dbUser"),
      $config->get("dbPassword"),
      $config->get("dbDatabase")
     );

    $currentVersion = $config->get("dbVersion");
    $targetVersion = $config->get("dbTargetVersion");

    $migrator = new Migrator($currentVersion, $targetVersion, $db);
    $status = $migrator->migrateFolder();

    $newCurrentVersion = $migrator->getCurrentVersion();
    if ($currentVersion != $newCurrentVersion) {
        $config->set("dbVersion", $newCurrentVersion);
    }

    if ($status) {
        echo "Database has been migrated from $currentVersion to $newCurrentVersion";
    } else {
        echo "Something went wrong";
    }
}
// show command not found text
else {
    echo "Unkown command. Use 'php jlc.php help' for a list of commands";
}
