<?php

namespace Application\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;

/**
 * 共用 controller
 * Class BaseController
 * @package Application\Controller
 */
class BaseController extends AbstractActionController
{

    /** @var ServiceManager */
    public $sm;
    public function __construct($container)
    {
        $this->sm = $container;
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->sm;
    }



}