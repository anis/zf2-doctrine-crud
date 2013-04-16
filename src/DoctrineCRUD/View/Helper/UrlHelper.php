<?php
/**
 * URL helper
 *
 * Provides methods to edit current route and generate corresponding URL
 *
 * PHP Version 5.4.13
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
namespace DoctrineCRUD\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * URL helper
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class UrlHelper extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /**
     * Service locator
     *
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * {@inheritdoc}
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator->getServiceLocator();
    }

    /**
     * Edits current route match with the given parameters and returns the corresponding URL
     *
     * @param integer $page Page index (starting from 1)
     *
     * @return string
     */
    public function __invoke($page)
    {
        // Get some tools
        $plugins = $this->getServiceLocator()->get('ControllerPluginManager');
        $routeMatch = $this->getServiceLocator()->get('application')->getMvcEvent()->getRouteMatch();

        // Build the appropriate params and options
        $params = $routeMatch->getParams();
        $options = array(
            'query' => $plugins->get('params')->fromQuery(),
        );

        if (array_key_exists('page', $params)) {
            $params['page'] = $page;
        } else {
            $options['query']['page'] = $page;
        }

        // Generate the URL
        return $plugins->get('url')->fromRoute($routeMatch->getMatchedRouteName(), $params, $options);
    }
}
