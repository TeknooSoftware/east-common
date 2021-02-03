<?php

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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Step;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Contracts\Form\SearchFormInterface;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Recipe\Step\SearchFormHandling;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\SearchFormHandling
 */
class SearchFormHandlingTest extends TestCase
{
    public function buildStep(): SearchFormHandling
    {
        return new SearchFormHandling();
    }

    public function testInvokeBadRequest()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ManagerInterface::class),
            $this->createMock(SearchFormInterface::class)
        );
    }

    public function testInvokeBadManager()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            new \stdClass(),
            $this->createMock(SearchFormInterface::class)
        );
    }

    public function testInvokeBadForm()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ManagerInterface::class),
            new \stdClass()
        );
    }

    public function testInvoke()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $manager = $this->createMock(ManagerInterface::class);
        $form = $this->createMock(SearchFormInterface::class);

        $form->expects(self::once())
            ->method('handle')
            ->willReturnSelf();

        self::assertInstanceOf(
            SearchFormHandling::class,
            $this->buildStep()(
                $request,
                $manager,
                $form
            )
        );
    }

    public function testInvokeWithoutForm()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $manager = $this->createMock(ManagerInterface::class);

        self::assertInstanceOf(
            SearchFormHandling::class,
            $this->buildStep()(
                $request,
                $manager
            )
        );
    }
}
