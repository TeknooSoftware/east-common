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

namespace Teknoo\Tests\East\Website\Doctrine\DBSource\ODM;

use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Teknoo\East\Website\DBSource\RepositoryInterface;
use Teknoo\East\Website\Doctrine\DBSource\ODM\TypeRepository;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Website\Doctrine\DBSource\ODM\TypeRepository
 * @covers \Teknoo\East\Website\Doctrine\DBSource\ODM\RepositoryTrait
 * @covers \Teknoo\East\Website\Doctrine\DBSource\ODM\ExprConversionTrait
 */
class TypeRepositoryTest extends TestCase
{
    use RepositoryTestTrait;

    /**
     * @inheritDoc
     */
    public function buildRepository(): RepositoryInterface
    {
        return new TypeRepository($this->getDoctrineObjectRepositoryMock());
    }

    public function testWithNonSupportedRepository()
    {
        $this->expectException(\RuntimeException::class);
        new TypeRepository($this->createMock(ObjectRepository::class));
    }
}
