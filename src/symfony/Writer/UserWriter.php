<?php

declare(strict_types=1);

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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\WebsiteBundle\Writer;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Teknoo\East\Foundation\Promise\PromiseInterface;
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
    /**
     * @var UniversalWriter
     */
    private $universalWriter;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * UserWriter constructor.
     * @param UniversalWriter $universalWriter
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(UniversalWriter $universalWriter, EncoderFactoryInterface $encoderFactory)
    {
        $this->universalWriter = $universalWriter;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param BaseUser $user
     */
    private function encodePassword(BaseUser $user): void
    {
        if ($user->hasUpdatedPassword()) {
            $encoder = $this->encoderFactory->getEncoder(new User($user));
            $salt = $user->getSalt();
            $user->setPassword($encoder->encodePassword($user->getPassword(), $salt));
        } else {
            $user->eraseCredentials();
        }
    }

    /**
     * @param BaseUser $object
     * @param PromiseInterface|null $promise
     * @return WriterInterface
     */
    public function save($object, PromiseInterface $promise = null): WriterInterface
    {
        $this->encodePassword($object);

        $this->universalWriter->save($object, $promise);

        return $this;
    }
}
