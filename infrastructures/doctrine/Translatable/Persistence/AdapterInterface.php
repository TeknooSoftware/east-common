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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Persistence;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Teknoo\East\Website\Doctrine\Translatable\TranslationInterface;
use Teknoo\East\Website\Doctrine\Translatable\Wrapper\WrapperInterface;

interface AdapterInterface
{
    public function loadTranslations(
        WrapperInterface $wrapped,
        string $locale,
        string $translationClass,
        string $objectClass,
        callable $callback
    ): AdapterInterface;

    public function findTranslation(
        WrapperInterface $wrapped,
        string $locale,
        string $field,
        string $translationClass,
        string $objectClass,
        callable $callback
    ): AdapterInterface;

    public function removeAssociatedTranslations(
        WrapperInterface $wrapped,
        string $translationClass,
        string $objectClass
    ): AdapterInterface;

    public function insertTranslationRecord(TranslationInterface $translation): AdapterInterface;

    public function updateTranslationRecord(
        WrapperInterface $wrapped,
        ClassMetadata $metadata,
        string $field,
        TranslationInterface $translation
    ): AdapterInterface;

    /**
     * @param mixed $value
     */
    public function setTranslationValue(
        WrapperInterface $wrapped,
        ClassMetadata $metadata,
        string $field,
        $value
    ): AdapterInterface;
}
