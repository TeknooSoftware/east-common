<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Behat;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as SfRequest;
use Teknoo\East\Common\Doctrine\Object\Media;
use Teknoo\East\Common\Object\MediaMetadata;
use Teknoo\East\Common\Object\User;
use Teknoo\Tests\East\Common\Behat\Object\MyObject;

use function array_keys;
use function explode;
use function is_array;
use function json_decode;
use function parse_str;
use function sort;
use function str_replace;
use function str_starts_with;
use function strtoupper;

use const JSON_THROW_ON_ERROR;

/**
 * Steps to test JSON APIs, with the real Twig engine, the real Symfony Serializer, and the Twig extensions and the
 * templates shipped by the bundle.
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait ApiTrait
{
    /**
     * Headers sent by the API client in all its next requests of the scenario (like `Authorization`), as server
     * parameters of the Symfony request (`HTTP_*`)
     *
     * @var array<string, string>
     */
    private array $apiHeaders = [];

    /**
     * Values read in previous responses (like a generated token), reusable in next steps with `<name>`
     *
     * @var array<string, string>
     */
    private array $remembered = [];

    private function replaceRememberedValues(string $text): string
    {
        foreach ($this->remembered as $name => $value) {
            $text = str_replace("<{$name}>", $value, $text);
        }

        return $text;
    }

    private function sendApiRequest(
        string $method,
        string $url,
        array $parameters = [],
        array $server = [],
        ?string $content = null,
    ): void {
        $this->runSymfony(
            SfRequest::create(
                uri: $url,
                method: strtoupper($method),
                parameters: $parameters,
                server: $server + $this->apiHeaders,
                content: match ($content) {
                    null => null,
                    default => $this->replaceRememberedValues($content),
                },
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getJsonResponse(): array
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);

        $body = (string) $this->response->getBody();
        $decoded = json_decode($body, true);
        Assert::assertIsArray($decoded, "The response is not a JSON document : {$body}");

        return $decoded;
    }

    /**
     * @param array<int|string, mixed> $expected
     * @param array<int|string, mixed> $actual
     */
    private function assertJsonContains(array $expected, array $actual, string $path = ''): void
    {
        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $actual, "Missing key `{$path}.{$key}` in the response");

            if (is_array($value)) {
                Assert::assertIsArray($actual[$key], "`{$path}.{$key}` must be an array in the response");
                $this->assertJsonContains($value, $actual[$key], "{$path}.{$key}");

                continue;
            }

            Assert::assertSame($value, $actual[$key], "Bad value for `{$path}.{$key}` in the response");
        }
    }

    #[Given('an object :id named :name with the slug :slug')]
    public function anObjectNamedWithTheSlug(string $id, string $name, string $slug): void
    {
        $this->getObjectRepository()->setObject(['id' => $id], new MyObject($id, $name, $slug));
    }

    #[Given('a user :id named :firstName :lastName with the email :email')]
    public function aUserNamedWithTheEmail(string $id, string $firstName, string $lastName, string $email): void
    {
        $user = new User()
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setRoles(['ROLE_USER']);
        $user->setId($id);

        $this->getObjectRepository()->setObject(['id' => $id], $user);
    }

    #[Given('a media :id named :name')]
    public function aMediaNamed(string $id, string $name): void
    {
        $media = new Media()
            ->setName($name)
            ->setLength(123)
            ->setMetadata(new MediaMetadata('image/png', $name . '.png', 'Alt ' . $name, '/tmp/' . $name, 'legacy'));
        $media->setId($id);

        //Criteria used by the MediaLoader
        $this->getObjectRepository()->setObject(
            [
                'or' => [
                    ['id' => $id],
                    ['metadata.legacyId' => $id],
                ],
            ],
            $media,
        );
    }

    #[When('the API client sends a :method request to :url')]
    public function theApiClientSendsARequestTo(string $method, string $url): void
    {
        $this->sendApiRequest($method, $url);
    }

    #[When('the API client sends a :method JSON request to :url with:')]
    public function theApiClientSendsAJsonRequestToWith(string $method, string $url, PyStringNode $body): void
    {
        $this->sendApiRequest(
            method: $method,
            url: $url,
            server: [
                'CONTENT_TYPE' => 'application/json',
            ],
            content: $body->getRaw(),
        );
    }

    #[When('the API client sends a :method request to :url without JSON content type, with the body:')]
    public function theApiClientSendsARequestToWithoutJsonContentTypeWithTheBody(
        string $method,
        string $url,
        PyStringNode $body,
    ): void {
        $this->sendApiRequest(
            method: $method,
            url: $url,
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: $body->getRaw(),
        );
    }

    #[When('the API client sends a :method form request to :url with :body')]
    public function theApiClientSendsAFormRequestToWith(string $method, string $url, string $body): void
    {
        $parameters = [];
        parse_str($body, $parameters);

        $this->sendApiRequest(
            method: $method,
            url: $url,
            parameters: $parameters,
            server: [
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            ],
        );
    }

    #[When('the API client sends the header :header with the value :value in its next requests')]
    public function theApiClientSendsTheHeaderWithTheValueInItsNextRequests(string $header, string $value): void
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $header));

        $this->apiHeaders[$serverKey] = $this->replaceRememberedValues($value);
    }

    #[Then('the API response status code is :code')]
    public function theApiResponseStatusCodeIs(int $code): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertEquals($code, $this->response->getStatusCode(), (string) $this->response->getBody());
    }

    #[Then('the API response is a JSON response')]
    public function theApiResponseIsAJsonResponse(): void
    {
        Assert::assertInstanceOf(ResponseInterface::class, $this->response);
        Assert::assertTrue(
            str_starts_with($this->response->getHeaderLine('content-type'), 'application/json'),
            'The response content type is ' . $this->response->getHeaderLine('content-type'),
        );

        $this->getJsonResponse();
    }

    #[Then('the API response is:')]
    public function theApiResponseIs(PyStringNode $json): void
    {
        Assert::assertEquals(
            json_decode(json: $json->getRaw(), associative: true, flags: JSON_THROW_ON_ERROR),
            $this->getJsonResponse(),
        );
    }

    #[Then('the API response contains:')]
    public function theApiResponseContains(PyStringNode $json): void
    {
        $expected = json_decode(json: $json->getRaw(), associative: true, flags: JSON_THROW_ON_ERROR);
        Assert::assertIsArray($expected, 'The expected value is not a JSON object');

        $this->assertJsonContains($expected, $this->getJsonResponse());
    }

    private function getValueOfTheApiResponse(string $path): string
    {
        $data = $this->getJsonResponse();
        foreach (explode('.', $path) as $part) {
            Assert::assertIsArray($data);
            Assert::assertArrayHasKey($part, $data, "Missing `{$path}` in the response");
            $data = $data[$part];
        }

        Assert::assertIsString($data);

        return $data;
    }

    #[Then('the value :path of the API response is remembered as :name')]
    public function theValueOfTheApiResponseIsRememberedAs(string $path, string $name): void
    {
        $value = $this->getValueOfTheApiResponse($path);
        Assert::assertNotEmpty($value);

        $this->remembered[$name] = $value;
    }

    #[Then('the value :path of the API response matches :pattern')]
    public function theValueOfTheApiResponseMatches(string $path, string $pattern): void
    {
        Assert::assertMatchesRegularExpression($pattern, $this->getValueOfTheApiResponse($path));
    }

    #[Then('the API response does not contain the key :key in :path')]
    public function theApiResponseDoesNotContainTheKeyIn(string $key, string $path): void
    {
        $data = $this->getJsonResponse();
        foreach (explode('.', $path) as $part) {
            Assert::assertArrayHasKey($part, $data);
            $data = $data[$part];
        }

        Assert::assertIsArray($data);
        Assert::assertArrayNotHasKey($key, $data);
    }

    #[Then('the API response is the page :page of :totalPages with :count elements')]
    public function theApiResponseIsThePageOfWithElements(int $page, int $totalPages, int $count): void
    {
        $response = $this->getJsonResponse();

        Assert::assertEquals(
            [
                'totalPages' => $totalPages,
                'page' => $page,
                'count' => $count,
            ],
            $response['meta'] ?? null,
        );
        Assert::assertIsList($response['data']);
        Assert::assertCount($count, $response['data']);
    }

    #[Then('the API response contains errors on the fields :fields')]
    public function theApiResponseContainsErrorsOnTheFields(string $fields): void
    {
        $response = $this->getJsonResponse();

        Assert::assertTrue($response['meta']['errors'] ?? false);
        Assert::assertIsArray($response['data']);

        $expected = explode(',', $fields);
        $actual = array_keys($response['data']);
        sort($expected);
        sort($actual);
        Assert::assertEquals($expected, $actual);
    }

    #[Then('the API response is the error :code')]
    #[Then('the API response is the error :code with the message :message')]
    public function theApiResponseIsTheError(int $code, ?string $message = null): void
    {
        $this->theApiResponseStatusCodeIs($code);
        $this->theApiResponseIsAJsonResponse();

        $response = $this->getJsonResponse();
        Assert::assertTrue($response['meta']['error'] ?? false);
        Assert::assertEquals($code, $response['data']['code'] ?? null);

        if (null !== $message) {
            Assert::assertEquals($message, $response['data']['message'] ?? null);
        }
    }

    #[Then('the created object must be published')]
    public function theCreatedObjectMustBePublished(): void
    {
        Assert::assertNotEmpty($this->createdObjects);

        foreach ($this->createdObjects as $object) {
            Assert::assertNotNull($object->getPublishedAt());
        }
    }
}
