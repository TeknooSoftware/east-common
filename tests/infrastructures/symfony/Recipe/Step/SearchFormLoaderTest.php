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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\East\WebsiteBundle\Recipe\Step;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Contracts\Form\SearchFormInterface;
use Teknoo\East\Website\Object\Content;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\WebsiteBundle\Recipe\Step\SearchFormLoader;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 * @covers      \Teknoo\East\WebsiteBundle\Recipe\Step\SearchFormLoader
 */
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
        /*
        [
            'foo.html.twig' => [
                'bar1' => \stdClass::class
            ],
            SearchFormLoader::ANY_TEMPLATE => [
                'bar2' => \stdClass::class
            ]
        ]
        */

        return new SearchFormLoader(
            $this->getFormFactory(),
            $templates
        );
    }

    public function testInvokeWithNonAllowedInTemplate()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getQueryParams')->willReturn([
            'bar1' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects(self::never())->method('updateWorkPlan');
        $manager->expects(self::once())->method('error')->willReturnCallback(
            function (\Throwable $error) use ($manager) {
                self::assertInstanceOf(\DomainException::class, $error);
                self::assertEquals(403, $error->getCode());

                return $manager;
            }
        );

        self::assertInstanceOf(
            SearchFormLoader::class,
            $this->buildStep(
                []
            )($request, $manager, $template)
        );
    }

    public function testInvokeWithNoFormFoundWithAny()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getQueryParams')->willReturn([
            'bar3' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects(self::never())->method('updateWorkPlan');
        $manager->expects(self::once())->method('error')->willReturnCallback(
            function (\Throwable $error) use ($manager) {
                self::assertInstanceOf(\DomainException::class, $error);
                self::assertEquals(403, $error->getCode());

                return $manager;
            }
        );

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
        $request->expects(self::any())->method('getQueryParams')->willReturn([
            'bar3' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects(self::never())->method('updateWorkPlan');
        $manager->expects(self::once())->method('error')->willReturnCallback(
            function (\Throwable $error) use ($manager) {
                self::assertInstanceOf(\DomainException::class, $error);
                self::assertEquals(403, $error->getCode());

                return $manager;
            }
        );

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

    public function testInvokeWithFormFoundWithTemplateWithBadFromClass()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getQueryParams')->willReturn([
            'bar1' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects(self::never())->method('updateWorkPlan');
        $manager->expects(self::once())->method('error')->willReturnCallback(
            function (\Throwable $error) use ($manager) {
                self::assertInstanceOf(\RuntimeException::class, $error);
                self::assertEquals(500, $error->getCode());

                return $manager;
            }
        );

        $this->getFormFactory()
            ->expects(self::once())
            ->method('create')
            ->willReturn(new \stdClass());

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
        $request->expects(self::any())->method('getQueryParams')->willReturn([
            'bar1' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects(self::once())->method('updateWorkPlan');
        $manager->expects(self::never())->method('error');

        $this->getFormFactory()
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->createMock(SearchFormInterface::class));

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
        $request->expects(self::any())->method('getQueryParams')->willReturn([
            'bar2' => []
        ]);
        $manager = $this->createMock(ManagerInterface::class);
        $template = 'foo.html.twig';

        $manager->expects(self::once())->method('updateWorkPlan');
        $manager->expects(self::never())->method('error');

        $this->getFormFactory()
            ->expects(self::once())
            ->method('create')
            ->willReturn($this->createMock(SearchFormInterface::class));

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
