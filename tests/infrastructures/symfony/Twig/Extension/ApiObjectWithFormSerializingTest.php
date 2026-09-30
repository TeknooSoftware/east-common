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

namespace Teknoo\Tests\East\CommonBundle\Twig\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Twig\Attribute\AsTwigFilter;
use Symfony\Component\Form\FormError as SfFormError;
use Symfony\Component\Form\FormView;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Teknoo\East\Common\Object\User;
use Teknoo\East\CommonBundle\Twig\Extension\ApiObjectSerializing;
use Teknoo\East\CommonBundle\Twig\Extension\ApiObjectWithFormSerializing;
use Teknoo\East\CommonBundle\Twig\Extension\FormError;
use Teknoo\East\FoundationBundle\Normalizer\EastNormalizer;

use function json_decode;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(ApiObjectWithFormSerializing::class)]
class ApiObjectWithFormSerializingTest extends TestCase
{
    private function buildSerializer(): Serializer
    {
        $eastNormalizer = new EastNormalizer();
        $serializer = new Serializer([$eastNormalizer], [new JsonEncoder()]);
        $eastNormalizer->setNormalizer($serializer);

        return $serializer;
    }

    private function buildUser(): User
    {
        return new class () extends User {
        }
            ->setId('u1')
            ->setFirstName('Max')
            ->setLastName('Doe')
            ->setEmail('max@teknoo.software');
    }

    private function buildExtension(): ApiObjectWithFormSerializing
    {
        return new ApiObjectWithFormSerializing(
            new ApiObjectSerializing($this->buildSerializer()),
            new FormError(),
        );
    }

    public function testTwigAttribute(): void
    {
        $attributes = new \ReflectionMethod(ApiObjectWithFormSerializing::class, 'rendering')->getAttributes(AsTwigFilter::class);

        $this->assertCount(1, $attributes);
        $this->assertEquals('east_api_object_with_form_serialization', $attributes[0]->newInstance()->name);
    }

    public function testSerializeWithoutErrors(): void
    {
        $decoded = json_decode(
            $this->buildExtension()->rendering(
                object: $this->buildUser(),
                formView: new FormView(),
                context: ['groups' => ['digest']],
            ),
            true,
        );

        $this->assertEquals(['id' => 'u1', '@class' => User::class], $decoded['meta']);
        $this->assertEquals('max@teknoo.software', $decoded['data']['email']);
    }

    public function testSerializeWithoutFormView(): void
    {
        $decoded = json_decode(
            $this->buildExtension()->rendering(
                object: $this->buildUser(),
                formView: null,
                context: ['groups' => ['digest']],
            ),
            true,
        );

        $this->assertEquals('u1', $decoded['meta']['id']);
    }

    public function testSerializeWithErrorsOnTheRootForm(): void
    {
        $factory = Forms::createFormFactoryBuilder()->getFormFactory();
        $builder = $factory->createNamedBuilder('user', FormType::class);
        $builder->add('email', TextType::class, ['error_bubbling' => true]);
        $form = $builder->getForm();
        $form->get('email')->addError(new SfFormError('Bad email'));

        $this->assertEquals(
            [
                'meta' => ['errors' => true],
                'data' => ['.email' => 'Bad email'],
            ],
            json_decode(
                $this->buildExtension()->rendering(
                    object: $this->buildUser(),
                    formView: $form->createView(),
                    context: ['groups' => ['crud']],
                ),
                true,
            ),
        );
    }

    public function testSerializeTheObjectWhenOnlyChildrenHaveErrors(): void
    {
        $factory = Forms::createFormFactoryBuilder()->getFormFactory();
        $builder = $factory->createNamedBuilder('user', FormType::class);
        $builder->add('email', TextType::class, ['error_bubbling' => false]);
        $form = $builder->getForm();
        $form->get('email')->addError(new SfFormError('Bad email'));

        $decoded = json_decode(
            $this->buildExtension()->rendering(
                object: $this->buildUser(),
                formView: $form->createView(),
                context: ['groups' => ['digest']],
            ),
            true,
        );

        $this->assertEquals(['id' => 'u1', '@class' => User::class], $decoded['meta']);
        $this->assertEquals('max@teknoo.software', $decoded['data']['email']);
    }
}
