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
use Twig\Attribute\AsTwigFunction;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError as SfFormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;
use Teknoo\East\CommonBundle\Twig\Extension\FormError;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FormError::class)]
class FormErrorTest extends TestCase
{
    /**
     * @return FormInterface<mixed>
     */
    private function buildForm(): FormInterface
    {
        $factory = Forms::createFormFactoryBuilder()->getFormFactory();

        $builder = $factory->createNamedBuilder('my_form', FormType::class);
        $builder->add('name', TextType::class, ['error_bubbling' => false]);
        $builder->add('title', TextType::class, ['error_bubbling' => true]);
        $builder->add(
            $builder->create('blocks', FormType::class, ['error_bubbling' => false])
                ->add(
                    $builder->create('0', FormType::class, ['error_bubbling' => false])
                        ->add('type', TextType::class, ['error_bubbling' => false])
                )
        );

        $form = $builder->getForm();

        return $form;
    }

    public function testTwigAttribute(): void
    {
        $attributes = new \ReflectionMethod(FormError::class, 'getFieldErrors')->getAttributes(AsTwigFunction::class);

        $this->assertCount(1, $attributes);
        $this->assertEquals('east_api_form_errors', $attributes[0]->newInstance()->name);
    }

    public function testWithoutView(): void
    {
        $this->assertEquals([], new FormError()->getFieldErrors(null));
    }

    public function testWithoutErrors(): void
    {
        $this->assertEquals([], new FormError()->getFieldErrors($this->buildForm()->createView()));
    }

    public function testWithErrorsOnRootAndChildren(): void
    {
        $form = $this->buildForm();
        $form->addError(new SfFormError('Root error'));
        $form->get('name')->addError(new SfFormError('Name error'));
        $form->get('name')->addError(new SfFormError('Other name error'));
        $form->get('title')->addError(new SfFormError('Title error'));
        $form->get('blocks')->get('0')->get('type')->addError(new SfFormError('Type error'));

        $this->assertEquals(
            [
                '.' => 'Root error',
                '.title' => 'Title error',
                '.name' => 'Name error' . PHP_EOL . 'Other name error',
                '.blocks.0.type' => 'Type error',
            ],
            new FormError()->getFieldErrors($form->createView()),
        );
    }

    public function testWithErrorWithoutOrigin(): void
    {
        $view = new FormView();
        $child = new FormView($view);
        $view->children['foo'] = $child;
        $child->vars['errors'] = [new SfFormError('Foo error'), 'not an error'];

        $this->assertEquals(
            [
                '.foo' => 'Foo error',
            ],
            new FormError()->getFieldErrors($view),
        );
    }

    public function testWithPropertyPathAndFormWithoutName(): void
    {
        $factory = Forms::createFormFactoryBuilder()->getFormFactory();
        $builder = $factory->createNamedBuilder('', FormType::class);
        $builder->add('email', TextType::class, ['property_path' => '[mail]', 'error_bubbling' => false]);
        $form = $builder->getForm();

        $form->get('email')->addError(new SfFormError('Bad email'));

        $this->assertEquals(
            [
                '.mail' => 'Bad email',
            ],
            new FormError()->getFieldErrors($form->createView()),
        );
    }
}
