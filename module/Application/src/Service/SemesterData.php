<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/11/6
 * Time: 上午 11:32
 */
namespace Application\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * 同步學期資料
 * Class SemesterData
 * @package Application\Service
 */

class SemesterData implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $apiRes = new \Application\TcApi\SemesterData($container);
        return $apiRes;
    }

    public function test()
    {
        echo 'test';
    }
}