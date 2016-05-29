<?php
/**
 * Created by PhpStorm.
 * User: rsilveira
 * Date: 09/05/16
 * Time: 10:22
 */
namespace src\core;

use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use HipChat\HipChat;
use src\core\Config;


class Log
{
    protected $logger;
    protected $debugger;
    protected $hc;
    protected $hipConfig;

    /**
     * Log constructor.
     */
    public function __construct(Config $config)
    {
        $now = new \DateTime();
    }

    /**
     * Method to write logs.
     * @param $message
     * @return bool
     */
    public function addlog($message)
    {
        $this->logger->addInfo($message);
    }

}
