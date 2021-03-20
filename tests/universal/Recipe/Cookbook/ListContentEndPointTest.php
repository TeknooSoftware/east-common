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

namespace Teknoo\Tests\East\Website\Recipe\Cookbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Website\Recipe\Cookbook\ListContentEndPoint;
use Teknoo\East\Website\Recipe\Step\ExtractOrder;
use Teknoo\East\Website\Recipe\Step\ExtractPage;
use Teknoo\East\Website\Recipe\Step\LoadListObjects;
use Teknoo\East\Website\Recipe\Step\RenderError;
use Teknoo\East\Website\Recipe\Step\RenderList;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Recipe\Cookbook\ListContentEndPoint
 */
class ListContentEndPointTest extends TestCase
{
    use BaseCookbookTestTrait;

    private ?RecipeInterface $recipe = null;

    private ?ExtractPage $extractPage = null;

    private ?ExtractOrder $extractOrder = null;

    private ?LoadListObjects $loadListObjects = null;

    private ?RenderList $renderList = null;

    private ?RenderError $renderError = null;

    private ?SearchFormLoaderInterface $searchFormLoader = null;

    private ?ListObjectsAccessControlInterface $listObjectsAccessControl = null;

    /**
     * @return RecipeInterface|MockObject
     */
    public function getRecipe(): RecipeInterface
    {
        if (null === $this->recipe) {
            $this->recipe = $this->createMock(RecipeInterface::class);
        }

        return $this->recipe;
    }

    /**
     * @return ExtractPage|MockObject
     */
    public function getExtractPage(): ExtractPage
    {
        if (null === $this->extractPage) {
            $this->extractPage = $this->createMock(ExtractPage::class);
        }

        return $this->extractPage;
    }

    /**
     * @return ExtractOrder|MockObject
     */
    public function getExtractOrder(): ExtractOrder
    {
        if (null === $this->extractOrder) {
            $this->extractOrder = $this->createMock(ExtractOrder::class);
        }

        return $this->extractOrder;
    }

    /**
     * @return LoadListObjects|MockObject
     */
    public function getLoadListObjects(): LoadListObjects
    {
        if (null === $this->loadListObjects) {
            $this->loadListObjects = $this->createMock(LoadListObjects::class);
        }

        return $this->loadListObjects;
    }

    /**
     * @return RenderList|MockObject
     */
    public function getRenderList(): RenderList
    {
        if (null === $this->renderList) {
            $this->renderList = $this->createMock(RenderList::class);
        }

        return $this->renderList;
    }

    /**
     * @return RenderError|MockObject
     */
    public function getRenderError(): RenderError
    {
        if (null === $this->renderError) {
            $this->renderError = $this->createMock(RenderError::class);
        }

        return $this->renderError;
    }

    /**
     * @return SearchFormLoaderInterface|MockObject
     */
    public function getSearchFormLoader(): SearchFormLoaderInterface
    {
        if (null === $this->searchFormLoader) {
            $this->searchFormLoader = $this->createMock(SearchFormLoaderInterface::class);
        }

        return $this->searchFormLoader;
    }

    /**
     * @return ListObjectsAccessControlInterface|MockObject
     */
    public function getListObjectsAccessControl(): ListObjectsAccessControlInterface
    {
        if (null === $this->listObjectsAccessControl) {
            $this->listObjectsAccessControl = $this->createMock(ListObjectsAccessControlInterface::class);
        }

        return $this->listObjectsAccessControl;
    }

    public function buildCookbook(): ListContentEndPoint
    {
        return new ListContentEndPoint(
            $this->getRecipe(),
            $this->getExtractPage(),
            $this->getExtractOrder(),
            $this->getLoadListObjects(),
            $this->getRenderList(),
            $this->getRenderError(),
            $this->getSearchFormLoader(),
            $this->getListObjectsAccessControl()
        );
    }
}
