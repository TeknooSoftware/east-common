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

namespace Teknoo\East\Common\Recipe\Step\User;

use Teknoo\East\Common\Contracts\Loader\LoaderInterface;
use Teknoo\East\Common\Contracts\Object\ObjectInterface;
use Teknoo\East\Common\Contracts\User\UserInterface;
use Teknoo\East\Common\Object\EmailValue;
use Teknoo\East\Common\Query\User\UserByEmailQuery;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\Recipe\Promise\Promise;

/**
 * Step to load into the workplan an user, found thanks to its email via the UserByEmailQuery
 * If any user was found, the step do nothing
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FindUserByEmail
{
    /**
     * @param LoaderInterface<ObjectInterface> $loader
     */
    public function __invoke(
        LoaderInterface $loader,
        EmailValue $emailValue,
        ManagerInterface $manager,
    ): self {
        /** @var Promise<ObjectInterface, mixed, mixed> $fetchPromise */
        $fetchPromise = new Promise(
            static function (ObjectInterface $object) use ($manager): void {
                $manager->updateWorkPlan([UserInterface::class => $object]);
            },
            static function () use ($manager): void {
                //Do Nothing,
                $manager->continue();
            }
        );

        $loader->fetch(
            new UserByEmailQuery($emailValue->email),
            $fetchPromise,
        );

        return $this;
    }
}
