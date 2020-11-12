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
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Service\DatesService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Service\DatesService
 */
class DatesServiceTest extends TestCase
{
    public function buildService()
    {
        return new DatesService();
    }

    public function testPassMeTheDateWithNoDefinedDate()
    {
        $object = new class implements ObjectInterface {
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

        $object = new class implements ObjectInterface {
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
}
