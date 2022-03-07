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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Recipe\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Recipe\Step\SlugPreparation;
use Teknoo\East\Website\Service\FindSlugService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Step\SlugPreparation
 */
class SlugPreparationTest extends TestCase
{
    private ?FindSlugService $findSlugService = null;

    /**
     * @return FindSlugService|MockObject
     */
    private function getFindSlugService(): FindSlugService
    {
        if (!$this->findSlugService instanceof FindSlugService) {
            $this->findSlugService = $this->createMock(FindSlugService::class);
        }

        return $this->findSlugService;
    }

    public function buildStep(): SlugPreparation
    {
        return new SlugPreparation($this->getFindSlugService());
    }

    public function testInvokeBadLoader()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            new \stdClass(),
            $this->createMock(ObjectInterface::class),
            'foo'
        );
    }

    public function testInvokeBadObject()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            new \stdClass(),
            'foo'
        );
    }

    public function testInvokeBadSlugField()
    {
        $this->expectException(\TypeError::class);

        $this->buildStep()(
            $this->createMock(LoaderInterface::class),
            $this->createMock(ObjectInterface::class),
            new \stdClass()
        );
    }

    public function testInvokeWithNonSluggable()
    {
        self::assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createMock(LoaderInterface::class),
                $this->createMock(ObjectInterface::class),
                'slug'
            )
        );
    }

    public function testInvokeWithNonSlugField()
    {
        self::assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createMock(LoaderInterface::class),
                $this->createMock(Content::class),
                null
            )
        );
    }

    public function testInvokeWithSlugField()
    {
        self::assertInstanceOf(
            SlugPreparation::class,
            $this->buildStep()(
                $this->createMock(LoaderInterface::class),
                $this->createMock(Content::class),
                'slug'
            )
        );
    }
}
