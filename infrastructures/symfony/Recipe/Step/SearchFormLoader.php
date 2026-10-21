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

namespace Teknoo\East\CommonBundle\Recipe\Step;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Common\Contracts\Recipe\Step\SearchFormLoaderInterface;

use function array_pop;
use function explode;
use function is_array;
use function is_string;
use function key;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Recipe step to use into a HTTP EndPoint Recipe to load and inject into workplan a form dedicated to
 * search in a objects lists.
 * Symfony implementation for `SearchFormLoaderInterface`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class SearchFormLoader implements SearchFormLoaderInterface
{
    final public const string ANY_TEMPLATE = 'any';

    /**
     * @param array<string, array<string, class-string>> $formsInstances
     */
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly array $formsInstances,
    ) {
    }

    /**
     * Merges the values updated by the Live request (`_live_body.updated`, indexed by dotted paths like
     * `formName.field.subfield`), into the form's values sent in props, to submit them in the same request.
     *
     * @param array<mixed> $values
     * @param array<mixed> $updated
     * @return array<mixed>
     */
    private function mergeLiveUpdatedValues(array $values, array $updated, string $formName): array
    {
        foreach ($updated as $path => $value) {
            if (!is_string($path)) {
                continue;
            }

            if ($formName === $path) {
                if (is_array($value)) {
                    $values = $value;
                }

                continue;
            }

            if (!str_starts_with($path, $formName . '.')) {
                continue;
            }

            $keys = explode('.', substr($path, strlen($formName) + 1));
            $lastKey = array_pop($keys);
            $current = &$values;
            foreach ($keys as $key) {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }

                $current = &$current[$key];
            }

            $current[$lastKey] = $value;
            unset($current);
        }

        return $values;
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

        /** @var Request $sfRequest */
        $sfRequest = $request->getAttribute('request');
        $liveBody = $request->getAttribute('_live_body', []);
        $formName = key($params);

        if (
            'data' === $formName
            && is_array($liveBody)
            && is_array($liveBody['props'])
            && is_string($liveBody['props']['formName'])
        ) {
            $formName = $liveBody['props']['formName'];
        }

        if (
            !is_string($formName)
            || (
                !isset($this->formsInstances[$template][$formName])
                && !isset($this->formsInstances[self::ANY_TEMPLATE][$formName])
            )
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
        $form->handleRequest($sfRequest);

        if (!$form->isSubmitted() && is_array($liveBody)) {
            $values = [];
            if (
                is_array($liveBody['props'] ?? null)
                && is_array($liveBody['props'][$form->getName()] ?? null)
            ) {
                $values = $liveBody['props'][$form->getName()];
            }

            if (is_array($liveBody['updated'] ?? null)) {
                $values = $this->mergeLiveUpdatedValues($values, $liveBody['updated'], $form->getName());
            }

            if (!empty($values)) {
                $form->submit($values);
            }
        }

        $manager->updateWorkPlan([
            'searchForm' => $form
        ]);

        return $this;
    }
}
