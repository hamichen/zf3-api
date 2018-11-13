<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\Console\Service\ConsoleAdapterFactory;
use Zend\Mvc\MvcEvent;
use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;

class Module
{
    const VERSION = '3.0.3-dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getConsoleUsage(ConsoleAdapterInterface $console)
    {
        return array(
            'show <class_id>'    => 'show a class',
            array('class_id', 'class id'),

        );
    }
}
