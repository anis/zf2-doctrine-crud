<?php
/**
 * CRUD controller
 *
 * Provides basic CRUD actions for a given entity
 *
 * PHP Version 5.4.13
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
namespace DoctrineCRUD\Controller;

use Zend\Mvc\Controller\AbstractActionController;

/**
 * CRUD controller
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
abstract class CRUDController extends AbstractActionController
{
    /**
     * Returns the name of the entity class this controller is related to
     *
     * @return string
     */
    abstract public function getEntityClass();

    /**
     * Gets the Entity Manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEM()
    {
        return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    }
}
