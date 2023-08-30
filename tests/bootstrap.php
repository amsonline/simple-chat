<?php

require __DIR__ . '/../vendor/autoload.php';

// Run Phinx migrations for the testing environment
$phinxConfigFile = __DIR__ . '/../phinx.yml';

// Rolling back all migrations to empty the database
$phinxCommand = 'rollback -t 0 -c ' . $phinxConfigFile . ' -e testing -q > /dev/null';
passthru('vendor/bin/phinx ' . $phinxCommand);

// Re-creating migrations on the test database in order to have a fresh db
$phinxCommand = 'migrate -c ' . $phinxConfigFile . ' -e testing -q > /dev/null';
passthru('vendor/bin/phinx ' . $phinxCommand);
