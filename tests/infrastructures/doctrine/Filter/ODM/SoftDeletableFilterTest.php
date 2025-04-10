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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
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
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SoftDeletableFilter::class)]
class SoftDeletableFilterTest extends TestCase
{
    public function testAddFilterCriteriaNonDeletable()
    {
        $rc = $this->createMock(ReflectionClass::class);
        $rc->expects($this->any())->method('implementsInterface')->willReturn(false);

        $targetDocument = $this->createMock(ClassMetadata::class);
        $targetDocument->expects($this->any())->method('getReflectionClass')->willReturn($rc);

        self::assertEquals(
            [],
            (new SoftDeletableFilter($this->createMock(DocumentManager::class)))->addFilterCriteria($targetDocument),
        );
    }
    public function testAddFilterCriteriaWithDeletable()
    {
        $rc = $this->createMock(ReflectionClass::class);
        $rc->expects($this->any())->method('implementsInterface')->willReturn(true);

        $targetDocument = $this->createMock(ClassMetadata::class);
        $targetDocument->expects($this->any())->method('getReflectionClass')->willReturn($rc);

        self::assertEquals(
            ['deletedAt' => null],
            (new SoftDeletableFilter($this->createMock(DocumentManager::class)))->addFilterCriteria($targetDocument),
        );
    }
}
