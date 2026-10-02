<?php

namespace ClassKit\Tests\Api;

use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use ClassKit\Api\Enum\ResponseType;
use ClassKit\Api\Response\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client as HttpClient;
use ClassKit\Api\ConnectionController;
use Psr\Http\Message\RequestInterface;
use ClassKit\Api\Response\ErrorResponse;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\Attributes\DataProvider;

class TestConnection extends ConnectionController
{
    public function useClient(HttpClient $client): void
    {
        $this->client = $client;
    }
}

class SlowTestConnection extends TestConnection
{
    protected int $timeout = 60;
    protected int $connectTimeout = 15;
}

class ConnectionControllerTest extends TestCase
{
    private array $history = [];

    private function connection(string $format, array $queue, string $class = TestConnection::class): TestConnection
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler($queue));
        $stack->push(Middleware::history($this->history));

        $connection = new $class('https://api.example.test', $format);
        $connection->useClient(new HttpClient(['handler' => $stack]));

        return $connection;
    }

    private static function body(string $format): string
    {
        return $format === 'xml'
            ? '<properties><property id="1"/></properties>'
            : '{"properties":[{"id":1}]}';
    }

    private function assertErrorBody(string $format, Response $response, string $expectedMessage): void
    {
        if ($format === 'xml') {
            $xml = simplexml_load_string($response->getBody());
            $this->assertNotFalse($xml, 'XML error body is not well-formed: ' . $response->getBody());
            $this->assertSame('error', $xml->getName());
            $this->assertStringContainsString($expectedMessage, (string) $xml->message);
        } else {
            $decoded = json_decode($response->getBody(), true);
            $this->assertIsArray($decoded);
            $this->assertStringContainsString($expectedMessage, $decoded['message']);
        }
    }

    public static function formats(): array
    {
        return [
            'xml' => ['xml'],
            'json' => ['json'],
        ];
    }

    public static function errorStatuses(): array
    {
        $cases = [];
        foreach (['xml', 'json'] as $format) {
            foreach ([401, 404, 500] as $status) {
                $cases["{$format} {$status}"] = [$format, $status];
            }
        }

        return $cases;
    }

    #[DataProvider('formats')]
    public function testSuccessfulResponseKeepsStatusAndBody(string $format): void
    {
        $connection = $this->connection($format, [new Psr7Response(200, [], self::body($format))]);

        $response = $connection->makeRequest('GET', '/properties');

        $this->assertSame(Response::class, $response::class);
        $this->assertSame(200, $response->getStatusCode());
        if ($format === 'xml') {
            $this->assertSame(self::body($format), $response->getBody());
        } else {
            $this->assertSame(['properties' => [['id' => 1]]], json_decode($response->getBody(), true));
        }
        $this->assertSame('https://api.example.test/properties', (string) $this->history[0]['request']->getUri());
    }

    #[DataProvider('errorStatuses')]
    public function testErrorStatusReturnsErrorResponse(string $format, int $status): void
    {
        $body = $format === 'xml' ? '<error>Nope</error>' : '{"error":"Nope"}';
        $connection = $this->connection($format, [new Psr7Response($status, [], $body)]);

        $response = $connection->makeRequest('GET', '/properties');

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame($status, $response->getStatusCode());
        if ($format === 'xml') {
            $this->assertSame($body, $response->getBody());
        } else {
            $this->assertSame(['error' => 'Nope'], json_decode($response->getBody(), true));
        }
    }

    #[DataProvider('formats')]
    public function testConnectionFailureReturns500(string $format): void
    {
        $connection = $this->connection($format, [
            new ConnectException('cURL error 7: Failed to connect', new Psr7Request('GET', 'https://api.example.test/properties'), null, ['errno' => 7]),
        ]);

        $response = $connection->makeRequest('GET', '/properties');

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertErrorBody($format, $response, 'Failed to connect');
    }

    #[DataProvider('formats')]
    public function testTimeoutReturns504(string $format): void
    {
        $connection = $this->connection($format, [
            new ConnectException('cURL error 28: Operation timed out', new Psr7Request('GET', 'https://api.example.test/properties'), null, ['errno' => 28]),
        ]);

        $response = $connection->makeRequest('GET', '/properties');

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(Response::HTTP_GATEWAY_TIMEOUT, $response->getStatusCode());
        $this->assertErrorBody($format, $response, 'timed out');
    }

    public function testXmlErrorMessageIsEscaped(): void
    {
        $connection = $this->connection('xml', [
            new ConnectException('Failed <&> "here"', new Psr7Request('GET', 'https://api.example.test/properties')),
        ]);

        $response = $connection->makeRequest('GET', '/properties');

        $this->assertErrorBody('xml', $response, 'Failed <&> "here"');
    }

    #[DataProvider('formats')]
    public function testInvalidMethodReturns400(string $format): void
    {
        $connection = $this->connection($format, []);

        $response = $connection->makeRequest('FETCH', '/properties');

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertErrorBody($format, $response, 'Invalid request method: FETCH');
        $this->assertCount(0, $this->history);
    }

    #[DataProvider('formats')]
    public function testFromTypeReturnsCalledClass(string $format): void
    {
        $type = ResponseType::from($format);
        $data = $format === 'xml' ? '<ok/>' : ['ok' => true];

        $this->assertSame(Response::class, Response::fromType($type, $data)::class);
        $this->assertSame(ErrorResponse::class, ErrorResponse::fromType($type, $data, 500)::class);
    }

    public static function urlCases(): array
    {
        $cases = [];
        foreach (['xml', 'json'] as $format) {
            $cases["{$format} 200"] = [$format, 'GET', new Psr7Response(200, [], '')];
            $cases["{$format} 404"] = [$format, 'GET', new Psr7Response(404, [], '')];
            $cases["{$format} connect failure"] = [$format, 'GET', new ConnectException('Failed to connect', new Psr7Request('GET', 'https://api.example.test/properties?branch=1'))];
            $cases["{$format} invalid method"] = [$format, 'FETCH', null];
        }

        return $cases;
    }

    #[DataProvider('urlCases')]
    public function testResponseCarriesRequestUrl(string $format, string $method, mixed $queued): void
    {
        $connection = $this->connection($format, $queued === null ? [] : [$queued]);

        $response = $connection->makeRequest($method, '/properties?branch=1');

        $this->assertSame('https://api.example.test/properties?branch=1', $response->getUrl());
    }

    public function testDefaultTimeoutsReachGuzzle(): void
    {
        $options = [];
        $connection = $this->connection('xml', [
            function (RequestInterface $request, array $requestOptions) use (&$options) {
                $options = $requestOptions;

                return new Psr7Response(200);
            },
        ]);

        $connection->makeRequest('GET', '/properties');

        $this->assertSame(10, $options['timeout']);
        $this->assertSame(5, $options['connect_timeout']);
    }

    public function testSubclassTimeoutsReachGuzzle(): void
    {
        $options = [];
        $connection = $this->connection('xml', [
            function (RequestInterface $request, array $requestOptions) use (&$options) {
                $options = $requestOptions;

                return new Psr7Response(200);
            },
        ], SlowTestConnection::class);

        $connection->makeRequest('GET', '/properties');

        $this->assertSame(60, $options['timeout']);
        $this->assertSame(15, $options['connect_timeout']);
    }
}
