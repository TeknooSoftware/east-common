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

namespace Teknoo\Tests\East\Common\Doctrine\Filter\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Teknoo\East\Common\Doctrine\Filter\ODM\SoftDeletableFilter;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SoftDeletableFilter::class)]
class SoftDeletableFilterTest extends TestCase
{
    public function testAddFilterCriteriaNonDeletable(): void
    {
        $rc = $this->createStub(ReflectionClass::class);
        $rc->method('implementsInterface')->willReturn(false);

        $targetDocument = $this->createStub(ClassMetadata::class);
        $targetDocument->method('getReflectionClass')->willReturn($rc);

        $this->assertEquals(
            [],
            new SoftDeletableFilter($this->createStub(DocumentManager::class))->addFilterCriteria($targetDocument),
        );
    }

    public function testAddFilterCriteriaWithDeletable(): void
    {
        $rc = $this->createStub(ReflectionClass::class);
        $rc->method('implementsInterface')->willReturn(true);

        $targetDocument = $this->createStub(ClassMetadata::class);
        $targetDocument->method('getReflectionClass')->willReturn($rc);

        $this->assertEquals(
            ['deletedAt' => null],
            new SoftDeletableFilter($this->createStub(DocumentManager::class))->addFilterCriteria($targetDocument),
        );
    }
}
