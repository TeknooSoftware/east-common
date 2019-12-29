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

namespace Teknoo\Tests\East\WebsiteBundle\EndPoint;

use Symfony\Component\Templating\EngineInterface;
use Teknoo\East\Foundation\EndPoint\EndPointInterface;
use Teknoo\East\WebsiteBundle\EndPoint\ContentEndPoint;
use Teknoo\Tests\East\Website\EndPoint\ContentEndPointTraitTest;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\EndPoint\ContentEndPoint
 */
class ContentEndPointTest extends ContentEndPointTraitTest
{
    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @return EngineInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    public function getTemplating(): EngineInterface
    {
        if (!$this->templating instanceof EngineInterface) {
            $this->templating = $this->createMock(EngineInterface::class);

            $this->templating->expects(self::any())
                ->method('render')
                ->willReturnCallback(function ($script) {
                    return $script.':executed';
                });
        }

        return $this->templating;
    }

    public function buildEndPoint(): EndPointInterface
    {
        return (new ContentEndPoint($this->getContentLoader(), 'error-404'))
            ->setTemplating($this->getTemplating());
    }
}
