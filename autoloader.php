<?php

/**
 * PHP class Autoloader for users who are not using composer.
 *
 * If using composer please use the composer autoload.php
 *
 * PLEASE NOTE: If using this autoloader please ensure that GuzzleHttp is installed and included
 *
 * @author George Webb <george.webb1@pb.com>
 */

require_once __DIR__ . '/src/ApiClient.php';
require_once __DIR__ . '/src/Purl.php';
require_once __DIR__ . '/src/Exception/RequestFailureException.php';

?>

