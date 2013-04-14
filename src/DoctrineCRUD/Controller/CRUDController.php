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
    use ReadAction;

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

    /**
     * Formats the given name to a nice and displayable name
     *
     * This method detects and separates each word from the given string, set them to lowercase, capitalize the first
     * letter and returns that now-fancy string.
     *
     * What is here called a word is any group of letters preceeded by an underscore or began by a capital letter.
     *
     * Examples :
     * - youAreTheBest => You are the best
     * - you_are_super_cool => You are super cool
     * - thankYou_forUsingMy_module => Thank you for using my module
     *
     * @param string $str String to be formatted
     *
     * @return string
     *
     * @throws InvalidArgumentException if the given parameter is not a string
     */
    public function formatName($str)
    {
        // Check the parameter type
        if (!is_string($str)) {
            throw new \InvalidArgumentException(sprintf('$str should be a string, got "%s" instead', gettype($str)));
        }

        // Separate each word (per underscores, then per capital letters)
        $words = array();
        foreach (explode('_', $str) as $particle) {
            foreach (preg_split('`(?=[A-Z])`', $particle, -1, \PREG_SPLIT_NO_EMPTY) as $word) {
                array_push($words, $word);
            }
        }

        // Give it some makeup!
        return ucfirst(strtolower(implode(' ', $words)));
    }
}
