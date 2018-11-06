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
 * 資料庫連線 PDO 物件
 * Class PdoDb
 * @package Application\Service
 */
class PdoDb implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $db =$config['db'];

        $dsn = 'mysql:host='.$db['host'].';dbname='.$db['dbname'].';charset='.$db['charset'];

        $dbh = new \PDO( $dsn, $db['user'] , $db['password']);

        return $dbh;
    }
}