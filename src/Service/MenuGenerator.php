<?php

/*
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

declare(strict_types=1);

namespace Teknoo\East\Website\Service;

use Teknoo\East\Foundation\Promise\Promise;
use Teknoo\East\Website\Loader\ContentLoader;
use Teknoo\East\Website\Loader\ItemLoader;
use Teknoo\East\Website\Object\Item;
use Teknoo\East\Website\Query\Content\PublishedContentFromIdsQuery;
use Teknoo\East\Website\Query\Item\TopItemByLocationQuery;

use function array_keys;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class MenuGenerator
{
    public function __construct(
        private ItemLoader $itemLoader,
        private ContentLoader $contentLoader,
        private ?ProxyDetectorInterface $proxyDetector = null,
    ) {
    }

    /**
     * @return iterable<Item>
     */
    public function extract(string $location): iterable
    {
        $itemsStacks = [];
        $contentsStacks = [];

        $itemsSorting = function (iterable $items) use (&$itemsStacks, &$contentsStacks) {
            foreach ($items as $item) {
                if (null !== $this->proxyDetector && null !== ($content = $item->getContent())) {
                    $this->proxyDetector->checkIfInstanceBehindProxy(
                        $content,
                        new Promise(function ($content) use (&$contentsStacks, $item) {
                            $contentsStacks[$content->getId()] = $item;
                        })
                    );
                }

                if (!($parent = $item->getParent())) {
                    $itemsStacks['top'][] = $item;

                    continue;
                }

                $itemsStacks[$parent->getId()][] = $item;
            }

            if (empty($contentsStacks)) {
                return;
            }

            $this->contentLoader->query(
                new PublishedContentFromIdsQuery(array_keys($contentsStacks)),
                new Promise(function (iterable $contents) use (&$contentsStacks) {
                    foreach ($contents as $content) {
                        $cId = $content->getId();

                        if (!isset($contentsStacks[$cId])) {
                            continue;
                        }

                        $contentsStacks[$cId]->setContent($content);
                    }
                })
            );
        };

        $promise = new Promise($itemsSorting);

        $this->itemLoader->query(new TopItemByLocationQuery($location), $promise);

        if (empty($itemsStacks['top'])) {
            return $this;
        }

        foreach ($itemsStacks['top'] as $element) {
            $haveChildren = !empty($itemsStacks[$id = $element->getId()]);

            if ($haveChildren) {
                yield 'parent' => $element;
                foreach ($itemsStacks[$id] as $child) {
                    yield $id => $child;
                }
            } else {
                yield 'top' => $element;
            }
        }

        return $this;
    }
}
