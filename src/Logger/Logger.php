<?php

namespace App\Logger;

use Exception;

class Logger
{
    private static Logger $instance;

    private function __construct()
    {
    }

    public static function getInstance(): Logger
    {
        if (!isset(self::$instance)) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    public function log(LogLevel $level, string $message): bool
    {
        $toWrite = "[$level->value] ";
        $toWrite .= '[' . date('d-m-Y H:i:s eP') . '] ';
        $toWrite .= debug_backtrace()[1]['class'] . '->' . debug_backtrace()[1]['function'] . '() ';
        $toWrite .= $message . "\r\n";

        return $this->writeLog($toWrite);
    }

    private function writeLog(string $logMessage): bool
    {
        $currentDate = date('dmY');
        if (!file_exists(__DIR__ . '/../../log')) {
            @mkdir(__DIR__ . '/../../log');
            $logMessage = "New log directory created\r\n" . $logMessage;
        }

        $logfile = fopen(__DIR__ . "/../../log/$currentDate.log", 'a+');
        if (fread($logfile, 2) == '') {
            $logMessage = "New log file created\r\n" . $logMessage;
        }
        if ($logfile) {
            fwrite($logfile, $logMessage);
            fclose($logfile);
        } else {
            return false;
        }

        return true;
    }

    private function __clone()
    {
    }

    /**
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception('Calling this method is not allowed');
    }
}
