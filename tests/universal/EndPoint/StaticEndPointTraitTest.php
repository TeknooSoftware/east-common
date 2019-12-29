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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\Website\EndPoint;

use Psr\Http\Message\ResponseInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\Foundation\Http\ClientInterface;
use Teknoo\East\FoundationBundle\EndPoint\EastEndPointTrait;
use Teknoo\East\Website\EndPoint\StaticEndPointTrait;
use Zend\Diactoros\Response;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\Website\EndPoint\StaticEndPointTrait
 */
class StaticEndPointTraitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return EndPointInterface
     */
    public function buildEndPoint(): EndPointInterface
    {
        return new class implements EndPointInterface {
            use EastEndPointTrait;
            use StaticEndPointTrait;

            /**
             * {@inheritdoc}
             */
            public function render(ClientInterface $client, string $view, array $parameters = array(), int $status = 200): EndPointInterface
            {
                $client->acceptResponse(new Response\TextResponse($view.':executed'));
                return $this;
            }
        };
    }

    public function testInvokeBadClient()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(new \stdClass(), 'fooBar');
    }

    public function testInvokeBadTemplate()
    {
        $this->expectException(\TypeError::class);
        $this->buildEndPoint()(
            $this->createMock(ClientInterface::class),
            new \stdClass()
        );
    }

    public function testInvoke()
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects(self::once())
            ->method('acceptResponse')
            ->with($this->callback(function ($value) {
                if ($value instanceof ResponseInterface) {
                    return 'fooBar:executed' == (string) $value->getBody();
                }

                return false;
            }))
            ->willReturnSelf();

        self::assertInstanceOf(
            EndPointInterface::class,
            $this->buildEndPoint()($client, 'fooBar')
        );
    }
}
