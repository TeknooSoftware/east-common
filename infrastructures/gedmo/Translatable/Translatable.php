<?php

namespace Teknoo\East\Website\Gedmo\Translatable;

/**
 * This interface is not necessary but can be implemented for
 * Entities which in some cases needs to be identified as
 * Translatable
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface Translatable
{
    // use now annotations instead of predefined methods, this interface is not necessary

    /**
     * @Teknoo\East\Website\Gedmo:TranslationEntity
     * to specify custom translation class use
     * class annotation @Teknoo\East\Website\Gedmo:TranslationEntity(class="your\class")
     */

    /**
     * @Teknoo\East\Website\Gedmo:Translatable
     * to mark the field as translatable,
     * these fields will be translated
     */

    /**
     * @Teknoo\East\Website\Gedmo:Locale OR @Teknoo\East\Website\Gedmo:Language
     * to mark the field as locale used to override global
     * locale settings from TranslatableListener
     */
}
