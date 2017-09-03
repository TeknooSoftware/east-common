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

namespace Teknoo\East\WebsiteBundle\Form\Type;

use Gedmo\Translatable\TranslatableListener;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait TranslatableTrait
{
    /**
     * @var TranslatableListener
     */
    private $listenerTranslatable;

    /**
     * @var string
     */
    private $locales;

    /**
     * @param TranslatableListener $listenerTranslatable
     *
     * @return self
     */
    public function setListenerTranslatable(TranslatableListener $listenerTranslatable)
    {
        $this->listenerTranslatable = $listenerTranslatable;

        return $this;
    }

    /**
     * @param string $locales
     *
     * @return self
     */
    public function setLocales(string $locales): TranslatableTrait
    {
        $this->locales = $locales;

        return $this;
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addTranslatableLocaleFieldChoice(FormBuilderInterface $builder)
    {
        $builder->add(
            'translatableLocale',
            ChoiceType::class, [
            'choices' => $this->locales,
            'label' => 'Language version',
        ]);

        return $this;
    }

    /**
     * @param FormBuilderInterface $builder
     *
     * @return self
     */
    protected function addTranslatableLocaleFieldHidden(FormBuilderInterface $builder)
    {
        $builder->add(
            'translatableLocale',
            HiddenType::class
        );

        return $this;
    }

    /**
     * To disable all non translatable field for translated instances.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    protected function disableNonTranslatableField(FormBuilderInterface $builder, $options)
    {
        if (!isset($options['data_class']) || !$this->listenerTranslatable instanceof TranslatableListener) {
            //Need the class attached to this form to retrieve the list of translatable fields
            //Need also the translatable listener of gedmo doctrine extension to check required locale
            return;
        }

        if ($this->listenerTranslatable->getListenerLocale() == $this->listenerTranslatable->getDefaultLocale()) {
            //Check if the form edit the default locale, aka the original local, of the entity,
            // keep all fields are readable
            return;
        }

        //Get the function reference to get it and prevent non implemented function
        $listTranslatableFieldsFunction = [$options['data_class'], 'listTranslatableFields'];
        if (!is_callable($listTranslatableFieldsFunction)) {
            return;
        }

        $translatableFieldList = array_flip($listTranslatableFieldsFunction());

        foreach ($builder->all() as $child) {
            if ($child instanceof FormBuilder) {
                $name = $child->getName();
                if (!isset($translatableFieldList[$name]) && 'translatableLocale' != $name) {
                    $child->setDisabled(true);
                }
            }
        }
    }
}
