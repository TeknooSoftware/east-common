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

namespace Teknoo\Tests\East\Common\Writer;

use PHPUnit\Framework\TestCase;
use Teknoo\East\Common\Object\User;
use Teknoo\East\Common\Writer\UserWriter;
use Teknoo\East\Common\Writer\WriterInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers \Teknoo\East\Common\Writer\UserWriter
 * @covers \Teknoo\East\Common\Writer\PersistTrait
 */
class UserWriterTest extends TestCase
{
    use PersistTestTrait;

    public function buildWriter(): WriterInterface
    {
        return new UserWriter($this->getObjectManager(), $this->getDatesServiceMock());
    }

    public function getObject()
    {
        return new User();
    }
}
