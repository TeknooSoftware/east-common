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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Writer;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Writer\UserWriter;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 * @covers \Teknoo\East\Common\Writer\UserWriter
 * @covers \Teknoo\East\Common\Writer\PersistTrait
 */
class UserWriterTest extends TestCase
{
    use PersistTestTrait;

    public function buildWriter(bool $prefereRealDateOnUpdate = false,): \Teknoo\East\Common\Contracts\Writer\WriterInterface
    {
        return new UserWriter($this->getObjectManager(), $this->getDatesServiceMock(), $prefereRealDateOnUpdate);
    }

    public function getObject()
    {
        return new User();
    }
}
