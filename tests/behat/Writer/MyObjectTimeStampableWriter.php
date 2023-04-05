<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\East\Common\Behat\Writer;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Writer\PersistTrait;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Tests\East\Common\Behat\Object\MyObjectTimeStampable;
use Throwable;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements WriterInterface<MyObjectTimeStampable>
 */
class MyObjectTimeStampableWriter implements WriterInterface
{
    /**
     * @use PersistTrait<MyObjectTimeStampable>
     */
    use PersistTrait;

    /**
     * @throws Throwable
     */
    public function save(
        ObjectInterface $object,
        PromiseInterface $promise = null,
        ?bool $prefereRealDateOnUpdate = null,
    ): WriterInterface {
        $this->persist($object, $promise, $prefereRealDateOnUpdate);

        if ($object instanceof MyObjectTimeStampable) {
            $object->saved = 'foo';
        }

        return $this;
    }
}
