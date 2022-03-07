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

namespace Teknoo\East\Website\Doctrine\Translatable;

/**
 * Interface to define object storing translations for object. Each translated field in a object has is dedicated
 * TranslationInterface instance.
 * Instances of this interface are not directly usable by developers, or reader or writer.
 * They are internals objecst used by this Doctrine extension to store translations.
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface TranslationInterface
{
    public function getIdentifier(): string;

    public function setLocale(string $locale): self;

    public function setField(string $field): self;

    public function setObjectClass(string $objectClass): self;

    public function setForeignKey(string $foreignKey): self;

    public function setContent(string $content): self;
}
