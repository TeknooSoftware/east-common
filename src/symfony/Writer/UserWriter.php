<?php

/*
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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\WebsiteBundle\Writer;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Object\User as BaseUser;
use Teknoo\East\Website\Writer\WriterInterface;
use Teknoo\East\Website\Writer\UserWriter as UniversalWriter;
use Teknoo\East\WebsiteBundle\Object\User;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class UserWriter implements WriterInterface
{
    private UniversalWriter $universalWriter;

    private EncoderFactoryInterface $encoderFactory;

    public function __construct(UniversalWriter $universalWriter, EncoderFactoryInterface $encoderFactory)
    {
        $this->universalWriter = $universalWriter;
        $this->encoderFactory = $encoderFactory;
    }

    private function encodePassword(BaseUser $user): void
    {
        if ($user->hasUpdatedPassword()) {
            $encoder = $this->encoderFactory->getEncoder(new User($user));
            $salt = $user->getSalt();
            $user->setPassword((string) $encoder->encodePassword($user->getPassword(), $salt));
        } else {
            $user->eraseCredentials();
        }
    }

    public function save(ObjectInterface $object, PromiseInterface $promise = null): WriterInterface
    {
        if (!$object instanceof BaseUser) {
            if (null !== $promise) {
                $class = \get_class($object);
                $promise->fail(new \RuntimeException("The class $class is not managed by this writer"));
            }

            return $this;
        }

        $this->encodePassword($object);

        $this->universalWriter->save($object, $promise);

        return $this;
    }
}
