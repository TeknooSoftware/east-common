<?php

/*
 * East Website.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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
use Teknoo\East\Website\Contracts\Recipe\Step\SearchFormLoaderInterface;

use function is_array;
use function key;

/**
 * Recipe step to use into a HTTP EndPoint Recipe to load and inject into workplan a form dedicated to
 * search in a objects lists.
 * Symfony implementation for `SearchFormLoaderInterface`.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class SearchFormLoader implements SearchFormLoaderInterface
{
    final public const ANY_TEMPLATE = 'any';

    /**
     * @param array<string, array<string, class-string>> $formsInstances
     */
    public function __construct(
        private FormFactoryInterface $formFactory,
        private array $formsInstances,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        ManagerInterface $manager,
        string $template
    ): SearchFormLoader {
        $params = $request->getParsedBody();
        if (!is_array($params)) {
            return $this;
        }

        $instances = &$this->formsInstances;
        if (!isset($instances[$template]) && !isset($instances[self::ANY_TEMPLATE])) {
            return $this;
        }

        $formName = key($params);

        if (
            !isset($instances[$template][$formName])
            && !isset($instances[self::ANY_TEMPLATE][$formName])
        ) {
            return $this;
        }

        $formClass = $instances[$template][$formName] ?? $instances[self::ANY_TEMPLATE][$formName];
        $form = $this->formFactory->create(
            $formClass,
            null,
            [
                'manager' => $manager
            ]
        );

        $form->handleRequest($request->getAttribute('request'));

        $manager->updateWorkPlan([
            'searchForm' => $form
        ]);

        return $this;
    }
}
