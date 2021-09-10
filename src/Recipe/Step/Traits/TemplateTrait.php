<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Recipe\Step\Traits;

use Psr\Http\Message\StreamFactoryInterface;
use Teknoo\East\Foundation\Client\ClientInterface;
use Teknoo\East\Foundation\Http\Message\CallbackStreamInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\East\Foundation\Template\EngineInterface;
use Teknoo\East\Foundation\Template\ResultInterface;
use Throwable;

/**
 * Trait used into recipe's steps to render a page thanks to template engine and pass the result to the response in a
 * StreamInterface instance.
 * Callback streams are supported to render template only on stream string conversion.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait TemplateTrait
{
    use ResponseTrait;

    private EngineInterface $templating;

    private StreamFactoryInterface $streamFactory;

    /**
     * Renders a view.
     *
     * @param string          $view       The view name
     * @param array<string, mixed> $parameters An array of parameters to pass to the view
     * @param int             $status The status code to use for the Response
     * @param array<string, mixed> $headers An array of values to inject into HTTP header response
     */
    private function render(
        ClientInterface $client,
        string $view,
        array $parameters = array(),
        int $status = 200,
        array $headers = []
    ): void {
        $response = $this->responseFactory->createResponse($status);
        $headers['content-type'] = 'text/html; charset=utf-8';

        $response = $this->addHeadersIntoResponse($response, $headers);
        $stream = $this->streamFactory->createStream();

        $this->templating->render(
            new Promise(
                static function (ResultInterface $result) use ($stream, $client, $response) {
                    if ($stream instanceof CallbackStreamInterface) {
                        $stream->bind(static function () use ($result) {
                            return (string) $result;
                        });
                    } else {
                        $stream->write((string) $result);
                    }

                    $response = $response->withBody($stream);

                    $client->acceptResponse($response);
                },
                static function (Throwable $error) use ($client) {
                    $client->errorInRequest($error, false);
                }
            ),
            $view,
            $parameters
        );
    }
}
