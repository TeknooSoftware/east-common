<?php

/**
 * East Common.
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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Common\Doctrine\DBSource\Common;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Doctrine\DBSource\Common\Manager;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Common\Doctrine\DBSource\Common\Manager
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

    public function testRemove()
    {
        $object = new \stdClass();
        $this->getDoctrineObjectManagerMock()
            ->expects(self::once())
            ->method('remove')
            ->with($object);

        self::assertInstanceOf(
            Manager::class,
            $this->buildManager()->remove($object)
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
