<?php

namespace App\Dispatcher;

use App\Annotation\ControllerMapping;
use App\Annotation\RequestBodyVariable;
use App\Annotation\RequestMapping;
use App\Annotation\PathVariable;
use App\Controller\Controller;
use App\Logger\Logger;
use App\Logger\LogLevel;
use Doctrine\Common\Annotations\AnnotationReader;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Dispatcher
{
    private array $controllers = [];
    private AnnotationReader $annotationReader;
    private Logger $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $filesInFolder = scandir(ROOT_DIR . 'Controller');
        foreach ($filesInFolder as $fileName) {
            if (preg_match("/^[A-Z][a-z]+Controller\.php$/", $fileName)) {
                include ROOT_DIR . 'Controller\\' . $fileName;
                $fileName = preg_replace("/\.php/", '', $fileName);
                $fileName = "App\\Controller\\$fileName";
                $this->controllers[] = new $fileName();
            }
        }
        $this->logger->log(LogLevel::INFO, "Initialized Dispatcher, found " . sizeof($this->controllers) . ' controller(s)');
        $this->annotationReader = new AnnotationReader();
    }

    public function dispatchRequest()
    {
    }

    public function dispatchApiRequest(string $request): string
    {
        $requestData = $this->parseHttpRequest($request);
        $this->logger->log(LogLevel::INFO, "Dispatching request to " . $requestData['path']);

        try {
            $controller = $this->getRequestController($requestData['path']);
            if (!$controller) {
                $this->logger->log(LogLevel::ERROR, 'No controller for request ' . $requestData['path'] . ' found');

                return $this->buildErrorResponse(
                    new Exception('This route does not exist', 404)
                );
            }
            $method = $this->getRequestMethod($controller, $requestData['method'], $requestData['path']);
            if (!$method) {
                $this->logger->log(LogLevel::ERROR, 'No method for request ' . $requestData['path'] . ' found');

                return $this->buildErrorResponse(
                    new Exception('This route does not exist', 404)
                );
            }
            $argument = $this->getArgument($controller, $method, $requestData);

            return $this->buildHttpResponse($this->callControllerMethod($controller, $method, $argument));
        } catch (Exception $e) {
            return $this->buildErrorResponse($e);
        }
    }

    private function parseHttpRequest(string $request): array
    {
        $result = [];

        $splitRequest = preg_split('/\r\n/', $request);

        $head = $splitRequest[0];
        $chunks = preg_split('/\s/', $head);
        $result['method'] = $chunks[0];
        $result['path'] = $chunks[1];

        $result['body'] = json_encode(array_pop($splitRequest));

        if (json_decode($result['body'])) {
            $result['body'] = json_decode($result['body'], true);
        } else {
            $result['body'] = null;
        }

        if ($result['method'] == 'OPTIONS') {
            foreach ($splitRequest as $line) {
                if (preg_split('/:\s/', $line)[0] == 'Access-Control-Request-Method') {
                    $result['method'] = preg_split('/:\s/', $line)[1];
                }
            }
        }

        return $result;
    }

    /**
     * @throws ReflectionException
     */
    private function getRequestController(string $request): Controller|bool
    {
        $relevantRequestPart = preg_split('/\//', $request)[1];
        foreach ($this->controllers as $controller) {
            $refClass = new ReflectionClass($controller::class);
            $classMapping = $this->annotationReader
                ->getClassAnnotation($refClass, ControllerMapping::class);
            if ($classMapping->classMapping === ('/' . $relevantRequestPart)) {
                return $controller;
            }
        }

        return false;
    }

    private function buildErrorResponse(Exception $e): string
    {
        $jsonException = json_encode($e);

        return 'HTTP/1.1 ' . $e->getCode() . "\r\n" .
            'Date: ' . date('D, d M Y H:i:s e') . "\r\n" .
            "ContentType: application/json\r\n" .
            'ContentLength: ' . strlen($jsonException) . "\r\n" .
            "Access-Control-Allow-Origin: *\r\n" .
            "Connection: Closed\r\n\r\n" .
            $jsonException;
    }

    private function getRequestMethod(Controller $controller, string $requestMethod, string $requestPath): string|bool
    {
        $methods = get_class_methods($controller);
        $class = $controller::class;
        foreach ($methods as $method) {
            $refMethod = new ReflectionMethod("$class::$method");
            $mapping = $this->annotationReader
                ->getMethodAnnotation($refMethod, RequestMapping::class);
            if (!$mapping) {
                continue;
            }
            $pathVariable = $this->annotationReader
                ->getMethodAnnotation($refMethod, PathVariable::class);
            $variableName = '';
            if ($pathVariable) {
                $variableName = $pathVariable->variableName;
            }
            if ($requestMethod === $mapping->method
                && $this->pathMatches($requestPath, $mapping->path, $variableName)) {
                return $method;
            }
        }

        return false;
    }

    private function pathMatches(string $requestPath, string $methodPath, string $variableName): bool
    {
        if ($variableName) {
            $variableValue = $this->getPathVariableValue($requestPath, $methodPath, $variableName);
            $methodPathReplaced = preg_replace('/\{' . $variableName . '}/', $variableValue, $methodPath);

            return $methodPathReplaced === $requestPath;
        } else {
            return $methodPath === $requestPath;
        }
    }

    private function getPathVariableValue(string $requestPath, string $methodPath, string $variableName): string
    {
        $requestPathChunked = preg_split('/\//', $requestPath);
        $methodPathChunked = preg_split('/\//', $methodPath);
        $variableIndex = array_search('{' . $variableName . '}', $methodPathChunked);

        return $requestPathChunked[$variableIndex];
    }

    private function getArgument(Controller $controller, string $method, array $parsedRequest): string
    {
        $class = $controller::class;
        $refMethod = new ReflectionMethod("$class::$method");
        $pathVariable = $this->annotationReader
            ->getMethodAnnotation($refMethod, PathVariable::class);
        $requestBodyVariable = $this->annotationReader
            ->getMethodAnnotation($refMethod, RequestBodyVariable::class);
        if ($pathVariable) {
            $mapping = $this->annotationReader->getMethodAnnotation($refMethod, RequestMapping::class);

            return $this->getPathVariableValue($parsedRequest['path'], $mapping->path, $pathVariable->variableName);
        } elseif ($requestBodyVariable) {
            return $parsedRequest['body'];
        } else {
            return '';
        }
    }

    private function buildHttpResponse(string $responseBody = ''): string
    {
        return 'HTTP/1.1 ' . ($responseBody !== '' ? 200 : 204) . "\r\n" .
            'Date: ' . date('D, d M Y H:i:s e') . "\r\n" .
            ($responseBody === '' ? '' : ('Content-Length: ' . strlen($responseBody) . "\r\n")) .
            ($responseBody === '' ? '' : ("Content-Type: application/json\r\n")) .
            "Access-Control-Allow-Origin: *\r\n" .
            "Connection: Closed\r\n" .
            ($responseBody === '' ? '' : ("\r\n$responseBody"));
    }

    private function callControllerMethod(Controller $controller, string $method, string $argument): string
    {
        $class = $controller::class;
        $refMethod = new ReflectionMethod("$class::$method");
        if ($argument) {
            if ($refMethod->getReturnType() != null && $refMethod->getReturnType() != 'void') {
                return $controller->$method($argument);
            } else {
                $controller->$method($argument);

                return '';
            }
        } else {
            if ($refMethod->getReturnType() != null && $refMethod->getReturnType() != 'void') {
                return $controller->$method();
            } else {
                $controller->$method();

                return '';
            }
        }
    }
}
