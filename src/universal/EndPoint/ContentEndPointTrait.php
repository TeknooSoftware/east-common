<?php

declare(strict_types=1);

/**
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\Website\EndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Middleware\ViewParameterInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Query\Content\PublishedContentFromSlugQuery;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait ContentEndPointTrait
{
    /**
     * @var ContentLoader
     */
    private $contentLoader;

    /**
     * @var string
     */
    private $error404Template;

    /**
     * ContentEndPointTrait constructor.
     * @param ContentLoader $contentLoader
     * @param string $error404Template
     */
    public function __construct(ContentLoader $contentLoader, string $error404Template)
    {
        $this->contentLoader = $contentLoader;
        $this->error404Template = $error404Template;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string[]
     */
    private function parseUrl(ServerRequestInterface $request): array
    {
        return \explode('/', \trim((string) $request->getUri()->getPath(), '/'));
    }

    /**
     * @param ClientInterface $client
     * @param ServerRequestInterface $request
     * @return self
     */
    public function __invoke(
        ClientInterface $client,
        ServerRequestInterface $request
    ) {
        $urlParts = $this->parseUrl($request);
        $contentSlug = \array_pop($urlParts);

        if (empty($contentSlug)) {
            $contentSlug = 'default';
        }

        $this->contentLoader->query(
            new PublishedContentFromSlugQuery($contentSlug),
            new Promise(
                function (Content $content) use ($client, $request) {
                    $type = $content->getType();
                    $viewParameters = $request->getAttribute(ViewParameterInterface::REQUEST_PARAMETER_KEY, []);
                    $viewParameters = \array_merge($viewParameters, ['content' => $content]);

                    $this->render(
                        $client,
                        $type->getTemplate(),
                        $viewParameters
                    );
                },
                function (\Throwable $e) use ($client, $request) {
                    if ($e instanceof \DomainException) {
                        $viewParameters = $request->getAttribute(ViewParameterInterface::REQUEST_PARAMETER_KEY, []);

                        $this->render(
                            $client,
                            $this->error404Template,
                            $viewParameters,
                            404
                        );
                    } else {
                        $client->errorInRequest($e);
                    }
                }
            )
        );

        return $this;
    }
}
