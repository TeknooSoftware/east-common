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
 * @link        https://teknoo.software/east-collection/common Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Recipe\Step;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Foundation\Manager\ManagerInterface;;
use Teknoo\East\CommonBundle\Recipe\Step\SearchFormLoader;

/**
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(SearchFormLoader::class)]
class SearchFormLoaderTest extends TestCase
{
    private ?FormFactory $formFactory = null;

    /**
     * @return FormFactory|MockObject
     */
    private function getFormFactory(): FormFactory
    {
        if (!$this->formFactory instanceof FormFactory) {
            $this->formFactory = $this->createMock(FormFactory::class);
        }

        return $this->formFactory;
    }

    public function buildStep(array $templates): SearchFormLoader
    {
        return new SearchFormLoader(
            $this->getFormFactory(),
            $templates
        );
    }

    public function testInvokeWithNonAllowedInTemplate()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects($this->any())->method('getParsedBody')->willReturn([
            'bar1' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            SearchFormLoader::class,
            $this->buildStep(
                []
            )($request, $manager, $template)
        );
    }

    public function testInvokeWithNotArrayInBody()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects($this->any())->method('getParsedBody')->willReturn(null);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            SearchFormLoader::class,
            $this->buildStep(
                [
                    'bar.html.twig' => [
                        'bar1' => \stdClass::class
                    ],
                    SearchFormLoader::ANY_TEMPLATE => [
                        'bar2' => \stdClass::class
                    ]
                ]
            )($request, $manager, $template)
        );
    }


    public function testInvokeWithNoFormFoundWithAny()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getParsedBody')->willReturn([
            'bar3' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            SearchFormLoader::class,
            $this->buildStep(
                [
                    'bar.html.twig' => [
                        'bar1' => \stdClass::class
                    ],
                    SearchFormLoader::ANY_TEMPLATE => [
                        'bar2' => \stdClass::class
                    ]
                ]
            )($request, $manager, $template)
        );
    }

    public function testInvokeWithNoFormFoundWithTemplate()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects($this->any())->method('getParsedBody')->willReturn([
            'bar3' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects($this->never())->method('error');

        self::assertInstanceOf(
            SearchFormLoader::class,
            $this->buildStep(
                [
                    'foo.html.twig' => [
                        'bar1' => \stdClass::class
                    ],
                    SearchFormLoader::ANY_TEMPLATE => [
                        'bar2' => \stdClass::class
                    ]
                ]
            )($request, $manager, $template)
        );
    }

    public function testInvokeWithFormFoundWithTemplate()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects($this->any())->method('getParsedBody')->willReturn([
            'bar1' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects($this->never())->method('error');

        $this->getFormFactory()
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->createMock(FormInterface::class));

        self::assertInstanceOf(
            SearchFormLoader::class,
            $this->buildStep(
                [
                    'foo.html.twig' => [
                        'bar1' => \stdClass::class
                    ],
                    SearchFormLoader::ANY_TEMPLATE => [
                        'bar2' => \stdClass::class
                    ]
                ]
            )($request, $manager, $template)
        );
    }

    public function testInvokeWithFormFoundWithAny()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getAttribute')->willReturn(
            $this->createMock(Request::class)
        );
        $request->expects($this->any())->method('getParsedBody')->willReturn([
            'bar2' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects($this->never())->method('error');

        $this->getFormFactory()
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->createMock(FormInterface::class));

        self::assertInstanceOf(
            SearchFormLoader::class,
            $this->buildStep(
                [
                    'foo.html.twig' => [
                        'bar1' => \stdClass::class
                    ],
                    SearchFormLoader::ANY_TEMPLATE => [
                        'bar2' => \stdClass::class
                    ]
                ]
            )($request, $manager, $template)
        );
    }
}
