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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Service;

use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Loader\CategoryLoader;
use Teknoo\East\Website\Object\Category;
use Teknoo\East\Website\Service\MenuGenerator;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Service\MenuGenerator
 */
class MenuGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CategoryLoader
     */
    private $categoryLoader;

    /**
     * @return CategoryLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getCategoryLoader(): CategoryLoader
    {
        if (!$this->categoryLoader instanceof CategoryLoader) {
            $this->categoryLoader = $this->createMock(CategoryLoader::class);
        }

        return $this->categoryLoader;
    }

    /**
     * @return MenuGenerator
     */
    public function buildService()
    {
        return new MenuGenerator($this->getCategoryLoader());
    }

    public function testExtract()
    {
        $category1 = new Category();
        $category2 = new Category();
        $category3 = new Category();

        $this->getCategoryLoader()
            ->expects(self::any())
            ->method('topBySlug')
            ->with('location1')
            ->willReturnCallback(function ($value, PromiseInterface $promise) use ($category1, $category2, $category3) {
                $promise->success([$category1, $category2, $category3]);

                return $this->getCategoryLoader();
            });

        $stack = [];
        foreach ($this->buildService()->extract('location1') as $element) {
            $stack[] = $element;
        }

        self::assertEquals([$category1, $category2, $category3], $stack);
    }
}
