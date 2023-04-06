<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
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

namespace Teknoo\Tests\East\Common\Behat\Loader;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Loader\LoaderTrait;
use Teknoo\Tests\East\Common\Behat\Object\MyObjectTimeStampable;
use Teknoo\Tests\East\Common\Behat\Repository\MyObjectRepository;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements LoaderInterface<MyObjectTimeStampable>
 */
class MyObjectTimeStampableLoader implements LoaderInterface
{
    /**
     * @use LoaderTrait<MyObjectTimeStampable>
     */
    use LoaderTrait;

    public function __construct(MyObjectRepository $repository)
    {
        $this->repository = $repository;
    }
}
