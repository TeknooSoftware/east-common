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

use Teknoo\East\Website\Object\DeletableInterface;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Service\DeletingService;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Service\DeletingService
 */
class DeletingServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @return WriterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getWriterMock(): WriterInterface
    {
        if (!$this->writer instanceof WriterInterface) {
            $this->writer = $this->createMock(WriterInterface::class);
        }

        return $this->writer;
    }

    public function buildService()
    {
        return new DeletingService($this->getWriterMock());
    }

    public function testDeleteWithNoDefinedDate()
    {
        $object = new class implements ObjectInterface, DeletableInterface{
            private $date;
            public function getDeletedAt(): ?\DateTimeInterface {
                return $this->date;
            }
            public function setDeletedAt(\DateTimeInterface $deletedAt): DeletableInterface {
                $this->date = $deletedAt;
                return $this;
            }
            public function getId(): string {}
        };

        $this->getWriterMock()
            ->expects(self::once())
            ->method('save')
            ->with($object)
            ->willReturnSelf();

        self::assertInstanceOf(
            DeletingService::class,
            $this->buildService()->delete($object)
        );
        self::assertInstanceOf(\DateTimeInterface::class, $object->getDeletedAt());
    }

    public function testDeleteWithDefinedDate()
    {
        $date = new \DateTime('2017-01-01');

        $object = new class implements ObjectInterface, DeletableInterface{
            private $date;
            public function getDeletedAt(): ?\DateTimeInterface {
                return $this->date;
            }
            public function setDeletedAt(\DateTimeInterface $deletedAt): DeletableInterface {
                $this->date = $deletedAt;
                return $this;
            }
            public function getId(): string {}
        };

        $this->getWriterMock()
            ->expects(self::once())
            ->method('save')
            ->with($object)
            ->willReturnSelf();

        $service = $this->buildService();

        self::assertInstanceOf(
            DeletingService::class,
            $service = $service->setCurrentDate($date)
        );

        self::assertInstanceOf(
            DeletingService::class,
            $service->delete($object)
        );

        self::assertEquals($date, $object->getDeletedAt());
    }
}
