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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\East\WebsiteBundle\AdminEndPoint;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

trait AdminFormTrait
{
    /**
     * @var string
     */
    private $formClass;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var string
     */
    private $viewPath;

    /**
     * @param string $viewPath
     * @return self
     */
    public function setViewPath(string $viewPath)
    {
        $this->viewPath = $viewPath;

        return $this;
    }

    /**
     * @param string $formClass
     * @return self
     */
    public function setFormClass(string $formClass)
    {
        if (!\class_exists($formClass)) {
            throw new \LogicException("Error the class $formClass is not available");
        }

        $this->formClass = $formClass;

        return $this;
    }

    /**
     * @param FormFactory $formFactory
     * @return self
     */
    public function setFormFactory(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;

        return $this;
    }

    /**
     * @param null|mixed $data
     * @param array $options
     * @return FormInterface
     */
    private function createForm(
        $data = null,
        array $options = array()
    ) : FormInterface {
        return $this->formFactory->create($this->formClass, $data, $options);
    }
}