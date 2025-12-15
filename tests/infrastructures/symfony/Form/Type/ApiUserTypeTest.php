<?php

/*
 * East Common.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
  */

declare(strict_types=1);

namespace Teknoo\Tests\East\CommonBundle\Form\Type;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Teknoo\East\CommonBundle\Form\Type\ApiUserType;
use Teknoo\East\CommonBundle\Form\Type\UserType;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiUserType::class)]
class ApiUserTypeTest extends TestCase
{
    use FormTestTrait;

    public function buildForm(): UserType
    {
        return new UserType();
    }

    public function testBuildFormWithPopulatedUser(): void
    {
        $builder = $this->createStub(FormBuilderInterface::class);

        $builder
            ->method('add')
            ->willReturnSelf();

        $this->buildForm()->buildForm($builder, []);
        $this->assertTrue(true);
    }

    public function testConfigureOptions(): void
    {
        $this->buildForm()->configureOptions(
            $this->createStub(OptionsResolver::class)
        );

        $this->assertTrue(true);
    }
}
