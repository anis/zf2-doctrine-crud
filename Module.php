<?php
/**
 * CRUD actions module for Doctrine entities on Zend Framework 2
 *
 * PHP Version 5.4.13
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
namespace DoctrineCRUD;

use Zend\Mvc\MvcEvent;

/**
 * CRUD actions module for Doctrine entities on Zend Framework 2
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class Module
{
    /**
     * Returns the application configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    /**
     * Returns the autoloader configuration
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                ),
            ),
        );
    }

    /**
     * Bootstraps the module
     *
     * Basically, it's here to attach our listeners on important ZF2 events.
     *
     * @return void
     */
    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getEventManager()->attach(MvcEvent::EVENT_RENDER, array($this, 'setDefaultTemplate'));
    }

    /**
     * Sets a default view template
     *
     * In case a custom template has not been defined for the requested action, this method sets a default template
     * provided by this module.
     * This method is triggered by the Event Listener on render.
     *
     * @param \Zend\View\MvcEvent $e View event (triggerer)
     *
     * @eturn void
     */
    public function setDefaultTemplate(MvcEvent $e)
    {
        // Resolve the name of matched controller
        $matchedController = $e->getRouteMatch()->getParam('controller');

        foreach ($e->getApplication()->getConfig()['controllers'] as $controllers) {
            if (array_key_exists($matchedController, $controllers)) {
                $matchedController = $controllers[$matchedController];
                break;
            }
        }

        // Ensure matched controller is part of that module
        if (!is_subclass_of(new $matchedController(), __NAMESPACE__ . '\Controller\CRUDController')) {
            return;
        }

        // Ensure matched action is a CRUD action
        $matchedAction = $e->getRouteMatch()->getParam('action');
        if (!in_array($matchedAction, array('create', 'read', 'update', 'delete'))) {
            return;
        }

        // Get the content view (if it exists)
        $contentView = null;
        foreach ($e->getViewModel()->getChildren() as $view) {
            if ($view->captureTo() === 'content') {
                $contentView = $view;
                break;
            }
        }

        if ($contentView === null) {
            return;
        }

        // Check if the template can be resolved for that view
        $sm = $e->getApplication()->getServiceManager();
        if ($sm->get('ViewResolver')->resolve($contentView->getTemplate(), $sm->get('ViewRenderer')) !== false) {
            return;
        }

        // If not, set a default template
        $contentView->setTemplate('doctrine-crud/crud/'.$matchedAction.'/'.$matchedAction);
    }
}
