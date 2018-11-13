<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Service\PdoDb;
use Application\Service\PdoTest;
use Application\TcApi\SemesterData;
use Application\TcApi\TcApi;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

class IndexController extends BaseController
{
    public function indexAction()
    {

//        /** @var  $request \Zend\Http\PhpEnvironment\Request */
//        $request = $this->getServiceManager()->get('Request');
//        echo $request->getEnv('PHP_VERSION');

//        $config = $this->getServiceManager()->get('Config');
//        $db =$config['db'];
//
//        $dsn = 'mysql:host='.$db['host'].';dbname='.$db['dbname'].';charset='.$db['charset'];
//
//        $dbh = new \PDO( $dsn, $db['user'] , $db['password']);


        /** @var  $dbh \PDO */
        $dbh = $this->getServiceManager()->get('pdodb');

        $sql = "SELECT * FROM student";
        $arr = $dbh->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $viewModel = new ViewModel();

        $viewModel->setVariable('data', $arr);

        return $viewModel;

    }

    public function testAction()
    {
//       $tcApi = new SemesterData($this->sm);
//        $arr = $tcApi->syncData();
//        echo '<pre>';
//        print_r($arr);

        $tcApi = $this->sm->get('semester_data');
        $arr = $tcApi->syncData();


        echo '<pre>';
        print_r($arr);

        exit;
    }

    public function helpAction()
    {

        $arr = [
            'abc' =>'123456',
            'def' => '567880'
        ];

        $viewModel = new ViewModel();

        $viewModel->setVariable('data', $arr);

        return $viewModel;

    }

    public function barcodeAction()
    {

        $type = $this->params()->fromRoute('type');
        echo "type :".$type;
        $label  = $this->params()->fromRoute('label');
        echo "label: ". $label;
        $abc = $this->params()->fromQuery('abc');
        echo "abc: ". $abc;

        print_r($this->params()->fromQuery());
    }


    public function showUsersAction()
    {
        //$verbose = $this->request->getParam('verbose') ;
        $classId = $this->params()->fromRoute('class_id');

        $sql = "select * from semester_class where grade = $classId";
        echo $sql;
        $pdo = $this->sm->get(PdoTest::class);
        $arr = $pdo->query($sql)->fetchAll();

        print_r($arr);

    }
}
