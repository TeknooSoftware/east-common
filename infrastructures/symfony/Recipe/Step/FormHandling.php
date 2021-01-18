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
use Symfony\Component\Form\FormInterface;
use Teknoo\East\Foundation\Manager\ManagerInterface;
use Teknoo\East\Website\Contracts\Recipe\Step\FormHandlingInterface;
use Teknoo\East\Website\Object\ObjectInterface;
use Teknoo\East\Website\Object\PublishableInterface;
use Teknoo\East\Website\Service\DatesService;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class FormHandling implements FormHandlingInterface
{
    private DatesService $datesService;

    private FormFactoryInterface $formFactory;

    public function __construct(DatesService $datesService, FormFactoryInterface $formFactory)
    {
        $this->datesService = $datesService;
        $this->formFactory = $formFactory;
    }

    /**
     * @param array<string, mixed> $options
     * @return FormInterface<mixed>
     */
    private function createForm(
        string $formClass,
        ObjectInterface $data,
        array $options = array()
    ): FormInterface {
        return $this->formFactory->create($formClass, $data, $options);
    }

    public function __invoke(
        ServerRequestInterface $request,
        ManagerInterface $manager,
        string $formClass,
        array $formOptions,
        ObjectInterface $object
    ): FormHandlingInterface {
        $parsedBody = (array) $request->getParsedBody();
        if (
            $object instanceof PublishableInterface
            && isset($parsedBody['publish'])
            && \is_callable([$object, 'setPublishedAt'])
        ) {
            $this->datesService->passMeTheDate([$object, 'setPublishedAt']);
        }

        $form = $this->createForm($formClass, $object, $formOptions);
        $form->handleRequest($request->getAttribute('request'));

        $manager->updateWorkPlan([
            FormInterface::class => $form,
        ]);

        return $this;
    }
}
