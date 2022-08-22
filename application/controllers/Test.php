<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
require( APPPATH.'/libraries/REST_Controller.php');
// use REST_Controller;



class Test extends REST_Controller {

  public function __construct(){

    parent::__construct();
    $this->load->helper('json_utilities');
    $this->load->helper('jwt');
    $this->load->helper('validators');
    $this->load->helper('model_loader');
    $this->load->database();
  }
  
  public function server_get(){
      okResp('Datos de Server', 'data', $_SERVER);
  }

  public function ping_get(){
    $ip = "172.217.170.174";
    exec("ping -c 3 $ip", $output, $result);
    print_r($output);
  }
  
  public function image_get(){
      // Crear una imagen de 100*30
        $im = imagecreate(500, 30);
        
        // Fondo blanco y texto azul
        $fondo = imagecolorallocate($im, 255, 255, 255);
        $color_texto = imagecolorallocate($im, 0, 0, 255);
        
        // Escribir la cadena en la parte superior izquierda
        $out = "<table><tr><td>There will be data</td></tr></table>";
        imagestring($im, 5, 0, 0, $out, $color_texto);
        
        // Imprimir la imagen
        header('Content-type: image/png');
        imagepng($im);
  }
  
  public function pdf_get(){
      
        $pdf = model_pdf( array("mt" =>55, "mb" => 38), 'corpo' );
        
        setlocale(LC_ALL,"es_ES");
        $fecha = strftime("%d de %B del %Y");
        
        //Add a new page
        $pdf->pdf->AddPage();
          
        // ENCABEZADO
        $pdf->align("FORMATO DE AUTORIZACIÓN DE CARGO A TARJETA DE CRÉDITO O DÉBITO", 'C', array("size" => 12, "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        
        $data = array(
                "tratamiento" => "Lic.",
                "nombre" => "Jorge Alberto Sánchez",
                "tel" => "998 214 0469",
                "cia" => "G-Media Solutions",
                "email" => "geosh2000@gmail.com",
                "referencia" => "Facebook",
                "fecha_inicio" => "2022/06/13",
                "fecha_fin" => "2022/06/20",
                "no_habs" => "150",
                "habs" => array("Hotel Grand Oasis Cancún", "Hotel The Pyramid at Grand Oasis Cancún")
            );
        
        // SALUDO
        $pdf->write('Estimado Huésped. ', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write(" ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write('Favor de imprimir este formato y llenarlo a mano, será necesario responderlo sobre el mismo correo donde se le hace llegar', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4));
        $pdf->write(" ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write('acompañado de una identificación oficial INE o Pasaporte.', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        $pdf->write('Confirmo que autorizo y reconozco el cargo a ', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4));
        $pdf->write(" ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write('Interacciones Zafiro S.A. de C.V. // Operadora Rio Ingles S.A. de C.V. por la cantidad de $', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write(" ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write('MONTO', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        
        $datos = array(
                "Nombre de titular de la tarjeta:               ______________________________________",
                "Tipo de tarjeta:                                        ( ) VISA    ( ) MASTERCARD    ( ) AMEX",
                "Últimos 4 dígitos de la tarjeta:                ______________________________________",
                "Válida hasta (mes / año):                       ______________________________________",
                "Nombre de titular de la reserva:             ______________________________________",
                "Localizador:                                             147491",
                "Fecha en que se envía el formato:          $fecha",
            );
        
        $pdf->addBullets($datos, array("size" => 10, "font" => 'Arial'));
        
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        
        $pdf->write('**Todos los cargos con AMEX se realizaran en MXN de acuerdo al tipo de cambio del día en el hotel**', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        
        $pdf->saltoLinea(50);
        $pdf->align("______________________________________", 'C', array("size" => 12, "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->align("Nombre y firma del titular de la tarjeta", 'C', array("size" => 12, "font" => 'Arial'));
        // $pdf->write("Compañía: ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        // $pdf->write($data['cia'] ?? 'N/A', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4));
        // $pdf->saltoLinea(6);
        // $pdf->write("E-mail: ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        // $pdf->write($data['email'] ?? 'N/A', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4));
        // $pdf->saltoLinea(6);
        // $pdf->saltoLinea(6);
        // 
        // // REFERENCIAS
        // $pdf->align("", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 105));
        // $pdf->align("Referencia:", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 40));
        // $pdf->align($data['referencia'] ?? 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1, "w" => 50));
        // $pdf->align("", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 105));
        // $pdf->align("Fehas:", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 40));
        // $pdf->align( isset($data['fecha_inicio']) ? ($data['fecha_fin']." a ".$data['fecha_fin']) : 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1, "w" => 50));
        // $pdf->align("", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 105));
        // $pdf->align("No. habs probables:", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 40));
        // $pdf->align($data['no_habs'] ?? 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1, "w" => 50));
        // $pdf->align("", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 105));
        // $pdf->align("Hoteles:", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 40));
        // if( isset($data['habs']) ){
        //     $pdf->align($data['habs'][0] ?? 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1, "w" => 50));
        //     array_splice($data['habs'],0,1);
        //     $habs = implode(", ", $data['habs']);
        // }
        // if( $habs != "" ){
        //     $pdf->align(isset($habs) ? ($habs == '' ? 'N/A' : $habs) : 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1));
        // }
        // $pdf->saltoLinea(6);
        // 
        // // INICIO
        // $pdf->write("Estimado ".$data['nombre'].":", array("size" => 10, "style" => 'B', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->saltoLinea(6);
        // $saludo = "Agradeciendo su preferencia y de acuerdo con su amable solicitud, le presento la siguiente propuesta de hospedaje para su grupo, esperando que sea de su agrado y teniendo el placer de servirle cubriendo sus necesidades:";
        // $pdf->addParagraph($saludo, array("size" => 10, "style" => '', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->saltoLinea(6);
        // 
        // // CUERPO
        // $pdf->addBullets($generales, array("size" => 10, "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->addParagraph("La Tarifa incluye", array("size" => 10, "style" => 'BU', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->addBullets($incluye, array("size" => 10, "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->addParagraph("La Tarifa no incluye", array("size" => 10, "style" => 'BU', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->addBullets($noIncluye, array("size" => 10, "font" => 'Arial'));
        // $pdf->addBullets($noIncluye_2, array("size" => 10, "style" => 'B', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->addParagraph("Gratuidades e inclusiones", array("size" => 10, "style" => 'BU', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->addBullets($gratuidades, array("size" => 10, "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $t = "Esta cotización no bloquea el número de habitaciones requeridas, ";
        // $pdf->write($t, array("size" => 10, "style" => '', "font" => 'Arial'));
        // $t = "tiene una vigencia de 10 días a partir de la fecha de envío y se debe reconfirmar disponibilidad una vez aceptada esta propuesta. ";
        // $pdf->write($t, array("size" => 10, "style" => 'B', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->saltoLinea(6);
        // $t = "Precios sujetos a cambio sin previo aviso. ";
        // $pdf->write($t, array("size" => 10, "style" => 'BU', "font" => 'Arial'));
        // $t = "En caso de bajar la garantía de 10 habitaciones, se tendrá que re cotizar y no se podrán confirmar las tarifas otorgadas en esta cotización.";
        // $pdf->write($t, array("size" => 10, "style" => '', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->saltoLinea(6);
        // $t = "Ésta es sólo una cotización y no implica responsabilidad de disponibilidad para el Hotel al momento de confirmar.";
        // $pdf->addParagraph($t, array("size" => 10, "style" => '', "font" => 'Arial'));
        // $pdf->saltoLinea(6);
        // $pdf->saltoLinea(6);
        // 
        // $name = "Zacil Ramirez";
        // $puesto = "Coordinadora de Grupos y Convenciones";
        // $hoteles = array("Hotel Grand Oasis Cancún", "Hotel The Pyramid at Grand Oasis Cancún");
        // $cel = "99 82 40 94 71";
        // $pdf->addSignature( $name, $puesto, $hoteles, $cel, 10);
        
        // return the generated output
        $pdf->pdf->Output('D', 'test.pdf');
  }
  
  public function sf_get(){
      $sf = model_salesforce();
      
      $tokenCall = $sf->updateSfCatalogs();
      
      if( $tokenCall['err'] ){
          errResp($tokenCall['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $tokenCall['error'] );
      }else{
          
          
          okResp($tokenCall['msg'], 'data', $tokenCall['data']);
      }
  }
  
  public function testq_get(){
      $val = $this->uri->segment(3) ?? 'no hay';
      
      echo $val;
  }
  
  public function paym_get(){
      $pm = $this->db->select("IF(itemType=1,itemLocatorId,'Complemento') as itemLocatorId,
                                CONCAT(a.confirm,IF(isValidated=1,'',' \n(Esperando Validacion)')) as confirm,
                                c.operacion,
                                CONCAT('\"',LPAD(c.aut, 6, 0),'\"') as aut,
                                c.referencia,
                                c.proveedor,
                                SUM(b.monto) AS montoAsignado,
                                c.monto AS montoVoucher,
                                c.moneda as moneda,
                                tipo as tipoCobro,
                                c.validateTicket,
                                IF(SUBSTRING(tarjeta,1,1) IN ('X','V'),1,0) as inDirectory, tarjeta", false)
                    ->from('cycoasis_rsv.r_items a')
                    ->join('cycoasis_rsv.p_cashTransaction b', 'a.itemId=b.itemId', 'left')
                    ->join('res_ligasPago c', 'b.accountId = c.operacion', 'left')
                    ->group_by('b.accountId')
                    ->group_by('itemLocatorId')
                    ->having('montoAsignado >', 0, FALSE);;
                                
                                
    echo $this->db->get_compiled_select();
  }
  
  public function testSf_get(){
      $sf = model_salesforce();
      $query = "SELECT 
                    a.id,
                    a.nombre,
                    a.apellido,
                    fecha_nacimiento,
                    correo,
                    nombre_del_nivel,
                    total_rsvas_disfrutadas,
                    total_noches_disfrutadas,
                    ROUND(total_ttv_gastado, 2) AS total_ttv_gastado,
                    total_ultima_estancia,
                    CASE
                        WHEN
                            codigo = 'basic'
                        THEN
                            IF(total_noches_disfrutadas > 10,
                                0,
                                11 - total_noches_disfrutadas)
                        WHEN
                            codigo = 'gold'
                        THEN
                            IF(total_noches_disfrutadas > 20,
                                0,
                                21 - total_noches_disfrutadas)
                    END AS noches_next,
                    CASE
                        WHEN
                            codigo = 'basic'
                        THEN
                            IF(total_ttv_gastado >= 2000,
                                0,
                                ROUND(2000 - total_ttv_gastado, 2))
                        WHEN
                            codigo = 'gold'
                        THEN
                            IF(total_ttv_gastado >= 3000,
                                0,
                                ROUND(3000 - total_ttv_gastado, 2))
                    END AS ttv_next,
                    GROUP_CONCAT(DISTINCT hotel) AS hotelesVisitados,
                    idioma,
                    a.pais,
                    CASE
                        WHEN nombre_del_nivel = 'silver' THEN '5%'
                        WHEN nombre_del_nivel = 'gold' THEN '10%'
                        WHEN nombre_del_nivel = 'platinum' THEN '15%'
                    END AS descuento
                FROM
                    cycoasis_rsv.or_master a
                        LEFT JOIN
                    t_reservations b ON a.id = b.cieloOrId AND e IN ('o')
                WHERE
                    a.id=586393
                GROUP BY a.id
                ORDER BY id
                ";
                
        if( $q = $this->db->query( $query ) ){
            $result = $sf->updateOR( $q->result_array() );
          
            if( $result['err'] ){
                return errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error'] );
            }else{
                return okResp( $result['msg'], 'data', $result['data'], 'response', $result['response'] );
            }
        }else{
            errResp( 'Error al obtener info de or',  REST_Controller::HTTP_BAD_REQUEST, 'error', $this->db->error() );
        }
      
      
                    
  }
  
  public function testSameDay_get(){
      $rsv = model_rsv();
      
      $result = $rsv->sameDayNotif( $_GET['loc'], array('title' => 'Reserva saldada', "msg" => "Reserva nueva creada, captura necesaria") );
      
      if( $result['err'] ){
          errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error'] );
      }
      
      okResp( $result['msg'], 'eval', $result['data'], 'item', $result['item'] );
                    
  }
  
  public function testconf_get(){
      
      $mail = model_mailing();
      
      $send = $mail->sendFullTest( 139051, true, true );
      
      okResp( 'ok', 'data',$send );
                    
  }
  
  public function testchat_get(){
      
    $this->db->query("DROP TEMPORARY TABLE IF EXISTS lastRsv");
        $this->db->query("CREATE TEMPORARY TABLE lastRsv SELECT 
            masterLocatorId,
            itemLocatorId,
            itemType,
            isQuote,
            isConfirmable,
            hotel,
            inicio,
            ROUND(IF(m.grupo='ohr',0,monto)*IF(moneda='MXN',1,21),2) as monto,
            dtNowIsRsv,
            COALESCE(tipoCambio,IF(moneda='MXN',1,21)) as tipoCambio, monto as montoOk
        FROM
            cycoasis_rsv.r_items a LEFT JOIN cycoasis_rsv.r_hoteles b ON a.itemId=b.itemId
            LEFT JOIN cycoasis_rsv.r_monto m ON a.itemId=m.itemId
        WHERE
            dtNowIsRsv >= ADDDATE(CURDATE(),-0) AND isCancel = 0
        ORDER BY dtNowIsRsv DESC");
        
        $r = $this->db->query("SELECT NOW() as hora, SUM(monto) as Venta, COUNT(DISTINCT masterlocatorid) as Rsvas, COUNT(IF(itemType=1,1,null)) as Cuartos, COUNT(IF(itemType=10,1,null)) as Seguros, SUM(IF(itemType=10,monto,0)) as montoSeguros FROM lastRsv");
        $sum = $r->row_array();
        
        $t = $this->db->query("SELECT 
            ROUND(SUM(IF(m.grupo = 'ohr', 0, monto) * IF(moneda = 'MXN', 1, 21)),
                    2) AS monto
        FROM
            cycoasis_rsv.r_items a
                LEFT JOIN
            cycoasis_rsv.r_hoteles b ON a.itemId = b.itemId
                LEFT JOIN
            cycoasis_rsv.r_monto m ON a.itemId = m.itemId
        WHERE
            dtNowIsRsv >= CAST(CONCAT(YEAR(CURDATE()),
                        '-',
                        MONTH(CURDATE()),
                        '-01')
                AS DATE)
                AND isCancel = 0
                AND itemType = 1
                AND isOpen = 0");
        $tot = $t->row_array();
        
          $chat = model_wh();
          
          $arr = array(
            "link" => "https://cyc-oasishoteles.com/#/dashboard2",
            "title"   => "Venta del dia (".$sum['hora']." CDMX)",
            "sub"     => "Venta en MXN (total: \$".number_format(floatval($tot['monto']), 2, ".", ",").")",
            "cards"   => array(
                array(
                        "txt"     => 
                            "<b>Venta Total: </b> \$".number_format(floatval($sum['Venta']), 2, ".", ",")."<br>"
                            ."<b>Localizadores: </b> ".$sum['Rsvas']
                            ." (".$sum['Cuartos']." RNs)",
                        "tlSec"   => "Hoteles",
                    ),
                array(
                        "txt"     =>
                            "<b>Tarjetas: </b> ".$sum['Seguros']."<br>"
                            ."<b>Monto: </b> \$".number_format(floatval($sum['montoSeguros']), 2, ".", ",")."<br>",
                        "tlSec"   => "Seguros",
                    )
              )
          
          );
          
          $sendMsg = $chat->sendChat('ventas', $arr);
      
      if( $sendMsg['err'] ){
          errResp( $sendMsg['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $sendMsg['error'] );
      }else{
          okResp( $sendMsg['msg'], 'data', $sendMsg['data'] );
      }
  }
  
  public function confirm_get(){
      $socket = model_socket();
      $s = $socket->post( 'rsv-confirmation', array( "data" => array("itemId" => '138160-2', 'userConfirm' => 'test', 'confirmation' => '123test' )));
      okResp('Ok', 'socket', $s);
  }
  
  public function ping_get(){
      okResp('Ping devuelto', 'data', true);
  }
  
  public function set_level_put(){
      $data = $this->put();
      
      $result = loyaltyRB('setLevel',$data);
    //   $result = loyaltyRB('get',$data['email']);
      
      okResponse('Data modificada', 'data', $result, $this);
  }
  
  public function get_user_put(){
      $data = $this->put();
      
      $original = loyaltyRB('get',$data['email']);
      
      okResponse('Data obtenida', 'data', $original, $this);
  }
  
  public function getid_get(){
      $rsv = model_rsv();
      
      $result = $rsv->getId(116133, 'masterlocatorid')['data'];
      $idq = $rsv->getHistoryTicket($result);
      
      okResp('Resultado', 'data', $result, 'ml', $idq);
  }
  
  public function test_get(){
        
        $pm = model_pagos();
        
        $itemId = 56025;
        
        $result = $pm->applyPayments( $itemId, false, true );

        if( $result['err'] ){
            errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['errores'], 'correctas', $result['correctas']);
        }else{
            okResp($result['msg'],'data', array('txCorrectas' => $result['correctas'], 'txErrores' => $result['errores']));
        }
    }
    
    public function post_get(){
        
        $post = model_post();
        
        $result = $post->post( 'http://searchconsoles.googleapis.com/v1/urlTestingTools/mobileFriendlyTest', array() );
        
        okResp( 'Post enviado', 'data', $result );
    }
    
    
    
  private function rbReq($data){
      
      $arrParams = array();
      $params = "";
      $post = false;
      $put = false;
      $x = 0;
      $curlType = 'get';
      
      foreach($data as $field => $value){
          if( $field == 'route' || $field == 'type' ){
              if( $field == 'route' ){
                $route = $value;
              }else{
                  switch($value){
                      case 'get':
                        $post = false;
                        $put = false;
                        break;
                      case 'post':
                        $curlType = 'post';
                        $post = true;
                        $put = false;
                        break;
                      case 'put':
                        $curlType = 'put';
                        $post = true;
                        $put = true;
                        break;
                  }
              }
          }else{
              $arrParams[$field] = $value;
          }
      }
      
      if( count($arrParams) > 0 && $data['type'] == 'get' ){
          foreach($arrParams as $f => $v){
              if( $x > 0 ){
                  $params .= "&";
              }
              
              $params .= "$f=".urlencode($v);
              $x++;
          }
      }
      
      
      $result = getRbApi($route, $params,$post, $put, $arrParams);
      
      return $result;
      
  }
  
  public function roiback_put(){
      
      $data = $this->put();
      
      $result = $this->rbReq($data);
      
      okResponse('Info', 'data', $result, $this);
      
  }
  
  public function roibackBook_put(){
      $data = $this->put();
      $roomData = array();
      
      $avail = array(
            "route" => "availability/by-hotels",
            "type" => "get",
            "codes" => $data['codes'],
            "checkIn" => $data['checkIn'],
            "checkOut" => $data['checkOut'],
            "adults" => $data['adults'],
            "channel" => 'bookingcenter'
          );
          
        if( isset($data['childrenAges']) ){
            $avail['childrenAges'] = $data['childrenAges'];
        }
        
        $getAvail = $this->rbReq($avail);
        
        // okResponse('Info', 'data', $getAvail['data'][0]->roomRates, $this);
        
        foreach($getAvail['data'][0]->roomRates as $room => $rinfo ){
            if( $rinfo->roomCode == $data['roomcode'] ){
                $roomData = json_decode(json_encode($rinfo),true);
            }

        }
        
        if( count($roomData) == 0 ){
            errResponse('No se encontro disponibilidad para esta habitacion', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
        }
        
        $bookData = array(
                "route" => "bookings",
                "type" => "post",
                "purchaseTokens" => array($roomData['purchaseToken']),
                "customer" => array (
                        "name" => "Jorge",
                        "lastName" => "Sanchez",
                        "email" => "geosh2000@gmail.com"
                    ),
                // "paymentMethod" => "PAY_AT_HOTEL",
                "paymentMethod" => $data['paymentMethod'],
                "creditCard" => array(
                        "holderName" => "cyc",
                        "type" => "VISA",
                        "number" => "4444444444444448",
                        "cvc" => "123",
                        "expiration" => "12-2025"
                    )
            );
            
        $result = $this->rbReq($bookData);
        
        okResponse('Info', 'data', $result, $this, 'postData', $bookData);
      
      
  }
    
  public function getStatus_get(){
      
      $support = model_support();
      
      $agent = $this->uri->segment(3);
      
      $result = $support->getTalkStatus($agent);
      
      okResponse('Status Obtenido', 'data', $result, $this);
  }  
  
  public function setStatus_get(){
      
      $support = model_support();
      
      $agent = $this->uri->segment(3);
      $status = $this->uri->segment(4);
      
      $result = $support->setTalkStatus($agent, $status);
      
      okResponse('Status Obtenido', 'data', $result, $this);
  }  
  
  public function transaction_get(){
      $pagos = model_pagos();
      
      $pagos->setTransaction( array( array('a' => 'a') ) );
  }
  
  public function insertbatch_get(){
      $insert = array(
            array(
                "category" => "a1",
                "name"  => 'a1',
                "player" => 1
                ),
            array(
                "category" => "a1",
                "name"  => 'a2',
                "player" => 2
                ),
            array(
                "category" => "a1",
                "name"  => 'a2',
                "player" => 3
                ),
            array(
                "category" => "a1",
                "name"  => 'a1',
                "player" => 'a1'
                )
          );
          
    $this->db->insert_batch('cycoasis_sarabi.game_clue_cards', $insert);
    
    $ids = $this->db->insert_id();
    
    okResp('Insertados correctamente', 'data', $ids);
  }
  
}
