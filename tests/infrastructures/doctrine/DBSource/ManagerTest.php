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

namespace Teknoo\Tests\East\Website\Doctrine\DBSource;

use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\Doctrine\DBSource\Manager;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Doctrine\DBSource\Manager
 */
class ManagerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @return ObjectManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getDoctrineObjectManagerMock(): ObjectManager
    {
        if (!$this->objectManager instanceof ObjectManager) {
            $this->objectManager = $this->createMock(ObjectManager::class);
        }

        return $this->objectManager;
    }

    public function buildManager(): Manager
    {
        return new Manager($this->getDoctrineObjectManagerMock());
    }

    public function testPersist()
    {
        $object = new \stdClass();
        $this->getDoctrineObjectManagerMock()
            ->expects(self::once())
            ->method('persist')
            ->with($object);

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->persist($object)
        );
    }

    public function testFlush()
    {
        $this->getDoctrineObjectManagerMock()
            ->expects(self::once())
            ->method('flush');

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->flush()
        );
    }
}
