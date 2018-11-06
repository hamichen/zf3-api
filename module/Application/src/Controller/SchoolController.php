<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Service\PdoDb;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SchoolController extends BaseController
{
    public function indexAction()
    {
        // 取得 PDO 物件
        $pdo = $this->getServiceManager()->get(PdoDb::class);

        $sql = 'SELECT * FROM semester ORDER BY year desc , semester desc ';

        // 取得 SemesterData Api Service
        $semesterData = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $classData = null;
        if ($this->params()->fromPost('semester_id')) {
            $sql = "SELECT * FROM semester_class WHERE semester_id=".$_POST['semester_id'];
            $classData = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        }

        $viewModel = new ViewModel();

        $viewModel->setVariable('semester', $semesterData);
        $viewModel->setVariable('class', $classData);

        return $viewModel;

    }

    /**
     * 取出學期學生資料
     * @return ViewModel
     */
    public function classAction()
    {
        $id = (int) $this->params()->fromQuery('id');
        $sql = "SELECT a.*, b.* from semester_student a
LEFT JOIN student b ON a.student_id=b.id 
where a.semester_class_id=$id order by a.number";

        $pdo = $this->getServiceManager()->get(PdoDb::class);

        $arr = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $viewModel = new ViewModel();
        $viewModel->setVariable('data', $arr);

        return $viewModel;
    }

}
