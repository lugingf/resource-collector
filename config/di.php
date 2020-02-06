<?php

use Cache\Adapter\Apcu\ApcuCachePool;
use Middlewares\ErrorHandler;
use Middlewares\TrailingSlash;
use Middlewares\Utils\Factory\SlimFactory;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use RM\HttpRequestLogMiddleware\FailedRequestLogMiddleware;
use RM\HttpRequestLogMiddleware\RequestLogMiddleware;
use RM\HttpRequestLogMiddleware\SlowRequestLogMiddleware;
use RM\OpenApi\Specification;
use RM\OpenApiMiddleware\OpenApiEditorMiddleware;
use RM\OpenApiMiddleware\OpenApiMiddleware;
use RMS\ResourceCollector\Controller\GetRuleController;
use RMS\ResourceCollector\Controller\KubernetesController;
use RMS\ResourceCollector\Controller\OpenApiController;
use RMS\ResourceCollector\Controller\ResourceCollectingController;
use RMS\ResourceCollector\Controller\RuleSaveAndLinkController;
use RMS\ResourceCollector\Controller\TagNameSuggestController;
use RMS\ResourceCollector\Controller\TagRuleCheckController;
use RMS\ResourceCollector\Controller\TagRuleNameSuggestController;
use RMS\ResourceCollector\Controller\TagValueSuggestController;
use RMS\ResourceCollector\Middleware\OpenApiUriFormatter;
use RMS\ResourceCollector\Middleware\JsonFormatter;
use RMS\ResourceCollector\Middleware\SentryMiddleware;
use RMS\ResourceCollector\ResourceCollector;
use Tutu\MonologExtensions\LogstashJsonFormatter;
use Tutu\MonologExtensions\RequestMetadataProcessor;
use Tutu\OpenTracingMiddleware\OpenTracingMiddleware;
use TutuRu\HttpRequestMetadata\RequestMetadataMiddleware;
use TutuRu\Metrics\StatsdExporterClientInterface;
use TutuRu\MetricsMiddleware\RequestTimingMiddleware;
use TutuRu\MetricsMiddleware\RequestUriFormatterInterface;
use TutuRu\MetricsMiddleware\StatsdExporterSaveMiddleware;
use TutuRu\RequestMetadata\RequestMetadata;

$definitions = [

    'name'             => 'ResourceCollector',
    'openapi.spec_dir' => __DIR__ . '/../api/v1/openapi',
    'openapi.path'     => 'openapi.json',
    'log.max_bytes'    => 8192,
    'log.stream'       => 'php://stdout',

    // handler from php-di/slim-bridge invoke router without $args (3rd argument)
    'foundHandler'     => DI\create(\Slim\Handlers\Strategies\RequestResponse::class),

    // PSR-15 support
    'callableResolver' => function (ContainerInterface $container) {
        return new \Bnf\Slim3Psr15\CallableResolver($container);
    },

    'requestStartTime' => function (ContainerInterface $container) {
        return (float)getenv('START_TIME') ?? null;
    },

    CacheInterface::class  => DI\create(ApcuCachePool::class),

    RequestMetadata::class => DI\autowire(),
    Specification::class   => DI\autowire()
        ->constructorParameter('specificationsDir', DI\get('openapi.spec_dir')),

    StatsdExporterClientInterface::class => function (ContainerInterface $container) {
        return new \TutuRu\Metrics\StatsdExporterClient(
            $container->get('name'),
            (string)(getenv('STATSD_EXPORTER_HOST') ?: 'localhost'),
            (int)(getenv('STATSD_EXPORTER_PORT') ?: 9125),
            (float)(getenv('STATSD_EXPORTER_TIMEOUT') ?: 0.1)
        );
    },

    LoggerInterface::class => function (ContainerInterface $container) {
        $logger = new \Monolog\Logger($container->get('name'));
        $streamHandler = new StreamHandler($container->get('log.stream'));
        $formatter = getenv('LOG_FORMAT') === 'json'
            ? new LogstashJsonFormatter(null, $container->get('log.max_bytes'))
            : new LineFormatter(null, null, true);
        $streamHandler->setFormatter($formatter);
        $logger->pushHandler($streamHandler);
        $logger->pushProcessor(new RequestMetadataProcessor($container->get(RequestMetadata::class)));
        return $logger;
    },

    Raven_Client::class => function (ContainerInterface $container) {
        $client = new Raven_Client(getenv('SENTRY_DSN'));
        $client->setRelease(getenv('SENTRY_RELEASE'));
        $client->setEnvironment(getenv('SENTRY_ENVIRONMENT'));
        $client->tags_context(['app' => $container->get('name')]);
        return $client;
    },


    \OpenTracing\Tracer::class => function (ContainerInterface $container) {
        $config = \Jaeger\Config::getInstance();
        $config->gen128bit();
        $tracer = $config->initTrace($container->get('name'), getenv('APP_OPENTRACING'));
        $tracer->setTags(['env' => getenv('OPENTRACING_ENV')]);
        return $tracer;
    },

    'slim_http_factory'             => DI\create(SlimFactory::class),
    ResponseFactoryInterface::class => DI\get('slim_http_factory'),
    StreamFactoryInterface::class  => DI\get('slim_http_factory'),

    OpenApiController::class            => DI\autowire(),
    KubernetesController::class         => DI\autowire(),

    // Middlewares
    StatsdExporterSaveMiddleware::class => DI\autowire(),
    RequestUriFormatterInterface::class => DI\autowire(OpenApiUriFormatter::class),
    RequestTimingMiddleware::class      => DI\autowire()
        ->constructorParameter('startTime', DI\get('requestStartTime'))
        ->method('setUriFormatter', DI\get(RequestUriFormatterInterface::class)),

    RequestMetadataMiddleware::class => DI\autowire(),
    OpenTracingMiddleware::class     => DI\autowire()
        ->method('setUriFormatter', DI\get(RequestUriFormatterInterface::class)),

    JsonFormatter::class => DI\autowire(),
    ErrorHandler::class => DI\autowire()
        ->method('addFormatters', DI\get(JsonFormatter::class))
        ->method('defaultFormatter', DI\get(JsonFormatter::class)),
    FailedRequestLogMiddleware::class => DI\create()
        ->constructor(DI\get(LoggerInterface::class), [404]),
    SentryMiddleware::class => DI\autowire(),

    TrailingSlash::class            => DI\autowire()
        ->constructor(false)
        ->method('redirect', true),
    OpenApiEditorMiddleware::class  => DI\autowire(),
    OpenApiMiddleware::class        => DI\autowire()
        ->constructorParameter('requestPath', DI\get('openapi.path')),
    RequestLogMiddleware::class     => DI\autowire(),
    SlowRequestLogMiddleware::class => DI\autowire()
        ->constructorParameter('maxAllowedTimeSec', floatval(getenv('DEBUG_SLOW_REQUEST_TIME_MS') ?? 10)),

    ResourceCollector::class => DI\autowire(),
    ResourceCollectingController::class => DI\autowire(),
    GetRuleController::class => DI\autowire(),
    RuleSaveAndLinkController::class => DI\autowire(),
    TagRuleCheckController::class => DI\autowire(),
    TagNameSuggestController::class => DI\autowire(),
    TagValueSuggestController::class => DI\autowire(),
    TagRuleNameSuggestController::class => DI\autowire(),
];

return $definitions;
