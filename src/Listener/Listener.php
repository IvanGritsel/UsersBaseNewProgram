<?php

namespace App\Listener;

use App\Dispatcher\Dispatcher;
use App\Logger\Logger;
use App\Logger\LogLevel;
use JetBrains\PhpStorm\NoReturn;

class Listener
{
    private string $HOST;
    private int $PORT;
    private Logger $logger;

    private Dispatcher $dispatcher;

    public function __construct()
    {
        $fileContents = json_decode(file_get_contents(__DIR__ . '/../../resource/config/web_config.json'), true);
        $this->HOST = $fileContents['host'];
        $this->PORT = $fileContents['port'];

        $this->logger = Logger::getInstance();

        $this->dispatcher = new Dispatcher();
    }

    public function startListening(): void
    {
        set_time_limit(0);

        $sock = socket_create(AF_INET, SOCK_STREAM, 0) or die('Unable to create socket');

        $result = socket_bind($sock, $this->HOST, $this->PORT) or die('Unable to bind socket');
        $this->logger->log(LogLevel::INFO, "Listening to $this->HOST:$this->PORT");
        while (true) {
            $result = socket_listen($sock, 3) or die('Im dead');
            $spawn = socket_accept($sock) or die('Im dead');
            $input = socket_read($spawn, 2097152) or die('Im dead');

            $this->logger->log(LogLevel::INFO, "Received following:\r\n$input");

            $response = $this->dispatcher->dispatchRequest($input);

            $this->logger->log(LogLevel::INFO, "Sending back this:\r\n$response");

            socket_write($spawn, $response, strlen($response));
        }
    }
}
