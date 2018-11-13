<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/11/13
 * Time: 上午 09:23
 */

namespace Application\Controller;


use Application\Form\ContactForm;
use Application\Service\PdoTest;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TestController extends BaseController
{

    public function indexAction()
    {

        $contactForm = new ContactForm();
       // $contactForm->setAttribute('action', '/test/save');

        if ($this->request->isPost()) {
            $data = $this->params()->fromPost();
            $contactForm->setData($data);

            if ( $contactForm->isValid() )
            {
                $data = $contactForm->getData();
                print_r($data);
                $contactForm->setData($data);
            }

           // var_dump($data);
        }

        $viewModel = new ViewModel();

        $viewModel->setVariable('contactForm', $contactForm);

        return $viewModel;

    }


}