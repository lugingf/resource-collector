<?php
declare(strict_types=1);

namespace RMS\ResourceCollector;

use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager;
use Middlewares\ErrorHandler;
use Middlewares\Utils\HttpErrorException;
use Middlewares\TrailingSlash;
use RM\HttpRequestLogMiddleware\FailedRequestLogMiddleware;
use RM\HttpRequestLogMiddleware\RequestLogMiddleware;
use RM\HttpRequestLogMiddleware\SlowRequestLogMiddleware;
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
use RMS\ResourceCollector\Middleware\SentryMiddleware;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\RouteGroupInterface;
use Tutu\OpenTracingMiddleware\OpenTracingMiddleware;
use TutuRu\HttpRequestMetadata\RequestMetadataMiddleware;
use TutuRu\MetricsMiddleware\RequestTimingMiddleware;
use TutuRu\MetricsMiddleware\StatsdExporterSaveMiddleware;

class Application extends \DI\Bridge\Slim\App
{
    public function __construct()
    {
        parent::__construct();
        $this->initDb();
    }

    protected function isDebug()
    {
        return null;
    }

    protected function configureContainer(ContainerBuilder $builder)
    {
        $debug = (bool)($this->isDebug() ?? getenv('DEBUG') ?? true);
        if (!$debug) {
            $builder->enableCompilation(__DIR__ . '/../config/cache');
        }
        $builder->addDefinitions(__DIR__ . '/../config/di.php');
    }


    public function configure()
    {
        $this->group(
            '/k8s',
            function () {
                $this->get('/healthz', KubernetesController::class . ':healthz');
                $this->get('/readyz', KubernetesController::class . ':readyz');
            }
        );

        $this->get('/openapi.json', OpenApiController::class . ':getOpenApiJson');

        // @todo в группу, под мидлвары и написать спеку
        $this->get("/resources", ResourceCollectingController::class . ':collect');
        $this->get("/rule", GetRuleController::class . ':process');
        $this->post("/rule", RuleSaveAndLinkController::class . ":process");
        $this->get("/rule_hosts", TagRuleCheckController::class . ":process");
        $this->get("/tag_suggest", TagNameSuggestController::class . ":process");
        $this->get("/tag_value_suggest", TagValueSuggestController::class . ":process");
        $this->get("/rule_suggest", TagRuleNameSuggestController::class . ":process");

        $greetingGroup = $this->group(
            '/greeting',
            function () {
                $this->get(
                    '/{name}',
                    function (Request $request, Response $response, array $args) {
                        $name = $args['name'];
                        if (strcasecmp("error", $name) === 0) {
                            throw new HttpErrorException('Error', 400);
                        }
                        return $response->withJson(['result' => "Hello, {$name}"]);
                    }
                );
            }
        );
        $this->addOpenApiMiddlewares($greetingGroup);
        $this->addCommonMiddlewares();
    }


    private function addCommonMiddlewares()
    {
        // Порядок подключения middlewares: LIFE - Last In First Executed
        $container = $this->getContainer();

        $this->add($container->get(TrailingSlash::class));

        if (getenv('DEBUG_SLOW_REQUEST_TIME_MS')) {
            $this->add($container->get(SlowRequestLogMiddleware::class));
        }
        if (getenv('DEBUG_REQUEST_DATA')) {
            $this->add($container->get(RequestLogMiddleware::class));
        }

        $this->add($container->get(SentryMiddleware::class));
        $this->add($container->get(FailedRequestLogMiddleware::class));
        $this->add($container->get(ErrorHandler::class));

        $this->add($container->get(RequestMetadataMiddleware::class));
        // Should always be in the END of this file for correct timings
        $this->add($container->get(RequestTimingMiddleware::class));
        $this->add($container->get(StatsdExporterSaveMiddleware::class));
    }


    private function addOpenApiMiddlewares(RouteGroupInterface $routeGroup)
    {
        $routeGroup->add($this->getContainer()->get(OpenApiMiddleware::class));
        $routeGroup->add($this->getContainer()->get(OpenApiEditorMiddleware::class));
        $routeGroup->add($this->getContainer()->get(OpenTracingMiddleware::class));
    }

    protected function initDb(): void
    {
        $capsule = new Manager();

        $serviceName = 'resourcecollector';
        $capsule->addConnection(
            [
                'driver'    => 'mysql',
                'host'      => getenv('MYSQL_HOST'),
                'port'      => getenv('MYSQL_PORT'),
                'database'  => $serviceName,
                'username'  => $serviceName,
                'password'  => getenv('MYSQL_PASSWORD'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ]
        );
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}
