<?php
namespace RMT\ResourceCollector;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

abstract class SlimBaseTest extends Test
{
    /**
     * Process the application given a request method and URI
     *
     * @param string            $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string            $requestUri    the request URI
     * @param array             $headers       Array of HTTP headers
     * @param array|object|null $requestData   the request data
     * @param string|null       $requestBody   request body
     *
     * @return Response
     *
     * @throws \Throwable
     */
    public function runApp($requestMethod, $requestUri, $headers = [], $requestData = null, $requestBody = null)
    {
        $envParams = [
            'REQUEST_METHOD' => $requestMethod,
            'REQUEST_URI'    => $requestUri
        ];

        foreach ($headers as $name => $value) {
            $envParams['HTTP_' . strtoupper($name)] = $value;
        }

        // Create a mock environment for testing with
        $environment = Environment::mock($envParams);

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        if (!is_null($requestBody)) {
            $bodyStream = $request->getBody();
            $bodyStream->write($requestBody);
            $bodyStream->rewind();
            $request = $request->withBody($bodyStream);
        }

        // Set up a response object
        $response = new Response();

        // Instantiate the application
        $app = new TestApp();
        $app->configure();

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }
}
