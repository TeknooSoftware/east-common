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

namespace Teknoo\Tests\East\Website\Service;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\Item;
use Teknoo\East\Website\Query\Content\PublishedContentFromIdsQuery;
use Teknoo\East\Website\Query\Item\TopItemByLocationQuery;
use Teknoo\East\Website\Service\MenuGenerator;
use Teknoo\East\Website\Service\ProxyDetectorInterface;

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
     * @var ContentLoader
     */
    private $contentLoader;

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
     * @return ContentLoader|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getContentLoader(): ContentLoader
    {
        if (!$this->contentLoader instanceof ContentLoader) {
            $this->contentLoader = $this->createMock(ContentLoader::class);
        }

        return $this->contentLoader;
    }

    /**
     * @return MenuGenerator
     */
    public function buildService()
    {
        return new MenuGenerator(
            $this->getItemLoader(),
            $this->getContentLoader(),
            new class implements ProxyDetectorInterface {
                public function checkIfInstanceBehindProxy(
                    object $object,
                    PromiseInterface $promise
                ): ProxyDetectorInterface {
                    if ($object instanceof Content && 'c4' === $object->getId()) {
                        $promise->fail(new \Exception());
                    } else {
                        $promise->success($object);
                    }

                    return $this;
                }
            }
        );
    }

    public function testExtract()
    {
        $item1 = (new Item())->setId('i1');
        $item2 = (new Item())->setId('i2')->setContent(
            new class extends Content {
                public function getId(): string
                {
                    return 'c1';
                }
            }
        );
        $item3 = (new Item())->setId('i3')->setParent($item1)->setContent(
            new class extends Content {
                public function getId(): string
                {
                    return 'c2';
                }
            }
        );;
        $item4 = (new Item())->setId('i3')->setParent($item1);

        $content1 = (new Content())->setId('c1');
        $content2 = (new Content())->setId('c2');
        $content3 = (new Content())->setId('c3');
        $content4 = (new Content())->setId('c4');
        $item4->setContent($content4);

        $this->getItemLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new TopItemByLocationQuery('location1'))
            ->willReturnCallback(function ($value, PromiseInterface $promise) use ($item1, $item2, $item3, $item4) {
                $promise->success([$item1, $item2, $item3, $item4]);

                return $this->getItemLoader();
            });

        $this->getContentLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new PublishedContentFromIdsQuery(['c1', 'c2']))
            ->willReturnCallback(function ($value, PromiseInterface $promise) use ($content1, $content2, $content3) {
                $promise->success([$content1, $content2, $content3]);

                return $this->getContentLoader();
            });

        $stack = [];
        foreach ($this->buildService()->extract('location1') as $key=>$element) {
            $stack[$key][] = $element;
        }

        self::assertEquals(['parent' => [$item1], 'top' => [$item2], 'i1' => [$item3, $item4]], $stack);
    }

    public function testExtractWithoutTop()
    {
        $this->getItemLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new TopItemByLocationQuery('location1'))
            ->willReturnCallback(function ($value, PromiseInterface $promise) {
                $promise->success([]);

                return $this->getItemLoader();
            });

        $this->getContentLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new PublishedContentFromIdsQuery([]))
            ->willReturnCallback(function ($value, PromiseInterface $promise)  {
                $promise->success([]);

                return $this->getContentLoader();
            });

        $stack = [];
        foreach ($this->buildService()->extract('location1') as $key=>$element) {
            $stack[$key][] = $element;
        }

        self::assertEquals([], $stack);
    }

    public function testExtractWithoutContent()
    {
        $item1 = (new Item())->setId('i1');
        $item2 = (new Item())->setId('i2');
        $item3 = (new Item())->setId('i3')->setParent($item1);

        $this->getItemLoader()
            ->expects(self::any())
            ->method('query')
            ->with(new TopItemByLocationQuery('location1'))
            ->willReturnCallback(function ($value, PromiseInterface $promise) use ($item1, $item2, $item3) {
                $promise->success([$item1, $item2, $item3]);

                return $this->getItemLoader();
            });

        $stack = [];
        foreach ($this->buildService()->extract('location1') as $key=>$element) {
            $stack[$key][] = $element;
        }

        self::assertEquals(['parent' => [$item1], 'top' => [$item2], 'i1' => [$item3]], $stack);
    }
}
