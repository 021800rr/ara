<?php
# https://github.com/symfony/symfony/issues/53812#issuecomment-1962311843

declare(strict_types=1);

require_once dirname(__DIR__).'/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\ErrorHandler;

ErrorHandler::register(null, false);

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
