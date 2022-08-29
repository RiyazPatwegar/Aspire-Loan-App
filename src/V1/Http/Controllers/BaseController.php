<?php

namespace AspireRESTAPI\V1\Http\Controllers;

use config;
use Illuminate\Support\Facades\Storage;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Monolog\Formatter\LineFormatter;
use App\Providers\CustomLoggerProvider;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;
use AspireRESTAPI\V1\Http\Controllers\Utility\Utils;
use AspireRESTAPI\V1\Http\Controllers\Utility\Validation;
use AspireRESTAPI\V1\Http\Controllers\Utility\SecureUtils;
use Throwable;

/* TO set common log file format */
abstract class BaseController
{
    /** @var Logger $logger */
    protected $logger;

    protected $version = 'v1';

    protected function __construct($logName = 'aspire-loan-app')
    {
        /**
         * Initialize logger
         */
        $this->logger = new Logger('aspire-loan-app');

        $lineFormatter = new LineFormatter(
            env('LOG_FORMAT_CUSTOM'). PHP_EOL,
            env('LOG_DATE_FORMAT'),
            false,
            true
        );

        $logRotater = new RotatingFileHandler(
            \storage_path('logs/'. $this->version . '/' . gethostname().'-'.$logName . '.log'),
            0,
            Logger::INFO,
            true,
            0777
        );

        $clientIp = 'REMOTE_ADDR';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $clientIp = 'HTTP_CLIENT_IP';
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientIp = 'HTTP_X_FORWARDED_FOR';
        }

        $webProcessor = new WebProcessor(null, [
            'ip' =>  $clientIp,
            'protocol' => 'SERVER_PROTOCOL',
            'url' => 'REQUEST_URI',
            'http_method' => 'REQUEST_METHOD',
            'server' => 'SERVER_NAME',
            'referrer' => 'HTTP_REFERER',
        ]);

        $introspection = new IntrospectionProcessor;

        $this->logger->pushProcessor($webProcessor);
        $this->logger->pushProcessor($introspection);

        $this->logger->pushHandler($logRotater);
    }
}
