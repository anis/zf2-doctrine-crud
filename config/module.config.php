<?php
/**
 * Module configuration
 *
 * PHP Version 5.4.13
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
namespace DoctrineCRUD;

return array(
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

    'view_helpers' => array(
        'factories' => array(
            'page' => function($sm) {
                $helper = new View\Helper\UrlHelper();
                return $helper->setServiceLocator($sm->getServiceLocator());
            },
        ),
    ),
);
