<?php
/**
 * Read action
 *
 * Provides methods to read the entries of a specific entity.
 * This trait is expected to be used by \DoctrineCRUD\Controller\CRUDController.
 *
 * PHP Version 5.4.13
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
namespace DoctrineCRUD\Controller;

/**
 * Read action
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
trait ReadAction
{
    /**
     * Read action
     *
     * Retrieves and passes to the view all existing entities.
     *
     * @return array Variables to be passed to the view :
     *               "entities" array List of retrieves entities
     */
    public function readAction()
    {
        // List all the fields to be displayed : all fields except the relations
        $fields = array();

        $metadata = $this->getEM()->getClassMetadata($this->getEntityClass());
        foreach ($metadata->getFieldNames() as $fieldName) {
            if ($metadata->hasAssociation($fieldName)) {
                continue;
            }

            if ($metadata->isIdentifier($fieldName)) {
                array_unshift($fields, $fieldName);
            } else {
                array_push($fields, $fieldName);
            }
        }

        // Collect the data into a simple array
        $data = array();
        foreach ($this->getEM()->getRepository($this->getEntityClass())->findAll() as $entity) {
            $row = array();
            foreach ($fields as $fieldName) {
                $row[$fieldName] = call_user_func(array($entity, 'get'.$fieldName));
            }

            array_push($data, $row);
        }

        return array(
            'entities' => $data,
        );
    }
}
