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

namespace Teknoo\Tests\East\WebsiteBundle\Provider;

use Teknoo\East\Website\Loader\UserLoader;
use Teknoo\East\WebsiteBundle\Provider\UserProvider;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Provider\UserProvider
 */
class UserProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UserLoader
     */
    private $loader;

    /**
     * @return UserLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLoader(): UserLoader
    {
        if (!$this->loader instanceof UserLoader) {
            $this->loader = $this->createMock(UserLoader::class);
        }

        return $this->loader;
    }

    public function buildProvider(): UserProvider
    {
        return new UserProvider($this->getLoader());
    }
}
