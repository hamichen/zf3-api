<?php
/**
 *
 *
 * @author    sfs teams <zfsfs.team@gmail.com>
 * @copyright 2010-2018 (http://www.sfs.tw)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://www.sfs.tw
 * Date: 2018/11/5

 */
namespace Application\Controller\Factory;

use Application\Controller\BaseController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Class ControllerFactory
 * @package Application\Controller\Factory
 */
class ControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
//
//       // echo $requestedName;
//        $service = (null === $options) ? new $requestedName : new $requestedName($options);
//        return $service->setServiceManager($container);
        return new $requestedName($container);
    }

}