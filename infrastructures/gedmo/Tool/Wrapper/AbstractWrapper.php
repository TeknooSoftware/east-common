<?php

namespace Teknoo\East\Website\Gedmo\Tool\Wrapper;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\Persistence\ObjectManager;
use Teknoo\East\Website\Gedmo\Tool\WrapperInterface;
use Teknoo\East\Website\Gedmo\Exception\UnsupportedObjectManagerException;

/**
 * Wraps entity or proxy for more convenient
 * manipulation
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
abstract class AbstractWrapper implements WrapperInterface
{
    /**
     * Object metadata
     *
     * @var object
     */
    protected $meta;

    /**
     * Wrapped object
     *
     * @var object
     */
    protected $object;

    /**
     * Object manager instance
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * List of wrapped object references
     *
     * @var array
     */
    private static $wrappedObjectReferences;

    /**
     * Wrap object factory method
     *
     * @param object        $object
     * @param ObjectManager $om
     *
     * @throws \Teknoo\East\Website\Gedmo\Exception\UnsupportedObjectManagerException
     *
     * @return \Teknoo\East\Website\Gedmo\Tool\WrapperInterface
     */
    public static function wrap($object, ObjectManager $om)
    {
        if ($om instanceof DocumentManager) {
            return new MongoDocumentWrapper($object, $om);
        }
        throw new UnsupportedObjectManagerException('Given object manager is not managed by wrapper');
    }

    public static function clear()
    {   
        self::$wrappedObjectReferences = array();
    }

    /**
     * {@inheritDoc}
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata()
    {
        return $this->meta;
    }

    /**
     * {@inheritDoc}
     */
    public function populate(array $data)
    {
        foreach ($data as $field => $value) {
            $this->setPropertyValue($field, $value);
        }

        return $this;
    }
}
