<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
require( APPPATH.'/libraries/REST_Controller.php');
// use REST_Controller;


class Avalon extends REST_Controller {

    protected $avalon;
    protected $hotelMap;
    
    public function __construct(){
        parent::__construct();
        $this->load->helper('json_utilities');
        $this->load->helper('jwt');
        $this->load->helper('validators');
        $this->load->helper('model_loader');   
        $this->load->database();

        $this->avalon = model_avalon();

        $this->hotelMap = array(
            "goc" => "HD01"
        );
    }
  
    public function getPaquetes_put(){

        $data = $this->put();

        $data['Hotel'] = $this->hotelMap[strtolower($data['Hotel'])] ?? null;

        if( $data['Hotel'] == null ){
            unset($data['Hotel']);
        }
    
        $result = $this->avalon->getPackages( $data );
        
        if( $result['err'] ){
            errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error'] );
        }else{
            okResp($result['msg'], 'data', $result['data']);
        }
    } 
  
}
