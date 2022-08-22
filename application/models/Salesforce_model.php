<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Salesforce_model extends CI_Model{
    
    private $subdomain = "mcsvvr0plh875vgnpspkyhyqy6xy";
    private $clientId = "7a7j6xijj7qcmqei2d3h4j1t";
    private $clientSecret = "vu3NxUFqptGGiVaIJj4Juvbh";
    private $accountId = "100014320";
    
    // TOKEN INFO
    private $tokenData = array();
    private $token = '';
    private $tokenExpire = '';
    protected $i;
    
    function __construct() {
        parent::__construct();
        $this->i = get_instance();
    }
    
    private function getAccessToken(){
        
        if( $this->token != '' ){
            $now = new DateTime();
            if( $now->getTimestamp() < ($this->tokenExpire - 5) ){
                return modelResp( false, 'Token obtenido', 'data', $this->tokenData, 'auth', array( "token" => $this->token, "expires" => $this->tokenExpire ) );
            }
        }
        
        $url = "https://".$this->subdomain.".auth.marketingcloudapis.com/v2/token";
        
        $ch = curl_init();
        
        $arr = array(
                "grant_type"    => "client_credentials",
                "client_id"     => $this->clientId,
                "client_secret" => $this->clientSecret,
                "account_id"    => $this->accountId
            );
        
        curl_setopt( $ch, CURLOPT_URL, $url );
        // curl_setopt( $ch, CURLOPT_USERPWD, $username . '/token:' . $password );
        // curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($arr) );

        $data = curl_exec( $ch );
        
        if( $data == false){
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            return modelResp( true, 'No se pudo conectar para obtener token', 'error', json_decode(curl_error($ch)));
        }else{
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            $this->tokenData = json_decode($data);
            $date = new DateTime();
            $expires = $date->add(new DateInterval('PT'.$this->tokenData->expires_in.'S')); 
            
            $this->token = "Authorization: Bearer ".$this->tokenData->access_token;
            $this->tokenExpire = $expires->getTimestamp();
        
            return modelResp( false, 'Token obtenido', 'data', $this->tokenData, 'auth', array( "token" => $this->token, "expires" => $this->tokenExpire ) );
        }

    }
    
    private function getAccessTokenSF(){
        
        if( $this->token != '' ){
            $now = new DateTime();
            if( $now->getTimestamp() < ($this->tokenExpire - 5) ){
                return modelResp( false, 'Token obtenido', 'data', $this->tokenData, 'auth', array( "token" => $this->token, "expires" => $this->tokenExpire ) );
            }
        }
        
        $username = "jasanchez@oasishoteles.com.cotizadoroasis";
        $password = "@Dyj21278370";
        $token = "bVLiO60RZqvKjQ3Zx3d8fKec1";
        $url = "https://test.salesforce.com/services/oauth2/token?grant_type=password&client_id=3MVG9oZtFCVWuSwN8HgM_C4_S_XQZGQkXt2I34rzr1tisPZt6KKQaBBjbp_RQ2vjxwBBVJl_0nJfHq.3YY4nq&client_secret=05F7A2AAEAB024ABADC26C43A26C449830FE64D2266B99CBAD15583B8CCE3602&username=$username&password=$password$token";
        $ch = curl_init();
        
        $arr = array(
                "grant_type"    => "client_credentials",
                "client_id"     => $this->clientId,
                "client_secret" => $this->clientSecret,
                "account_id"    => $this->accountId
            );
        
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($arr) );

        $data = curl_exec( $ch );
        
        if( $data == false){
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            return modelResp( true, 'No se pudo conectar para obtener token', 'error', json_decode(curl_error($ch)));
        }else{
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            $this->tokenData = json_decode($data);
            $date = new DateTime();
            
            $this->token = "Authorization: Bearer ".$this->tokenData->access_token;

            return modelResp( false, 'Token obtenido', 'data', $this->tokenData, 'auth', array( "token" => $this->token ) );
        }

    }
    
    protected function putData( $url, $arr = null ){
        
        $token = $this->getAccessToken();
        if( $token['err'] ){
            return modelResp( true, $token['msg'], 'error', $token['error'] );
        }
        
        $baseUrl = "https://".$this->subdomain.".rest.marketingcloudapis.com";
        
        $auth = $this->token;
        
        $ch = curl_init();
        
        if( is_array($arr) ){
            $arr = json_encode($arr);
        }
        
        $urlOk = $baseUrl.$url;
        
        curl_setopt( $ch, CURLOPT_URL, $urlOk );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $auth ));
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $arr);

        $data = curl_exec( $ch );
        
        if( $data == false){
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            return modelResp( true, 'No se pudo conectar con la api', 'error', json_decode(curl_error($ch)));
        }else{
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            return modelResp( false, 'Api '.$url." exitosa", 'data', $data, 'response', $status );
        }
    }
    
    protected function getDataSF( $url, $arr = null, $post = false, $service = 'apexrest' ){
        
        $token = $this->getAccessTokenSF();
        if( $token['err'] ){
            return modelResp( true, $token['msg'], 'error', $token['error'] );
        }
        
        $baseUrl = "https://oasishoteles--cotizador.my.salesforce.com/services/$service/";
        
        $auth = $this->token;
        
        $ch = curl_init();
        
        if( is_array($arr) ){
            $arr = json_encode($arr);
        }
        
        $urlOk = $baseUrl.$url;
        
        curl_setopt( $ch, CURLOPT_URL, $urlOk );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $auth ));
        
        if( $post ){
            curl_setopt( $ch, CURLOPT_POST, 1);
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $arr);
        }

        $data = curl_exec( $ch );
        
        if( $data == false){
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            return modelResp( true, 'No se pudo conectar con la api', 'error', json_decode(curl_error($ch)));
        }else{
            $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
            curl_close( $ch );
            
            return modelResp( false, 'Api '.$url." exitosa", 'data', $data, 'response', $status );
        }
    }
    
    private function dataExtension( $data ){
        
        $key = "CE37B108-8D78-4F77-8C54-D2E36CA6B609";
        $url = "/data/v1/async/dataextensions/key:$key/rows";
        
        $mapping = array(
                "Email" => "correo",
                "ID_ORewards" => "id",
                "Nombre" => "nombre",
                "Apellido" => "apellido",
                "Nivel_orewards" => "nombre_del_nivel",
                "total_rsvas_disfrutadas" => "total_rsvas_disfrutadas",
                "total_noches_disfrutadas" => "total_noches_disfrutadas",
                "fecha_ultima_estancia" => "total_ultima_estancia",
                "noches_next" => "noches_next",
                "Hotel" => "hotelesVisitados",
                "total_ttv_gastado" => "total_ttv_gastado",
                "ttv_next" => "ttv_next",
                "Pais" => "pais",
                "Idioma" => "idioma",
                "Descuento" => "descuento",
                "Fecha_nacimiento" => "fecha_nacimiento",
            );
        
        $arrayInsert = array( "items" => array());
        
        foreach( $data as $i => $info ){
            
            $tmp = array();
            foreach( $mapping as $field => $db ){
                $tmp[$field] = $info[$db];
            }
            
            array_push($arrayInsert['items'], $tmp);
            
        }
        
        $result = $this->putData( $url, $arrayInsert );
        
        if( $result['err'] ){
            return modelResp( true, $result['msg'], 'error', $result['error'] );
        }else{
            return modelResp( false, $result['msg'], 'data', $result['data'], 'response', $result['response'] );
        }
        
    }
    
    private function getSfId( $tipo ){
        if( !isset($_GET['usid']) ){
            errResp('No se obtuvo id del agente por lo que no se pudo encontrar id en salesforce', REST_Controller::HTTP_BAD_REQUEST, 'error', array() );
        }
        
        $this->i->db->select('salesforceId_'.$tipo)->from('Asesores')->where('id', $_GET['usid']);
        
        if( $sfidq = $this->i->db->get() ){
            $sfidr = $sfidq->row_array();
            
            $sfid = $sfidr['salesforceId_'.$tipo];
            
            if( $sfid == null ){
                errResp('No existe un id en salesforce del tipo "'.$tipo.'" asociado al agente actual. Verifica con tu administrador', REST_Controller::HTTP_BAD_REQUEST, 'error', array() );
            }
            
            return $sfid;
        }else{
            errResp('Error al obtener ID de salesforce', REST_Controller::HTTP_BAD_REQUEST, 'error', $this->i->db->error() );
        }
    }
    
    private function validateTipo( $t, $u = false ){
        $tipo = strtolower($t);
        
        switch( $tipo ){
            case 'boda':
            case 'bodas':
                return $u ? 'Bodas' : 'boda';
            case 'grupo':
            case 'grupos':
                return $u ? 'Grupos' : 'grupo';
            default:
                errResp('El tipo ingresado no es valido', REST_Controller::HTTP_BAD_REQUEST, 'error', array() );
        }
    }
    
    public function updateOR( $data ){
        
        $result = $this->dataExtension( $data );
          
        if( $result['err'] ){
            return modelResp( true, $result['msg'], 'error', $result['error'] );
        }else{
            return modelResp( false, $result['msg'], 'data', $result['data'], 'response', $result['response'] );
        }
    }
    
    public function sfCatalogs(){
        
        $url = "getPickListValues";
        
        $result = $this->getDataSF( $url );
          
        if( $result['err'] ){
            return modelResp( true, $result['msg'], 'error', $result['error'] );
        }else{
            return modelResp( false, $result['msg'], 'data', json_decode($result['data'], true), 'response', $result['response'] );
        }
    }
    
    public function updateSfCatalogs(){
        $cats = $this->sfCatalogs();
        
        $map = array(
                "Contact-Nacionalidad__c"                       => "cycoasis_cats.sf_pais",
                "Account-Nacionalidad_lt__c"                    => "cycoasis_cats.sf_nacionalidad",
                "Opportunity-OA_Mercado__c"                     => "cycoasis_cats.sf_mercado",
                "Opportunity-CurrencyIsoCode"                   => "cycoasis_cats.sf_currency",
                "Opportunity-Idioma__c"                         => "cycoasis_cats.sf_idioma",
                "Habitaciones__c-Tipo_de_ocupaci_n__c"          => "cycoasis_cats.sf_ocupacion"
            );
            
        if( $cats['err'] ){
            return modelResp( true, $cats['msg'], 'error', $cats['error'] );
        }else{
            $counter = 0;
            $errors = array();
            foreach( $cats['data']['FieldPerObject'] as $i => $obj ){
                if( isset($map[$obj['obj']."-".$obj['field']]) ){
                    if( $this->i->db->insert_on_duplicate_update_batch($map[$obj['obj']."-".$obj['field']], $obj['values']) ){
                        $counter++;
                    }else{
                        array_push($errors,$map[$obj['obj']."-".$obj['field']]);
                    }
                }
            }

            if( $counter > 0 ){
                if(count($errors) > 0){
                    $err = ". Hubieron algunos errores";
                }else{
                    $err = ".";
                }
                
                okResp( "Catalogos actualizados$err", 'data', $cats, 'errores', $errors);
            }            
        }
    }
    
    public function searchSfUsertOportunities( $params ){
        
        $url = "v55.0/query/?q=";
   
        $tipos = array('Bodas', 'Grupos');

        $results = array('Bodas' => array(), 'Grupos' => array());
        
        foreach( $tipos as $i => $t ){
            $params['idComercial'] = $this->getSfId( $this->validateTipo($t) );
            $defaultQuery = "SELECT+"
                            ."createddate,id,name,accountId,LeadSource,Socio_comercial__c,OA_Mercado__c,Idioma__c,Hotel_evento__c,Fecha_inicio_estancia__c,Fecha_fin_estancia__c,"
                            ."CloseDate,CurrencyIsoCode,StageName,OA_No_Pax__c,RecordTypeId,OwnerId,Observaciones__c,OA_Fecha_Boda__c+"
                            ."from+Opportunity+"
                            ."WHERE+RecordType.developername=%27".$t."%27+AND+Socio_comercial__c=%27".$params['idComercial']."%27+AND+createddate>LAST_N_YEARS:2";
    
            $result = $this->getDataSF( $url.$defaultQuery, $params, false, 'data' );
              
            if( $result['err'] ){
                return modelResp( true, $result['msg'], 'error', $result['error'] );
            }

            $data = json_decode($result['data'], true);
            $results[$t] = $data['records'] ?? array();
            //$results[$t] = $data;
        }

        return modelResp( false, 'Consulta Exitosa', 'data', $results, true);


    }
    
    public function searchSfOportunity( $params ){
        
        $url = "getOpportunities";
        
        if( !isset($params['tipo']) || $params['tipo'] == null ){
            errResp('No se obtuvo el tipo de oportunidad', REST_Controller::HTTP_BAD_REQUEST, 'error', array() );
        }
        
        $params['tipo'] = $this->validateTipo($params['tipo']);
        
        $params['idComercial'] = $this->getSfId( $params['tipo'] );
        // $params['idComercial'] = '0001cotizador';
        
        $result = $this->getDataSF( $url, $params, true );
          
        if( $result['err'] ){
            return modelResp( true, $result['msg'], 'error', $result['error'] );
        }else{
            return modelResp( false, $result['msg'], 'data', json_decode($result['data'], true) );
        }

    }
    
    public function updateSfOportunity( $p ){
        
        if( !isset($p['create']) ){
            return modelResp( true, 'No obtuvo el parametro diferenciador entre creacion y actualizacion', 'error', $result['error'] );
        }
        
        if( !isset($p['params']) ){
            return modelResp( true, 'No se obtuvieron los datos para '.$p['create'] ? 'crear' : 'actualizar'.' la oportunidad', 'error', $result['error'] );
        }
        
        $params = $p['params'];
        
        $url = "postOpportunity";
        
        if( !isset($params['TipoDeOportunidad']) || $params['TipoDeOportunidad'] == null ){
            errResp('No se obtuvo el tipo de oportunidad aqui', REST_Controller::HTTP_BAD_REQUEST, 'error', array() );
        }
        
        $params['TipoDeOportunidad'] = $this->validateTipo($params['TipoDeOportunidad'], true);

        $params['SocioComercialId'] = $this->getSfId( $this->validateTipo($params['TipoDeOportunidad']) );
        
        if( $p['create'] ){
            $params['OwnerId'] = $params['TipoDeOportunidad'] == 'Bodas' ? '3D00G3C0000032bLr' : '3D00G3C0000032bLw';
        }

        $result = $this->getDataSF( $url, $params, true );
          
        if( $result['err'] ){
            return modelResp( true, $result['msg'], 'error', $result['error'] );
        }else{
            return modelResp( false, $result['msg'], 'data', json_decode($result['data'], true), 'extra', $params );
        }
    }
    
    public function sendRoom( $params ){
        
        // if( !isset($p['create']) ){
        //     return modelResp( true, 'No obtuvo el parametro diferenciador entre creacion y actualizacion', 'error', $result['error'] );
        // }
        
        // if( !isset($p['params']) ){
        //     return modelResp( true, 'No se obtuvieron los datos para '.$p['create'] ? 'crear' : 'actualizar'.' la oportunidad', 'error', $result['error'] );
        // }
        
        // $params = $p['params'];
        
        $url = "createRooms";
        
        $err = '';
        
        !isset($params['idOportunidad']) ? ($err .= ($err == '' ? '' : ', ')."No se obtuvo el tipo id de la oportunidad") : $err.='';
        
        if( $err != '' ){
            return modelResp( true, $err, 'error', $params );
        }
        
        $result = $this->getDataSF( $url, $params, true );
          
        if( $result['err'] ){
            return modelResp( true, $result['msg'], 'error', $result['error'] );
        }else{
            return modelResp( false, $result['msg'], 'data', json_decode($result['data'], true), 'extra', $params );
        }
        
    }

}