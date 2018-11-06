<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2018/10/3
 * Time: 下午 02:18
 */
namespace Application\TcApi;

class TcApi
{

    public $method;

    protected $_ch;

    public $apiName;

    protected $apiConfig = [];

    /** @var \Zend\ServiceManager\ServiceManager */
    protected $sm;

    public function __construct($serviceManage)
    {
        $this->sm = $serviceManage;
        $config = $this->sm->get('Config');

        $this->apiConfig = $config['api'];
        // 建立 CURL 連線
        $this->_ch = curl_init();

    }

    /**
     * get token
     * @return string
     */
    public function getToken()
    {
       

// 取 access token
        curl_setopt($this->_ch, CURLOPT_URL, $this->apiConfig['url'] . "/oauth?authorize");

// 設定擷取的URL網址
        curl_setopt($this->_ch, CURLOPT_POST, TRUE);

// the variable
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, TRUE);


        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, array(
            'client_id' => $this->apiConfig['client_id'],
            'client_secret' => $this->apiConfig['client_secret'],
            'grant_type' => 'client_credentials'
        ));

        $data = curl_exec($this->_ch);
        $data = json_decode($data);
        //print_r($data);

        $access_token = $data->access_token;



        $authorization = "Authorization: Bearer " . $access_token;

        return $authorization;
    }

    public function setMethod($method){
        $this->method = $method;
    }



    public function getData($data = null)
    {
        $authorization = $this->getToken();
//        echo self::API_URL.$apiName;
        curl_setopt($this->_ch, CURLOPT_URL, $this->apiConfig['url'].'/'.$this->apiName);
        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // **Inject Token into Header**
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($this->_ch, CURLOPT_POST, TRUE);
        if ($data) {
            $data = json_encode($data);
            curl_setopt($this->_ch, CURLOPT_POST, TRUE);
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $data);
        }

//curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($this->_ch);

        $arr = json_decode($result);

        return $arr;
    }
}