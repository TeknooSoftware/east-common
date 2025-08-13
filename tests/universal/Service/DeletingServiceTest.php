<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Object\DeletableInterface;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Service\DeletingService;
use Teknoo\East\Foundation\Time\DatesService;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DeletingService::class)]
class DeletingServiceTest extends TestCase
{
    private (WriterInterface&MockObject)|null $writer = null;

    private (DatesService&MockObject)|null $datesService = null;

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

    /**
     * @return DatesService|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getDatesServiceMock(): DatesService
    {
        if (!$this->datesService instanceof DatesService) {
            $this->datesService = $this->createMock(DatesService::class);
        }

        return $this->datesService;
    }

    public function buildService(): \Teknoo\East\Common\Service\DeletingService
    {
        return new DeletingService($this->getWriterMock(), $this->getDatesServiceMock());
    }

    public function testDeleteWithDeletable(): void
    {
        $date = new \DateTime('2017-01-01');

        $object = new class () implements IdentifiedObjectInterface, DeletableInterface {
            private ?\DateTimeInterface $date = null;

            public function getDeletedAt(): ?\DateTimeInterface
            {
                return $this->date;
            }

            public function setDeletedAt(\DateTimeInterface $deletedAt): DeletableInterface
            {
                $this->date = $deletedAt;
                return $this;
            }

            public function getId(): string
            {
            }
        };

        $this->getWriterMock()
            ->expects($this->once())
            ->method('save')
            ->with($object)
            ->willReturnSelf();

        $this->getWriterMock()
            ->expects($this->never())
            ->method('remove');

        $this->getDatesServiceMock()
            ->method('passMeTheDate')
            ->willReturnCallback(
                function ($setter) use ($date): \Teknoo\East\Foundation\Time\DatesService {
                    $setter($date);

                    return $this->getDatesServiceMock();
                }
            );

        $service = $this->buildService();

        $this->assertInstanceOf(
            DeletingService::class,
            $service->delete($object)
        );

        $this->assertEquals($date, $object->getDeletedAt());
    }

    public function testDeleteWithNonDeletable(): void
    {
        $object = new class () implements IdentifiedObjectInterface {
            public function getId(): string
            {
            }
        };

        $this->getWriterMock()
            ->expects($this->never())
            ->method('save');

        $this->getWriterMock()
            ->expects($this->once())
            ->method('remove')
            ->with($object)
            ->willReturnSelf();


        $service = $this->buildService();

        $this->assertInstanceOf(
            DeletingService::class,
            $service->delete($object)
        );
    }
}
