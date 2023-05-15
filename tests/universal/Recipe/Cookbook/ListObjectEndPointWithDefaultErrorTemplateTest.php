<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Recipe\Cookbook;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Recipe\Step\ListObjectsAccessControlInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;
use Teknoo\East\Common\Recipe\Cookbook\ListObjectEndPoint;
use Teknoo\East\Common\Recipe\Step\ExtractOrder;
use Teknoo\East\Common\Recipe\Step\ExtractPage;
use Teknoo\East\Common\Recipe\Step\LoadListObjects;
use Teknoo\East\Common\Recipe\Step\RenderError;
use Teknoo\East\Common\Recipe\Step\RenderList;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Tests\Recipe\Cookbook\BaseCookbookTestTrait;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Recipe\Cookbook\ListObjectEndPoint
 */
class ListObjectEndPointWithDefaultErrorTemplateTest extends TestCase
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

    public function buildCookbook(): ListObjectEndPoint
    {
        return new ListObjectEndPoint(
            $this->getRecipe(),
            $this->getExtractPage(),
            $this->getExtractOrder(),
            $this->getLoadListObjects(),
            $this->getRenderList(),
            $this->getRenderError(),
            $this->getSearchFormLoader(),
            $this->getListObjectsAccessControl(),
            'foo.template',
        );
    }
}
