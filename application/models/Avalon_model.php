<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Avalon_model extends CI_Model{
    
    private $baseUrl = "https://api.antfor.com:20443/AWWebAPI_OASIS/";
    private $apiKey = "250a825d89b14fdb9d54100ea55e0fad6c5f1e8bafaa47b4b1fadfc72816c5c1";
    
    protected $i;
    
    function __construct() {
        parent::__construct();
        $this->i = get_instance();
    }

    protected function getData( $url, $arr = null, $post = false ){
        
        $ch = curl_init();
        
        if( is_array($arr) ){
            $arr = json_encode($arr);
        }
        
        $urlOk = $this->baseUrl.$url;
        
        curl_setopt( $ch, CURLOPT_URL, $urlOk );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('x-api-key', $this->apiKey ));
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $arr);
        
        if( $post ){
            curl_setopt( $ch, CURLOPT_POST, 1);
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST");
        }

        $data = curl_exec( $ch );
        
        if( $data == false){
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            $error = json_decode(curl_error($ch));
            curl_close( $ch );
            return modelResp( true, 'No se pudo conectar con la api', 'error', array($data, $status, $_SERVER, $urlOk, $error));
        }else{
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            return modelResp( false, 'Api '.$url." exitosa", 'data', $data, 'response', $status );
        }
    }

    public function getPackages( $arr = array() ){

        if( !isset( $arr['Hotel'] ) ){
            return modelResp( true, 'No se obtuvo el parametro del hotel a buscar. Revisa la informacion solicitada', 'error', $arr );
        }

        $apiUrl = 'api/aw/oasis/GetPackages';

        return $this->getData( $apiUrl, $arr, false );
    }

}