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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\Writer;

use Teknoo\East\Website\Object\User;
use Teknoo\East\Website\Writer\UserWriter;
use Teknoo\East\Website\Writer\WriterInterface;

/**
 * @covers \Teknoo\East\Website\Writer\UserWriter
 * @covers \Teknoo\East\Website\Writer\DoctrinePersistTrait
 */
class UserWriterTest extends \PHPUnit_Framework_TestCase
{
    use DoctrinePersistTestTrait;

    public function buildWriter(): WriterInterface
    {
        return new UserWriter($this->getObjectManager());
    }

    public function getEntity()
    {
        return new User();
    }
}