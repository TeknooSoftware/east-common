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

namespace Teknoo\Tests\East\Common\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Contracts\Object\IdentifiedObjectInterface;
use Teknoo\East\Common\Service\DatesService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(DatesService::class)]
class DatesServiceTest extends TestCase
{
    public function buildService()
    {
        return new DatesService();
    }

    public function testPassMeTheDateWithNoDefinedDate()
    {
        $object = new class implements IdentifiedObjectInterface {
            private $date;
            public function getDate(): ?\DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(\DateTimeInterface $date): self
            {
                $this->date = $date;

                return $this;
            }
            public function getId(): string
            {
            }
        };

        $service = $this->buildService();
        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate([$object, 'setDate'])
        );

        self::assertInstanceOf(\DateTimeInterface::class, $object->getDate());
        $oldDate = $object->getDate();

        $service->passMeTheDate([$object, 'setDate']);
        self::assertEquals($oldDate, $object->getDate());
    }

    public function testPassMeTheDateWithDefinedDate()
    {
        $date = new \DateTime('2017-01-01');

        $object = new class implements IdentifiedObjectInterface {
            private $date;
            public function getDate(): ?\DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(\DateTimeInterface $date): self
            {
                $this->date = $date;
                return $this;
            }
            public function getId(): string
            {
            }
        };

        $service = $this->buildService();

        self::assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate([$object, 'setDate'])
        );

        self::assertEquals($date, $object->getDate());
    }

    public function testPassMeTheDateWithRealDate()
    {
        $date = new \DateTime('2017-01-01');

        $object = new class implements IdentifiedObjectInterface {
            private $date;
            public function getDate(): ?\DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(\DateTimeInterface $date): self
            {
                $this->date = $date;
                return $this;
            }
            public function getId(): string
            {
            }
        };

        $service = $this->buildService();

        self::assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate([$object, 'setDate'], true)
        );

        self::assertNotEquals($date, $object->getDate());
    }

    public function testPassMeTheDateWithRealDateAndRefresInternalDate()
    {
        $date = new \DateTime('2017-01-01');

        $object = new class implements IdentifiedObjectInterface {
            private $date;
            public function getDate(): ?\DateTimeInterface
            {
                return $this->date;
            }
            public function setDate(\DateTimeInterface $date): self
            {
                $this->date = $date;
                return $this;
            }
            public function getId(): string
            {
            }
        };

        $service = $this->buildService();

        self::assertInstanceOf(
            DatesService::class,
            $service->setCurrentDate($date)
        );

        $object2 = clone $object;
        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate([$object, 'setDate'], true)
        );

        self::assertNotEquals($date, $object->getDate());

        self::assertInstanceOf(
            DatesService::class,
            $service->passMeTheDate([$object2, 'setDate'])
        );

        self::assertNotEquals($date, $object2->getDate());
        self::assertNotSame($object->getDate(), $object2->getDate());
        self::assertEquals($object->getDate(), $object2->getDate());
    }
}
