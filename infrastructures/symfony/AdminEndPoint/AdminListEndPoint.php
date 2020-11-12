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

namespace Teknoo\East\WebsiteBundle\AdminEndPoint;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\EndPoint\RenderingInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\FoundationBundle\EndPoint\AuthenticationTrait;
use Teknoo\East\FoundationBundle\EndPoint\TemplatingTrait;
use Teknoo\East\Website\Query\PaginationQuery;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class AdminListEndPoint implements RenderingInterface
{
    use AuthenticationTrait;
    use TemplatingTrait;
    use AdminEndPointTrait;

    private int $itemsPerPage = 15;

    private string $defaultOrderColumn;

    private string $defaultOrderDirection = 'ASC';

    public function setOrder(string $column, string $direction): self
    {
        switch ($value = \strtoupper($direction)) {
            case 'ASC':
            case 'DESC':
                $direction = $value;
                break;
            default:
                throw new \InvalidArgumentException('Invalid direction value %value');
        }

        $this->defaultOrderColumn = $column;
        $this->defaultOrderDirection = $direction;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    private function extractOrder(ServerRequestInterface $request): array
    {
        $order = [];
        $queryParams = $request->getQueryParams();
        $direction = $this->defaultOrderDirection;
        if (isset($queryParams['direction'])) {
            switch ($value = \strtoupper($queryParams['direction'])) {
                case 'ASC':
                case 'DESC':
                    $direction = $value;
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid direction value %value');
            }
        }

        if (!empty($queryParams['order'])) {
            $order[(string) $queryParams['order']] = $direction;
        } elseif (!empty($this->defaultOrderColumn)) {
            $order[(string) $this->defaultOrderColumn] = $direction;
        }

        return $order;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ClientInterface $client,
        string $page = '1',
        string $viewPath = null
    ): AdminListEndPoint {
        $page = (int) $page;

        if ($page < 1) {
            $page = 1;
        }

        if (null === $viewPath) {
            $viewPath = $this->viewPath;
        }

        try {
            $order = $this->extractOrder($request);
        } catch (\Throwable $e) {
            $client->errorInRequest($e);

            return $this;
        }

        $this->loader->query(
            new PaginationQuery([], $order, $this->itemsPerPage, ($page - 1) * $this->itemsPerPage),
            new Promise(
                function ($objects) use ($client, $page, $viewPath, $request) {
                    $pageCount = 1;
                    if ($objects instanceof \Countable) {
                        $pageCount =  \ceil($objects->count() / $this->itemsPerPage);
                    }

                    $this->render(
                        $client,
                        $viewPath,
                        [
                            'objectsCollection' => $objects,
                            'page' => $page,
                            'pageCount' => $pageCount,
                            'queryParams' => $request->getQueryParams()
                        ]
                    );
                },
                static function ($error) use ($client) {
                    $client->errorInRequest($error);
                }
            )
        );

        return $this;
    }
}
