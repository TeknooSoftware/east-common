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

namespace Teknoo\Tests\East\Common\Behat\Writer;

use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\Writer\WriterInterface;
use Teknoo\East\Common\Writer\PersistTrait;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Tests\East\Common\Behat\Object\MyObject;
use Throwable;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements WriterInterface<MyObject>
 */
class MyObjectWriter implements WriterInterface
{
    /**
     * @use PersistTrait<MyObject>
     */
    use PersistTrait;

    /**
     * @throws Throwable
     */
    public function save(
        ObjectInterface $object,
        PromiseInterface $promise = null,
        ?bool $preferRealDateOnUpdate = null,
    ): WriterInterface {
        $this->persist($object, $promise, $preferRealDateOnUpdate);

        if ($object instanceof MyObject) {
            $object->saved = 'foo';
        }

        return $this;
    }
}
