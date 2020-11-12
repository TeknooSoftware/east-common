<?php

/*
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

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
    private ContentLoader $contentLoader;

    private string $error404Template;

    public function __construct(ContentLoader $contentLoader, string $error404Template)
    {
        $this->contentLoader = $contentLoader;
        $this->error404Template = $error404Template;
    }

    /**
     * @return array<int, string>
     */
    private function parseUrl(ServerRequestInterface $request): array
    {
        return \explode('/', \trim((string) $request->getUri()->getPath(), '/'));
    }

    public function __invoke(
        ClientInterface $client,
        ServerRequestInterface $request
    ): self {
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
                    if (null === $type) {
                        $client->errorInRequest(new \RuntimeException('Content type is not available'));

                        return;
                    }

                    $viewParameters = $request->getAttribute(ViewParameterInterface::REQUEST_PARAMETER_KEY, []);
                    $viewParameters = \array_merge($viewParameters, ['content' => $content]);

                    $headers = [];
                    $updatedAt = $content->updatedAt();
                    if (null !== $updatedAt) {
                        $headers['Last-Modified'] = $updatedAt->format('D, d M Y H:i:s \G\M\T');
                    }

                    $this->render(
                        $client,
                        $type->getTemplate(),
                        $viewParameters,
                        200,
                        $headers
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
