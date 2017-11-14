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

namespace Teknoo\Tests\East\WebsiteBundle\AdminEndPoint;

use Symfony\Component\Routing\RouterInterface;
use Teknoo\East\Website\Loader\LoaderInterface;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\WebsiteBundle\AdminEndPoint\AdminDeleteEndPoint;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminDeleteEndPoint
 * @covers      \Teknoo\East\WebsiteBundle\AdminEndPoint\AdminEndPointTrait
 */
class AdminDeleteEndPointTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DeletingService
     */
    private $deletingService;

    /**
     * @var LoaderInterface
     */
    private $loaderService;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @return DeletingService
     */
    public function getDeletingService(): DeletingService
    {
        if (!$this->deletingService instanceof DeletingService) {
            $this->deletingService = $this->createMock(DeletingService::class);
        }

        return $this->deletingService;
    }

    /**
     * @return LoaderInterface
     */
    public function getLoaderService(): LoaderInterface
    {
        if (!$this->loaderService instanceof LoaderInterface) {
            $this->loaderService = $this->createMock(LoaderInterface::class);
        }

        return $this->loaderService;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        if (!$this->router instanceof RouterInterface) {
            $this->router = $this->createMock(RouterInterface::class);
        }

        return $this->router;
    }

    public function buildEndPoint()
    {
        return (new AdminDeleteEndPoint())
            ->setDeletingService($this->getDeletingService())
            ->setLoader($this->getLoaderService())
            ->setRouter($this->getRouter());
    }
}
