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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Service;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Object\Item;
use Teknoo\East\Website\Query\Item\TopItemByLocationQuery;
use Teknoo\East\Website\Service\MenuGenerator;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Service\MenuGenerator
 */
class MenuGeneratorTest extends TestCase
{
    /**
     * @var ItemLoader
     */
    private $itemLoader;

    /**
     * @return ItemLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getItemLoader(): ItemLoader
    {
        if (!$this->itemLoader instanceof ItemLoader) {
            $this->itemLoader = $this->createMock(ItemLoader::class);
        }

        return $this->itemLoader;
    }

    /**
     * @return MenuGenerator
     */
    public function buildService()
    {
        return new MenuGenerator($this->getItemLoader());
    }

    public function testExtract()
    {
        $item1 = new Item();
        $item2 = new Item();
        $item3 = new Item();

        $this->getItemLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new TopItemByLocationQuery('location1'))
            ->willReturnCallback(function ($value, PromiseInterface $promise) use ($item1, $item2, $item3) {
                $promise->success([$item1, $item2, $item3]);

                return $this->getItemLoader();
            });

        $stack = [];
        foreach ($this->buildService()->extract('location1') as $element) {
            $stack[] = $element;
        }

        self::assertEquals([$item1, $item2, $item3], $stack);
    }
}
