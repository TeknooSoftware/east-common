<?php

declare(strict_types=1);

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MockDoctrineType extends ChoiceType
{
    public function configureOptions(OptionsResolver $resolver): self
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'class' => '',
            'query_builder' => '',
        ));

        return $this;
    }
}