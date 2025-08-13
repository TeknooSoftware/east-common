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

namespace Teknoo\East\Common\Recipe\Step\Traits;

use Psr\Http\Message\StreamFactoryInterface;
use SensitiveParameter;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Throwable;
use tidy;

use function class_exists;
use function function_exists;
use function tidy_get_output;
use function trim;

/**
 * Trait used into recipe's steps to render a page thanks to template engine and pass the result to the response in a
 * StreamInterface instance.
 * Callback streams are supported to render template only on stream string conversion.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait TemplateTrait
{
    use ResponseTrait;

    private EngineInterface $templating;

    private StreamFactoryInterface $streamFactory;

    /**
     * @var array<string, mixed>
     */
    private array $tidyConfig = [
        'drop-empty-elements' => false,
        'indent' => true,
        'output-xhtml' => false,
        'wrap' => 200,
    ];

    /**
     * @param array<string, mixed> $config
     */
    public function setTidyConfig(array $config): self
    {
        $this->tidyConfig = $config;

        return $this;
    }

    private function cleanOutput(string &$output): string
    {
        if (class_exists(tidy::class) && function_exists('tidy_get_output')) {
            $tidy = new tidy();
            $tidy->parseString(
                $output,
                $this->tidyConfig,
                'utf8',
            );
            $tidy->cleanRepair();

            $output = tidy_get_output($tidy);
        }

        return $output;
    }

    /**
     * Renders a view.
     *
     * @param string          $view       The view name
     * @param array<string, mixed> $parameters An array of parameters to pass to the view
     * @param int             $status The status code to use for the Response
     * @param array<string, array<string>|string> $headers An array of values to inject into HTTP header response
     */
    private function render(
        ClientInterface $client,
        string $view,
        array $parameters = [],
        int $status = 200,
        array $headers = [],
        ?string $api = null,
        bool $cleanHtml = false,
    ): void {
        $response = $this->responseFactory->createResponse($status);

        $headers['content-type'] = match ($api) {
            'json' => 'application/json; charset=utf-8',
            default => 'text/html; charset=utf-8',
        };

        if (!empty($api)) {
            //Prevent issue with API mode
            $cleanHtml = false;
        }

        $response = $this->addHeadersIntoResponse($response, $headers);
        $stream = $this->streamFactory->createStream();

        if ($stream instanceof CallbackStreamInterface) {
            $stream->bind(function () use ($client, &$view, &$parameters, $cleanHtml, $api): string {
                /** @var Promise<ResultInterface, string, mixed> $promise */
                $promise = new Promise(
                    static fn (ResultInterface $result): string => (string) $result,
                    static fn (
                        #[SensitiveParameter] Throwable $error,
                    ): ClientInterface => $client->errorInRequest($error, false),
                );

                $this->templating->render(
                    $promise,
                    $view,
                    $parameters,
                );

                $resultStr = (string) $promise->fetchResult();

                if ($cleanHtml) {
                    $resultStr = $this->cleanOutput($resultStr);
                }

                return match ($api) {
                    'json' => trim($resultStr),
                    default => $resultStr,
                };
            });

            $response = $response->withBody($stream);

            $client->acceptResponse($response);
        } else {
            $cleanOuput = null;
            if ($cleanHtml) {
                $cleanOuput = $this->cleanOutput(...);
            }

            $this->templating->render(
                new Promise(
                    static function (
                        ResultInterface $result,
                    ) use (
                        $stream,
                        $client,
                        $response,
                        $cleanOuput,
                        $api,
                    ): void {
                        $resultStr = (string) $result;
                        if (null !== $cleanOuput) {
                            $resultStr = $cleanOuput($resultStr);
                        }

                        $resultStr = match ($api) {
                            'json' => trim($resultStr),
                            default => $resultStr,
                        };

                        $stream->write($resultStr);
                        $response = $response->withBody($stream);

                        $client->acceptResponse($response);
                    },
                    static fn (
                        #[SensitiveParameter] Throwable $error,
                    ): ClientInterface => $client->errorInRequest($error, false),
                ),
                $view,
                $parameters,
            );
        }
    }
}
