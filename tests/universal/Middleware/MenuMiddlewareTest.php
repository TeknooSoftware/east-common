<?php

/**
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

namespace Teknoo\Tests\East\Website\Middleware;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Middleware\MenuMiddleware;
use Teknoo\East\Website\Middleware\ViewParameterInterface;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\View\ParametersBag;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\Middleware\MenuMiddleware
 */
class MenuMiddlewareTest extends TestCase
{
    /**
     * @var MenuGenerator
     */
    private $menuGenerator;

    /**
     * @return MenuGenerator|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getMenuGenerator()
    {
        if (!$this->menuGenerator instanceof MenuGenerator) {
            $this->menuGenerator = $this->createMock(MenuGenerator::class);
        }

        return $this->menuGenerator;
    }

    public function buildMiddleware(): MenuMiddleware
    {
        return new MenuMiddleware($this->getMenuGenerator());
    }

    public function testExecute()
    {
        $bag = $this->createMock(ParametersBag::class);

        self::assertInstanceOf(
            MenuMiddleware::class,
            $this->buildMiddleware()->execute($bag)
        );
    }
}
