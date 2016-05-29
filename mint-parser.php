#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use src\core\MintCommand;

$application = new Application();
$application->add(new MintCommand());
$application->run();