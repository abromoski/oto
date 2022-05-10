<?php
namespace Magetracer\Zottopay\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_configScopeConfigInterface;

    public function __construct(Context $context)
    {
        $this->_configScopeConfigInterface = $context->getScopeConfig();

        parent::__construct($context);
    }
    
	
	/**
     * post log
     */
    public function postLog($logType, $data){
    
        $filedate   = date('Y-m-d');
        $newfile    = fopen(  dirname(dirname(__FILE__)) . "/magetracer_log/" . $filedate . ".log", "a+" );      
        $return_log = date('Y-m-d H:i:s') . $logType . "\r\n";  
        foreach ($data as $k=>$v){
            $return_log .= $k . " = " . $v . "\r\n";
        }   
        $return_log .= '*****************************************' . "\r\n";
        $return_log = $return_log.file_get_contents( dirname(dirname(__FILE__)) . "/magetracer_log/" . $filedate . ".log");     
        $filename   = fopen( dirname(dirname(__FILE__)) . "/magetracer_log/" . $filedate . ".log", "r+" );      
        fwrite($filename,$return_log);
        fclose($filename);
        fclose($newfile);
    
    }
	

    /**
     *  JS 
     *
     */
    public function getParentLocationReplace($url)
    {
        echo '<script type="text/javascript">parent.location.replace("'.$url.'");</script>';
    }
    
}