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

namespace Teknoo\East\Website\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ExtractOrder
{
    /**
     * @return array<string, string>
     */
    private function extractOrder(
        ServerRequestInterface $request,
        string $defaultOrderDirection,
        string $defaultOrderColumn
    ): array {
        $order = [];
        $queryParams = $request->getQueryParams();
        $direction = $defaultOrderDirection;
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
        } elseif (!empty($defaultOrderColumn)) {
            $order[(string) $defaultOrderColumn] = $direction;
        }

        return $order;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ManagerInterface $manager,
        string $defaultOrderDirection = 'DESC',
        string $defaultOrderColumn = 'id'
    ): self {

        try {
            $listOrder = $this->extractOrder($request, $defaultOrderDirection, $defaultOrderColumn);

            $manager->updateWorkPlan([
                'order' => $listOrder,
            ]);
        } catch (\Throwable $error) {
            $manager->error($error);
        }

        return $this;
    }
}
