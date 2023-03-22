<?php

/*
 * East Common.
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/common Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;

use function is_array;
use function key;

/**
 * Recipe step to use into a HTTP EndPoint Recipe to load and inject into workplan a form dedicated to
 * search in a objects lists.
 * Symfony implementation for `SearchFormLoaderInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
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
        private readonly FormFactoryInterface $formFactory,
        private readonly array $formsInstances,
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

        if (!isset($this->formsInstances[$template]) && !isset($this->formsInstances[self::ANY_TEMPLATE])) {
            return $this;
        }

        $formName = key($params);

        if (
            !isset($this->formsInstances[$template][$formName])
            && !isset($this->formsInstances[self::ANY_TEMPLATE][$formName])
        ) {
            return $this;
        }

        $fClass = $this->formsInstances[$template][$formName] ?? $this->formsInstances[self::ANY_TEMPLATE][$formName];
        $form = $this->formFactory->create(
            $fClass,
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
