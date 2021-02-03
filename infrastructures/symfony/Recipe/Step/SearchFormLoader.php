<?php

/*
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

declare(strict_types=1);

namespace Teknoo\East\WebsiteBundle\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Contracts\Form\SearchFormInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\SearchFormLoaderInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class SearchFormLoader implements SearchFormLoaderInterface
{
    public const ANY_TEMPLATE = 'any';

    private FormFactoryInterface $formFactory;

    /**
     * @var array<string, array<string, class-string>>
     */
    private array $formsInstances;

    /**
     * @param array<string, array<string, class-string>> $formsInstances
     */
    public function __construct(FormFactoryInterface $formFactory, array $formsInstances)
    {
        $this->formFactory = $formFactory;
        $this->formsInstances = $formsInstances;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ManagerInterface $manager,
        string $template
    ): SearchFormLoader {
        $instances = &$this->formsInstances;
        if (!isset($instances[$template]) && !isset($instances[static::ANY_TEMPLATE])) {
            $manager->error(
                new \DomainException("No form allowed for the template $template", 403),
            );

            return $this;
        }

        $params = $request->getQueryParams();
        $formName = \key($params);

        if (
            !isset($instances[$template][$formName])
            && !isset($instances[static::ANY_TEMPLATE][$formName])
        ) {
            $manager->error(
                new \DomainException("The form $formName is not allowed for the template $template", 403)
            );

            return $this;
        }

        $formClass = $instances[$template][$formName] ?? $instances[static::ANY_TEMPLATE][$formName];
        $form = $this->formFactory->create($formClass);

        if (!$form instanceof SearchFormInterface) {
            $manager->error(new \RuntimeException("$formClass must implement SearchFormInterface", 500));
            return $this;
        }

        $manager->updateWorkPlan([SearchFormInterface::class => $form]);

        return $this;
    }
}
