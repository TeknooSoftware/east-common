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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/east/website Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\East\Website\Doctrine\Translatable\Wrapper;

use Teknoo\East\Website\Doctrine\Translatable\ObjectManager\AdapterInterface as ManagerAdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\Persistence\AdapterInterface;
use Teknoo\East\Website\Doctrine\Translatable\TranslationInterface;

/**
 * Interface to help this extension to work evenly with Doctrine Document and Doctrine Entity.
 * Implementations of this interface must redirect calls to they wrapped object or class metadata.
 *
 * This interface defines method to update value in wrapped object, manipulate data in the object's manager (according
 * to its implementations/technology) or manage `TranslationInterface` instances linked to the wrapped object.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface WrapperInterface
{
    public function setPropertyValue(string $name, mixed $value): WrapperInterface;

    public function setObjectPropertyInManager(ManagerAdapterInterface $manager, string $name): WrapperInterface;

    public function updateTranslationRecord(
        TranslationInterface $translation,
        string $name,
        mixed $type
    ): WrapperInterface;

    public function linkTranslationRecord(TranslationInterface $translation): WrapperInterface;

    public function loadAllTranslations(
        AdapterInterface $adapter,
        string $locale,
        string $translationClass,
        string $objectClass,
        callable $callback
    ): WrapperInterface;

    public function findTranslation(
        AdapterInterface $adapter,
        string $locale,
        string $field,
        string $translationClass,
        string $objectClass,
        callable $callback
    ): WrapperInterface;

    public function removeAssociatedTranslations(
        AdapterInterface $adapter,
        string $translationClass,
        string $objectClass
    ): WrapperInterface;
}
