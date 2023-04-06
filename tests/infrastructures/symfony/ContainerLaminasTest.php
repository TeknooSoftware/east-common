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
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle;

use DI\Container;
use DI\ContainerBuilder;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class DefinitionProviderTest.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class ContainerLaminasTest extends TestCase
{
    /**
     * @return Container
     */
    protected function buildContainer() : Container
    {
        $containerDefinition = new ContainerBuilder();
        $containerDefinition->addDefinitions(__DIR__.'/../../../infrastructures/symfony/Resources/config/laminas_di.php');
        $containerDefinition->useAutowiring(false);
        
        return $containerDefinition->build();
    }

    public function testStreamFactory()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            StreamFactory::class,
            $container->get(StreamFactory::class)
        );
    }

    public function testMediaEndPointStreamFactory()
    {
        $container = $this->buildContainer();

        self::assertInstanceOf(
            StreamFactory::class,
            $container->get('teknoo.east.common.endpoint.media.stream.factory')
        );
    }
}
