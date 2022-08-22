<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
require( APPPATH.'/libraries/REST_Controller.php');
// use REST_Controller;


class Rsv extends REST_Controller {
    
    protected $zd;

    public function __construct(){
        parent::__construct();
        $this->load->helper('json_utilities');
        $this->load->helper('jwt');
        $this->load->helper('validators');
        $this->load->helper('templates');
        $this->load->helper('confirmations');   
        $this->load->helper('confirmationsv2');   
        $this->load->helper('assistcard');
        $this->load->helper('confTst');   
        $this->load->helper('model_loader');   
        $this->load->database();
        
        $this->zd = model_zd();
    }
    
    
  
    public function sendComment_put(){
        tokenValidation12( $func = function(){
            
            $data = $this->put();

            $msg = $_GET['usn'].": ".$data['comment'];
                if( $this->zd->saveHistory( $data['ticket'], $msg ) ){
                    okResponse('Comentario guardado', 'data',true, $this);
                }else{
                    errResponse('Error al guardar comentario', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }
        });
    }
    
    public function extranetConfirm_put(){
        tokenValidation12( $func = function(){

            $data = $this->put();
            
            // GET CURRENT TIME
            $dtCreated = new DateTime();
            
            
            // START QUERY
            $this->db->where('itemId', $data['itemId']);
            
            if( $data['isCancel'] ){
                $this->db->set(array('confirmCancel' => $data['confirm'], 'userConfirmCancel' => $_GET['usn'], 'dtConfirmCancel' => $dtCreated->format('Y-m-d H:i:s')));
                $msg = "**** Confirmación de Cancelación Item: ".$data['itemLocatorId']." con la clave ".$data['confirm']." por ".$_GET['usn']." ****\n\nConfirmación hecha a través del módulo de Extranet";
            }else{
                $this->db->set(array('confirm' => $data['confirm'], 'userConfirm' => $_GET['usn'], 'dtConfirm' => $dtCreated->format('Y-m-d H:i:s')));
                $msg = "**** Item: ".$data['itemLocatorId']." confirmado con la clave ".$data['confirm']." por ".$_GET['usn']." ****\n\nConfirmación hecha a través del módulo de Extranet";
            }
            
            if( $this->db->update('cycoasis_rsv.r_items') ){
                
                // SAVE TO TICKET HISTORY
                if( $this->zd->saveHistory( $data['mlTicket'], $msg ) ){
                    okResponse('Comentario guardado', 'data',true, $this);
                }else{
                    errResponse('Error al guardar comentario', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }
            }else{
                errResponse('No se pudo ingresar la confirmación', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        
        });
    }
  
    public function setNR_put(){
        tokenValidation12( $func = function(){
        
            $data = $this->put();
            
            if( !is_array($data) ){
                errResp('Se debe obtener un put de arreglos', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }
            
            if( !isset($data['itemId']) ){
                errResp('No se encontro el parametro itemId', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }
            
            if( !isset($data['set']) ){
                errResp('No se encontro el parametro set que define si es NR (true) o Flexible (false)', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }
            
            $rsv = model_rsv();
            $nr = $rsv->setNR( $data['itemId'], $data['set'] );
            
            if( $nr['err'] ){
                errResp( $nr['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $nr['error'] );
            }else{
                okResp( $nr['msg'], 'data', $nr['data'] );
            }
        
        });
    }
    
    public function saveConfirm_put(){
        tokenValidation12( $func = function(){
        
            $data = $this->put();
            
            if( !is_array($data) ){
                errResp('Se debe obtener un put de arreglos', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }
            
            // Arregla data si se recibe el item completo
            if( isset($data['item']) ){
                if( isset($data['item']['itemLocatorId']) ){
                    $data['itemLocatorId'] = $data['item']['itemLocatorId'];
                }
            }
            
            if( !isset($data['itemLocatorId']) ){
                errResp('No se encontro el parametro itemLocatorId', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }
            
            if( !isset($data['confirm']) ){
                errResp('No se encontro el parametro confirm', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }
            
            if( !isset($_GET['usn']) ){
                errResp('No se recibió el usuario que está confirmando. Reinivia sesión en tu CYC', REST_Controller::HTTP_BAD_REQUEST, 'error', $_GET);
            }
            
            $rsv = model_rsv();
            $confirm = $rsv->setConfirm( $data['itemLocatorId'], $data['confirm'] );
            
            if( $confirm['err'] ){
                errResp( $confirm['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $confirm['error'] );
            }else{
                okResp( $confirm['msg'], 'data', $confirm['data'] );
            }
        
        });
    }
    
    public function setCaptured_put(){
        tokenValidation12( $func = function(){
        
            $data = $this->put();
            
            if( !is_array($data) ){
                errResp('Se debe obtener un put de arreglos', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }

            if( !isset($data['itemLocatorId']) ){
                errResp('No se encontro el parametro itemLocatorId', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }
            
            $rsv = model_rsv();
            $confirm = $rsv->setCaptured( $data['itemLocatorId'] );
            
            if( $confirm['err'] ){
                errResp( $confirm['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $confirm['error'] );
            }else{
                okResp( $confirm['msg'], 'data', $confirm['data'] );
            }
        
        });
    }

    

    
    // ************ DEPRECATED START ************
    
        // public function sendValidate($data, $mlTicket) {
        //     //NORMALIZED
            
        //     $main = 385732785951;
        //     $cc = array();
            
        //     switch($data['complejo']){
        //         case 'Cancun':
        //             $main = 385732785951;
        //             $cc = array(
        //                     array('user_id'=>379095551432, 'action'=>'put'),
        //                     array('user_id'=>385732780711, 'action'=>'put'),
        //                     array('user_id'=>385732783051, 'action'=>'put'),
        //                 );
        //             break;
        //         case 'Palm':
        //             $main = 378182456892;
        //             $cc = array(
        //                     array('user_id'=>389589806232, 'action'=>'put'),
        //                     array('user_id'=>378182456912, 'action'=>'put'),
        //                 );
        //             break;
        //         case 'Smart':
        //             $main = 385791902072;
        //             break;
        //         case 'Vcm':
        //             $main = 388394355452;
        //             $cc = array(
        //                     array('user_id'=>388493735651, 'action'=>'put'),
        //                     array('user_id'=>389233356812, 'action'=>'put'),
        //                 );
        //             break;
        //     }
            
    
        //     $title = "Validacion de deposito rsvas: - ".$data['confs'];
        //     $openMsg = "Validacion de pago - ".$data['accountId']." rsvas: - ".$data['confs']." (Ultima rsva confirada por: ".(isset($_GET['usn']) ? $_GET['usn'] : 'robot').")";
        //     $group = 360013241092;
            
        //     $id = $this->zd->newTicket( $title, $openMsg, $main, $cc, $group );
            
        //     $msg = "<p>Buen dia estimados!</p><br><p>Serían tan amables de ayudarnos a validar el depósito por ".$data['monto']." ".$data['moneda']." para la(s) reserva(s): ".$data['confs']." ?</p><br><p>El comprobante lo encontrarán en la siguiente liga:</p>";
        //     $msg .= "<p><a href='https://cyc-oasishoteles.com/payments/".$data['accountId']."' target='_blank'>https://cyc-oasishoteles.com/payments/".$data['accountId']."</a></p><br><br>Muchas gracias y saludos<br><br><p>El equipo de Contact Center Oasis</p>";
            
        //     $editTkt = array("ticket" => array(
        //             "submitter_id" => 360005313512, 
        //             "status" => "hold",
        //             "comment" => array("html_body" => $msg, "public"=> true, "author_id" => 360005313512)));
            
        //     $responseOk = $this->zd->addComment($id, $editTkt);
            
        //     $this->db->set(array('validateTicket' => $id))->where('operacion',$data['accountId'])->update('res_ligasPago');
            
        //     $this->zd->saveHistory( $mlTicket, "Envio de validacion de deposito ".$data['accountId']." en ticket $id" ); 
            
        //     return true;
            
        // }
        
        // private function validateDeposit( $itemId, $mlTicket, $return = false ){
        //     // NORMALIZED
        //     $qValidate = $this->db->query("SELECT 
        //                                     a.accountId,
        //                                     COUNT(*) AS items,
        //                                     COUNT(d.confirm) AS confirmations,
        //                                     IF(COUNT(*) = COUNT(d.confirm), 1, 0) AS sendValidation,
        //                                     GROUP_CONCAT(DISTINCT CONCAT(CASE
        //                                                     WHEN d.itemType = 1 THEN  CONCAT('Hotel: ', d.confirm)
        //                                                     WHEN
        //                                                         d.itemType = 10
        //                                                     THEN
        //                                                         CONCAT('Seguro ',
        //                                                                 d.confirm,
        //                                                                 ' en ',
        //                                                                 IF(si.confirm IS NULL,
        //                                                                     'voucher: ',
        //                                                                     'rsva: '),
        //                                                                 COALESCE(si.confirm, si.itemLocatorId))
        //                                                     WHEN
        //                                                         d.itemType = 11
        //                                                     THEN
        //                                                         CONCAT('Traslado ',
        //                                                                 d.confirm,
        //                                                                 ' en voucher: ',
        //                                                                 d.masterlocatorid)
        //                                                     ELSE CONCAT('Servicio ',
        //                                                             d.confirm,
        //                                                             ' en voucher: ',
        //                                                             d.masterlocatorid)
        //                                                 END)) AS confs,
        //                                     complejo,
        //                                     b.monto,
        //                                     b.moneda,
        //                                     validateTicket
        //                                 FROM
        //                                     cycoasis_rsv.p_cashTransaction a
        //                                         LEFT JOIN
        //                                     res_ligasPago b ON a.accountId = b.operacion
        //                                         LEFT JOIN
        //                                     cycoasis_rsv.p_cashTransaction c ON a.accountId = c.accountId
        //                                         AND c.monto > 0
        //                                         LEFT JOIN
        //                                     cycoasis_rsv.r_items d ON c.itemId = d.itemId
        //                                         LEFT JOIN
        //                                     cycoasis_rsv.r_seguros s ON c.itemId = s.itemId
        //                                         LEFT JOIN
        //                                     cycoasis_rsv.r_items si ON s.sg_itemRelated = si.itemLocatorId
        //                                 WHERE
        //                                     a.itemId IN ($itemId)
        //                                         AND tipo = 'Deposito'
        //                                         AND d.isCancel = 0
        //                                         AND isValidated = 0
        //                                         AND validateTicket IS NULL
        //                                 GROUP BY a.accountId");
                                    
        //     $qvR = $qValidate->result_array();
            
        //     foreach( $qvR as $qind => $va ){
        //         if( $va['sendValidation'] == '1' ){
        //             $this->sendValidate($va, $mlTicket);
        //         }    
        //     }
            
        //     if( $return ){
        //         return $qvR;
        //     }
        // }
        
    
    // ************ DEPRECATED END ************
    
    
    
    // ************ RSV READ START  ************
    
        public function getItem_get(){
          
            tokenValidation12( $func = function(){
                
                $rsv = model_rsv();
              
                $loc = $this->uri->segment(3);
                $g = strpos( $loc , '-' );
                $ml = substr($loc,0, $g);
                $idn = substr($loc, $g+1,100);
                $il = $ml."-".$idn;
                
                $mlQ = $rsv->manageMaster( $ml );
                $itQ = $rsv->manageItem( true, $loc );
                            
                if( $mlDataQ = $this->db->query($mlQ) ){
                    if( $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId") ){
                        if( $sumQ = $this->db->query("SELECT min(llegada) as llegada, GROUP_CONCAT(DISTINCT grupo) as gposTfas, GROUP_CONCAT(DISTINCT tipoPago) as tiposPago, if(MAX(llegada)>MAX(salida),MAX(llegada),MAX(salida)) as sallida FROM items") ){
                            $sum = $sumQ->row_array();
                            $master =  $mlDataQ->row_array();
                            $master['llegada'] = $sum['llegada'];
                            $master['grupos'] = $sum['gposTfas'];
                            $master['tiposPago'] = $sum['tiposPago'];
                            $master['test'] = 100;
                            $mlQ = $this->db->query("SELECT historyTicket FROM cycoasis_rsv.r_masterlocators WHERE masterlocatorid = $ml");
                            $mlR = $mlQ->row_array();
                            $master['mlTicket'] = $mlR['historyTicket'];
                            
                            $ids = array();
                            foreach( $itemDataQ->result_array() as $index => $a ){
                                array_push($ids, $a['itemId']);
                                $proveedor = $a['proveedor'];
                            }
                            
                            $crA = $this->db->query("SELECT rsv_extranetAll FROM userDB a LEFT JOIN cat_profiles b ON a.profile=b.id WHERE asesor_id=".$_GET['usid']);
                            $crR = $crA->row_array();
                            
                            if( $proveedor != '0' && $crR['rsv_extranetAll'] == '0'){
                                $crQ = $this->db->from('cycoasis_rsv.usr_providers')->where(array('agentId'=>$_GET['usid'],'provider'=>$proveedor,'activo'=>1))->get();
                                if( count($crQ->result_array()) <= 0 ){
                                    errResponse('No tienes los permisos necesarios para ver esta reservación. Para más información comunícate con el Contact Center', REST_Controller::HTTP_BAD_REQUEST, $this, 'error',array());
                                }
                            }
                            
                            
                            if( $acQ = $this->db->query("SELECT 
                                                            b.*, items
                                                        FROM
                                                            cycoasis_rsv.p_cashTransaction a
                                                                LEFT JOIN
                                                            res_ligasPago b ON a.accountId = IF(a.cashTransactionId<630,b.paymentId,b.operacion)
                                                                LEFT JOIN
                                                            (SELECT 
                                                                accountId, GROUP_CONCAT(DISTINCT itemLocatorId) AS items
                                                            FROM
                                                                cycoasis_rsv.p_cashTransaction a
                                                            LEFT JOIN cycoasis_rsv.r_items b ON a.itemId = b.itemId
                                                            GROUP BY accountId) c ON a.accountId = c.accountId WHERE b.moneda != 'BZA' AND a.accountId != 0 AND a.itemId = ".$ids[0]) ){
                                
                                okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $itemDataQ->result_array()), $this, 'payments', $acQ->result_array());
                            }else{
                                errResponse('Error al obtener el resumen '.__LINE__, REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                            }    
                        }else{
                            errResponse('Error al obtener el resumen '.__LINE__, REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                        }
                    }else{
                        errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }
                }else{
                    errResponse('Error al obtener el masterlocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            
            });
        }
        
        private function m2Loc($loc, $rFlag = true){
            
            $rsv = model_rsv();
          
            $mlQ = $rsv->manageMaster($loc);
            $rsv->manageItem(false, $loc);
                        
            if( $mlDataQ = $this->db->query($mlQ) ){
                if( $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId") ){
                    if( $sumQ = $this->db->query("SELECT min(llegada) as llegada, GROUP_CONCAT(DISTINCT grupo) as gposTfas, GROUP_CONCAT(DISTINCT tipoPago) as tiposPago, if(MAX(llegada)>MAX(salida),MAX(llegada),MAX(salida)) as sallida FROM items") ){
                        $sum = $sumQ->row_array();
                        $master =  $mlDataQ->row_array();
                        $master['llegada'] = $sum['llegada'];
                        $master['grupos'] = $sum['gposTfas'];
                        $master['tiposPago'] = $sum['tiposPago'];
                        $master['test'] = 100;
                        $mlQ = $this->db->query("SELECT historyTicket FROM cycoasis_rsv.r_masterlocators WHERE masterlocatorid = $loc");
                        $mlR = $mlQ->row_array();
                        $master['mlTicket'] = $mlR['historyTicket'];
                        
                        $master['payments'] = $rsv->getPayments($loc);
                        
                        $this->db->select("CONCAT(itemLocatorId, ' reembolso') as itemLocatorId,
                                    SUM(IF(c.monto<0,c.monto,0)) as monto,
                                    r.dtCreated AS fechaCobro,
                                    r.proveedor,
                                    r.complejo,
                                    IF(c.accountId = '0',
                                        'cortesía',
                                        r.operacion) AS operacion,
                                    r.aut,
                                    r.afiliacion,
                                    r.tarjeta,
                                    r.monto AS montoTx,
                                    r.moneda")
                                ->from("cycoasis_rsv.r_items i")
                                ->join("cycoasis_rsv.p_cashTransaction c", "i.itemId = c.itemId", "left")
                                ->join("res_ligasPago p", "c.accountId = p.operacion", "left")
                                ->join("res_ligasPago r", "p.operacionReembolso = r.operacion", "left")
                                ->where('masterlocatorid',$loc)
                                ->where('c.itemId IS NOT',' NULL', FALSE)
                                ->where('r.operacion IS NOT',' NULL', FALSE)
                                ->group_by(array('itemLocatorId','r.operacion'));
                    
                        if( $rm = $this->db->get() ){
                            $tmpR = $rm->result_array();
                            foreach( $tmpR as $index => $rmi ){
                                array_push($master['payments'], $rmi);
                            }
                        }
                        
                        if( $rFlag ){
                            
                            $items = $itemDataQ->result_array();
                            
                            foreach($items as $itm => $it ){
                                $items[$itm]['pkgItems'] = $it['jsonPkg'] == null ? array() : json_decode($it['jsonPkg']);
                                $items[$itm]['pkgItems']->servicios = json_decode($items[$itm]['pkgItems']->servicios);
                                $items[$itm]['pkgItems']->itemDets = json_decode($items[$itm]['pkgItems']->itemDets);
                                $items[$itm]['pkgItems']->pkgInsItemIds = json_decode($items[$itm]['pkgInsItemIds']);
                                $items[$itm]['pkgItems']->pkgInsPax = json_decode($items[$itm]['pkgInsPax']);
                            }
                            
                            okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this);
                        }else{
                            return array('master' => $master, 'items' => $itemDataQ->result_array());
                        }
                    }else{
                        errResponse('Error al obtener el resumen '.__LINE__, REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }
                }else{
                    errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
                errResponse('Error al obtener el masterlocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
          
        }
      
        public function manage2Loc_get(){
          
            tokenValidation12( $func = function(){
                $loc = $this->uri->segment(3);
                $this->m2Loc($loc, true);
            });
        }
        
        public function getPayments_put(){
            
            tokenValidation12( $func = function(){
                $data = $this->put();
                
                $rsv = model_rsv();
                
                $result = $rsv->getPayments($data['loc']);
                
                okResp( 'Pagos Obtenidos', 'data', $result );
            });
            
        }
        
        public function getItemPayments_put(){
            
            tokenValidation12( $func = function(){
                $data = $this->put();
                
                $rsv = model_rsv();
                
                $result = $rsv->getItemPayments($data['itemId']);
                
                okResp( 'Pagos Obtenidos', 'data', $result );
            });
            
        }
    
    // ************ RSV READ END ************
    
    
    
    // ************ CONFIRMATIONS START ************
    
        public function sendDespertar_get(){
            // tokenValidation12( $func = function(){
                $page = $this->uri->segment(3);
                $offset = 10 * $page;
                
                $query = "SELECT 
                                b.masterlocatorid, COUNT(a.itemId) as habs, COUNT(x.itemId) as xfers
                            FROM
                                cycoasis_rsv.r_hoteles a
                                    LEFT JOIN
                                cycoasis_rsv.r_items b ON a.itemId = b.itemId
                                    LEFT JOIN
                                cycoasis_rsv.r_masterlocators m ON b.masterLocatorId = m.masterlocatorid
                            		LEFT JOIN cycoasis_rsv.r_items x ON m.masterlocatorid=x.masterLocatorId AND x.itemType=11 AND x.isQuote=0 AND x.isCancel=0 AND x.isOpen=0 
                            WHERE
                                gpoTfa = 'GDESPT' AND b.isQuote=0 AND b.isCancel=0 AND b.isOpen=0 
                            GROUP BY 
                            	masterlocatorid
                            HAVING xfers=0
                            ORDER BY masterlocatorid
                            LIMIT 10
                            OFFSET $offset";
                
                if( $q = $this->db->query($query) ){
                    $results = array();
                    
                    foreach( $q->result_array() as $i => $info ){
                        $mail = model_mailing();
                
                        $conf = $mail->sendDespertar($info['masterlocatorid']);
                        $conf['loc'] = $info['masterlocatorid'];
                        
                        array_push($results, $conf);
                    }
                    
                    okResp( 'Confirmaciones enviadas', 'data', $results );
                    
                }else{
                    errResp( "error al obtener query", REST_Controller::HTTP_BAD_REQUEST, 'error', $this->db->error() );
                }
                
                
                
            // });
        } 
        
        public function sendFullConf_put(){
            tokenValidation12( $func = function(){
                $data = $this->put();
                
                $loc = $data['loc'];
                
                $mail = model_mailing();
                
                $conf = $mail->sendFull($loc, true, true);
                modelResponse( $conf );
            });
        } 
        
        public function viewConf_get(){
            $loc = $this->uri->segment(3);
            
            $mling = model_mailing();
            $mling->fullConf($loc);
        }
        
        public function verConfirmacion_get(){
            $ml = $this->uri->segment(3);
            $mail = $this->uri->segment(4);
            $mail = str_replace('_arroba_','%40',$mail);
            
            $mling = model_mailing();
            $mling->sendFull($ml, true, false, false, false, urldecode($mail));
        }  
        
        public function sendFullConf_get(){
            $mling = model_mailing();
            $mling->fullConf($this->uri->segment(3));
        }
        
        public function confirmacionesPendientes_get(){
            
            $xls = model_xls();
            
            $confirm = $xls->getQuery('confPendientes');
            
            if( $confirm['err'] ){
                errResp( $confirm['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $confirm['error'] );
            }
            
            $mls = array();
            
            if( count($confirm['data']) > 0 ){
                $complementos = $xls->getQuery('complementosPendientes');
                if( !$complementos['err'] ){
                    
                    foreach( $confirm['data'] as $i => $c ){
                        
                        array_push($mls, substr($c['itemLocatorId'],0,6));
                        
                        if( $c['hasInsurance'] = '1' ){
                            foreach( $complementos['data'] as $y => $s ){
                                if( $c['itemLocatorId'] == $s['parentLoc'] ){
                                    $confirm['data'][$i]['insurance'] = $s;
                                    continue;
                                }
                            }
                            
                            if( $c['jsonPkg'] != null ){
                                $confirm['data'][$i]['packedItems'] = json_decode($c['jsonPkg'], true);
                            }
                        }

                    }
                    
                    $xfers = $xls->getQuery('freeXferPend', implode(',', $mls) );
                    
                    if( !$xfers['err'] ){
                        if( count($xfers) > 0 ){
                            foreach( $confirm['data'] as $i => $c ){
                                foreach( $xfers['data'] as $x => $t ){
                                    if( substr($c['itemLocatorId'],0,6) == substr($t['itemLocatorId'],0,6) ){
                                        $confirm['data'][$i]['xfer'] = true;
                                        continue;
                                    }
                                }
                            }
                        }
                    }
                    
                    
                    okResp( $confirm['msg'], 'data', $confirm['data'], 'extra', array($xfers, $complementos) );
                }
            }
            
            okResp( $confirm['msg'], 'data', $confirm['data'] );
            
        }
    
    // ************ CONFIRMATIONS END ************
    
    
    public function editPickup_put(){
    
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
            $data = $this->put();
            $mlg = model_mailing();
            
            if( $this->db->where('itemId',$data['itemId'])->update('cycoasis_rsv.r_xfer',$data) ){
              
                if( $itQ = $this->manageQueries('item', true, $data['itemId'], "it.itemId = '".$data['itemId']."'") ){
                
                    if( $dQ = $this->db->from('items')->get() ){
                      $d = $dQ->row_array();
                        
                        $mlTicket = $d['mlTicket'];
                        $mlItem = $d['itemLocatorId'];
                        $msg = "Información de pickup para item $mlItem ingresada por ".$_GET['usn'];
                        $this->zd->saveHistory( $mlTicket, $msg ); 
                        
                        $sentMail = $mlg->sendFull($d['masterLocatorId'], 3, false, true);
                        if( !sentMail['err'] ){
                            okResponse('Pickup Actualizado, se ha enviado confirmación al cliente', 'data', true, $this);
                        }else{
                            okResponse('Pickup Actualizado, no se ha notificado al cliente', 'data', true, $this);
                        }
                    }else{
                        errResponse('Hubo un error al enviar la confirmación al cliente. Asegúrate de hacérsela llegar de manera manual', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array()); 
                    }
                }else{
                    errResponse('Hubo un error al enviar la confirmación al cliente. No se pudo obtener info del item. Asegúrate de hacérsela llegar de manera manual', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array()); 
                }
              
            }else{
              errResponse('Error al actualizar pickup', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        
        });
    }
    
    
    // -------------------------- OTLC QUOTES && RSV --------------------------
    
    public function rsvOtlc_put(  ){
        
        $data = $this->put();
        
        // $defaults = array(
        //         'inicio' => '20220824',
        //         'fin' => '20220827',
        //         'habs' => 1,
        //         'isUsd' => true,
        //         'habitaciones' => array(
        //                 'hab1' => array(
        //                         'adultos' => 4,
        //                         'menores' => 0
        //                     ),
        //                 'hab2' => array(
        //                         'adultos' => 3,
        //                         'menores' => 0
        //                     )
        //             ),
        //         'hotel' => 'GOC',
        //         // 'cat'   => 'GSDR',
        //         // 'rsvData' => array(
        //         //         'nombre' => 'Jorge',
        //         //         'apellido' => 'Sanchez',
        //         //         'correo' => 'geosh2000@gmail.com',
        //         //         'isEnglish' => false,
        //         //     )
        //     );
            
        // Comment if in production
        // $data = $defaults;
        
        // Set defaults
        $data['nacionalidad'] = 'nacional';
        $data['noRestrict'] = false;
        $data['grupo'] = array(
                "gpoTitle" => "OTLC",
                "grupo" => "OTLC",
                "hasInsurance" => "1",
                "hasPaq" => "1",
                "mainCampaign" => "1",
            );

        $otlc = model_otlc();
        
        $result = $otlc->getQuote($data); 
        
        if( isset($result['extra']['isNewRsv']) ){
            $_GET['usid']=103;
            $this->saveRsv12($result['data'], true);
            
            errResp('No se guardo ninguna reserva', REST_Controller::HTTP_BAD_REQUEST, 'error', array());
        }

        if( !$result['err'] ){
            okResp($result['msg'], 'data',$result['data'], 'extra',$result['extra']);
        }else{
            errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
        }
    }
    
    // -------------------------- OTLC QUOTES && RSV --------------------------
  
    public function cotizaPax_put(){
        tokenValidation12( function(){
        
            $data = $this->put();
            
            errResp('Cotiza tus cambios desde la nueva version de RSV', REST_Controller::HTTP_BAD_REQUEST, 'error', array());
            
            $cotizador = model_cotizador();
            $result = $cotizador->cambioHospedaje($data['itemId']);
            
            if( !$result['err'] ){
                okResp($result['msg'], 'data',$result['data'], 'assist', $result['assist']);
            }else{
                errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }
        
        });
    }
    
    public function cotizaChangeHotel_put(){
        tokenValidation12( function(){
        
            $data = $this->put();
            
            $cotizador = model_cotizador();
            $result = $cotizador->cambioHotel12($data['itemId'], $data['params']);
            
            if( !$result['err'] ){
                
                $result['data']['newAmmount'] = floatVal($result['data']['newAmmount']);
                $result['data']['difAmmount'] = floatVal($result['data']['difAmmount']);
                
                okResp($result['msg'], 'data',$result['data'], 'assist', $result['assist']);
            }else{
                errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }
        
        });
    }
    
    public function xldInsV12_put(){
        tokenValidation12( function(){
        
            $data = $this->put();
            
            if( $insRemoved = $this->cancelInsurance( $data ) ){
                okResp('Seguro Cancelado', 'data', true);
            }else{
                errResp('Error al cancelar seguro', REST_Controller::HTTP_BAD_REQUEST, 'error', array());
            }
        
        });
    }
    
    private function cancelInsurance( $item ){
        $rsv = model_rsv();
        $rsv->manageItem(true, $item['insuranceRelated'], "it.itemId = ".$item['insuranceRelated']);
        $inQ = $this->db->query("SELECT * FROM items");
        $in = $inQ->row_array();
        
        $params = array(
                'item' => array( "sg_cobertura" => "" ),
                'seguro' => $in,
                'itemId' => $item['itemId'],
                'masterLoc' => $item['masterLocatorId'],
                'mlTicket' => $item['mlTicket'],
                'traspaso' => true
             );
            
        return array($this->mdfIns( $params, true ), $in);
    }
    
    public function changeHotel12_put(){
        
        $data = $this->put();
        
        $rsv = model_rsv();
        
        $item = $data['original'];
        $changes = $data['changes'];
        
        // -------------- Modify Monto -------------- 
        $montoQ = $this->db->from('cycoasis_rsv.r_monto')->where('itemId', $item['itemId'])->get();
        $montoOg = $montoQ->row_array();
        
        $montoOg["lv"]              = $montoOg["lv_goalCode"];
        $montoOg["lv_name"]         = $montoOg["lv_goalName"];
        $montoOg["monto"]           = $changes['monto'];
        $montoOg["lv_originalRate"] = $changes['monto'];
        $montoOg["importeManual"]   = 0;
        
        $montoOg['montoParcial'] = $montoOg['montoParcial'] == 0 ? 0 : ($montoOg['montoParcial'] < $montoOg["monto"] ? $montoOg['montoParcial'] : $montoOg['monto']);

        // crear transacciones para traspaso
        if( floatVal($montoOg['montoPagado']) > floatVal($montoOg['montoParcial']) ){
            
            $rsv = model_rsv();
            $rsv->manageItem(true, $item['itemId'], "it.itemId = ".$item['itemId']);
            $ogQ = $this->db->query("SELECT * FROM items");
            $og = $ogQ->row_array();
            
            $og['pkgItems'] = $og['jsonPkg'] == null ? array() : json_decode($og['jsonPkg']);
            $og['pkgItems']->servicios = json_decode($og['pkgItems']->servicios);
            $og['pkgItems']->itemDets = json_decode($og['pkgItems']->itemDets);
            $og['pkgItems']->pkgInsItemIds = json_decode($og['pkgInsItemIds']);

            $cash = array(
                    "original" => $og,
                    "isR" => false,
                    "itemId" => $item['itemId'],
                    "new" => array(
                            "montoParcial" => $montoOg['montoParcial']
                        )
                );
            
            $this->editMontoParcial($cash, false);
                
            unset( $montoOg['montoPagado'] );
            unset( $montoOg['montoParcial'] );
        }
        
        $this->db->set($montoOg)->where('itemId', $item['itemId'])->update('cycoasis_rsv.r_monto');
        // -------------- Modify Monto -------------- 
        
        // -------------- Modify Hotel -------------- 
            $hotel = array(
                    'inicio' => $changes['llegada'],
                    'fin' => $changes['salida'],
                    'noches' => $changes['nights'],
                    'adultos' => $changes['adultos'],
                    'juniors' => $changes['juniors'],
                    'menores' => $changes['menores'],
                    'categoria' => $changes['cat']
                );
            $this->db->set($hotel)->where('itemId', $item['itemId'])->update('cycoasis_rsv.r_hoteles');
        
        // -------------- Modify Hotel -------------- 
        
        
        // -------------- Seguros Incluidos ---------------
        
        $insRemoved = false;
        $insSeguroAdded = false;
        
        if( $item['insuranceRelated'] != NULL ){
            
            $insResult = $this->cancelInsurance( $item );
            $insRemoved = $insResult[0];
            $in = $insResult[1];
            
            if( $insRemoved ){
                
                $insData = array(
                        "inicio" => $hotel['inicio'],
                        "fin" => $hotel['fin'],
                        "hotel" => $item['hotel'],
                        "itemId" => $item['itemId'],
                        "masterlocator" => $item['masterLocatorId'],
                        "moneda" => $item['moneda'],
                        'related' => $item['itemLocatorId']
                    );
                    
                $insSeguro = array(
                        "cobertura" => $in['sg_cobertura'],
                        "mdo" => $in['sg_mdo'],
                        "pax" => $changes['adultos']+$changes['juniors']+$changes['menores'],
                        "insRate" => $data['insRate']
                    );
                   
                $packM = model_package(); 
                $insArr = $packM->insuranceBuilder( $insData, $insSeguro );
                
                $insSeguroAdded = $this->saveRsvFnc($insArr, true);
            }
        }
        

            
        
        // -------------- Seguros Incluidos ---------------
        
        
        
        // -------------- Modify Package -------------- 
        
        if( $data['hasIns'] ){
            foreach( $data['original']['pkgItems']['pkgInsItemIds'] as $p => $pkId ){
                
                // XLD PACKAGE
                if( !$data['keepOld'] ){
                    $confirmQ = $rsv->getConfirm( $pkId );
                    $confirm = $confirmQ['data'];
                    
                    if( $confirm != null ){
                        $cancel = $this->cancelaAssist( $confirm );
                        
                        if( $cancel['err'] ){
                            errResponse($cancel['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                        }else{
                            $this->db->query("UPDATE cycoasis_rsv.r_assist SET status=0, fecBaja=NOW() WHERE codigo='$confirm'");
                        }
                    }
                    
                    $rsv->manageItem( true, $pkId, "it.itemId = $pkId" );
                    $iQ = $this->db->query("SELECT * FROM items");
                    $ig = $iQ->row_array();
                           
                    $itemData = $ig;
                    $itemData['xldType'] = 'traspaso';
                    $itemData['penalidad'] = 0;
                            
                    $insCancel = $this->cancelItemV2(array('data' => $itemData, 'flag' => false), false, false);
            
                    if( $insCancel['err'] ){
                        errResp($insCancel['err']['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $insCancel['error']);
                    }
                }else{
                    if( $data['pkgNew'] ){
                        $this->db->set(array('pkgActive' => 0))->where('itemId', $pkId)->update('cycoasis_rsv.r_items');
                    }
                }
                                
            }
            
            if( $data['pkgNew'] ){
                $this->package( array('itemId' => $item['itemId']) );
            }
            
        }
        
        $msg = "Cambios aplicados a localizador ".$item['itemLocatorId']." por ".($_GET['usn'] ?? 'desconocido').".<br>".$this->getChanges($data['changes'], $item);
        
        if( $insRemoved ){ 
            $msg .= $insSeguroAdded ? "<br>Seguro Modificado" : "<br>Seguro Eliminado"; 
            
        }
        $this->zd->saveHistory( $item['mlTicket'], $msg );
        okResp('Cambios aplicados, reserva sin paquete. Deseas empaquetar?', 'data', true);
        
        
        
    }
    
    private function getChanges( $ch, $og, $pack = false ){
        
        $msg = "";
        
        if( $ch['llegada']  != $og['llegada'] || $ch['salida']   != $og['salida'] || $ch['nights']   != $og['htlNoches'] ){ 
            $msg .= "<br><b>Fechas</b>-> Antes: ".$og['llegada']." a ".$og['salida']." (".$og['htlNoches']." noches) || Ahora: ".$ch['llegada']." a ".$ch['salida']." (".$ch['nights']." noches)"; 
        }

        if( $ch['adultos']  != $og['adultos'] || $ch['juniors']  != $og['juniors'] || $ch['menores']  != $og['menores'] ){ 
            $msg .= "<br><b>Ocupacion</b>-> Antes: ".$og['adultos'].".".$og['juniors'].".".$og['menores']." || Ahora: ".$ch['adultos'].".".$ch['juniors'].".".$ch['menores']; 
        }
        
        if( $ch['cat']      != $og['categoria']       ){ $msg .= "<br><b>Categoria</b>-> Antes: ".$og['categoria']." || Ahora: ".$ch['cat'];  }
        
        if( !$pack ){
            if( $ch['monto']    != $og['monto']     ){ $msg .= "<br><b>Monto Total</b>-> Antes: ".$og['monto']." || Ahora: ".$ch['monto']; }
        }
        
        return $msg;
    }
    
    
    public function package_put(){
        tokenValidation12( function(){
        
            $data = $this->put();
            $this->package($data);
        });
    }
    
    private function package($data){
        
            
            $cotizador = model_cotizador();
            $result = $cotizador->empaquetarHospedaje($data['itemId']);
            
            if( !$result['err'] ){
                
                $montoQ = $this->db->from('cycoasis_rsv.r_monto')->where('itemId', $data['itemId'])->get();
                $montoR = $montoQ->row_array();
                
                $monto = $result['quote']['monto'];
                $monto['montoParcial'] = $montoR['montoParcial'] == 0 ? 0 : ( floatVal($montoR['montoParcial']) - floatVal($result['quote']['quote']['insRate']) * count($result['quote']['insurance']) );
                
                if( $monto['montoParcial'] < 0 ){
                   errResp('El monto parcial no permite empaquetar', REST_Controller::HTTP_BAD_REQUEST, 'error', $monto); 
                }
                
                $monto['montoPagado'] = $montoR['montoPagado'] == 0 ? 0 : ( floatVal($montoR['montoPagado']) - floatVal($result['quote']['quote']['insRate']) * count($result['quote']['insurance']) );
                
                // crear transacciones para traspaso
                if( $monto['montoPagado'] > 0 ){
                    $rsv = model_rsv();
                    $rsv->manageItem(true, $data['itemId'], "it.itemId = ".$data['itemId']);
                    $ogQ = $this->db->query("SELECT * FROM items");
                    $og = $ogQ->row_array();
                    
                    $og['pkgItems'] = $og['jsonPkg'] == null ? array() : json_decode($og['jsonPkg']);
                    $og['pkgItems']->servicios = json_decode($og['pkgItems']->servicios);
                    $og['pkgItems']->itemDets = json_decode($og['pkgItems']->itemDets);
                    $og['pkgItems']->pkgInsItemIds = json_decode($og['pkgInsItemIds']);

                    $cash = array(
                            "original" => $og,
                            "isR" => false,
                            "itemId" => $data['itemId'],
                            "new" => array(
                                    "montoParcial" => $monto['montoPagado']
                                )
                        );
                    
                    $this->editMontoParcial($cash, false);
                        
                    unset( $monto['montoPagado'] );
                    unset( $monto['montoParcial'] );
                }
                
                
                
                $this->db->set($monto)->where('itemId', $data['itemId'])->update('cycoasis_rsv.r_monto');
                
                $inserts = array();
                
                foreach( $result['quote']['insurance'] as $index => $ins ){
                    array_push($inserts, $this->saveRsvFnc($ins));
                }
                
                okResp($result['msg'], 'data',$result['data'], 'quote', $result['quote']);
            }else{
                errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }
        
        
    }
  
    public function cotizaChange_put(){
        // tokenValidation12( function(){
        
            $data = $this->put();
            
            $cotizador = model_cotizador();
            $result = $cotizador->cambioHospedaje( $data['itemId'], $data['inicio'], $data['fin'] );
            
            if( !$result['err'] ){
                okResp($result['msg'], 'data',$result['data'], 'assist', $result['assist']);
            }else{
                errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }
        
        // });
    }
  
    
  
  function updateOR( $ml, $flag ){
      //   Revisa si el usuario exta en la base del cyc
        $existsQ = $this->db->select('orw.id, splitName(nombreCliente,1) as nombre, splitName(nombreCliente,2) as apellido, correoCliente as correo', FALSE)
            ->from('cycoasis_rsv.r_masterlocators a')->join('cycoasis_rsv.or_master orw','a.correoCliente = orw.correo', 'left')->where('masterlocatorid',$ml)->get();
        $existsR = $existsQ->row_array();
        
        // okResponse('Usuario Actualizado', 'data', $existsR, $this);
      
        if( $existsR['id'] == null ){
          
          $mapping = array(
                'id'        =>  "id",
                'nombre'    =>  "nombre",
                'apellido'  =>  "apellidos",
                'correo'    =>  "email",
                'alta'      =>  "alta",
                'idioma_2'  =>  "idioma_alta",
                'origen'    =>  "origen_alta",
                'proyecto'  =>  "proyecto_alta"
            );
            
            // Revisa si el usuario ya esta registrado en RB
            $original = loyaltyRB('get',$existsR['correo']);
            
            // Si no existe, lo crea
            if( isset($original['codigo']) && $original['codigo'] == '051' ){
                $result = loyaltyRB('create',array("nombre"=>$existsR['nombre'],"apellidos"=>$existsR['apellido'],"email"=>$existsR['correo']));
                
                if( isset($result['error']) && !$result['error'] ){
                    $original = loyaltyRB('get',$existsR['correo']);
                }else{
                    errResponse('Error al crear usuario', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $result);
                }
                
            }
            
            // Actualiza la base del CYC
            $insert = array(
                'codigo' => 'basic',
                'nombre_del_nivel' => 'Silver'
            );
            
            foreach( $mapping as $field => $f ){
                $insert[$field] = $original[$f];
            }
            
            $this->db->set($insert)->insert('cycoasis_rsv.or_roiback');
            $this->db->set($insert)->insert('cycoasis_rsv.or_master');
          
      }
      
      if ($this->db->query("UPDATE cycoasis_rsv.r_masterlocators a
                                    LEFT JOIN
                                cycoasis_rsv.or_master orw ON a.correoCliente = orw.correo 
                            SET 
                                orId = orw.id,
                                orLevel = orw.nombre_del_nivel
                            WHERE
                                masterlocatorid=".$ml." AND dtCreated>='20210501'") ){
                                    
            
            if( $flag ){
                $resultQ = $this->db->select('orId,orLevel')->from('cycoasis_rsv.r_masterlocators')->where('masterlocatorid', $ml)->get();
                $result = $resultQ->row_array();
                if( $result['orId'] == null ){
                    errResponse('Esta reserva no aplica para OREWARDS con un usuario nuevo', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }else{
                    okResponse('Usuario Actualizado', 'data', $result, $this);
                }
            }else{
                return true;
            }
        }else{
            if( $flag ){
                errResponse('No se pudo crear el masterLocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }else{
                return false;
            }
        }
        
  }
  
  public function updateORewardsUser_put(){
      $data = $this->put();
      
      $this->updateOR( $data['masterlocatorid'], true );
      
  }
  
    // DEPRECATING SOON
  
  public function saveRsv_put(){
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
        $data = $this->put();
        $this->saveRsvFnc($data);
      });
  }
  
  private function saveRsvFnc( $data, $flag = false ){
      

        $masterFlag = !isset($data['masterLoc']);
        
        if( !isset($data['masterLoc']) ){
            
            $insertMaster = $data['master'];
            
            if( !isset($data['hasTransfer']) ){
                $data['hasTransfer'] = 0;
            }
            
            $insertMaster['hasTransfer'] = $data['hasTransfer'] ? 1 : 0;
            $insertMaster['xldPol'] = isset($data['item']['habs']) ? ($data['item']['habs'][0] ? $data['item']['habs'][0]['xldPolicy'] : 'default') : 'default';
            
            if( $this->db->set($insertMaster)->insert('cycoasis_rsv.r_masterlocators') ){
                $data['masterLoc'] = $this->db->insert_id();
                
                $this->updateOR( $data['masterLoc'], false );
                
                // $this->db->query("UPDATE cycoasis_rsv.r_masterlocators a
                //                     LEFT JOIN
                //                 cycoasis_rsv.or_master orw ON a.correoCliente = orw.correo 
                //             SET 
                //                 orId = orw.id,
                //                 orLevel = orw.nombre_del_nivel
                //             WHERE
                //                 masterlocatorid=".$data['masterLoc']);
                
                $newTicket = array("ticket" => array(
                    "subject" => "Historial para localizador ".$data['masterLoc'],
                        "requester_id" => 373644140032,
                        "submitter_id" => 373644140032, 
                    "group_id" => 360006241112,
                    "assignee_id" => 373644140032,
                    "status" => "solved",
                    "tags" => array("rsv_history", "rsva-".$data['masterLoc']),
                    "comment" => array("body" => "****** Inicio de Historial, reserva creada en ComeyCome. Masterlocator: ".$data['masterLoc']." ******", "public" => false, "author_id" => 373644140032)));
        
                $tkt = json_encode($newTicket);
                $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
    
                $this->db->where('masterlocatorid',$data['masterLoc'])->set(array('historyTicket' => $response['data']->{'ticket'}->{'id'}))->update('cycoasis_rsv.r_masterlocators');
                $mlTicket = $response['data']->{'ticket'}->{'id'};
            }else{
                errResponse('No se pudo crear el masterLocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
        }else{
            
            $mlQ = $this->db->query("SELECT historyTicket, languaje, hasTransfer  FROM cycoasis_rsv.r_masterlocators WHERE masterlocatorid = ".$data['masterLoc']);
            $mlR = $mlQ->row_array();
            $mlTicket = $mlR['historyTicket'];
            $lang = $mlR['languaje'];
            
            $mlupdt = array();
            
            if( $data['type'] == 'xfer' ){
                 $mlupdt['hasTransfer'] = 0;
            }
            
            if( $lang == null ){
                $mlupdt['languaje'] = $data['master']['languaje'];
            }
            
            if( count($mlupdt) > 0 ){
            $this->db->where('masterlocatorid',$data['masterLoc'])->set($mlupdt)->update('cycoasis_rsv.r_masterlocators');
            }
        }
        
        $iq = $this->db->query("SELECT COUNT(*) as items FROM cycoasis_rsv.r_items WHERE masterlocatorId=".$data['masterLoc']);
        
        $ir = $iq->row_array();
        $i = intval($ir['items']) + 1;
        
        switch($data['type']){
            case 'hotel':
                $this->hotelRsv($data, $i, $data['masterLoc'], $masterFlag, $mlTicket);
                break;
            case 'auto':
                $this->autoRsv($data, $i, $data['masterLoc'], $masterFlag, $mlTicket);
                break;
            case 'daypass':
                $this->daypassRsv($data, $i, $data['masterLoc'], $masterFlag, $mlTicket);
                break;
            case 'xfer':
                $this->xferRsv($data, $i, $data['masterLoc'], $masterFlag, $mlTicket);
                break;
            case 'seguro':
                return $this->insuranceRsv($data, $i, $data['masterLoc'], $masterFlag, $mlTicket, false, $flag);
                break;
            case 'seguro-i':
                return $this->insuranceRsv($data, $i, $data['masterLoc'], $masterFlag, $mlTicket, true, true);
                break;
            case 'tour':
                $this->tourRsv($data, $i, $data['masterLoc'], $masterFlag, $mlTicket);
                break;
            case 'concert':
                $this->concertRsv($data, $i, $data['masterLoc'], $masterFlag, $mlTicket);
                break;
        }
        

  }
  
  public function concertRsv($data, $i, $master, $masterFlag, $mlTicket){

    $dp = $data['item'];
    $itemsCreados = array();
      
    $item = array(
           'masterlocatorid' => $data['masterLoc'],
           'itemNumber' => $i,
           'itemLocatorId' =>  $data['masterLoc']."-".$i,
           'itemType' => 9,
           'userCreated' => $_GET['usid']
        );
            
    $this->db->insert('cycoasis_rsv.r_items', $item);
    $itemId = $this->db->insert_id();
    
    $dp['itemId'] = $itemId;
    $dpTable = array(
        'productId' => $dp['concertId'],
        'serviceId' => 9,
        'inicio' => $dp['date'],
        'notasOperador' => isset($dp['notasOperador']) ? $dp['notasOperador'] : '',
        'itemId' => $dp['itemId'],
        'pax_q' => $dp['adultos'] + $dp['menores']
        );

    $ammount = $data['moneda'] ? $dp['totalMXN'] : $dp['totalUSD'];
    $monto = array(
        'itemId' => $itemId,
        'montoOriginal' => $ammount,
        'lv' => 1,
        'monto' => $ammount,
        'montoParcial' => $ammount,
        'moneda' => $data['moneda'] ? 'MXN' : 'USD',
        'isPagoHotel' => 0,
        'grupo' => $dp['evento']
        );
    
    if( $this->db->insert('cycoasis_rsv.r_xtras', $dpTable) ){
        if( !$this->db->insert('cycoasis_rsv.r_monto', $monto) ){
            $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'concert');
        }
    }else{
        $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'concert');
        errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
    }
    
    array_push($itemsCreados,$itemId);
    $i++;
    
    $msg = "Item de Concierto ".$item['itemLocatorId']." creado por ".$_GET['usn'];
    $this->zd->saveHistory( $mlTicket, $msg );           
    
    okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterLoc'], 'items'=>$itemsCreados), $this);
  }
  
  public function insuranceRsv($data, $i, $master, $masterFlag, $mlTicket, $inclusion = false, $flag){
      
    $insertItem = $data['item'];
    $insertMonto = $data['monto'];
    
    $itemsCreados = array();
      
    $item = array(
           'masterlocatorid' => $data['masterLoc'],
           'itemNumber' => $i,
           'itemLocatorId' =>  $data['masterLoc']."-".$i,
           'itemType' => $inclusion ? 15 : 10,
           'userCreated' => $_GET['usid'],
        );
        
    if( $inclusion ){
        $item['pkgItemId'] = $data['itemId'];
    }else{
        $item['parentItem'] = $data['itemId'];
    }
            
    $this->db->insert('cycoasis_rsv.r_items', $item);
    $itemId = $this->db->insert_id();
    
    $insertItem['itemId'] = $itemId;
    $insertMonto['itemId'] = $itemId;
    
    $insertMonto['lv'] = 1;
    $insertMonto['isPagoHotel'] = 0;
    $insertMonto['montoOriginal'] = $data['monto']['monto'];
    $insertMonto['montoParcial'] = $data['monto']['monto'];
    $insertMonto['grupo'] = $inclusion ? 'assistCard-i' : 'assistCard';
    
    if( $this->db->insert('cycoasis_rsv.r_seguros', $insertItem) ){
        if( !$this->db->insert('cycoasis_rsv.r_monto', $insertMonto) ){
            $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'seguro');
        }else{
            if( !$inclusion ){
                $this->db->set(array("insuranceRelated"=>$itemId))->where(array('itemId'=>$data['itemId']))->update('cycoasis_rsv.r_items');
            }
        }
    }else{
        $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'seguro');
        errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
    }
    
    array_push($itemsCreados,$itemId);
    $i++;
    
    if( !$inclusion ){
        $msg = "Seguro creado en ".$item['itemLocatorId']." relacionado al item de hospedaje ".$data['item']['sg_itemRelated']." ||creado por ".$_GET['usn'];
    }else{
        $msg = "Paquete armado en ".$item['itemLocatorId'];
    }
    
    $this->zd->saveHistory( $mlTicket, $msg );           
    
    if( $flag ){
        return array('masterlocator'=>$data['masterLoc'], 'items'=>$itemsCreados);
    }else{
        okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterLoc'], 'items'=>$itemsCreados), $this);
    }
  }
  
  public function tourRsv($data, $i, $master, $masterFlag, $mlTicket){
      
    $dp = $data['item'];
    $itemsCreados = array();
      
    $item = array(
           'masterlocatorid' => $data['masterLoc'],
           'itemNumber' => $i,
           'itemLocatorId' =>  $data['masterLoc']."-".$i,
           'itemType' => 4,
           'userCreated' => $_GET['usid']
        );
            
    $this->db->insert('cycoasis_rsv.r_items', $item);
    $itemId = $this->db->insert_id();
    
    $dp['itemId'] = $itemId;
    $dpTable = array(
        'tourId' => $dp['id'],
        'pickup' => $dp['pickup'],
        'adultos' => $dp['adultos'],
        'menores' => $dp['menores'],
        'fecha' => $dp['fecha'],
        'notasOperador' => isset($dp['notasOperador']) ? $dp['notasOperador'] : '',
        'itemId' => $dp['itemId']
        );
    
    $ammount = $data['moneda'] ? $dp['totalMXN'] : $dp['totalUSD'];
    $monto = array(
        'itemId' => $itemId,
        'montoOriginal' => $ammount,
        'lv' => 1,
        'monto' => $ammount,
        'montoParcial' => $ammount,
        'moneda' => $data['moneda'] ? 'MXN' : 'USD',
        'isPagoHotel' => 0,
        'grupo' => $dp['grupo']
        );
    
    if( $this->db->insert('cycoasis_rsv.r_tour', $dpTable) ){
        if( !$this->db->insert('cycoasis_rsv.r_monto', $monto) ){
            $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'tour');
        }
    }else{
        $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'tour');
        errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
    }
    
    array_push($itemsCreados,$itemId);
    $i++;
    
    $msg = "Item de tour ".$item['itemLocatorId']." creado por ".$_GET['usn'];
    $this->zd->saveHistory( $mlTicket, $msg );           
    
    okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterLoc'], 'items'=>$itemsCreados), $this);
  }
  
  public function xferRsv($data, $i, $master, $masterFlag, $mlTicket,$notExit = false){
      
    $dp = $data['item'];
    $itemsCreados = array();
      
    $item = array(
           'masterlocatorid' => $data['masterLoc'],
           'itemNumber' => $i,
           'itemLocatorId' =>  $data['masterLoc']."-".$i,
           'itemType' => 5,
           'userCreated' => $_GET['usid']
        );
            
    $this->db->insert('cycoasis_rsv.r_items', $item);
    $itemId = $this->db->insert_id();
    
    $dp['itemId'] = $itemId;
    $dpTable = array(
        'xferId' => $dp['xferId'],
        'hotel' => $dp['hotel'],
        'zone' => $dp['zone'],
        'adultos' => $dp['adultos'],
        'menores' => $dp['menores'],
        'infantes' => $dp['infantes'],
        'fecha_in' => $dp['fecha'],
        'hora_in' => $dp['horaLlegada'],
        'vuelo_in' => $dp['vueloLlegada'],
        'aerolinea_in' => $dp['alLlegada'],
        'notasOperador' => isset($dp['notasOperador']) ? $dp['notasOperador'] : '',
        'itemId' => $dp['itemId']
        );
    
    if( isset($dp['itemRelated']) ){
        $dpTable = $dp['itemRelated'];
    }
    
    if( $dp['xferType'] == 'round' ){
        $dpTable['fecha_out'] = $dp['fechaSalida'];
        $dpTable['hora_out'] = $dp['horaSalida'];
        $dpTable['vuelo_out'] = $dp['vueloSalida'];
        $dpTable['aerolinea_out'] = $dp['alSalida'];
    }    
        
    $ammount = $data['moneda'] ? $dp['totalMXN'] : $dp['totalUSD'];
    $monto = array(
        'itemId' => $itemId,
        'montoOriginal' => $ammount,
        'lv' => 1,
        'monto' => $ammount,
        'montoParcial' => $ammount,
        'moneda' => $data['moneda'] ? 'MXN' : 'USD',
        'isPagoHotel' => 0,
        'grupo' => $dp['grupo']
        );
    
    if( $this->db->insert('cycoasis_rsv.r_xfer', $dpTable) ){
        if( !$this->db->insert('cycoasis_rsv.r_monto', $monto) ){
            $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'xfer');
        }
    }else{
        $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'xfer');
        errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
    }
    
    array_push($itemsCreados,$itemId);
    $i++;
    
    $msg = "Item de traslado ".$item['itemLocatorId']." creado por ".$_GET['usn'];
    $this->zd->saveHistory( $mlTicket, $msg );           
    
    if( !$notExit ){
        okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterLoc'], 'items'=>$itemsCreados), $this);
    }
  }
  
  public function autoRsv($data, $i, $master, $masterFlag, $mlTicket){
      
    $dp = $data['item'];
    $itemsCreados = array();
      
    $item = array(
           'masterlocatorid' => $data['masterLoc'],
           'itemNumber' => $i,
           'itemLocatorId' =>  $data['masterLoc']."-".$i,
           'itemType' => 3,
           'userCreated' => $_GET['usid']
        );
            
    $this->db->insert('cycoasis_rsv.r_items', $item);
    $itemId = $this->db->insert_id();
    
    $dp['itemId'] = $itemId;
    $dpTable = array(
        'productId' => $dp['autoId'],
        'serviceId' => 3,
        'fin' => date('Y-m-d', strtotime($dp['Fin'])),
        'inicio' => date('Y-m-d', strtotime($dp['Inicio'])),
        'horaInicio' => date('H:i:s', strtotime($dp['Inicio'])),
        'horaFin' => date('H:i:s', strtotime($dp['Fin'])),
        'notasOperador' => isset($dp['notasOperador']) ? $dp['notasOperador'] : '',
        'itemId' => $dp['itemId'],
        'rqChar01' => $dp['pickup'],
        'dias' => $dp['dias']
        );

    $ammount = $data['moneda'] ? $dp['totalMXN'] : $dp['totalUSD'];
    $monto = array(
        'itemId' => $itemId,
        'montoOriginal' => $ammount,
        'lv' => 1,
        'monto' => $ammount,
        'montoParcial' => $ammount,
        'moneda' => $data['moneda'] ? 'MXN' : 'USD',
        'isPagoHotel' => 0,
        'grupo' => $dp['grupo']
        );
    
    if( $this->db->insert('cycoasis_rsv.r_xtras', $dpTable) ){
        if( !$this->db->insert('cycoasis_rsv.r_monto', $monto) ){
            $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'auto');
        }
    }else{
        $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'auto');
        errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
    }
    
    array_push($itemsCreados,$itemId);
    $i++;
    
    $msg = "Item de Auto ".$item['itemLocatorId']." creado por ".$_GET['usn'];
    $this->zd->saveHistory( $mlTicket, $msg );           
    
    okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterLoc'], 'items'=>$itemsCreados), $this);
  }
  
  public function daypassRsv($data, $i, $master, $masterFlag, $mlTicket,$notExit = false){
      
    $dp = $data['item'];
    $itemsCreados = array();
      
    $item = array(
           'masterlocatorid' => $data['masterLoc'],
           'itemNumber' => $i,
           'itemLocatorId' =>  $data['masterLoc']."-".$i,
           'itemType' => 2,
           'userCreated' => $_GET['usid']
        );
            
    $this->db->insert('cycoasis_rsv.r_items', $item);
    $itemId = $this->db->insert_id();
    
    if( !isset($dp['juniors']) ){
        $jr = 0;
    }else{
        $jr = $dp['juniors'];
    }
    
    $dp['itemId'] = $itemId;
    $dpTable = array(
        'hotel' => $dp['hotel'],
        'dayPassType' => $dp['id'],
        'adultos' => $dp['adultos'],
        'juniors' => $jr,
        'menores' => $dp['menores'],
        'fecha' => $dp['fecha'],
        'itemId' => $dp['itemId']
        );
        
    if( isset($dp['itemRelated']) ){
        $dpTable = $dp['itemRelated'];
    }
        
    if( isset($dp['notasHotel']) ){
        $dpTable['notasHotel'] = $dp['notasHotel'];
    }
    
    $ammount = $data['moneda'] ? $dp['totalMXN'] : $dp['totalUSD'];
    $monto = array(
        'itemId' => $itemId,
        'montoOriginal' => $ammount,
        'montoParcial' => $ammount,
        'lv' => 1,
        'monto' => $ammount,
        'moneda' => $data['moneda'] ? 'MXN' : 'USD',
        'isPagoHotel' => 0,
        );
    
    if( $this->db->insert('cycoasis_rsv.r_daypass', $dpTable) ){
        if( !$this->db->insert('cycoasis_rsv.r_monto', $monto) ){
            $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'daypass');
        }
    }else{
        $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'daypass');
        errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
    }
    
    array_push($itemsCreados,$itemId);
    $i++;
    
    $msg = "Item de daypass ".$item['itemLocatorId']." creado por ".$_GET['usn'];
    $this->zd->saveHistory( $mlTicket, $msg ); 
    
    if( !$notExit ){
        okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterLoc'], 'items'=>$itemsCreados), $this);
    }
  }
  
  public function hotelRsv($data, $i, $master, $masterFlag, $mlTicket){
      $itemsCreados = array();
        
        foreach( $data['habs'] as $index => $info ){
            
            $data['habs'][$index]['item']['masterlocatorid'] = $data['masterLoc'];
            $data['habs'][$index]['item']['itemNumber'] = $i;
            $data['habs'][$index]['item']['itemLocatorId'] = $data['masterLoc']."-".$i;
            
            if( $data['habs'][$index]['hotel']['gpoCC'] == 'openDates_2020' ){
                $data['habs'][$index]['item']['isOpen'] = 1;
                $data['habs'][$index]['item']['limite_od'] = '20211201';
                $data['habs'][$index]['item']['travelLimit'] = '20211210';
                $data['habs'][$index]['item']['originalDate'] = '20211210';
                
                $info['hotel']['isNR'] =  1;
                $info['hotel']['fin'] =  '20211219';
                $endDate = new DateTime("2021-12-19");
                $startDate = $endDate->sub(new DateInterval('P'.$data['item']['habs'][$index]['noches'].'D'));
                $info['hotel']['inicio'] =  $startDate->format('Y-m-d');
            }
            
            // Item or Monto Shown
            if( $data['habs'][$index]['hotel']['gpoCC'] == 'BOX_2020' ){
                $data['habs'][$index]['item']['showMontoInConfirm'] = 0;
            }
            
            $this->db->insert('cycoasis_rsv.r_items', $data['habs'][$index]['item']);
            $itemId = $this->db->insert_id();
            
            $info['hotel']['itemId'] = $itemId;
            $info['hotel']['notasHotel'] =  isset($data['item']['habs'][$index]['notasHotel']) ? $data['item']['habs'][$index]['notasHotel'] : '';
            
            
            $info['monto']['itemId'] = $itemId;
            $info['monto']['grupo'] = $data['habs'][$index]['hotel']['gpoTfa'];
            $info['monto']['grupoTfas'] = $data['habs'][$index]['hotel']['gpoCC'];
            
            if($info['monto']['isPagoHotel'] != 1){
                $info['monto']['montoParcial'] = $info['monto']['monto'];
            }
            
            if( isset( $info['hotel']['bedPreference'] ) ){
                $info['hotel']['bedPreference'] = $info['hotel']['bedPreference'] == 2 ? 'Double Beds' : 'King';
            }else{
                $info['hotel']['bedPreference'] = 'N/A';
            }
            
            if( $info['monto']['lv'] >= 3 ){
                $info['hotel']['isNR'] =  1;
            }
            
            $tmpLv = $info['monto']['lv'];
            
            $endBfin = new DateTime("2020-12-20");
            $endRsva = new DateTime($info['hotel']['fin']);
            
            switch( $info['monto']['lv'] ){
                case 1:
                    if( ($endRsva <= $endBfin && ($info['monto']['grupoTfas'] == 'AGN_Nov_2020' || $info['monto']['grupoTfas'] == 'Nov_2020' || $info['monto']['grupoTfas'] == 'Nov_2020 - Locales') && $info['hotel']['hotel'] != 'SMART' && $info['hotel']['hotel'] != 'OH') || $info['monto']['grupoTfas'] == 'beFree_2020' || $info['monto']['grupoTfas'] == 'AGN_beFree_2020' || $info['monto']['grupoTfas'] == 'beFree - Locales_2020' ){
                        switch( intVal($info['hotel']['noches']) ){
                            case 1:
                            case 2:
                            case 3:
                                $tmpLv = $data['item']['habs'][$index]['code1'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code1'];
                                break;
                            case 4:
                            case 5:
                            case 6:
                                $tmpLv = $data['item']['habs'][$index]['code4'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code4'];
                                break;
                            default:
                                $tmpLv = $data['item']['habs'][$index]['code7'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code7'];
                                break;
                        }
                            
                    }else{
                        $tmpLv = $data['item']['habs'][$index]['code1'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code1'];
                    }
                    break;
                case 2:
                    if( ($endRsva <= $endBfin && ($info['monto']['grupoTfas'] == 'AGN_Nov_2020' || $info['monto']['grupoTfas'] == 'Nov_2020' || $info['monto']['grupoTfas'] == 'Nov_2020 - Locales') && $info['hotel']['hotel'] != 'SMART' && $info['hotel']['hotel'] != 'OH') || $info['monto']['grupoTfas'] == 'beFree_2020' || $info['monto']['grupoTfas'] == 'AGN_beFree_2020' || $info['monto']['grupoTfas'] == 'beFree - Locales_2020' ){
                        switch( intVal($info['hotel']['noches']) ){
                            case 1:
                            case 2:
                            case 3:
                                $tmpLv = $data['item']['habs'][$index]['code2'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code2'];
                                break;
                            case 4:
                            case 5:
                            case 6:
                                $tmpLv = $data['item']['habs'][$index]['code5'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code5'];
                                break;
                            default:
                                $tmpLv = $data['item']['habs'][$index]['code8'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code8'];
                                break;
                        }
                            
                    }else{
                        $tmpLv = $data['item']['habs'][$index]['code2'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code2'];
                    }
                    break;
                case 3:
                    if( ($endRsva <= $endBfin && ($info['monto']['grupoTfas'] == 'AGN_Nov_2020' || $info['monto']['grupoTfas'] == 'Nov_2020' || $info['monto']['grupoTfas'] == 'Nov_2020 - Locales') && $info['hotel']['hotel'] != 'SMART' && $info['hotel']['hotel'] != 'OH') || $info['monto']['grupoTfas'] == 'beFree_2020' || $info['monto']['grupoTfas'] == 'AGN_beFree_2020' || $info['monto']['grupoTfas'] == 'beFree - Locales_2020' ){
                        switch( intVal($info['hotel']['noches']) ){
                            case 1:
                            case 2:
                            case 3:
                                $tmpLv = $data['item']['habs'][$index]['code3'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code3'];
                                break;
                            case 4:
                            case 5:
                            case 6:
                                $tmpLv = $data['item']['habs'][$index]['code6'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code6'];
                                break;
                            default:
                                $tmpLv = $data['item']['habs'][$index]['code9'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code9'];
                                break;
                        }
                            
                    }else{
                        $tmpLv = $data['item']['habs'][$index]['code3'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code3'];
                    }
                    break;
                case 4:
                    $tmpLv = $data['item']['habs'][$index]['code5'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code5'];
                    break;
                case 5:
                    $tmpLv = $data['item']['habs'][$index]['code5'] == null ? $info['monto']['lv'] : $data['item']['habs'][$index]['code5'];
                    break;
            }
            
            $info['monto']['lv'] = $tmpLv;
            
            // Ajuste de monto por paquete
                    
                // Box
                if( $data['item']['habs'][$index]['gpotfaOK'] == "BOX_2020" ){
                    $paxXtra = intval($data['item']['habs'][$index]['rateAdults']) + intval($data['item']['habs'][$index]['rateMinors']);
                    $info['hotel']['isNR'] = 1;
                    $servId = 10;
                
                    switch($info['monto']['lv']){
                        case 1:
                        case "1":
                            $servId = 8;
                            $xtraMontoInd = $info['monto']['moneda'] == 'MXN' ? 800 : 40;
                            $catXtra = "RingSide para Box Barby vs Cobrita 2020";
                            break;
                        case 2:
                        case "2":
                            $servId = 9;
                            $xtraMontoInd = $info['monto']['moneda'] == 'MXN' ? 500 : 25;
                            $catXtra = "Zona VIP para Box Barby vs Cobrita 2020";
                            break;
                        case 3:
                        case "3":
                            $servId = 10;
                            $xtraMontoInd = $info['monto']['moneda'] == 'MXN' ? 250 : 12.5;
                            $catXtra = "Preferente para Box Barby vs Cobrita 2020";
                            break;
                    }
                    
                    $info['hotel']['notasHotel'] .= ' Incluye '.$paxXtra.' entradas '.$catXtra;
                    $info['monto']['monto'] = $info['monto']['monto'] - $xtraMontoInd * ($paxXtra);
                    $info['monto']['montoParcial'] = $info['monto']['monto'];
                }
            
            if( $this->db->insert('cycoasis_rsv.r_hoteles', $info['hotel']) ){
                
                if( !$this->db->insert('cycoasis_rsv.r_monto', $info['monto']) ){
                    $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'hotel');
                    errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
                
                $xtraMsg = "";
                
                // Complemento BOX
                if( $data['item']['habs'][$index]['gpotfaOK'] == "BOX_2020" ){
                    
                    $i++;
                    
                    $xtraItem = $data['habs'][$index]['item'];
                    $xtraItem['relatedItem'] = $itemId;
                    $xtraItem['showItemInConfirm'] = 0;
                    $xtraItem['itemNumber'] = $i;
                    $xtraItem['itemLocatorId'] = $data['masterLoc']."-".$i;
                    $xtraItem['itemType'] = 9;
                    
                    $this->db->insert('cycoasis_rsv.r_items', $xtraItem);
                    $xtraItemId = $this->db->insert_id();
                    
                    
                    $xtraArr = array(
                            "itemId" => $xtraItemId,
                            "serviceId" => 9,
                            "productId" => $servId,
                            "inicio" => '2020-10-31',
                            "pax_q" => intval($data['item']['habs'][$index]['rateAdults']) + intval($data['item']['habs'][$index]['rateMinors'])
                        );
                    $this->db->insert('cycoasis_rsv.r_xtras', $xtraArr);
                        
                    $xtraMonto = array(
                            "itemId" => $xtraItemId,
                            "lv" => 1,
                            "grupo" => "BOX20",
                            "grupoTfas" => "BOX_2020",
                            "moneda" => $info['monto']['moneda'],
                            "montoOriginal" => $xtraMontoInd * $xtraArr['pax_q'],
                            "monto" => $xtraMontoInd * $xtraArr['pax_q'],
                            "montoParcial" => $xtraMontoInd * $xtraArr['pax_q'],
                            "montoPagado" => 0,
                            "isPagoHotel" => 0,
                            "isParcial" => 0,
                            "promo" => "C",
                        );
                    $this->db->insert('cycoasis_rsv.r_monto', $xtraMonto);
                    
                    $xtraMsg = "  y complemento ".$xtraItem['itemLocatorId'];
                        
                }
                
            }else{
                $this->deleteMaster($itemId, $master, $itemsCreados, $masterFlag, 'hotel');
                errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
            array_push($itemsCreados,$itemId);
            
            
            $i++;
            $msg = "Item de hotel ".$data['habs'][$index]['item']['itemLocatorId'].$xtraMsg;
            
            if($info['hotel']['gpoCC'] == 'ccenter_pkgSB2020'){
                $lvl = intVal($info['monto']['lv']);
                $noches = intVal($info['hotel']['noches']);
                for($x=($lvl > 2 ? 1 : 0);$x<$noches;$x++){
                    
                    // Item suplemento daypass
                    $fecha = date('Y-m-d', strtotime($info['hotel']['inicio']. ' + '.$x.' days'));
                    $pax = intVal($info['hotel']['adultos']) + intVal($info['hotel']['juniors']) + intVal($info['hotel']['menores']);
                    
                    $dpass = array(
                            'item' => array(
                                'hotel' => 'GOC',
                                'id' => 44,
                                'adultos' => $info['hotel']['adultos'],
                                'juniors' => 0,
                                'menores' => 0,
                                'fecha' => $fecha,
                                'moneda' => $info['monto']['moneda'],
                                'totalMXN' => (700 * $pax),
                                'totalUSD' => (38.88 * $pax),
                                'notasHotel' => isset($info['hotel']['notasHotel']) ? $info['hotel']['notasHotel'] : ''
                                ),
                            'moneda' => $info['monto']['moneda'],
                            'masterLoc' => $data['masterLoc']
                        );
                        
                    $this->daypassRsv($dpass, $i, $master, $masterFlag, $mlTicket, true);
                    $msg .= " // Item de daypassPackage ".$data['masterLoc']."-".$i;
                    $i++;
                    
                    // Item suplemento xfer
                    // $xfer = array(
                    //         'item' => array(
                    //             'xferId' => 1299,
                    //             'hotel' => 'GOC',
                    //             'zone' => 1,
                    //             'adultos' => $info['hotel']['adultos'],
                    //             'menores' => 0,
                    //             'infantes' => 0,
                    //             'fecha' => $fecha,
                    //             'horaLlegada' => '09:00:00',
                    //             'vueloLlegada' => '18:00:00',
                    //             'alLlegada' => '',
                    //             'notasOperador' => isset($info['hotel']['notasHotel']) ? $info['hotel']['notasHotel'] : '',
                    //             'totalMXN' => (150 * $pax),
                    //             'totalUSD' => (7.89 * $pax),
                    //             'grupo' => 'packSB',
                    //             'fechaSalida' => $fecha,
                    //             'horaSalida' => '18:00:00',
                    //             'vueloSalida' => '',
                    //             'alSalida' => '',
                    //             'xferType' => 'round'
                    //             ),
                    //         'moneda' => $info['monto']['moneda'],
                    //         'masterLoc' => $data['masterLoc']
                    //     );
                        
                        
                    // $this->xferRsv($xfer, $i, $master, $masterFlag, $mlTicket, true);
                    // $msg .= " // Suplemento de xfer de daypassPackage ".$data['masterLoc']."-".$i;
                    // $i++;
                }
                
                
            }
                
            $msg .= " creado por ".$_GET['usn'];
            $this->zd->saveHistory( $mlTicket, $msg ); 
        }
    
    okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterLoc'], 'items'=>$itemsCreados), $this);
  }
  
  public function deleteMaster($itemId, $master, $oldItems, $new, $tipo){
      switch($tipo){
          case 'hotel':
              $this->db->where("itemId", $itemId)->delete('cycoasis_rsv.r_hoteles');
              break;
          case 'daypass':
              $this->db->where("itemId", $itemId)->delete('cycoasis_rsv.r_daypass');
              break;
          case 'xfer':
              $this->db->where("itemId", $itemId)->delete('cycoasis_rsv.r_xfer');
              break;
          case 'seguro':
              $this->db->where("itemId", $itemId)->delete('cycoasis_rsv.r_seguros');
              break;
          case 'tour':
              $this->db->where("itemId", $itemId)->delete('cycoasis_rsv.r_tour');
              break;
          case 'auto':
              $this->db->where("itemId", $itemId)->delete('cycoasis_rsv.r_xtras');
              break;
          case 'concert':
              $this->db->where("itemId", $itemId)->delete('cycoasis_rsv.r_xtras');
              break;
      }
      $this->db->where("itemId", $itemId)->delete('cycoasis_rsv.r_monto');
      $this->db->where("masterlocatorid", $master)->where("itemNumber" > $oldItems)->delete('cycoasis_rsv.r_items');
      if( $new ){
        $this->db->where("masterlocatorid", $master)->delete('cycoasis_rsv.r_masterlocators');
      }
  }
  
//   FIN DEPRECATING SOON
  
  public function linkTicket_put(){
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();
        
        $newTicket = array("ticket" => array(
                "comment" => array("body" => "Reserva creada en ComeyCome. Masterlocator: ".$data['ml'], "public" => false, "author_id" => 373644140032)));

        $tkt = json_encode($newTicket);
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$data['ticket'].'.json';
            
        if( $responseOk = getUrlContent( $url, true, true, $tkt) ){
            
            $urlTkt = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$data['ticket'].'.json';
            
            if( $responseTkt = getUrlContent( $urlTkt ) ){
                $chan = $responseTkt['data']->ticket->via->channel;
                
                $upd = array(
                        'zdTicket' => $data['ticket'],
                        'zdChannel' => $chan
                    );
                
                
                if($this->db->where_in('itemId',$data['tickets'])->where('dtCreated >=','ADDDATE(CURDATE(),-1)',FALSE)->set($upd)->update('cycoasis_rsv.r_items')){
    
                    $mlQ = $this->db->query("SELECT historyTicket, CONCAT(i.masterlocatorid,'-',itemNumber) as item FROM cycoasis_rsv.r_masterlocators a RIGHT JOIN cycoasis_rsv.r_items i ON i.masterlocatorid=a.masterlocatorid WHERE itemId = ".$data['tickets'][0]);
                    $mlR = $mlQ->row_array();
                    $mlTicket = $mlR['historyTicket'];
                    $mlItem = $mlR['item'];
                    $msg = "Ticket ".$data['ticket']." ligado al item $mlItem por ".$_GET['usn'];
                    $this->zd->saveHistory( $mlTicket, $msg ); 
                    
                    okResponse('Ticket Ligado', 'data',true, $this);
                }else{
                  errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
              errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
            
        }else{
          errResponse('Error al editar el ticket', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
        }

      });
  }
  
  public function testTicket_get(){
      
      $data = $this->uri->segment(3);
      
      $newTicket = array("ticket" => array(
                "via" => array("chan" => "api")));

        $tkt = json_encode($newTicket);
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$data.'.json';
        $responseOk = getUrlContent( $url, true, true, $tkt);
        
        $chan = $responseOk['data']->ticket->via->channel;
        
        okResponse('Ticket Ligado', 'data',$responseOk, $this, 'chan', $chan);
  }
  
  public function tableConfig_get(){
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        if( $q = $this->db->where('activo',1)->order_by('order')->from('rsv_listTable')->get() ){
            okResponse('Configuracion Obtenida', 'data',$q->result_array(), $this);
        }else{
          errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
    public function searchLoc_put(){
      
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

            $data = $this->put();
            
            if( $result = $this->searchLoc( $data['val'] ) ){
              okResponse('Reserva Creada', 'data', $result, $this);
            }else{
              errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }

        });
    }
    
    private function searchLoc( $v ){
        $split = explode(" ", $v);
            
        $this->db->select("a.*, MIN(inicio) as inicio")->group_by('a.masterlocatorid')
            ->from('cycoasis_rsv.r_masterlocators a')
            ->join('cycoasis_rsv.r_items b', 'a.masterlocatorid=b.masterlocatorid', 'left')
            ->join('cycoasis_rsv.r_hoteles c', 'b.itemId=c.itemId', 'left')
            ->where('a.masterlocatorid', $v)
            ->or_where('a.nombreCliente LIKE ', "'%".$v."%'", FALSE)
            ->or_where('a.correoCliente LIKE ', "'%".$v."%'", FALSE);
        
        $this->db->or_group_start();
            foreach( $split as $index => $val ){
                $this->db->where('a.nombreCliente LIKE ', "'%$val%'", FALSE);
            }
        $this->db->group_end();
        
        $query = $this->db->get_compiled_select();
        
        if( $q = $this->db->query($query) ){
          return $q->result_array();
        }else{
          return false;
        }
    }
  
    public function updateMlUser_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

            $data = $this->put();

            if( $this->db->set($data['update'])->where(array('masterlocatorid' => $data['masterlocatorid']))->update('cycoasis_rsv.r_masterlocators') ){
                
                if( $result = $this->searchLoc( $data['masterlocatorid'] ) ){
                    okResponse('Reserva Creada', 'data', $result[0], $this);
                }else{
                    okResponse('Reserva Creada', 'data', array(), $this);
                }
            }else{
                errResponse('Error al actualizar datos del cliente en tabla de localizadores', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }

        });
    }
  
  public function searchPay_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();
        $split = explode(" ", $data['val']);
        
        $this->db->select("a.*, MIN(llegada) as llegada")->group_by('a.masterlocatorid')
            ->from('res_master a')
            ->join('res_items b', 'a.masterlocatorid=b.masterlocatorid', 'left')
            ->where('a.masterlocatorid', $data['val'])
            ->or_where('a.nombreCliente LIKE ', "'%".$data['val']."%'", FALSE)
            ->or_where('a.correoCliente LIKE ', "'%".$data['val']."%'", FALSE);
        
        $this->db->or_group_start();
            foreach( $split as $index => $val ){
                $this->db->where('a.nombreCliente LIKE ', "'%$val%'", FALSE);
            }
        $this->db->group_end();
        
        $query = $this->db->get_compiled_select();
        
        if( $q = $this->db->query($query) ){
          okResponse('Reserva Creada', 'data',$q->result_array(), $this);
        }else{
          errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function searchPaymentsToLinkV2_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();
        $split = explode(" ", $data['val']);
        
        $this->db->select("a.masterItemLocator,
                            b.id,
                            titular,
                            a.monto as montoItem,
                            b.moneda,
                            b.monto AS montoRef,
                            CASE 
                        		WHEN paymentType = 0 THEN 'PayPal'
                        		WHEN paymentType = 1 THEN 'Santander'
                        		WHEN paymentType = 2 THEN 'Deposito'
                        	END as tipoPago,
                            NOMBREASESOR(b.createdBy, 1) AS creadorRef,
                            IF(operacion IS NULL,0,1) as linked,
                            GROUP_CONCAT(x.masterItemLocator) AS Locs")
                    ->from('res_payRelates a')
                    ->join('res_payments b', 'a.paymentId = b.id', 'left')
                    ->join('res_items c', 'a.masterItemLocator = c.masterItemLocator', 'left')
                    ->join('res_ligasPago l', 'b.id=l.paymentId', 'left')
                    ->join('res_payRelates x', 'b.id=x.paymentId', 'left')
                    ->where('a.masterItemLocator LIKE \'%', $data['val']."%'", FALSE)
                    ->group_by('a.masterItemLocator')
                    ->order_by('a.masterItemLocator');
        
        
        $query = $this->db->get_compiled_select();
        
        if( $q = $this->db->query($query) ){
          okResponse('Reserva Creada', 'data',$q->result_array(), $this);
        }else{
          errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function searchPaymentsToLink_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();
        $split = explode(" ", $data['val']);
        
        $this->db->select("a.masterItemLocator,
                            b.id,
                            titular,
                            a.monto as montoItem,
                            b.moneda,
                            b.monto AS montoRef,
                            CASE 
                        		WHEN paymentType = 0 THEN 'PayPal'
                        		WHEN paymentType = 1 THEN 'Santander'
                        		WHEN paymentType = 2 THEN 'Deposito'
                        	END as tipoPago,
                            NOMBREASESOR(b.createdBy, 1) AS creadorRef,
                            IF(operacion IS NULL,0,1) as linked,
                            GROUP_CONCAT(x.masterItemLocator) AS Locs")
                    ->from('res_payRelates a')
                    ->join('res_payments b', 'a.paymentId = b.id', 'left')
                    ->join('res_items c', 'a.masterItemLocator = c.masterItemLocator', 'left')
                    ->join('res_ligasPago l', 'b.id=l.paymentId', 'left')
                    ->join('res_payRelates x', 'b.id=x.paymentId', 'left')
                    ->where('a.masterItemLocator LIKE \'%', $data['val']."%'", FALSE)
                    ->group_by('a.masterItemLocator')
                    ->order_by('a.masterItemLocator');
        
        
        $query = $this->db->get_compiled_select();
        
        if( $q = $this->db->query($query) ){
          okResponse('Reserva Creada', 'data',$q->result_array(), $this);
        }else{
          errResponse('Error en la base de datos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function cieloList_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
        $data = $this->put();
        $controlFlag = true;

        if( isset($data['loc']) ){
            $loc = $data['loc'];
            
            if( $qM = $this->db->from('res_master')->where('masterlocatorid',$loc)->get() ){
                $master = $qM->row_array();
            }else{
                errResponse('Error al obtener el masterlocatorid', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }else{
            $master = array();
        }
        
        $cred = $this->db->query("SELECT cielo_viewAll FROM Asesores a LEFT JOIN userDB b ON a.id=b.asesor_id LEFT JOIN cat_profiles c ON b.profile=c.id WHERE a.id=".$_GET['usid']);
        $credQ = $cred->row_array();
        $viewAll = $credQ['cielo_viewAll'] == "0" ? false : true;
        
        $this->db->select("a.*, isNR, NOMBREASESOR(b.id,1) as asesor, SUBSTR(notas,1,2) as prefix, COUNT(DISTINCT p.operacion) as pagosRecibidos")->from('t_reservations a')
                ->join('t_desplazos d', 'a.rsva=d.rsva_destino','left')
                ->join('Asesores b', ' IF(isDesplazo_d=1,originalCreator,userComision) = b.cieloUser','left', FALSE)
                ->join('res_ligasPago p', 'p.referencia LIKE CONCAT(\'%\',a.rsva,\'%\')','left')
                ->join('cat_habitaciones h', 'a.hotel = h.hotelCode
                                                AND a.rp_char01 = h.habCode','left')
                ->where("COALESCE(dtCancel,'2030-12-31') > ","a.dtCreated", FALSE)
                ->group_by('a.rsva')
                ->order_by('a.dtCreated');
                
        if( isset($loc) ){
            $this->db->where('rsva',$loc);
        }else{
            if( !$viewAll ){
                $this->db->where('b.id',$_GET['usid']);
            }
        }
        
        if( isset($data['onlyCC']) && $data['onlyCC'] == true ){
            $this->db->group_start()
                    ->where("(agencia LIKE ", "'cal%' AND mdo = 'dir')", FALSE)
                    ->or_where("agencia LIKE '", "%paq%'", FALSE)
                    ->or_group_start()
                        ->where("agencia LIKE '", "oasis%'", FALSE)
                        ->where('userComision != ', 'userCreated', FALSE)
                    ->group_end()
                ->group_end();
        }
        
        if( isset($data['mdoFlag']) && $data['mdoFlag'] ){
            $this->db->where("a.mdo", $data['mdo']);
        }
        
        if( isset($data['dtArrivalFlag']) && $data['dtArrivalFlag'] ){
            $this->db->where("a.llegada BETWEEN '".$data['arrival_inicio']."' AND '",$data['arrival_fin']."'", FALSE);
            $controlFlag = false;
        }
        
        if( isset($data['dtDepartureFlag']) && $data['dtDepartureFlag'] ){
            $this->db->where("a.salida BETWEEN '".$data['departure_inicio']."' AND '",$data['departure_fin']."'", FALSE);
            $controlFlag = false;
        }
        
        if( isset($data['dtCreatedFlag']) && $data['dtCreatedFlag'] ){
            $this->db->where("a.dtCreated BETWEEN '".$data['created_inicio']."' AND '",$data['created_fin']."'", FALSE);
            $controlFlag = false;
        }
        
        if( isset($data['asesorFlag']) && $data['asesorFlag'] ){
            $this->db->where("IF(isDesplazo_d=1,originalCreator,userComision)= '",$data['asesor']."'", FALSE);
            $controlFlag = false;
        }
        
        if( isset($data['locFlag']) && $data['locFlag'] ){
             $this->db->group_start()->where('a.rsva',$data['searchString'])->or_like('a.voucher',$data['searchString'])->or_like('a.nombre',$data['searchString'])->or_like('a.voucher',$data['searchString'])->group_end();
             $controlFlag = false;
        }
        
        if( isset($data['isNR']) && $data['isNR'] ){
            $this->db->where("isNR",1);
            $controlFlag = false;
        }
        
        if( isset($data['isXld']) && $data['isXld'] ){
            $this->db->where("a.e","c");
            $controlFlag = false;
        }
        
        if( isset($data['noXld']) && $data['noXld'] ){
            $this->db->where("a.e != ","c");
            $controlFlag = false;
        }
        
        if( isset($data['isNS']) && $data['isNS'] ){
            $this->db->where("a.e","n");
            $controlFlag = false;
        }
        
        if( isset($data['isPay']) && $data['isPay'] ){
            $this->db->group_start()->where("a.notas LIKE ","%PP%")->or_where("a.notas LIKE ","%PT%")->group_end();
            $controlFlag = false;
        }
        
        if( isset($data['nr_overdue']) && $data['nr_overdue'] ){
            $this->db->where("a.llegada >=", "CURDATE()", FALSE)->where("a.e NOT IN ('c','n')","",FALSE)->not_like('a.notas', 'PP')->not_like('a.notas', 'PT')
                    ->where('DATEDIFF(CURDATE(), a.dtCreated) >= ', '4', FALSE);
            $controlFlag = false;
        }
        
        if( $controlFlag ){
            $this->db->where("a.dtCreated BETWEEN ", "ADDDATE(CURDATE(),-1) AND CURDATE()", FALSE);
        }
        
        // $query = $this->db->get_compiled_select();
        
        // if( $qM = $this->db->query($query) ){
        if( $qM = $this->db->get() ){
            $items = $qM->result_array();
        }else{
            errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
        // okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this, 'q', $query);
        okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this);

      });
  }
  
  public function listLoc_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
        $data = $this->put();
        
        // if( !($_GET['usid'] == 29 || $_GET['usid'] == 72)  ){
        //     errResponse('Reporte temporalmente suspendido', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
        // }

        if( isset($data['loc']) ){
            $loc = $data['loc'];
            
            if( $qM = $this->db->from('res_master')->where('masterlocatorid',$loc)->get() ){
                $master = $qM->row_array();
            }else{
                errResponse('Error al obtener el masterlocatorid', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }else{
            $master = array();
        }
        
        $locFlag = false;
        
        if( isset($data['locFlag']) ){
            $locFlag=$data['locFlag'];
        }
        
        $cred = $this->db->query("SELECT rsv_viewAll FROM Asesores a LEFT JOIN userDB b ON a.id=b.asesor_id LEFT JOIN cat_profiles c ON b.profile=c.id WHERE a.id=".$_GET['usid']);
        $credQ = $cred->row_array();
        $viewAll = $credQ['rsv_viewAll'] == "0" ? false : true;
        
        $this->db->select("ml.*,
                            COUNT(i.itemId) AS items,
                            GROUP_CONCAT(DISTINCT h.titular) as nombresTitulares,
                            SUM(IF(moneda = 'MXN' AND isCancel=0 AND (isQuote=0 OR (isQuote=1 AND NOW() < vigencia)), monto, 0)) AS montoMXN,
                            SUM(IF(moneda = 'USD' AND isCancel=0 AND (isQuote=0 OR (isQuote=1 AND NOW() < vigencia)), monto, 0)) AS montoUSD,
                            GROUP_CONCAT(DISTINCT title) AS servicios,
                            GROUP_CONCAT(DISTINCT COALESCE(m.grupoTfas,m.grupo)) AS grupos,
                            SUM(IF(moneda = 'MXN' AND isCancel=0 AND (isQuote=0 OR (isQuote=1 AND NOW() < vigencia)), montoPagado, 0)) AS montoPagadoMXN,
                            SUM(IF(moneda = 'USD' AND isCancel=0 AND (isQuote=0 OR (isQuote=1 AND NOW() < vigencia)), montoPagado, 0)) AS montoPagadoUSD,
                            SUM(IF(moneda = 'MXN' AND isCancel=0 AND (isQuote=0 OR (isQuote=1 AND NOW() < vigencia)), montoParcial, 0)) - SUM(IF(moneda = 'MXN', montoPagado, 0)) AS saldoMXN,
                            SUM(IF(moneda = 'USD' AND isCancel=0 AND (isQuote=0 OR (isQuote=1 AND NOW() < vigencia)), montoParcial, 0)) - SUM(IF(moneda = 'USD', montoPagado, 0)) AS saldoUSD,
                            IF(SUM(IF(moneda = 'MXN', montoPagado, 0)) >= SUM(IF(moneda = 'MXN', montoParcial, 0)) AND SUM(IF(moneda = 'USD', montoPagado, 0)) >= SUM(IF(moneda = 'USD', montoParcial, 0)),1,0) as allPaid,
                            IF(COUNT(IF(isQuote=0 AND isCancel=0,1,null))>0,'Reserva',IF(COUNT(IF(isQuote=1 AND isCancel=0,1,null))>0,IF(NOW() > MAX(IF(isQuote=1 AND isCancel=0,vigencia,NOW())),'Expirada','Cotizacion'),'Cancelada')) as quoteStatus,
                            MAX(IF(isQuote=1,vigencia,null)) as vigencia,
                            COUNT(confirm) AS confirmados,
                            CONCAT(COUNT(confirm),' - ',COUNT(i.itemId)-SUM(COALESCE(isCancel,0))) as sumConfirm,
                            COUNT(i.itemId)-SUM(COALESCE(isCancel,0)) as toConfirm,
                            NOMBREASESOR(ml.userCreated, 1) AS agentName,
                            SUM(COALESCE(isCancel,0)) AS cancelados,
                            COUNT(confirmCancel) AS confirmCancelados,
                            MIN(CASE
                        		WHEN itemType = 1 THEN h.inicio
                        		WHEN itemType = 2 THEN d.fecha
                        		WHEN itemType = 3 THEN xt.inicio
                        		WHEN itemType = 4 THEN tr.fecha
                        		WHEN itemType = 5 THEN x.fecha_in
                        		WHEN itemType = 9 THEN xt.inicio
                            END) as llegadaOk,
                            MAX(CASE
                        		WHEN itemType = 1 THEN h.fin
                        		WHEN itemType = 2 THEN d.fecha
                        		WHEN itemType = 3 THEN xt.fin
                        		WHEN itemType = 4 THEN tr.fecha
                        		WHEN itemType = 5 THEN COALESCE(x.fecha_out,x.fecha_in)
                        		ELSE null
                            END) as salidaOk,
	                        COUNT(isNR) as nrCount, GROUP_CONCAT(DISTINCT zdTicket) as tickets", FALSE)
                            ->from('cycoasis_rsv.r_masterlocators ml')
                            ->join('cycoasis_rsv.r_items i', 'ml.masterlocatorid = i.masterlocatorid', 'left')
                            ->join('cycoasis_rsv.servicios s', 'i.itemType = s.id', 'left')
                            ->join('cycoasis_rsv.r_monto m', 'i.itemId = m.itemId', 'left')
                            ->join('cycoasis_rsv.r_hoteles h', 'i.itemId = h.itemId', 'left')
                            ->join('cycoasis_rsv.r_tour tr', 'i.itemId = tr.itemId', 'left')
                            ->join('cycoasis_rsv.r_xfer x', 'i.itemId = x.itemId', 'left')
                            ->join('cycoasis_rsv.r_xtras xt', 'i.itemId = xt.itemId', 'left')
                            ->join('cycoasis_rsv.r_daypass d', 'i.itemId = d.itemId', 'left')
                            ->group_by('ml.masterlocatorid')
                            ->order_by('ml.masterlocatorid', 'desc');
                            
        if( isset($loc) ){
            $this->db->where('ml.masterlocatorid',$loc);
        }else{
            if( !$viewAll && !$locFlag ){
                $this->db->where('ml.userCreated',$_GET['usid']);
            }
        }
        
        if( isset($data['dtArrivalFlag']) && $data['dtArrivalFlag'] ){
            $this->db->having("llegadaOk BETWEEN '".$data['arrival_inicio']."' AND '",$data['arrival_fin']."'", FALSE);
        }
        
        if( isset($data['dtCreatedFlag']) && $data['dtCreatedFlag'] ){
            $this->db->where("ml.dtCreated BETWEEN '".$data['created_inicio']."' AND ADDDATE('",$data['created_fin']."',1)", FALSE);
        }
        
        if( isset($data['asesorFlag']) && $data['asesorFlag'] ){
            $this->db->where("ml.userCreated",$data['asesor']);
        }
        
        if( isset($data['locFlag']) && $data['locFlag'] ){
             $this->db->group_start()->where('ml.masterlocatorid',$data['searchString'])->or_like('h.titular',$data['searchString'])->or_like('ml.nombreCliente',$data['searchString'])->or_where('ml.correoCliente',$data['searchString'])->or_where('i.confirm',$data['searchString'])->group_end();
        }
        
        if( isset($data['nr']) && $data['nr'] ){
            $this->db->having("nrCount >",1);
        }
        
        if( isset($data['noXld']) && $data['noXld'] ){
            $this->db->where("isCancel",0);
        }
        
        if( $qM = $this->db->get() ){
            $items = $qM->result_array();
        }else{
            errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
        okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this);

      });
  }
  
  public function listPollen_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
        $data = $this->put();
        
        $master = array();
        
        $locFlag = false;
        
        if( isset($data['locFlag']) ){
            $locFlag=$data['locFlag'];
        }
        
        $cred = $this->db->query("SELECT pollen_manage FROM Asesores a LEFT JOIN userDB b ON a.id=b.asesor_id LEFT JOIN cat_profiles c ON b.profile=c.id WHERE a.id=".$_GET['usid']);
        $credQ = $cred->row_array();
        $viewAll = $credQ['pollen_manage'] == "0" ? false : true;
        
         $this->db->select("a.*, habName")->from('t_reservations a')
                ->join('cat_habitaciones h', 'a.hotel = h.hotelCode
                                                AND a.rp_char01 = h.habCode','left')
                ->where("COALESCE(dtCancel,'2030-12-31') > ","a.dtCreated", FALSE)
                ->where("a.llegada > ","20210501", FALSE)
                ->where("agencia","groupsai")
                ->where("grupo","mufe21" )
                ->order_by("llegada, rsva");
                
        if( isset($loc) ){
            $this->db->where('rsva',$loc);
        }
        
        if( isset($data['dtArrivalFlag']) && $data['dtArrivalFlag'] ){
            $this->db->having("llegadaOk BETWEEN '".$data['arrival_inicio']."' AND '",$data['arrival_fin']."'", FALSE);
        }
        
        
        if( isset($data['locFlag']) && $data['locFlag'] ){
             $this->db->group_start()->where('ml.masterlocatorid',$data['searchString'])
             ->or_like('guest1_nombre',$data['searchString'])
             ->or_like('guest1_apellido',$data['searchString'])
             ->or_like('guest2_nombre',$data['searchString'])
             ->or_like('guest2_apellido',$data['searchString'])
             ->or_like('guest3_nombre',$data['searchString'])
             ->or_like('guest3_apellido',$data['searchString'])
             ->or_like('guest4_nombre',$data['searchString'])
             ->or_like('guest4_apellido',$data['searchString'])
             ->group_end();
        }
        
        if( isset($data['eventFlag']) && $data['eventFlag'] ){
             $this->db->like('rp_char02',$data['eventName']);
        }
        
        if( $qM = $this->db->get() ){
            $items = $qM->result_array();
        }else{
            errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
        okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this);

      });
  }
  
  public function manageLoc_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
        $data = $this->put();

        if( isset($data['loc']) ){
            $loc = $data['loc'];
            
            if( $qM = $this->db->from('res_master')->where('masterlocatorid',$loc)->get() ){
                $master = $qM->row_array();
                
            }else{
                errResponse('Error al obtener el masterlocatorid', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }else{
            $master = array();
        }
        
        $cred = $this->db->query("SELECT rsv_viewAll FROM Asesores a LEFT JOIN userDB b ON a.id=b.asesor_id LEFT JOIN cat_profiles c ON b.profile=c.id WHERE a.id=".$_GET['usid']);
        $credQ = $cred->row_array();
        $viewAll = $credQ['rsv_viewAll'] == "0" ? false : true;
        
        $this->db->select("i.*,
                                    ROUND(SUM(IF(COALESCE(p.status,0) = 1 AND COALESCE(p.activo,0)=1, COALESCE(pr.monto,0), 0)),2) AS montoPagado,
                                    ROUND(SUM(IF(COALESCE(p.status,0) != 1 AND COALESCE(p.activo,0)=1, COALESCE(pr.monto,0), 0)),2) AS montoPendiente,
                                    ROUND(COUNT(IF(COALESCE(p.status,0) = 1 AND COALESCE(p.activo,0)=1, COALESCE(pr.monto,0), NULL)),2) AS pagosCompletados,
                                    ROUND(COUNT(IF(COALESCE(p.status,1) != 1 AND COALESCE(p.activo,0)=1, COALESCE(pr.monto,0), NULL)),2) AS pagosPendientes,
                                    CASE 
                                		WHEN fdp = 1 THEN 'PH'
                                		WHEN COUNT(IF(COALESCE(p.status,0) = 1,1,null))=0 AND COUNT(IF(COALESCE(p.status,0) != 1,1,null))>0 THEN 'PDT'
                                		WHEN COUNT(IF(COALESCE(p.status,0) = 1,1,null))=0 AND COUNT(IF(COALESCE(p.status,0) != 1,1,null))=0 THEN 'Agregar'
                                		WHEN COUNT(IF(COALESCE(p.status,0) = 1,1,null))>=1 THEN CASE
                                			WHEN SUM(IF(COALESCE(p.status,0) = 1, COALESCE(pr.monto,0), 0)) < i.monto THEN 'PP'
                                            ELSE 'PT'
                                        END
                                    END as pySt,
                                    NOMBREASESOR(i.userCreated,1) as creador, COUNT(r.rsva) as cieloItems, r.e,
                                    GROUP_CONCAT(DISTINCT r.rsva) as cieloConf")
                            ->from('res_items i')
                            ->join('res_master m', 'i.masterlocatorid=m.masterlocatorid', 'left')
                            ->join('res_payRelates pr', 'i.masterItemLocator = pr.masterItemLocator', 'left')
                            ->join('res_payments p', 'pr.paymentId = p.id AND p.activo=1', 'left')
                            ->join('t_reservations r', 'i.masterItemLocator = r.voucher', 'left')
                            ->group_by('i.masterItemLocator');
                            
        if( isset($loc) ){
            $this->db->where('i.masterlocatorid',$loc);
        }else{
            if( !$viewAll ){
                $this->db->where('i.userCreated',$_GET['usid']);
            }
        }
        
        if( isset($data['dtArrivalFlag']) && $data['dtArrivalFlag'] ){
            $this->db->where("i.llegada BETWEEN '".$data['arrival_inicio']."' AND '",$data['arrival_fin']."'", FALSE);
        }
        
        if( isset($data['dtCreatedFlag']) && $data['dtCreatedFlag'] ){
            $this->db->where("i.dtCreated BETWEEN '".$data['created_inicio']."' AND '",$data['created_fin']."'", FALSE);
        }
        
        if( isset($data['asesorFlag']) && $data['asesorFlag'] ){
            $this->db->where("i.userCreated",$data['asesor']);
        }
        
        if( isset($data['locFlag']) && $data['locFlag'] ){
             $this->db->group_start()->where('i.masterlocatorid',$data['searchString'])->or_where('m.nombreCliente',$data['searchString'])->or_where('m.correoCliente',$data['searchString'])->group_end();
        }
        
        if( isset($data['nr']) && $data['nr'] ){
            $this->db->where("i.isNR",1);
        }
        
        if( isset($data['nr_overDue']) && $data['nr_overDue'] ){
            $this->db->where("i.isNR",1)->where("i.llegada >=", "CURDATE()", FALSE)->where("r.e NOT IN ('c','n')","",FALSE)->having("pySt NOT IN ('PP','PT')");
        }
        
        if( $qM = $this->db->get() ){
            $items = $qM->result_array();
        }else{
            errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
        okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this);

      });
  }
  
    public function getItemData_put(){

        if( !isset($_GET['token']) ){
            errResponse('No se encontró ningún token. No es posible realizar la petición', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
        }
        
        $token = $_GET['token'];
        $data = $this->put();
        
        $mlDtQ = $this->db->select('a.masterlocatorId, a.dtCreated')->from('cycoasis_rsv.r_masterlocators a')->join('cycoasis_rsv.r_items b', 'a.masterlocatorid=b.masterlocatorid','left')->where('itemLocatorId',$data['itemLocatorId'])->get();
        $mlDtR = $mlDtQ->row_array();
        
        return validateItemToken($token, $mlDtR['masterlocatorId'], $mlDtR['dtCreated'], function(){
            
            $data = $this->put();
            
            $rsv = model_rsv();
            $rsv->manageItem(true, $data['itemLocatorId']);
            $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
            $result = $itemDataQ->row_array();
            
            okResponse('Token Validado correctamente', 'data', $result, $this);
        });
    }
  
  public function manageQueries( $t, $i, $l, $w = '' ){
      if( $t == 'ml' ){
          
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS linksFull"); 
        $this->db->query("CREATE TEMPORARY TABLE linksFull SELECT 
                            b.reference,
                            SUM(monto) as total,
                            moneda,
                            CONCAT(cuenta, ' ', IF(LOCATE('paypal',url) > 0, 'Paypal', 'Santander')) as cuenta,
                            promo,
                            GROUP_CONCAT(it.itemNumber) as items
                        FROM
                            cycoasis_rsv.r_items it
                                LEFT JOIN
                            cycoasis_rsv.r_pLinks b ON it.itemId = b.itemId
                        WHERE
                            it.masterlocatorid = $l
                            AND CURDATE() < b.vigencia AND active=1
                        GROUP BY reference
                        HAVING reference IS NOT NULL");
        $this->db->query("ALTER TABLE linksFull ADD PRIMARY KEY (reference)");
        
        $this->db->query("DROP TEMPORARY TABLE IF EXISTS lks"); 
        $this->db->query("CREATE TEMPORARY TABLE lks SELECT 
                        	CONCAT('[',GROUP_CONCAT(
                        		'{\"reference\":\"',
                        		reference,
                                '\",\"total\":\"',
                        		total,
                                '\",\"moneda\":\"',
                        		moneda,
                        		'\",\"items\":\"',
                        		items,
                        		'\",\"cuenta\":\"',
                        		cuenta,
                        		'\",\"promo\":\"',
                        		promo,
                                '\"}'
                                ),']') as allLinks
                        FROM
                            linksFull");
                            
            // ADD this at the end of the condition for cobroFull if MSI matters
            //  AND COUNT(DISTINCT CASE WHEN isCancel=0 AND isQuote=1 AND vigencia > NOW() THEN promo ELSE NULL END) = 1
                            
                            
            
          $mlQ = "SELECT 
                    ml.*,
                    IF(ml.dtCreated>='20210330',xldPol,xldPol) as xldPolicy, 
                    NOMBREASESOR(ml.userCreated, 2) AS creador,
                    GETZDID(ml.userCreated,5) as zdCreated,
                    GROUP_CONCAT(DISTINCT zdTicket) AS tickets,
                    SUM(IF(isCancel = 0, monto, montoPenalidad)) as montoTotal, SUM(COALESCE(montoPagado,0)) as montoPagadoTotal,
                    SUM(IF(isCancel = 0, monto, montoPenalidad)/(IF(itemType IN (1,2),1.225,1.16))) as montoLimpio,
                    IF(isCancel = 0 AND isOpen=0, COALESCE(getcomisionmaster(ml.masterlocatorid,2),0), 0) as comision,
                    SUM(COALESCE(montoParcial,0)) as montoParcialTotal, moneda, languaje as idioma, allLinks,
                    IF(SUM(IF(itemType IN (1,2,10),1,0))>= 1, 'ohr','vcm') as mainMailProv,
                    IF(SUM(IF(itemType IN (1,2) AND grupoTfas='openDates_2020',1,0))>= 1, 1,0) as openDatesPolicy,
                    IF(COUNT(DISTINCT CASE WHEN isCancel=0 AND isQuote=1 AND vigencia > NOW() THEN complejoOk ELSE NULL END) = 1 AND COUNT(DISTINCT CASE WHEN isCancel=0 AND isQuote=1 AND vigencia > NOW() THEN moneda ELSE NULL END) = 1,1,0) as cobroFull,
                    GROUP_CONCAT(DISTINCT CASE WHEN isCancel=0 AND isQuote=0 AND monto-montoPagado-montoEnValidacion>0 AND itemType = 1 AND inicio >= CURDATE() THEN complejoOk ELSE NULL END) as cobroAbonoComplejo,
                    GROUP_CONCAT(DISTINCT CASE WHEN isCancel=0 AND isQuote=0 AND monto-montoPagado-montoEnValidacion>0 AND itemType = 1 AND inicio >= CURDATE()  THEN promo ELSE NULL END) as cobroAbonoPromo,
                    GROUP_CONCAT(DISTINCT CASE WHEN isCancel=0 AND isQuote=0 AND monto-montoPagado-montoEnValidacion>0 AND itemType = 1 AND inicio >= CURDATE() AND moneda='MXN' THEN itemNumber ELSE NULL END) as cobroAbonoItemsMXN,
                    GROUP_CONCAT(DISTINCT CASE WHEN isCancel=0 AND isQuote=0 AND monto-montoPagado-montoEnValidacion>0 AND itemType = 1 AND inicio >= CURDATE() AND moneda='USD' THEN itemNumber ELSE NULL END) as cobroAbonoItemsUSD,
                    GROUP_CONCAT(DISTINCT CASE WHEN isCancel=0 AND isQuote=1 AND vigencia > NOW() THEN complejoOk ELSE NULL END) as cobroComplejo,
                    GROUP_CONCAT(DISTINCT CASE WHEN isCancel=0 AND isQuote=1 AND vigencia > NOW() THEN moneda ELSE NULL END) as cobroMoneda,
                    GROUP_CONCAT(DISTINCT CASE WHEN isCancel=0 AND isQuote=1 AND vigencia > NOW() THEN promo ELSE NULL END) as cobroPromo,
                    GROUP_CONCAT(DISTINCT CASE WHEN isCancel=0 AND isQuote=1 AND vigencia > NOW() THEN itemNumber ELSE NULL END) as cobroItems,
                    CASE
                        WHEN SUM(IF(isCancel=0 AND isQuote=0,1,0))>= 1 THEN 'confirm'
                        WHEN SUM(IF(isCancel=0 AND isQuote=1,1,0))>= 1 THEN 'quote'
                        WHEN SUM(IF(isCancel=1,1,0))>= 1 THEN 'cancel'
                    END as allSt,
                    CASE
                        WHEN SUM(IF(isCancel=0 AND isQuote=1,1,0))>= 1 THEN 1
                        ELSE 0
                    END as hasQuote,
                    CASE
						WHEN SUM(IF(itemType IN (1,2),1,0))>= 1 THEN CASE
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'pyr' AND isCancel=0 AND isQuote=0, 1 , 0))>= 1 THEN 'pyr' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'goc' AND isCancel=0 AND isQuote=0 , 1 , 0))>= 1 THEN 'goc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'ohoc' AND isCancel=0 AND isQuote=0 , 1 , 0))>= 1 THEN 'ohoc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'gsc' AND isCancel=0 AND isQuote=0 , 1 , 0))>= 1 THEN 'gsc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'gop' AND isCancel=0 AND isQuote=0 , 1 , 0))>= 1 THEN 'gop' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'opb' AND isCancel=0 AND isQuote=0 , 1 , 0))>= 1 THEN 'opb' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'oh' AND isCancel=0 AND isQuote=0 , 1 , 0))>= 1 THEN 'oh' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'smart' AND isCancel=0 AND isQuote=1 , 1 , 0))>= 1 THEN 'smart' 
                            WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'pyr' AND isCancel=0 AND isQuote=1, 1 , 0))>= 1 THEN 'pyr' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'ohoc' AND isCancel=0 AND isQuote=1 , 1 , 0))>= 1 THEN 'ohoc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'goc' AND isCancel=0 AND isQuote=1 , 1 , 0))>= 1 THEN 'goc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'gsc' AND isCancel=0 AND isQuote=1 , 1 , 0))>= 1 THEN 'gsc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'gop' AND isCancel=0 AND isQuote=1 , 1 , 0))>= 1 THEN 'gop' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'opb' AND isCancel=0 AND isQuote=1 , 1 , 0))>= 1 THEN 'opb' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'oh' AND isCancel=0 AND isQuote=1 , 1 , 0))>= 1 THEN 'oh' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'smart' AND isCancel=0 AND isQuote=1 , 1 , 0))>= 1 THEN 'smart' 
                            WHEN SUM(IF(itemType NOT IN (1,2) AND isQuote=0 AND isCancel = 0,1,0))>= 1 THEN 'vcm'
                            WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'pyr', 1 , 0))>= 1 THEN 'pyr' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'goc', 1 , 0))>= 1 THEN 'goc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'ohoc', 1 , 0))>= 1 THEN 'ohoc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'gsc', 1 , 0))>= 1 THEN 'gsc' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'gop', 1 , 0))>= 1 THEN 'gop' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'opb', 1 , 0))>= 1 THEN 'opb' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'oh', 1 , 0))>= 1 THEN 'oh' 
							WHEN SUM(IF(COALESCE(ht.hotel,dp.hotel) = 'smart', 1 , 0))>= 1 THEN 'smart' 
                        END
                    	WHEN mt.grupo='ccenterdir' THEN 'ohoc' 
						ELSE 'vcm'
                    END as portada,
                    SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD' AND itemType=1,monto-montoPagado-montoEnValidacion,0)) as totalAbonoUSD,
                    SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN' AND itemType=1,monto-montoPagado-montoEnValidacion,0)) as totalAbonoMXN,
                    SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD',monto,0)) as totalMontoUSD,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD',montoPagado,0)) as totalMontoPagadoUSD,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD',montoEnValidacion,0)) as totalMontoValidandoUSD,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD',montoParcial,0)) - (SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD',montoPagado,0)) + SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD',montoEnValidacion,0))) as totalMontoSaldoUSD,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD',monto,0)) - SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='USD',montoParcial,0)) as totalMontoHotelUSD,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN',monto,0)) as totalMontoMXN,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN',montoPagado,0)) as totalMontoPagadoMXN,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN',montoEnValidacion,0)) as totalMontoValidandoMXN,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN',montoParcial,0)) - (SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN',montoPagado,0)) + SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN',montoEnValidacion,0))) as totalMontoSaldoMXN,
					SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN',monto,0)) - SUM(IF(isCancel = 0 AND COALESCE(mt.grupo,'') != 'ohr' AND moneda='MXN',montoParcial,0)) as totalMontoHotelMXN,
					IF(GROUP_CONCAT(DISTINCT agencia) LIKE '%cal%' AND GROUP_CONCAT(DISTINCT mdo) LIKE '%dir%' AND orId IS NOT NULL,1,0) as locIsOr,
					IF(COUNT(IF(mt.grupo='ohr' AND itemType=5 AND isCancel=0,1,null))>0,1,0) as hasXferCortesy, isFree, tf.pagoEnHotel
                FROM
                    cycoasis_rsv.r_masterlocators ml
                        LEFT JOIN
                    cycoasis_rsv.r_items it ON ml.masterLocatorId = it.masterLocatorId
                        LEFT JOIN
					cycoasis_rsv.r_hoteles ht ON it.itemId=ht.itemId
						LEFT JOIN
					cycoasis_rsv.r_daypass dp ON it.itemId=dp.itemId
					    LEFT JOIN
					cycoasis_rsv.r_seguros sg ON it.itemId=sg.itemId
						
					-- cycoasis_oasis.cat_complejo cp ON COALESCE(ht.hotel,COALESCE(dp.hotel,sg_hotel))=cp.hotel
                        LEFT JOIN
                    cycoasis_rsv.r_monto mt ON it.itemId = mt.itemId 
                        LEFT JOIN
					cycoasis_oasis.cat_complejo cp ON COALESCE(ht.hotel,COALESCE(dp.hotel,COALESCE(sg_hotel,IF(itemType IN (4,5),IF(mt.grupo='ccenterdir','CANCUN','VCM'),NULL))))=cp.hotel
                        LEFT JOIN 
                    tarifas_grupos tf ON mt.grupoTfas=tf.grupo
                        JOIN
                    lks
                WHERE
                    ml.masterlocatorid = $l 
                GROUP BY ml.masterLocatorId";
        return $mlQ;
      }
      
      if( $t == 'item' ){
          
          
          if( $w == '' ){
              
              $w = "it.masterLocatorId = $l";
              
              if( $i ){
                $w = "it.itemLocatorId = '$l'";
              }
              
          }
          
          $jwtQ = $this->db->query("SELECT b.dtCreated, b.masterlocatorid, b.correoCliente FROM `cycoasis_rsv`.`r_items` it LEFT JOIN `cycoasis_rsv`.`r_masterlocators` b ON `it`.`masterlocatorid` = `b`.`masterlocatorid` WHERE $w GROUP BY b.masterlocatorid");
          $jwtR = $jwtQ->row_array();
          
          $jwt = generateToken( $jwtR, 'cycConf2021' );
        // $jwt = "test";
          
            $this->db->query("DROP TEMPORARY TABLE IF EXISTS links"); 
            $this->db->query("CREATE TEMPORARY TABLE links (
                                    `itemId` INT NULL,
                                    `links` VARCHAR(5000) NULL,
                                    `activeLinks`  INT NULL,
                                    `inactiveLinks` INT NULL
                                ) SELECT 
                                it.itemId,
                                CONCAT('[',GROUP_CONCAT(IF(b.active = 1 AND CURDATE() < b.vigencia,
                                    CONCAT(
                                    '{\"reference\":\"',
                                    reference,
                                    '\",\"itemId\":\"',
                                    b.itemId,
                                    '\",\"monto\":\"',
                                    monto,
                                    '\",\"moneda\":\"',
                                    moneda,
                                    '\",\"afiliacion\":\"',
                                    afiliacion,
                                    '\",\"cuenta\":\"',
                                    cuenta,
                                    '\",\"active\":\"',
                                    IF(b.active = 1 AND CURDATE() < b.vigencia,1,0),
                                    '\",\"link\":\"',
                                    b.url,
                                    '\",\"ref\":\"',
                                    IF(format_no IS NOT NULL, format_no, b.url),
                                    '\",\"paypal\":\"',
                                    IF(format_no IS NOT NULL, 1, 0),
                                    '\"}'),null)),']') AS links,
                            	COUNT(IF(b.active = 1 AND CURDATE() < b.vigencia,1,null)) as activeLinks,
                            	COUNT(IF(COALESCE(b.active,0) = 0 OR (b.active = 1 AND CURDATE() >= b.vigencia),1,null)) as inactiveLinks
                            FROM
                                cycoasis_rsv.r_items it
                                    LEFT JOIN
                                cycoasis_rsv.r_pLinks b ON it.itemId = b.itemId
                            WHERE
                                $w
                            GROUP BY itemId");
            $this->db->query("ALTER TABLE links ADD PRIMARY KEY (itemId)");
          
          $this->db->query("DROP TEMPORARY TABLE IF EXISTS items"); 
          if( $this->db->query("CREATE TEMPORARY TABLE items SELECT 
                    it.masterLocatorId, it.certificado,
                    it.itemId,
                    it.itemNumber,
                    it.itemLocatorId,
                    it.isQuote,
                    it.isCancel,
                    it.isConfirmable,
                    it.isOpen, it.limite_od, it.travelLimit, it.notasResetOd,
                    it.itemType,
                    it.dtCreated,
                    it.parentPriced,
                    COALESCE(NOMBREASESOR(it.userCreated, 11),289) AS vcmId,
                    NOMBREASESOR(it.userCreated, 2) AS creador, zdId as creatorZdId, ml.cc as ccMail,
                    CONCAT(NOMBREASESOR(it.userCreated, 6),'@oasishoteles.com') AS mailCreador,
                    tp.title AS Servicio, icon,
                    CASE 
                        WHEN it.isOpen = 1 THEN 'OD'
                        WHEN mnt.isPagoHotel = 1 THEN 'PH'
                        WHEN mnt.isPagoHotel = 0 THEN 
                        CASE
                            WHEN mnt.isParcial = 1 THEN if(mnt.montoParcial > mnt.montoPagado, 'PDT', 'PP')
                            WHEN mnt.isParcial = 0 THEN if(mnt.monto > mnt.montoPagado, 'PDT', 'PT')
                        END
                    END as tipoPago,
                    mnt.promo,
                    it.Last_Update,
                    COALESCE(mnt.montoParcial,0) as montoParcial,
                    mnt.isParcial,
                    mnt.isPagoHotel,
                    it.zdTicket,
                    IF(it.isQuote = 1, 'Cotizacion', IF(it.isCancel = 1, 'Cancelada', COALESCE(
                        IF( it.itemType IN (11,12), COALESCE(CONCAT('VCM:',vcm_confirm),it.confirm), it.confirm)
                        , 'Pendiente'))) as confirm,
                    it.confirm as confirmOK,
                    COALESCE(vcm_confirm,vcm_confirm) as vcm_confirm,
                    it.dtConfirm, it.userConfirm,
                    NOMBREASESOR(it.userCancel,1) as userCancel, it.confirmCancel, it.dtCancel,
                    it.dtConfirmCancel, it.userConfirmCancel,
                    mnt.montoOriginal,
                    mnt.idPagos,  
                    mnt.grupo,
                    mnt.grupoTfas,
                    mnt.lv,
                    mnt.monto, mnt.monto/(IF(it.itemType IN (1,2),1.225,1.16)) as montoLimpio,
                    mnt.montoPagado, mnt.montoEnValidacion,
                    mnt.montoParcial - (mnt.montoPagado + mnt.montoEnValidacion) as montoSaldoPrepago,
                    mnt.monto - (mnt.montoPagado + mnt.montoEnValidacion) as montoSaldoTotal,
                    mnt.monto - mnt.montoParcial as montoSaldoHotel,
                    mnt.montoPenalidad,
                    mnt.montoPagado as montoPagadoTotal,
                    mnt.moneda,
                    htl.isNR,
                    htl.noches as htlNoches,
                    htl.bedPreference,
                    historyTicket as mlTicket,
                    ctr.nombre as trName, ctr.titulo as trTitulo,it.vigencia,
                    it.isFree, it.cieloRelates, cnt.evento as concertEvent,
                    CASE 
                		WHEN it.itemType = 1 THEN htl.agencia
                	END as agencia,
                	habName, hotelName,
                	CASE
                	    WHEN it.isCancel = 1 THEN 'Cancelado'
                	    WHEN it.isQuote = 1 AND it.vigencia > NOW() THEN 'Cotizacion'
                	    WHEN it.isQuote = 1 AND it.vigencia <= NOW() THEN 'Cotizacion Expirada'
                	    WHEN it.isCancel = 0 AND it.isQuote = 0 THEN 'Confirmado'
                	END as itemStatus,
                	COALESCE(LOWER(htl.cieloStatus),'pdt') as cieloStatus,
                	LOWER(htl.cieloNotas) as cieloNotas,
                    CASE 
                		WHEN it.itemType = 1 THEN CONCAT('Hospedaje en ',htl.hotel)
                		WHEN it.itemType = 2 THEN CONCAT('Daypass en ', dp.hotel)
                		WHEN it.itemType = 3 THEN 'Auto'
                		WHEN it.itemType = 4 THEN CONCAT(ctr.nombre)
                		WHEN it.itemType = 5 THEN IF(mnt.grupo = 'ohr', 'Traslado Cortesia', 'Traslado')
                		WHEN it.itemType = 9 THEN 'Concierto'
                		WHEN it.itemType = 10 THEN CONCAT('Seguro ', sg_itemRelated)
                		WHEN it.itemType = 14 THEN CONCAT('Seguro_i ', COALESCE(sg_itemRelated,sg_cieloRelated))
                		WHEN it.itemType = 11 THEN IF(mnt.grupo = 'ohr', 'Traslado Cortesia', 'Traslado')
                		WHEN it.itemType = 12 THEN 'Tours'
                		WHEN it.itemType = 13 THEN 'Cupon'
                	END as tipoServicio,
                    CASE 
                		WHEN it.itemType = 1 THEN htl.hotel
                		WHEN it.itemType = 2 THEN dp.hotel
                		WHEN it.itemType = 3 THEN xt.rqChar01
                		WHEN it.itemType = 5 THEN xf.hotel
                		WHEN it.itemType = 9 THEN cnt.categoria
                		WHEN it.itemType = 10 THEN sg_hotel
                		WHEN it.itemType = 14 THEN sg_hotel
                		WHEN it.itemType = 11 THEN IF(xfv.transfer_type = 'Hacia al Aeropuerto', xfv.origin_text, xfv.destination_text)
                	END as hotel,
                    CASE 
                		WHEN it.itemType = 1 THEN htl.categoria
                		WHEN it.itemType = 2 THEN cdp.daypassType
                		WHEN it.itemType = 3 THEN cau.titulo
                		WHEN it.itemType = 4 THEN ctr.titulo
                		WHEN it.itemType = 5 THEN CONCAT(cxf.vehiculo,if(isShared=1,' (compartido)',' (privado)'),' ',cxf.xferType)
                		WHEN it.itemType = 9 THEN cnt.titulo
                		WHEN it.itemType = 10 THEN sg_mdo
                		WHEN it.itemType = 14 THEN sg_mdo
                		WHEN it.itemType = 11 THEN CONCAT(xfv.tourName,' ',xfv.transfer_type)
                	END as titulo, 
                    CASE 
                		WHEN it.itemType = 1 THEN htl.categoria
                		WHEN it.itemType = 2 THEN cdp.daypassType
                		WHEN it.itemType = 3 THEN CONCAT(cau.titulo, ', oficina: ',xt.rqChar01)
                		WHEN it.itemType = 4 THEN ctr.titulo
                		WHEN it.itemType = 5 THEN CONCAT(cxf.vehiculo,if(isShared=1,' (compartido)',' (privado)'),' ',cxf.xferType)
                		WHEN it.itemType = 9 THEN CONCAT(cnt.titulo, ' || Zona: ', cnt.categoria)
                		WHEN it.itemType = 10 THEN sg_cobertura
                		WHEN it.itemType = 14 THEN sg_cobertura
                		WHEN it.itemType = 11 THEN CONCAT(xfv.tourName,' ',xfv.transfer_type)
                	END as categoria, 
                    htl.mdo,  IF(it.itemType=1,htl.titular,nombreCliente) as nombreCliente, correoCliente, zdUserId as clientZd, ml.languaje as idioma,
                    CASE 
                		WHEN it.itemType = 1 THEN htl.adultos
                		WHEN it.itemType = 2 THEN dp.adultos
                		WHEN it.itemType = 4 THEN tr.adultos
                		WHEN it.itemType = 5 THEN xf.adultos
                		WHEN it.itemType = 9 THEN xt.pax_q
                		WHEN it.itemType = 10 THEN sg_pax
                		WHEN it.itemType = 14 THEN sg_pax
                		WHEN it.itemType = 11 THEN xfv.adults
                	END as adultos,
                    COALESCE(CASE 
                		WHEN it.itemType = 1 THEN htl.juniors
                		WHEN it.itemType = 2 THEN dp.juniors
                	END,0) as juniors,
                    CASE 
                		WHEN it.itemType = 1 THEN htl.menores
                		WHEN it.itemType = 2 THEN dp.menores
                		WHEN it.itemType = 4 THEN tr.menores
                		WHEN it.itemType = 5 THEN xf.menores
                		WHEN it.itemType = 11 THEN xfv.children
                	END as menores,
                    CASE 
                		WHEN it.itemType = 5 THEN xf.infantes
                		WHEN it.itemType = 11 THEN xfv.babies
                        ELSE 0
                	END as infantes,
                	CASE 
                		WHEN it.itemType = 1 THEN htl.inicio
                		WHEN it.itemType = 2 THEN dp.fecha
                		WHEN it.itemType = 3 THEN xt.inicio
                		WHEN it.itemType = 4 THEN tr.fecha
                		WHEN it.itemType = 5 THEN xf.fecha_in
                		WHEN it.itemType = 9 THEN xt.inicio
                		WHEN it.itemType = 10 THEN sg_inicio
                		WHEN it.itemType = 14 THEN sg_inicio
                		WHEN it.itemType = 11 THEN IF(xfv.llegada_date='0000-00-00',null,xfv.llegada_date)
                	END as llegada,
                	CASE 
                		WHEN it.itemType = 1 THEN htl.fin
                		WHEN it.itemType = 3 THEN xt.fin
                		WHEN it.itemType = 5 THEN xf.fecha_out
                		WHEN it.itemType = 10 THEN sg_fin
                		WHEN it.itemType = 14 THEN sg_fin
                		WHEN it.itemType = 11 THEN IF(xfv.salida_date='0000-00-00',null,xfv.salida_date)
                		ELSE null
                	END as salida,
                	CASE 
                		WHEN it.itemType = 1 THEN htl.notasHotel
                		WHEN it.itemType = 2 THEN dp.notasHotel
                		WHEN it.itemType = 3 THEN CONCAT('Recibe en ',xt.rqChar01, ' || Renta del ', xt.inicio, ' ', xt.horaInicio, ' al ', xt.fin, ' ', xt.horaFin, ' || ', xt.notasOperador)
                		WHEN it.itemType = 4 THEN tr.notasOperador
                		WHEN it.itemType = 5 THEN xf.notasOperador
                		WHEN it.itemType = 9 THEN xt.notasOperador
                		WHEN it.itemType = 11 THEN xfv.comments
                	END as notas,
                	CASE 
                		WHEN it.itemType = 1 THEN htl.notasHotel
                		WHEN it.itemType = 2 THEN cdp.proveedor
                		WHEN it.itemType = 3 THEN cau.proveedor
                		WHEN it.itemType = 4 THEN ctr.proveedor
                		WHEN it.itemType = 5 THEN cxf.proveedor
                		WHEN it.itemType = 9 THEN cnt.proveedor
                		WHEN it.itemType = 11 THEN 5
                	END as proveedor,
                	CASE 
                		WHEN it.itemType = 3 THEN xt.horaInicio
                		WHEN it.itemType = 5 THEN xf.hora_in
                		WHEN it.itemType = 11 THEN xfv.llegada_hour
                	END as hora_in,
                	CASE 
                		WHEN it.itemType = 3 THEN xt.horaFin
                		WHEN it.itemType = 5 THEN xf.hora_out
                		WHEN it.itemType = 11 THEN xfv.salida_hour
                		ELSE null
                	END as hora_out,
                	CASE 
                		WHEN it.itemType = 3 THEN xt.rqChar01
                		WHEN it.itemType = 4 THEN tr.pickup
                		WHEN it.itemType = 5 THEN COALESCE(dtPickUpOut,COALESCE(dtPickUpIn,'PENDIENTE'))
                		WHEN it.itemType = 11 THEN xfv.salida_pickup
                		ELSE null
                	END as pickup,
                	IF( it.itemType = 5, CONCAT(aerolinea_in,' ',vuelo_in), xfv.llegada_flight ) as vuelo_in, 
                	aerolinea_in,
                	IF( it.itemType = 5, CONCAT(aerolinea_out,' ',vuelo_out), xfv.salida_flight ) as vuelo_out, 
                	aerolinea_out, Zona, dtPickUpIn, COALESCE(dtPickUpOut,xfv.salida_pickup) as dtPickUpOut, xt.dias,
                    IF( it.itemType = 5, CONCAT(CASE
                                WHEN
                                    cxf.xferType LIKE '%apto-htl%'
                                        OR cxf.xferType LIKE '%round%'
                                THEN
                                    'Llegada Aeropuerto: '
                                WHEN cxf.xferType LIKE '%htl-apto%' THEN 'Salida Hotel: '
                            END,
                            xf.fecha_in,
                            ' ',
                            xf.hora_in,
                            ' vuelo ',
                            vuelo_in,
                            ' ',
                            aerolinea_in,
                            IF(cxf.xferType LIKE '%round%',
                                CONCAT(' || Salida Hotel: ',
                                        xf.fecha_out,
                                        ' ',
                                        xf.hora_out,
                                        ' vuelo ',
                                        vuelo_out,
                                        ' ',
                                        aerolinea_out),
                                '')),
                        CONCAT( IF( xfv.transfer_type NOT LIKE '%hacia%', CONCAT('Llegada: ',
                            COALESCE(IF(xfv.llegada_date='0000-00-00',null,xfv.llegada_date),''),
                            ' ',
                            COALESCE(xfv.llegada_hour,''),
                            ' vuelo ',
                            COALESCE(xfv.llegada_flight,''),'. '),'' ),
                            IF( xfv.transfer_type NOT LIKE '%desde%',
                                CONCAT('Salida Hotel: ',
                                        IF(xfv.salida_date='0000-00-00',null,xfv.salida_date),
                                        ' ',
                                        COALESCE(xfv.salida_hour,''),
                                        ' vuelo ',
                                        COALESCE(salida_flight,'')),
                                ''))) AS xfDetails_esp,
                    IF( it.itemType = 5, CONCAT(CASE
                                WHEN
                                    cxf.xferType LIKE '%apto-htl%'
                                        OR cxf.xferType LIKE '%round%'
                                THEN
                                    'Airport Arrival: '
                                WHEN cxf.xferType LIKE '%htl-apto%' THEN 'Hotel Departure: '
                            END,
                            xf.fecha_in,
                            ' ',
                            xf.hora_in,
                            ' flight ',
                            vuelo_in,
                            ' ',
                            aerolinea_in,
                            IF(cxf.xferType LIKE '%round%',
                                CONCAT(' || Hotel Departure: ',
                                        xf.fecha_out,
                                        ' ',
                                        xf.hora_out,
                                        ' flight ',
                                        vuelo_out,
                                        ' ',
                                        aerolinea_out),
                                '')),
                    CONCAT( IF( xfv.transfer_type NOT LIKE '%hacia%', CONCAT('Arrival: ',
                            COALESCE(IF(xfv.llegada_date='0000-00-00',null,xfv.llegada_date),''),
                            ' ',
                            COALESCE(xfv.llegada_hour,''),
                            ' flight ',
                            COALESCE(xfv.llegada_flight,''),'. '),'' ),
                            IF( xfv.transfer_type NOT LIKE '%desde%',
                                CONCAT('Departure: ',
                                        IF(xfv.salida_date='0000-00-00',null,xfv.salida_date),
                                        ' ',
                                        COALESCE(xfv.salida_hour,''),
                                        ' flight ',
                                        COALESCE(salida_flight,'')),
                                ''))) AS xfDetails_eng, 
                    if(mnt.moneda='MXN',afiliacionMXN, afiliacionUSD) as afiliacion, 
                    cpl.complejoOk as cuentaSantander,
                    links, activeLinks, inactiveLinks, referencias, blacklisted,
                    it.showItemInConfirm, it.showMontoInConfirm,
                    SUBSTRING(CONCAT(CASE
                                                        WHEN
                                                            (mnt.montoPagado + COALESCE(sm.montoPagado,0)) = 0
                                                        THEN
                                                            IF(mnt.monto = 0,
                                                                'Cortesia | ',
                                                                'PH - Paga Directo | ')
                                                        WHEN (mnt.montoPagado + COALESCE(sm.montoPagado,0)) = (mnt.monto + COALESCE(sm.monto,0)) THEN 'PT - Pagado 100% | '
                                                        ELSE CONCAT('PP - Pagado ',
                                                                (mnt.montoPagado + COALESCE(sm.montoPagado,0)),
                                                                ' restan ',
                                                                (mnt.monto + COALESCE(sm.monto,0)) - (mnt.montoPagado + COALESCE(sm.montoPagado,0)),
                                                                '| ')
                                                    END,
                                                    CONCAT('Total ',
                                                            (mnt.monto + COALESCE(sm.monto,0)),
                                                            ' | ',
                                                            CASE
                                                                WHEN (mnt.montoEnValidacion + COALESCE(sm.montoEnValidacion,0)) > 0 THEN
                                                                    CONCAT(' Depo en Validacion ',(mnt.montoEnValidacion + COALESCE(sm.montoEnValidacion,0)), ' | ')
                                                                ELSE
                                                                    ''
                                                            END,
                                                            COALESCE(htl.bedPreference,''),
                                                            ' | '),
                                                    IF(htl.agencia='CALLUSPH' AND htl.hotel IN ('GOP','GOC'),'Promo wifi mdo ame | ',''),
                                                    htl.notasHotel),
                                            1,
                                            150) AS notasSugeridas,
                    ml.orId as orId, ml.orLevel as orLevel,
                    COALESCE(CONVERT(htl.htl_nombre_1 USING utf8),splitName(htl.titular,1)) as htl_nombre_1,
                    COALESCE(CONVERT(htl.htl_apellido_1 USING utf8),splitName(htl.titular,2)) as htl_apellido_1,
					htl.htl_nombre_2, htl.htl_apellido_2,
					htl.htl_nombre_3, htl.htl_apellido_3,
					htl.htl_nombre_4, htl.htl_apellido_4,
					htl.htl_nombre_5, htl.htl_apellido_5,
					CONCAT(COALESCE(CONVERT(htl.htl_nombre_1 USING utf8),splitName(htl.titular,1)),' ',COALESCE(CONVERT(htl.htl_apellido_1 USING utf8),splitName(htl.titular,2))) as huesped1,
					CONCAT(htl.htl_nombre_2,' ',htl.htl_apellido_2) as huesped2,
					CONCAT(htl.htl_nombre_3,' ',htl.htl_apellido_3) as huesped3,
					CONCAT(htl.htl_nombre_4,' ',htl.htl_apellido_4) as huesped4,
					CONCAT(htl.htl_nombre_5,' ',htl.htl_apellido_5) as huesped5,
					it.insuranceRelated, sg_cobertura, sg_mdo, sg_pax, sg_itemRelated, CONCAT(sg_itemRelated,'-',it.isCancel) as sg_itemRelatedStatus, 
					\"$jwt\" as token
                FROM
                    cycoasis_rsv.r_items it
                        LEFT JOIN
                    cycoasis_rsv.servicios tp ON it.itemType = tp.id
                        LEFT JOIN
                    cycoasis_rsv.r_hoteles htl ON it.itemId = htl.itemId
                        LEFT JOIN
                    cycoasis_rsv.r_daypass dp ON it.itemId = dp.itemId
                        LEFT JOIN
                    cycoasis_rsv.r_xfer xf ON it.itemId = xf.itemId
                        LEFT JOIN
                    cycoasis_rsv.r_xfer_vcm xfv ON it.itemId = xfv.itemId
                        LEFT JOIN
                    cycoasis_rsv.r_tour tr ON it.itemId = tr.itemId
                        LEFT JOIN
                    cycoasis_rsv.r_xtras xt ON it.itemId = xt.itemId
                        LEFT JOIN
					cycoasis_rsv.r_seguros sg ON it.itemId=sg.itemId
					    LEFT JOIN
					cycoasis_rsv.r_monto sm ON it.insuranceRelated=sm.itemId
                        LEFT JOIN
                    cycoasis_rsv.cat_xfers cxf ON xf.xferId = cxf.id
                        LEFT JOIN
                    cycoasis_rsv.cat_tours ctr ON tr.tourId = ctr.id
                        LEFT JOIN
                    cycoasis_rsv.cat_concert cnt ON IF(xt.serviceId=9,productId,NULL) = cnt.concertId
                        LEFT JOIN
                    cycoasis_rsv.cat_daypass cdp ON dp.daypassType = cdp.id
                        LEFT JOIN
                    cycoasis_rsv.cat_xferZone xfz ON xf.zone = xfz.zoneId
                        LEFT JOIN
                    cycoasis_rsv.cat_autos cau ON IF(xt.serviceId=3,productId,NULL) = cau.id
                        LEFT JOIN
                    cycoasis_rsv.r_monto mnt ON it.itemId = mnt.itemId
                		LEFT JOIN
                    cycoasis_rsv.r_masterlocators ml ON it.masterLocatorId = ml.masterlocatorid
                        LEFT JOIN
                    cycoasis_oasis.cat_habitaciones ch ON htl.hotel=ch.hotelCode AND htl.categoria=ch.habCode
                        LEFT JOIN
                    cycoasis_oasis.cat_hoteles chtl ON COALESCE(htl.hotel,dp.hotel)=chtl.code
                        LEFT JOIN 
                    Asesores ase ON ml.userCreated=ase.id
                        LEFT JOIN
                    cycoasis_rsv.cat_providers pr ON pr.providerId = CASE 
                                                                    		WHEN it.itemType = 1 THEN null
                                                                    		WHEN it.itemType = 2 THEN cdp.proveedor
                                                                    		WHEN it.itemType = 3 THEN cau.proveedor
                                                                    		WHEN it.itemType = 4 THEN ctr.proveedor
                                                                    		WHEN it.itemType = 5 THEN cxf.proveedor
                                                                    		WHEN it.itemType = 9 THEN cnt.proveedor
                                                                    		WHEN it.itemType IN (11,5) THEN 5
                                                                    	END 
                        LEFT JOIN 
                    cat_complejo cpl ON COALESCE(pr.cplx,htl.hotel)=cpl.hotel
                        LEFT JOIN 
                    links lk ON it.itemId = lk.itemId
                        LEFT JOIN 
                    (SELECT 
                        it.itemId, GROUP_CONCAT(DISTINCT CONCAT(referencia, ' (', SUBSTR(proveedor,1,1), ' ', SUBSTR(complejo,1,1), ' ', p.operacion,') ', IF(m.reference IS NULL,0,1))) AS referencias
                    FROM
                        cycoasis_rsv.r_items it
                            LEFT JOIN
                        cycoasis_rsv.p_cashTransaction c ON it.itemId = c.itemId
                            LEFT JOIN
                        res_ligasPago p ON c.accountId = p.operacion
                    		LEFT JOIN
                    	mit_payments m ON referencia=m.reference
                    WHERE
                        c.monto > 0
                    AND it.masterlocatorid=$l GROUP BY it.itemId) csh ON it.itemId=csh.itemId
                WHERE
                    $w
                ORDER BY it.itemId") ){
                        return true;
                    }else{
                        return false;
                    }
      }
  }
  
  
    
  
  public function editName_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
          $data = $this->put();
          
          if( $this->db->where('itemId',$data['item']['itemId'])->update('cycoasis_rsv.r_hoteles',array('titular' => $data['name'])) ){
             
                $mlTicket = $data['item']['mlTicket'];
                $mlItem = $data['item']['itemLocatorId'];
                $msg = "Nombre de item $mlItem modificado. ANTES: ".$data['item']['nombreCliente']." || AHORA: ".$data['name']." (cambio realizado por ".$_GET['usn'].")";
                $this->zd->saveHistory( $mlTicket, $msg ); 
              okResponse('Nombre Actualizado', 'data', true, $this);
          }else{
              errResponse('Error al actualizar nombre', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
          }
      
      });
  }    
  
  public function editNameV2_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
          $data = $this->put();
          
          $data['names']['titular'] = $data['names']['htl_nombre_1'].' '.$data['names']['htl_apellido_1'];
          
          if( $this->db->where('itemId',$data['item']['itemId'])->update('cycoasis_rsv.r_hoteles',$data['names']) ){
             
                $mlTicket = $data['item']['mlTicket'];
                $mlItem = $data['item']['itemLocatorId'];
                
                $msg = "Modificacion de nombres en item $mlItem: <br>";
                
                for ($i = 1; $i <= 5; $i++) {
                    
                    if( $data['item']['htl_nombre_'.$i] != $data['names']['htl_nombre_'.$i] || $data['item']['htl_apellido_'.$i] != $data['names']['htl_apellido_'.$i] ){
                        $msg.= "Huesped $i. ANTES: ".$data['item']['huesped'.$i]." || AHORA: ".$data['names']['htl_nombre_'.$i].' '.$data['names']['htl_apellido_'.$i]."<br>";
                    }
                    
                }
                
                $msg .= "<br>Cambio realizado por ".$_GET['usn'].")";
                $this->zd->saveHistory( $mlTicket, $msg ); 
              okResponse('Nombre Actualizado', 'data', true, $this);
          }else{
              errResponse('Error al actualizar nombre', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
          }
      
      });
  } 
  
  public function confGetRsv_put(){
      $data = $this->put();
      
      $token = $this->confToken($data['token']);
      // $token = $data['token'];
      
      $result = validateGenericToken($token, 'cycConf2021' );
      
      if( $result['status'] ){
          
          $locData = $this->m2Loc($result['payload']->masterlocatorid);
          
      }else{
          errResponse('Token invalido, rsva no encontrada', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $result);
      }
      
      okResponse('Nombre Actualizado', 'data', $result, $this);
  }
  
    

    
  
  public function manageDayPass_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
        $data = $this->put();

        if( isset($data['loc']) ){
            $loc = $data['loc'];
            
            if( $qM = $this->db->from('res_master')->where('masterlocatorid',$loc)->get() ){
                $master = $qM->row_array();
            }else{
                errResponse('Error al obtener el masterlocatorid', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }else{
            $master = array();
        }
        
        $cred = $this->db->query("SELECT rsv_viewAll FROM Asesores a LEFT JOIN userDB b ON a.id=b.asesor_id LEFT JOIN cat_profiles c ON b.profile=c.id WHERE a.id=".$_GET['usid']);
        $credQ = $cred->row_array();
        $viewAll = $credQ['rsv_viewAll'] == "0" ? false : true;
        
        $this->db->select("a.*,
                            GROUP_CONCAT(DISTINCT proveedor) AS proveedor,
                            GROUP_CONCAT(ticket) AS ticket,
                            GROUP_CONCAT(operacion) AS operaciones,
                            SUM(b.monto) AS montoPago,
                            b.moneda AS monedaPago,
                            GROUP_CONCAT(a.dtCreated) AS fechaPago,
                            CONCAT('https://oasishoteles.zendesk.com/agent/tickets/',ticket) as linkTicket_pago,
                            CONCAT('https://oasishoteles.zendesk.com/agent/tickets/',ticket_conf) as linkTicket_conf,
                            CONCAT('https://oasishoteles.zendesk.com/agent/tickets/',ticket_cancel) as linkTicket_cancel, NOMBREASESOR(asesor,1) as creador,
                            IF(CURDATE()>llegada AND status=1 AND SUM(COALESCE(b.monto,0)),1,0) as o")
                        ->from('t_daypass a')
                        ->join('res_ligasPago b',  'b.referencia LIKE CONCAT(\'%\', a.conf, \'%\')', 'left')
                        ->group_by('a.conf');
                            
        if( isset($loc) ){
            $this->db->where('a.conf',$loc);
        }else{
            if( !$viewAll ){
                $this->db->where('a.asesor',$_GET['usid']);
            }
        }
        
        if( isset($data['dtArrivalFlag']) && $data['dtArrivalFlag'] ){
            $this->db->where("a.llegada BETWEEN '".$data['arrival_inicio']."' AND '",$data['arrival_fin']."'", FALSE);
        }
        
        if( isset($data['dtCreatedFlag']) && $data['dtCreatedFlag'] ){
            $this->db->where("a.dtCreated BETWEEN '".$data['created_inicio']."' AND '",$data['created_fin']."'", FALSE);
        }
        
        if( isset($data['asesorFlag']) && $data['asesorFlag'] ){
            $this->db->where("a.asesor",$data['asesor']);
        }
        
        if( isset($data['locFlag']) && $data['locFlag'] ){
             $this->db->group_start()->where('a.conf',$data['searchString'])->or_where('a.nombre',$data['searchString'])->group_end();
        }
        
        
        if( isset($data['nr_overDue']) && $data['nr_overDue'] ){
            $this->db->where("DATEDIFF(CURDATE(),a.dtCreated) >=", "2")->where("SUM(COALESCE(b.monto,0))<=0","",FALSE);
        }
        
        if( $qM = $this->db->get() ){
            $items = $qM->result_array();
        }else{
            errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
        okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this);

      });
  }
  
    public function listPaymentsV12_put(){
      
        tokenValidation12( function(){
          
            $data = $this->put();
            
            $pm = model_pagos();
            $result = $pm->searchPago( $data['searchString'] );

            if( !$result['err'] ){
                okResp( $result['msg'], 'data', $result['data'] );
            }else{
                errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }

        });
    }
  
  public function listPaymentsV2_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
        $data = $this->put();

        $master = array();
        $viewAll =true;
        
        $this->db->select("a.*, GROUP_CONCAT(DISTINCT masterlocatorid) as Locs")
                            ->from('res_ligasPago a')
                            ->join('cycoasis_rsv.p_cashTransaction c', 'a.operacion = c.accountId', 'left')
                            ->join('cycoasis_rsv.r_items i', 'c.itemId = i.itemId', 'left')
                            ->order_by('dtCreated')
                            ->group_by('a.operacion');
                            
        
        if( isset($data['dtArrivalFlag']) && $data['dtArrivalFlag'] ){
            $this->db->where("CAST(a.dtCreated as DATE) BETWEEN '".$data['arrival_inicio']."' AND '",$data['arrival_fin']."'", FALSE);
        }
        
        if( isset($data['dtCreatedFlag']) && $data['dtCreatedFlag'] ){
            $this->db->where("CAST(b.dtCreated as DATE) BETWEEN '".$data['created_inicio']."' AND '",$data['created_fin']."'", FALSE);
        }
        
        if( isset($data['locFlag']) && $data['locFlag'] ){
             $this->db->group_start()->where('a.operacion LIKE \'%',$data['searchString']."%'", FALSE)
                                    ->or_where('a.referencia LIKE \'%',$data['searchString']."%'", FALSE)
                                    ->or_where('a.aut LIKE \'%',$data['searchString']."%'", FALSE)
                                    ->or_where('a.monto',$data['searchString'])->group_end();
        }
        
        if( isset($data['reemb']) && $data['reemb'] ){
            $this->db->where('reembolsoReq', 1);
        }
        
        if( isset($data['reembPend']) && $data['reembPend'] ){
            $this->db->where('reembolsoAplicado', 0);
        }
        
        if( $qM = $this->db->get() ){
            $items = $qM->result_array();
        }else{
            errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
        okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this);

      });
  }
  
  public function listPayments_put(){
      
      $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
          
        $data = $this->put();

        $master = array();
        $viewAll =true;
        
        $this->db->select("a.*,
                            b.link,
                    		b.itemsIncluded,
                    		NOMBREASESOR(b.createdBy, 1) AS creador,
                    		b.dtCreated AS fechaCreacion,
                    		GROUP_CONCAT(r.masterItemLocator) AS Locs,
                    		IF(a.monto = b.monto
                    				AND IF(a.moneda = 'DLS', 'USD', a.moneda) = IF(b.moneda = 'MEX', 'MXN', b.moneda),
                    			1,
                    			0) AS consistencia,
                    			SUBSTRING(r.masterItemLocator, 1, 6) as master,
                    		IF(b.id IS NULL AND p.id IS NOT NULL,1,0) as possibleLink")
                            ->from('res_ligasPago a')
                            ->join('res_payments b', 'a.paymentId = b.id', 'left')
                            ->join('res_payments p', 'a.referencia=p.referencia', 'left')
                            ->join('res_payRelates r', 'b.id = r.paymentId', 'left')
                            ->order_by('dtCreated')
                            ->group_by('a.operacion');
                            
        
        if( isset($data['dtArrivalFlag']) && $data['dtArrivalFlag'] ){
            $this->db->where("CAST(a.dtCreated as DATE) BETWEEN '".$data['arrival_inicio']."' AND '",$data['arrival_fin']."'", FALSE);
        }
        
        if( isset($data['dtCreatedFlag']) && $data['dtCreatedFlag'] ){
            $this->db->where("CAST(b.dtCreated as DATE) BETWEEN '".$data['created_inicio']."' AND '",$data['created_fin']."'", FALSE);
        }
        
        if( isset($data['locFlag']) && $data['locFlag'] ){
             $this->db->group_start()->where('a.operacion LIKE \'%',$data['searchString']."%'", FALSE)
                                    ->or_where('a.referencia LIKE \'%',$data['searchString']."%'", FALSE)
                                    ->or_where('a.aut LIKE \'%',$data['searchString']."%'", FALSE)
                                    ->or_where('a.monto',$data['searchString'])
                                    ->or_where('b.monto',$data['searchString'])->group_end();
        }
        
        if( $qM = $this->db->get() ){
            $items = $qM->result_array();
        }else{
            errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
        okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $items), $this);

      });
  }
  
  
  public function itemFieldChg_put(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();
        
        
        
        if( $this->db->set($data['field'], $data['val'])->where('masterItemLocator', $data['masterItemLocator'])->update('res_items') ){
            
            $insert = array(
                'masterItemLocator' => $data['masterItemLocator'],
                'category' => 'itemLocator',
                'campo' => $data['field'],
                'new_val' => $data['val'],
                'user' => $_GET['usid']
                );
            $this->db->set($insert)->insert('res_history');
            okResponse('Reserva Actualizada', 'data', true, $this);
        }else{
            errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function addPayment_put(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();
        
        if( $this->db->set($data['payment'])->insert('res_payments') ){
            $id = $this->db->insert_id();
            
            foreach($data['items'] as $index => $info){
                $data['items'][$index]['paymentId'] = $id;
            }


            if( $this->db->insert_on_duplicate_update_batch('res_payRelates', $data['items']) ){
               okResponse('Pago Insertado', 'data', true, $this);
            }else{
                errResponse('Error al insertar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
        }else{
            errResponse('Error al insertar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
    private function getAfil( $c, $m, $t ){
        
        $afil = array(
                    "Cancun" => array(
                            "MXN-V" => "7633943",
                            "USD-V" => "7633947",
                            "MXN-A" => "9357302596",
                            "USD-A" => "7633947",
                        ),
                    "Palm" => array(
                            "MXN-V" => "7254149",
                            "USD-V" => "7254131",
                            "MXN-A" => "7254149",
                            "USD-A" => "7254131",
                        ),
                    "Smart" => array(
                            "MXN-V" => "000000",
                            "USD-V" => "000000",
                            "MXN-A" => "000000",
                            "USD-A" => "000000",
                        ),
                    "Vcm" => array(
                            "MXN-V" => "000000",
                            "USD-V" => "000000",
                            "MXN-A" => "000000",
                            "USD-A" => "000000",
                        )
            );
            
        $type = $t == null ? "-V" : $t;
        $type = strtolower($t) == 'amex' ? '-A' : '-V';
        
        return $afil[$c]["$m$type"];
        
    }
  
  public function regPayment_put(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();
        
        if( $data['tipo'] == 'Deposito' ){
            $data['isValidated'] = 0;
        }
        
        if( $data['tipo'] == 'CXC' ){
            $data['proveedor'] = 'CXC';
            $data['tipo'] = 'CXC';
            $data['tarjeta'] = 'sinTarjeta';
            $data['tipoTarjeta'] = 'sinTarjeta';
            $data['afiliacion'] = '000000';
        }
        
        if( $data['tipo'] == 'Roiback' || $data['tipo'] == 'Central' ){
            
            if( $data['tipo'] == 'Central' ){
                $data['montoSaldo'] = $data['monto']; 
            }else{
                $data['montoSaldo'] = 0;
                $data['montoUsado'] = $data['monto'];
            }
            
            $data['proveedor'] = 'Santander';
            $data['tipo'] = 'cobroCentral';
            $data['tarjeta'] = 'verVoucher';
            $data['afiliacion'] = $this->getAfil($data['complejo'], $data['moneda'], $data['tipoTarjeta'] ?? 'visa' );
            
            $data['tipoTarjeta'] = 'verVoucher';
            
            
        }else{
            $data['montoSaldo'] = $data['monto'];
        }
        
        if( $this->db->set($data)->insert('res_ligasPago') ){
            $slack = array(
              "text" => "Nuevo pago ".$data['tipo']." ".$data['complejo']." registrado en CyC",
              "attachments" => array(
                array(
                    "fallback" => "Pago Recibido",
                    "author_name" => "Owner: ".$_GET['usn'],
                    "title" => "Notificación de Pago",
                    "text" => "Pago registrado para reservas ".$data['referencia']." en el complejo ".$data['complejo'].".\n Monto total: ".money_format('%.2n', $data['monto'])." ".$data['moneda']
                    )
              )
            );
            
            // $editTkt = array("ticket" => array(
            //                 "status" => "solved",
            //                 "comment" => array("body" => "Rsva cancelada. Notificado en ticket ".$data['ticket'], "public"=> false, "author_id" => 360005313512)));
            //         $tkt = json_encode($editTkt);
                    
            //         $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$voRow['ticket_created'].'.json';
            //         $responseOk = getUrlContent( $url, true, true, $tkt);
                    
            postSlackPagos(json_encode($slack));
           okResponse('Pago Insertado', 'data', true, $this);
        }else{
            errResponse('Error al insertar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function getPayments_get(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $loc = $this->uri->segment(3);
        $rsva = $this->uri->segment(4);
        
        if( !isset($rsva) ){
            $rsva = "#########";
        }

        $this->db->select("a.masterItemLocator, a.monto as montoItem, b.*, proveedor, c.operacion, c.tipo, c.complejo")
                ->select("c.ticket, c.monto as montoLiga, c.afiliacion, c.tarjeta, c.tipoTarjeta" )
                ->select("c.referencia as pRef, c.aut, c.dtCreated as fechaPago" )
                ->select("CASE WHEN b.paymentType = 0 THEN 'PayPal' WHEN b.paymentType=1 THEN 'Santander' ELSE 'Deposito' END as tipoPago" )
                ->from('res_payRelates a')
                ->join('res_payments b', 'a.paymentid=b.id', 'left')
                ->join('res_ligasPago c', 'b.id = c.paymentId', 'left')
                ->order_by('id')
                ->where('b.activo',1)
                ->where('a.masterItemLocator', $loc);
        
        if( $q = $this->db->get() ){
            $this->db->from('res_ligasPago')->where('referencia LIKE ', "'%".$loc."%'", FALSE)->or_where('referencia LIKE ', "'%".$rsva."%'", FALSE);
            
            if( $p = $this->db->get() ){
                okResponse('Pagos Obtenidos', 'data', $q->result_array(), $this, 'ligas', $p->num_rows());
            }else{
                errResponse('Error al insertar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }else{
            errResponse('Error al insertar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function deletePayment_put(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $id = $this->put();

        if( $this->db->set('activo',0)->where('id',$id['id'])->update('res_payments') ){
           okResponse('Pagos Desactivados', 'data', true, $this);
        }else{
            errResponse('Error al desactivar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function getLinks_get(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $loc = $this->uri->segment(3);
        $tipo = $this->uri->segment(4);

        $this->db->from('res_ligasPago a')->order_by('operacion')
                ->where("referencia LIKE '%", $loc."%'", FALSE)
                ->where("proveedor LIKE '%", $tipo."%'", FALSE);
        
        if( $q = $this->db->get() ){
           okResponse('Pagos Obtenidos', 'data', $q->result_array(), $this);
        }else{
            errResponse('Error al insertar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function linkPayment_put(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();

        if( $this->db->set($data['link'])->where('operacion', $data['operacion'])->update('res_ligasPago') ){
            
            if( $data['unset'] != null ){
                $this->db->set(array('status'=>0))->where('id', $data['unset'])->update('res_payments');
            }
            
            if( $data['link']['paymentId'] == null ){
                $p = 0;
                $this->db->where('id', $data['last_id']);
            }else{
                $p = 1;
                $this->db->where('id', $data['last_id']);
            }
           $payment = array('status' => $p);
           if( $this->db->set($payment)->update('res_payments') ){
               okResponse('Pagos Editado', 'data', true, $this);
            }else{
                errResponse('Error al editar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }else{
            errResponse('Error al editar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function linkPaymentV2_put(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $data = $this->put();

        // GET ORIGINAL DATA
        if( $origQ = $this->db->from('res_ligasPago')->where('operacion',$data['op'])->get() ){
            $orig = $origQ->row_array();
            
            // WORK WITH OLD DATA
            if( $orig['paymentId'] != null ){
                $accQ = $this->db->from('cycoasis_rsv.p_accounts')->where('id',$orig['paymentId'])->get();
                $acc = $accQ->row_array();
                
                // IF AMMOUNT HAS NOT BEEN USED
                if( floatval($acc['saldo']) - floatval($orig['monto']) >= 0 ){
                    
                    // UPDATE ORIGINAL ACCOUNT
                    $query = "UPDATE cycoasis_rsv.p_accounts SET pagado = pagado - ".$orig['monto'].", saldo = pagado - usado WHERE id = ".$orig['paymentId'];
                    
                    // UPDATE NEW ACCOUNT
                    if( $this->db->query($query) ){
                        if( $ins = $this->addAccount( $data['zdUserId'], $orig['complejo'], $orig['monto'], $orig['moneda'], $orig['paymentId'] ) ){
                            
                            // UPDATE TRANSACTION
                            if( $this->db->set(array('linked' => 1, 'paymentId' => $ins[1]))->where('operacion', $data['op'])->update('res_ligasPago') ){
                                okResponse('Pago ligado correctamente en cuenta '.$ins[1]." La cuenta ya contaba con un saldo inicial", 'data', true, $this);
                            }else{
                                errResponse('Error al ligar pago. Se sumó al saldo pero no se agregó a la transacción', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                            }
                            
                        }else{
                            errResponse('No fue posible agregar el saldo a la cuenta', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                        }
                    }else{
                        errResponse('Error desvincular pago, no se realizó ningún movimiento', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }
                    
                // IF AMMOUNT HAS BEEN USED, DO NOTHING
                }else{
                    errResponse('El saldo ya está siendo utilizado en alguna reserva y no es posible moverlo de cuenta', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }
                
                return false;
            }
            
            // NO OLD DATA, UPDATE LINK
            if( $ins = $this->addAccount( $data['zdUserId'], $orig['complejo'], $orig['monto'], $orig['moneda'] ) ){
                if( $this->db->set(array('linked' => 1, 'paymentId' => $ins[1]))->where('operacion', $data['op'])->update('res_ligasPago') ){
                    okResponse('Pago ligado (first time) correctamente en cuenta '.$ins[1], 'data', true, $this);
                }else{
                    errResponse('Error al ligar pago. Se sumó al saldo pero no se agregó a la transacción', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
                errResponse('No fue posible agregar el saldo a la cuenta', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
        }else{
            errResponse('Error al obtener información del pago. No se realizó ningún movimiento', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
   public function addAccount( $client, $cuenta, $monto, $moneda, $pid = 0 ){
       

        $insQ = $this->db->set(array('zdClientId' => $client, 'cuenta' => $cuenta."-".$moneda, 'pagado' => $monto, 'saldo' => $monto, 'moneda' => $moneda))->get_compiled_insert('cycoasis_rsv.p_accounts');
        $insQ .= " ON DUPLICATE KEY UPDATE pagado=pagado+VALUES(pagado), saldo=pagado-usado";
            
        if( $this->db->query($insQ) ){
            return array( true, $this->db->insert_id() );
        }else{
            return false;
        }

        
            
    }
    
  public function getVoucher_get(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $loc = $this->uri->segment(3);
        $rsva = $this->uri->segment(4);
        
        $this->db->from('res_ligasPago')->where('referencia LIKE ', "'%".$loc."%'", FALSE)->or_where('referencia LIKE ', "'%".$rsva."%'", FALSE);
        
        if( $q = $this->db->get() ){
            okResponse('Pagos Obtenidos', 'data', $q->result_array(), $this);
        }else{
            errResponse('Error al insertar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
    
  public function getAccount_get(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $user = $this->uri->segment(3);
        
        $this->db->select('*')
            ->from('cycoasis_rsv.p_accounts')
            ->where('zdClientId', $user)
            ->order_by('cuenta');
        
        if( $q = $this->db->get() ){
            $accounts = $q->result_array();
            $ids = array();
            foreach( $accounts as $index => $a ){
                array_push($ids, $a['id']);
            }
            
            if( $acQ = $this->db->from('res_ligasPago')->where_in('paymentId', $ids)->order_by('dtCreated', 'DESC')->get() ){
                $resPay = array();
                foreach( $acQ->result_array() as $ind => $p ){
                    if( isset($resPay[$p['paymentId']]) ){
                        array_push($resPay[$p['paymentId']], $p);
                    }else{
                        $resPay[$p['paymentId']] = array($p);
                    }
                }
                okResponse('Cuentas Obtenidos', 'data', $accounts, $this, 'payments', $resPay);
            }else{
                errResponse('Error al obtener pagos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
            
        }else{
            errResponse('Error al obtener pagos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }

      });
  }
  
  public function editMontoTotal_put(){
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
         
         $data = $this->put();
         
         if( isset($data['original']['isR']) ){ 
            $data['isR'] = $data['original']['isR'];
         }
         $this->editMontoTotal($data);
         
     });
  }
    
    private function editMontoTotal($data, $flag = true){
        
        $rsv = model_rsv();
        $pm = model_pagos();
        $mlg = model_mailing();
        $zd = model_zendesk();
     
        $monto = array(
            'monto' => $data['newMonto'],
            'itemId' => $data['original']['itemId'],
            'discount' => 1 - ($data['newMonto']/$data['original']['montoOriginal']),
            'montoParcial' => ($data['original']['montoParcial'] > $data['newMonto'] || $data['original']['montoParcial'] == $data['original']['monto']) ? $data['newMonto'] : $data['original']['montoParcial'],
            'montoPagado' => ($data['original']['montoPagado'] >= $data['newMonto']) ? $data['newMonto'] : $data['original']['montoPagado']
         );
         
        $mp="";
        
        if( $data['original']['montoParcial'] > $data['newMonto'] || $data['original']['montoParcial'] == $data['original']['monto'] ){
            $mp = "(El monto parcial se ha modificado también a ".$monto['montoParcial'];
        }
        
        if( $monto['montoParcial'] == $data['newMonto'] ){
            $this->db->set('isParcial',0);
        }
        
        if( $this->db->where('itemId', $data['original']['itemId'])->set($monto)->update('cycoasis_rsv.r_monto') ){
        
            if( $monto['montoParcial'] == $data['original']['montoPagado'] && $data['original']['isQuote'] == 1 ){
                 $this->db->where('itemId', $data['original']['itemId'])->set(array('isQuote' => 0));
                 
                 if( $this->db->update('cycoasis_rsv.r_items') ){
            
                    // CONFIRMAR RESERVA
                    if( $data['original']['confirmOK'] == null ){
                        $confId = $this->notifyProvider($data['original']['itemId']);
                        if( $confId != 0 ){
                            $msg = "Correo de solicitud de confirmación a proveedor para item ".$data['original']['itemLocatorId']." enviado en ticket $confId";
                            $this->zd->saveHistory( $data['original']['mlTicket'], $msg );
                        }
                    }
                    
                    $data['original']['monto'] = $data['newMonto'];
                    
                    
                    if( $flag ){
                        $mlg->sendFull($data['original']['masterLocatorId'],3);
                    }else{
                        return array('err' => false, 'msg' => 'Monto confirmado');
                    }
                    
                }else{
                    if( $flag ){
                        $pm->setMontosPagados($data['original']['itemId']);
                        errResponse('Error al modificar el monto', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }else{
                        return array('err' => true, 'msg' => 'Error al modificar el monto', 'error' => $this->db->error());
                    }
                }
            }
            
            $mp="";
            
            $devol = $this->ajusteMonto_devolucion( $data['original'], $data['newMonto'], isset($data['isR']) ? $data['isR'] : false );
            
            if( $devol['ERR'] ){
                $pm->setMontosPagados($data['original']['itemId']);
                errResponse($devol['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $devol['error']);
            }
            
            $devMsg = isset($devol) ? "<br>".$devol['msg']."<br>" : '';
            
            if( $data['original']['montoParcial'] > $data['newMonto'] ){
                $mp = "(El monto parcial se ha modificado también a ".$monto['montoParcial'].")";
            }
            
            if( $data['newMonto'] == $data['original']['monto'] ){
                $msg = "Cambio en monto Total del item ".$data['original']['itemLocatorId']." solicitado, pero no se encontraron cambios. Monto definido: ".$data['newMonto']." por ".$_GET['usn']." ********** ".$data['comment']." ********** $mp";
            }else{
                $msg = "Monto Total del item ".$data['original']['itemLocatorId']." modificado a ".$data['newMonto']." (Monto original: ".$data['original']['monto'].") por ".$_GET['usn']."$devMsg ********** ".$data['comment']." ********** $mp";
            }
            $this->zd->saveHistory( $data['original']['mlTicket'], $msg ); 
            
            if( $flag ){
                
                $pm->setMontosPagados($data['original']['itemId']);
                $actualItem = $rsv->getItem( $data['original']['itemLocatorId'] );
                okResponse('Monto correctamente modificado', 'data', array('monto' => $data['newMonto'], 'montoParcial' => $monto['montoParcial'],'montoPagado' => $monto['montoPagado']), $this, 'item', $actualItem['data']);
            }else{
                return array('err' => false, 'msg' => 'Monto confirmado');
            }
        }else{
            $pm->setMontosPagados($data['original']['itemId']);
            errResponse('Error al modificar el monto', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
     
    }
    
    public function testpenalty_get(){
        if( moneyVal($dataO['montoPagado']) + moneyVal($dataO['montoEnValidacion']) <= $dataMonto ){
            return array('ERR' => false, 'msg' => "");
        }

        $updateBatch = array();
        $insertBatch = array();
        
        $montoR = (moneyVal($dataO['montoPagado']) + moneyVal($dataO['montoEnValidacion'])) - $dataMonto;
        $montoPagPen = moneyVal($dataO['montoPagado']) >= $dataMonto ? moneyVal($dataO['montoPagado']) - $dataMonto : 0;
        $montoValidPen = moneyVal($dataO['montoEnValidacion']) - ($dataMonto - (moneyVal($dataO['montoPagado']) - $montoPagPen));
        $montoFavor = $montoR;
        
        $related = $this->itemPaymentsV2($dataO['itemId'],false);
            
        // foreach( $related as $index => $p ){
            
            // DEPOSITS AND PAYMENTS
            foreach( $related as $index => $p ){
                if( $p['paymentValid'] == '1' ){
                    $tmpCT = array('operacion' => $p['accountId']);
                    $tmpIns = array('accountId' => $p['accountId'], 'itemId' => $dataO['itemId'], 'userCreated' => $_GET['usid'], 'monedaAplicada' => $p['monedaAplicada'], 'cambioAplicado' => $p['cambioAplicado']);
                    
                    if( $montoPagPen > 0 ){
                        if( $montoPagPen >= moneyVal($p['monto']) ){
                            $tmpMonto = moneyVal($p['montoTc']);
                            $tmpMontoOG = moneyVal($p['monto']);
                        }else{
                            $tmpMonto = $montoPagPen;
                            $tmpMontoOG = $montoPagPen;
                        }
                        
                        if( $isR ){
                            $tmpCT['montoReembolso'] = $tmpMonto + moneyVal($p['montoReembolso']);
                            $tmpCT['reembolsoReq'] = 1;
                        }else{
                            $tmpCT['montoSaldo'] = $tmpMonto + moneyVal($p['montoSaldo']);
                        }
                        $tmpCT['montoUsado'] = moneyVal($p['montoUsado']) - $tmpMonto;
                        $tmpIns['monto'] = $tmpMontoOG * (-1);
                        $tmpIns['txType'] = $isR ? 'reembolso' : 'traspaso';
                        $tmpIns['cieloTxRef'] = $p['cashTransactionId'];
                        
                        array_push($updateBatch, $tmpCT);
                        array_push($insertBatch, $tmpIns);
                        $montoPagPen -= $tmpMontoOG;
                    }
                }
            }
            
            foreach( $related as $index => $p ){
                if( $p['paymentValid'] == '0' ){
                    
                    $tmpCT = array('operacion' => $p['accountId']);
                    $tmpIns = array('accountId' => $p['accountId'], 'itemId' => $dataO['itemId'], 'userCreated' => $_GET['usid'], 'monedaAplicada' => $p['monedaAplicada'], 'cambioAplicado' => $p['cambioAplicado']);
                    
                    if( $montoValidPen > 0 ){
                        if( $montoValidPen >= moneyVal($p['monto']) ){
                            $tmpMonto = moneyVal($p['montoTc']);
                            $tmpMontoOG = moneyVal($p['monto']);
                        }else{
                            $tmpMonto = $montoValidPen;
                            $tmpMontoOG = $montoValidPen;
                        }
                        
                        if( $isR ){
                            $tmpCT['montoReembolso'] = $tmpMonto + moneyVal($p['montoReembolso']);
                            $tmpCT['reembolsoReq'] = 1;
                        }else{
                            $tmpCT['montoSaldo'] = $tmpMonto + moneyVal($p['montoSaldo']);
                        }
                        $tmpCT['montoUsado'] = moneyVal($p['montoUsado']) - $tmpMonto;
                        $tmpIns['monto'] = $tmpMontoOG * (-1);
                        $tmpIns['txType'] = $isR ? 'reembolso' : 'traspaso';
                        $tmpIns['cieloTxRef'] = $p['cashTransactionId'];
                        
                        array_push($updateBatch, $tmpCT);
                        array_push($insertBatch, $tmpIns);
                        $montoValidPen -= $tmpMontoOG;
                    }
                }
            }
            
        
        okResp('test', 'data', $updateBatch);
    }
    
    private function ajusteMonto_devolucion( $dataO, $dataMonto, $isR ){
        
       if( moneyVal($dataO['montoPagado']) + moneyVal($dataO['montoEnValidacion']) <= $dataMonto ){
            return array('ERR' => false, 'msg' => "");
        }

        $updateBatch = array();
        $insertBatch = array();
        
        $montoR = (moneyVal($dataO['montoPagado']) + moneyVal($dataO['montoEnValidacion'])) - $dataMonto;
        $montoPagPen = moneyVal($dataO['montoPagado']) >= $dataMonto ? moneyVal($dataO['montoPagado']) - $dataMonto : 0;
        $montoValidPen = moneyVal($dataO['montoEnValidacion']) - ($dataMonto - (moneyVal($dataO['montoPagado']) - $montoPagPen));
        $montoFavor = $montoR;
        
        $related = $this->itemPaymentsV2($dataO['itemId'],false);
            
        // foreach( $related as $index => $p ){
            
            // DEPOSITS AND PAYMENTS
            foreach( $related as $index => $p ){
                if( $p['paymentValid'] == '1' ){
                    $tmpCT = array('operacion' => $p['accountId']);
                    $tmpIns = array('accountId' => $p['accountId'], 'itemId' => $dataO['itemId'], 'userCreated' => $_GET['usid'], 'monedaAplicada' => $p['monedaAplicada'], 'cambioAplicado' => $p['cambioAplicado']);
                    
                    if( $montoPagPen > 0 ){
                        if( $montoPagPen >= moneyVal($p['monto']) ){
                            $tmpMonto = moneyVal($p['montoTc']);
                            $tmpMontoOG = moneyVal($p['monto']);
                        }else{
                            $tmpMonto = $montoPagPen;
                            $tmpMontoOG = $montoPagPen;
                        }
                        
                        if( $isR ){
                            $tmpCT['montoReembolso'] = $tmpMonto + moneyVal($p['montoReembolso']);
                            $tmpCT['reembolsoReq'] = 1;
                        }else{
                            $tmpCT['montoSaldo'] = $tmpMonto + moneyVal($p['montoSaldo']);
                        }
                        $tmpCT['montoUsado'] = moneyVal($p['montoUsado']) - $tmpMonto;
                        $tmpIns['monto'] = $tmpMontoOG * (-1);
                        $tmpIns['txType'] = $isR ? 'reembolso' : 'traspaso';
                        $tmpIns['cieloTxRef'] = $p['cashTransactionId'];
                        
                        array_push($updateBatch, $tmpCT);
                        array_push($insertBatch, $tmpIns);
                        $montoPagPen -= $tmpMontoOG;
                    }
                }
            }
            
            foreach( $related as $index => $p ){
                if( $p['paymentValid'] == '0' ){
                    
                    $tmpCT = array('operacion' => $p['accountId']);
                    $tmpIns = array('accountId' => $p['accountId'], 'itemId' => $dataO['itemId'], 'userCreated' => $_GET['usid'], 'monedaAplicada' => $p['monedaAplicada'], 'cambioAplicado' => $p['cambioAplicado']);
                    
                    if( $montoValidPen > 0 ){
                        if( $montoValidPen >= moneyVal($p['monto']) ){
                            $tmpMonto = moneyVal($p['montoTc']);
                            $tmpMontoOG = moneyVal($p['monto']);
                        }else{
                            $tmpMonto = $montoValidPen;
                            $tmpMontoOG = $montoValidPen;
                        }
                        
                        if( $isR ){
                            $tmpCT['montoReembolso'] = $tmpMonto + moneyVal($p['montoReembolso']);
                            $tmpCT['reembolsoReq'] = 1;
                        }else{
                            $tmpCT['montoSaldo'] = $tmpMonto + moneyVal($p['montoSaldo']);
                        }
                        $tmpCT['montoUsado'] = moneyVal($p['montoUsado']) - $tmpMonto;
                        $tmpIns['monto'] = $tmpMontoOG * (-1);
                        $tmpIns['txType'] = $isR ? 'reembolso' : 'traspaso';
                        $tmpIns['cieloTxRef'] = $p['cashTransactionId'];
                        
                        array_push($updateBatch, $tmpCT);
                        array_push($insertBatch, $tmpIns);
                        $montoValidPen -= $tmpMontoOG;
                    }
                }
            }
            
        // }
            
        // okResp('test dev', 'data', $updateBatch);
            
        if( $this->db->update_batch('res_ligasPago', $updateBatch, 'operacion') ){
            
            // Insertar en transacciones
            $ctxs = array();
            
            $pm = model_pagos();
            $cti = $pm->setTransaction($insertBatch);
            
            array_push($ctxs, $cti);
            
            return array('ERR' => false, 'tx' => $cti, 'montoFavor' => $montoFavor, 'msg' => ($montoFavor > 0 ? ("Se procesan como <span style='color: red'>".($isR ? "reembolso" : "traspaso")."</span> $montoFavor. <br>".$cti['msg']) : $cti['msg']));
        }else{
            return array('ERR' => true, 'tx' => $cti, 'montoFavor' => 0, 'msg' => 'Error al crear transacciones de devolución', 'error' => $this->db->error());
        }

    }
  
    private function editMontoParcial( $data, $flag = true ){
            $rsv = model_rsv();

            $confId=0;
            
            if( $flag ){
                
                if( floatval($data['original']['montoEnValidacion']) > 0 ){
                    errResponse('No es posible modificar prepagos con montos en validacion.', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }       
        
                if( floatVal($data['new']['montoParcial']) < floatval($data['original']['montoPagado']) && !( $_GET['usid'] == 29 ) ){
                    errResponse('No es posible editar el monto de prepago con un monto menor al ya prepagado', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }       
                
                if( floatVal($data['new']['montoParcial']) == 0 && !( $_GET['usid'] == 29 || $_GET['usid'] == 72 || $_GET['usid'] == 70 ) ){
                    errResponse('No cuentas con los permisos para establecer un pago en hotel. Por favor solicitalo a tu supervisor', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }  
                
                if( floatVal($data['new']['montoParcial']) == 0 && $data['original']['insuranceRelated'] != null && !( $_GET['usid'] == 29 ) ){
                    errResponse('No cuentas con permisos para establecer pago en hotel en una reserva con seguro. Solicitalo a tu gerente.', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }  
                
                if( floatVal($data['new']['montoParcial']) == 0 && $data['original']['insuranceRelated'] != null && $_GET['usid'] == 29 ) {
                    $this->setInsurancePh( $data['original']['insuranceRelated'], true, true, false);
                }
                
                if( floatVal($data['new']['montoParcial']) == 0 && isset($data['original']['pkgItems']) && isset($data['original']['pkgInsItemIds']) ) {
                    foreach($data['original']['pkgItems']['pkgInsItemIds'] as $pind => $pack ){
                        $this->setInsurancePh( $pack, true, true, false);
                    }
                }
            }
    
    
            $res = json_decode(json_encode($data['new'], true), true);
            
            if( floatval($data['new']['montoParcial']) > floatval($data['original']['monto']) ){
                $data['new']['montoParcial'] = $data['original']['monto'];
                $data['new']['isParcial'] = 0;
                $data['new']['isPagoHotel'] = 0;
                $res['montoParcial'] = $data['original']['monto'];
                $res['isParcial'] = 0;
                $res['isPagoHotel'] = 0;
                $res['tipoPago'] = 'PP';
                
            }else{
                if( $data['new']['montoParcial'] > 0 ){
                    $data['new']['isParcial'] = 1;
                    $data['new']['isPagoHotel'] = 0;
                    $res['isParcial'] = 1;
                    $res['isPagoHotel'] = 0;
                    $res['tipoPago'] = 'PP';
                }else{
                    $data['new']['isParcial'] = 0;
                    $data['new']['isPagoHotel'] = 1;
                    $data['new']['montoParcial'] = 0;
                    $res['isParcial'] = 0;
                    $res['isPagoHotel'] = 1;
                    $res['montoParcial'] = 0;
                    $res['tipoPago'] = 'PH';
                }
                
                // if( $data['new']['montoParcial'] > $data['original']['montoPagado'] ){
                    
                // }
            }
            
            // $updNew = $data['new'];
            $data['new']['montoParcial'] = $data['new']['montoParcial'];
            $data['new']['montoPagado']  = ($data['original']['montoPagado'] == 0 ? 0 : (($data['original']['montoPagado'] > $data['new']['montoParcial']) ? $data['new']['montoParcial'] : $data['original']['montoPagado']));
            
            if( ($data['xld'] ?? false) ){
                $data['new']['montoPenalidad'] = $data['new']['montoParcial'];
            }
            
            if( $this->db->set($data['new'])->where('itemId', $data['itemId'])->update('cycoasis_rsv.r_monto') ){
                
                $res['itemId'] = $data['itemId'];
                
                if( floatVal($data['original']['montoPagado']) == floatVal($data['new']['montoParcial']) ){
                    $res['tipoPago'] = 'PP';
                }
                
                if( floatVal($data['original']['montoPagado']) < floatVal($data['new']['montoParcial']) ){
                    $res['tipoPago'] = 'PDT';
                }
                
                if( $res['tipoPago'] == 'PH' ){
                    $this->db->query("UPDATE cycoasis_rsv.r_items SET isQuote=0 WHERE itemId=".$data['itemId']);
                    $res['isQuote'] = 0;
                    if( $data['original']['confirmOK'] == null ){
                        $res['confirm'] = 'Pendiente';
                        $confId = $this->notifyProvider($data['itemId']);
                    }
                }else{
                   if( $res['tipoPago'] == 'PP' ){
                        $this->db->query("UPDATE cycoasis_rsv.r_items SET isQuote=0 WHERE itemId=".$data['itemId']);
                        $res['isQuote'] = 0;
                        if( $data['original']['confirmOK'] == null ){
                            $res['confirm'] = 'Pendiente';
                            $confId = $this->notifyProvider($data['itemId']);
                        }
                    }else{
                        $res['isQuote'] = 1;
                        $res['confirm'] = 'Cotizacion';
                        
                        
                        if( $data['original']['montoPagado'] > 0 ){
                            $this->db->query("UPDATE cycoasis_rsv.r_items SET isQuote=1, vigencia = CAST(ADDDATE('".$data['original']['llegada']."',1) as DATETIME) WHERE itemId=".$data['itemId']);
                        }else{
                            $this->db->query("UPDATE cycoasis_rsv.r_items SET isQuote=1 WHERE itemId=".$data['itemId']);
                        }
                        // if( $data['original']['isQuote'] == '0' && $data['original']['confirmOK'] == null ){
                            // $this->db->query("UPDATE cycoasis_rsv.r_items SET isQuote=1 WHERE itemId=".$data['itemId']);
                            // $res['isQuote'] = 1;
                            // $res['confirm'] = 'Cotizacion';
                        // }else{
                        //     $res['isQuote'] = $data['original']['isQuote'];
                        // }
                    }
                }
                
                $devol = $this->ajusteMonto_devolucion( $data['original'], $data['new']['montoParcial'], isset($data['isR']) ? $data['isR'] : false );
    
                if( $devol['ERR'] ){
                    $devMsg = "<br> ".$devol['msg']."<br>";
                    $reload = false;
                }else{
                    $devMsg = "<br>".$devol['msg']."<br>";
                    $reload = true;
                }
    
                
                $mlQ = $this->db->query("SELECT historyTicket, CONCAT(i.masterlocatorid,'-',itemNumber) as item FROM cycoasis_rsv.r_masterlocators a RIGHT JOIN cycoasis_rsv.r_items i ON i.masterlocatorid=a.masterlocatorid WHERE itemId = ".$data['itemId']);
                $mlR = $mlQ->row_array();
                $mlTicket = $mlR['historyTicket'];
                $mlItem = $mlR['item'];
                $msg = "Monto parcial del item ".$mlItem." modificado a ".$data['new']['montoParcial']." por ".$_GET['usn']."$devMsg";
                $this->zd->saveHistory( $mlTicket, $msg ); 
                
                if( $confId != 0 ){
                    $msg = "Correo de solicitud de confirmación a proveedor enviado en ticket $confId";
                    $this->zd->saveHistory( $mlTicket, $msg );
                    
                    
                }
                
                if($res['isQuote'] == 0 && $data['original']['isQuote'] == '1'){
                    // $confTicket = $this->sendConf($data['original'],3);
                    $this->fullConf($data['original']['masterLocatorId'],3);
                    // $msg = "Confirmación de item ".$data['original']['itemLocatorId']." enviada en ticket $confTicket";
                    // $this->zd->saveHistory( $data['original']['mlTicket'], $msg );
                }
                
                $actualItem = $rsv->getItem( $data['original']['itemLocatorId'] );
                
                if( $flag ){
                    $pm = model_pagos();
                    $pm->setMontosPagados($data['itemId']);
                    okResponse('Monto editado', 'data', $actualItem['data'], $this, 'val', array(floatVal($data['original']['montoPagado']), floatVal($data['new']['montoParcial']), $reload, $devol));
                }else{
                    return true;
                }
            }else{
                errResponse('Error al editar monto', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
    }
    
    
    public function editMontoParcial_put(){
      
        tokenValidation12( function(){
            $data = $this->put();
            
            $this->editMontoParcial($data);
            

        });
    }
  
    public function testLog_get(){
        if( $this->db->from('Asesoress')->get() ){
            okResponse('Data obtenida','data', true, $this);
        }else{
            $flag = logError( "testLog", "constructor", $this->db->error(), "Obtener asesores", __LINE__, $this );
            errResponse('Prueba de logueo, error en db'.($flag ? ' Log guardado' : ' Error en log'), REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
    }
    
    public function checkOutV12_put(){
        
        tokenValidation12( function(){
            $data = $this->put();
            $userConfirm = getUrlParam( 'usn', 'undefined' );
            
            $sendConf = isset( $data['sendConf'] ) ? $data['sendConf'] : true;
            
            $msgZd = "";
            $msgCti = "";
            
            $pm = model_pagos();
            $depositos = array();
            
            // VALIDATE PAYMENT
            $pagoQ = $pm->searchPago( $data['account'], 'operacion');
            
            if( $pagoQ['err'] ){
                errResp( $pagoQ['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $pagoQ['error']);
            }
            $pago = $pagoQ['data'][0];
            
            if( $pago['montoSaldo'] != $data['balance'] ){
                errResp( "El pago seleccionado fue modificado mientras se realizaba lo operacion. Vuelve a buscar el pago para obtener la info actualizada. No se realizaron movimientos", REST_Controller::HTTP_BAD_REQUEST, 'error', $pago);
            }
            
            
            // GET ITEMS ACTUALES
            $mlid = $data['master'];
            
            $pm->manageItem( false, $mlid );
            $itq = $this->db->from("items")->get();
            $items = $itq->result_array();
            $compare = array();
            foreach( $items as $i => $itm ){
                $compare[$itm['itemLocatorId']] = $itm;
            }
            
            // VALIDACION DE ITEMS NO MODIFICADOS
            
            foreach( $data['items'] as $x => $itmp ){
                
                $resComp = array(
                        'montoPagado' => array( round( floatVal( $compare[$itmp['itemLocatorId']]['montoPagado'] ),2 ), $itmp['montoPagado'] ),
                        'montoEnValidacion' => array( round( floatVal( $compare[$itmp['itemLocatorId']]['montoEnValidacion'] ),2 ), $itmp['montoEnValidacion'] ),
                        'montoParcial' => array( round( floatVal( $compare[$itmp['itemLocatorId']]['montoParcial'] ),2 ), $itmp['montoParcialOriginal'] ),
                        'monto' => array( round( floatVal( $compare[$itmp['itemLocatorId']]['monto'] ),2 ), $itmp['monto'] ),
                    );
                if( 
                    round(floatVal($compare[$itmp['itemLocatorId']]['montoPagado']),2) != $itmp['montoPagado'] ||
                    round(floatVal($compare[$itmp['itemLocatorId']]['montoEnValidacion']),2) != $itmp['montoEnValidacion'] ||
                    round(floatVal($compare[$itmp['itemLocatorId']]['montoParcial']),2) != $itmp['montoParcialOriginal'] ||
                    round(floatVal($compare[$itmp['itemLocatorId']]['monto']),2) != $itmp['monto'] 
                ){
                    errResp( "El item ".$itmp['itemLocatorId']." fue modificado mientras se saldaba esta rsva. Actualiza para ver los cambios. No se realizo ninguna operacion", REST_Controller::HTTP_BAD_REQUEST, 'error', $resComp);
                }    
            }
            
            // Depositos nuevos
            $isValidated = $pago['tipo'] == 'Deposito' && $pago['isValidated'] == '0' ? 0 : 1;
            
            foreach( $data['items'] as $x => $info ){
                
                
                $info['montoEnValidacion'] = $info['montoEnValidacion'] ?? 0;
                
                $confId = 0;
                $cash = array(
                    "itemId" => $info['itemId'],
                    'accountId' => $data['account'],
                    'monto' => moneyVal($info['toPay']),
                    'paymentValid' => $isValidated,
                    'txType' => 'abono',
                    'monedaAplicada' => $info['monedaAplicada'] ?? $pago['moneda'],
                    'cambioAplicado' => $info['tc'] ?? 1
                );
                
                
                // APLICA TRANSACCION
                $pm = model_pagos();
                $cti = $pm->setTransaction(array($cash));
                
                    // ERROR DE TRANSACCION
                    if( $cti['err'] ){
                        errResp( $cti['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $cti['error']);
                    }
                    
                // ACTUALIZA PAGO
                $updatePago = $pm->updatePago( $data['account'], ( moneyVal($info['toPay']) / ($info['tc'] ?? 1) ));
                
                    // ERROR DE ACTUALIZACION DE PAGO
                    if( $updatePago['err'] ){
                        $msg = $info['itemLocatorId']." -> Error al modificar pago. Las transacciones ya fueron aplicadas";
                        $errArr = array(
                                "method"        => 'checkOutV12',
                                "step"          => 'Tabla de pago (res_ligasPago)',
                                "description"   => $msg,
                                "error"         => json_encode($updatePago['error']),
                                "userCaller"    => $userConfirm
                            );
                        
                        $this->db->set($errArr)->insert('errors_log');
                        errResp( $updatePago['msg']." La transaccion fue aplicada, pero el monto no fue descontado. Revisa con gerencia", REST_Controller::HTTP_BAD_REQUEST, 'error', $updatePago['error']);
                    }
                
                // ACTUALIZA TABLA DE MONTOS
                $monto = array(
                    'montoPagado' => $isValidated == 1 ? (moneyVal($info['toPay']) + moneyVal($info['montoPagado'])) : moneyVal($info['montoPagado']),
                    'montoEnValidacion' => $isValidated == 1 ? moneyVal($info['montoEnValidacion']) : (moneyVal($info['toPay']) + moneyVal($info['montoEnValidacion'])),
                    'montoParcial' => moneyVal($info['montoParcial']),
                    'isParcial' => $info['isParcial'],
                    'isPagoHotel' => $info['isPagoHotel']
                );
                

                // ACTUALIZACION DE MONTO
                
                if( !($this->db->set($monto)->where('itemId', $info['itemId'])->update('cycoasis_rsv.r_monto')) ){
                        
                    // ERROR EN ACTUALIZACION DE MONTO    
                    $msg = $info['itemLocatorId']." -> Error al modificar tabla de montos. Los pagos ya fueron aplicados";
                    $errArr = array(
                            "method"        => 'checkOutV12',
                            "step"          => 'Tabla de montos',
                            "description"   => $msg,
                            "error"         => json_encode($this->db->error()),
                            "userCaller"    => $userConfirm
                        );
                    
                    $this->db->set($errArr)->insert('errors_log');
                    
                    errResp( $msg, REST_Controller::HTTP_BAD_REQUEST, 'error', $this->db->error());
                }
                
                
                // NOTIFICACION A PROVEEDORES
                $flagNotify = false;
                
                if( $info['isPagoHotel'] == 1 ){ $flagNotify = true; }
                if( $info['isParcial'] && (moneyVal($monto['montoPagado']) >= moneyVal($info['montoParcial']) ) ){ $flagNotify = true; }
                if( moneyVal($monto['montoPagado']) >= moneyVal($info['monto']) ){ $flagNotify = true; }
                
                // PAGO EN HOTEL
                if( $info['isPagoHotel'] == 1 ){
                    $quote = false;
                    $cfble = false;
                
                // PREPAGOS  
                }else{
                    $nqQ = $this->db->query("SELECT IF(COUNT(IF(paymentValid=1,1,null))>0,0,1) as isQuoteValid, IF(COUNT(IF(paymentValid=0,1,null))>0,1,0) as isQuote, IF(COUNT(IF(paymentValid!=2,1,null))>0,1,0) as isConfirmable FROM cycoasis_rsv.p_cashTransaction WHERE itemId=".$info['itemId']." AND monto>0");
                    $nq = $nqQ->row_array();
                    
                    $quote = $nq['isQuote'] == '1';
                    
                    if( $flagNotify === false ){
                        $quote = $nq['isQuoteValid'] == '1';
                    }
                    $cfble = $nq['isConfirmable'] == '1';
                    
                }
                    
                // CAMBIA STATUS DE RSVA
                $quoteQ = $pm->setIsQuote( $info['itemId'], $quote, $cfble, false );
                    
                     // ERROR EN ACTUALIZACION DE STATUS DE RSVA    
                    if( $quoteQ['err'] ){
                        $msg = $info['itemLocatorId']." -> Error al modificar status (isQuote) de item. Los pagos fueron ya fueron aplicados";
                        $errArr = array(
                                "method"        => 'checkOutV12',
                                "step"          => 'Tabla de items (isQuote)',
                                "description"   => $msg,
                                "error"         => json_encode($quoteQ['error']),
                                "userCaller"    => $userConfirm
                            );
                        
                        $this->db->set($errArr)->insert('errors_log');
                        
                        errResp( $msg, REST_Controller::HTTP_BAD_REQUEST, 'error', $quoteQ['error']);
                    }
                
                
                if( $flagNotify === true || ($quote === false || $cfble === true) ){
                    $confId = $this->notifyProvider($info['itemId']);
                }
                    
                // AGREGA ARRAY PARA VALIDACION DE DEPOSITOS
                array_push($depositos, $info['itemId']);
                
                
                $msgZd .= " -> Monto por ".money_format('%.2n', $info['toPay'])." agregado al pago del item ".$info['itemLocatorId']." por $userConfirm";
                
                if( $isValidated == 0 ){
                    $msgZd .= " || monto en proceso de validacion.";
                }
                
                if( $confId != 0 ){
                    $msgZd .= "<br>Correo de solicitud de confirmación a proveedor enviado en ticket $confId";
                }
                
                $msgZd .= "<br>";
                $msgCti .= $cti['msg']."(".$info['itemLocatorId'].")<br>";
                
                // if($info['monto'] - (moneyVal($info['montoPagado']) + moneyVal($info['toPay'])) <= 0){
                //     $this->automaticConf($info, $mlTicket);
                // }
                
                $x++;
                
                // VALIDA DEPOSITO
                if($x >= count($data['items'])){
                    $validDepo = $pm->validateDeposit( implode(',',$depositos) );
                    
                    if( $sendConf === true ){
                        $this->fullConf($mlid, 3);
                    }
                    
                    $this->zd->saveHistory( array($mlid, 'masterlocatorid'), $msgCti ); 
                    $this->zd->saveHistory( array($mlid, 'masterlocatorid'), $msgZd ); 
                    
                    okResp('Items Saldados', 'data', true);
                }

            }
            
            
            
            okResp('Items obtenidos', 'data', $items, 'original', $data['items']);
            
            
        });
        
    }
  
  public function checkOut_put(){
      
     $result = tokenValidation12( function(){

        $data = $this->put();
        $items = count($data);
        $x=0;
        $mlid = '';
        
        $depositos = array();
        
        
        foreach( $data as $index => $info ){
            
            $flagError = false;
            $flagErrorMonto = false;
            
            $vdQ = $this->db->query("SELECT tipo, isValidated, moneda FROM res_ligasPago WHERE operacion = '".$info['account']."'");
            $vd = $vdQ->row_array();
            
            $info['montoEnValidacion'] = isset($info['montoEnValidacion']) ? moneyVal($info['montoEnValidacion']) : 0;
            
            $isValidated = $vd['tipo'] == 'Deposito' && $vd['isValidated'] == '0' ? 0 : 1;
            
            $confId = 0;
            $mlid = $info['masterLocatorId'];
            $cash = array(
                    "itemId" => $info['itemId'],
                    'accountId' => $info['account'],
                    'monto' => moneyVal($info['toPay']),
                    'paymentValid' => $isValidated,
                    'txType' => 'abono',
                    'monedaAplicada' => $vd['moneda']
                );
                
            $monto = array(
                    'montoPagado' => $isValidated == 1 ? (moneyVal($info['toPay']) + moneyVal($info['montoPagado'])) : moneyVal($info['montoPagado']),
                    'montoEnValidacion' => $isValidated == 1 ? moneyVal($info['montoEnValidacion']) : (moneyVal($info['toPay']) + moneyVal($info['montoEnValidacion'])),
                    'montoParcial' => moneyVal($info['montoParcial']),
                    'isParcial' => $info['isParcial'],
                    'isPagoHotel' => $info['isPagoHotel']
                );
            
            // Insertar en transacciones
            $ctxs = array();
            
            $pm = model_pagos();
            $cti = $pm->setTransaction(array($cash));
            
            array_push($ctxs, $cti);
            
            if( !$cti['err'] ){
                if( $this->db->set($monto)->where('itemId', $info['itemId'])->update('cycoasis_rsv.r_monto') ){
                    
                    $this->db->query("UPDATE res_ligasPago SET montoUsado = montoUsado + ".$info['toPay'].", montoSaldo=monto-montoUsado WHERE operacion = '".$info['account']."'");
                    
                    if( $info['isPagoHotel'] == 1 || ($info['isParcial'] && moneyVal($monto['montoPagado']) >= moneyVal($info['montoParcial'])) || moneyVal($monto['montoPagado']) >= moneyVal($info['monto'])  ){
                        
                        if( $info['isPagoHotel'] == 1 ){
                            
                            if( !$this->db->where('itemId', $info['itemId'])->set(array('isQuote'=>0))->update('cycoasis_rsv.r_items') ){
                                $flagError = true;
                                logError( "checkOut_put", "isQuote Update", $this->db->error(), 'Error al establecer cotización ('.$info['itemId'].') como reserva', __LINE__, $this );
                            }else{
                                 $confId = $this->notifyProvider($info['itemId']);
                            }
    
                        }else{
                            $nqQ = $this->db->query("SELECT IF(COUNT(IF(paymentValid=0,1,null))>0,1,0) as isQuote, IF(COUNT(IF(paymentValid!=2,1,null))>0,1,0) as isConfirmable FROM cycoasis_rsv.p_cashTransaction WHERE itemId=".$info['itemId']." AND monto>0");
                            $nq = $nqQ->row_array();
                            
                            if( !$this->db->where('itemId', $info['itemId'])->set(array('isQuote'=>$nq['isQuote'], 'isConfirmable' => $nq['isConfirmable']))->update('cycoasis_rsv.r_items') ){
                                $flagError = true;
                                logError( "checkOut_put", "isQuote, isConfirmable Update", $this->db->error(), 'Error al establecer cotización ('.$info['itemId'].') como reserva', __LINE__, $this );
                            }else{

                                 $confId = $this->notifyProvider($info['itemId']);

                            }
                            
                            
                        }
                        
                    }else{
                        $nqQ = $this->db->query("SELECT IF(COUNT(IF(paymentValid=1,1,null))>0,0,1) as isQuote, IF(COUNT(IF(paymentValid!=2,1,null))>0,1,0) as isConfirmable FROM cycoasis_rsv.p_cashTransaction WHERE itemId=".$info['itemId']." AND monto>0");
                        $nq = $nqQ->row_array();
                        
                        if( !$this->db->where('itemId', $info['itemId'])->set(array('isConfirmable' => $nq['isConfirmable']))->update('cycoasis_rsv.r_items') ){
                            $flagError = true;
                            logError( "checkOut_put", "isConfirmable Update", $this->db->error(), 'Error al establecer cotización ('.$info['itemId'].') como reserva', __LINE__, $this );
                        }else{
                            if( $nq['isConfirmable'] == 1 || $nq['isQuote'] == 0 ){
                                $confId = $this->notifyProvider($info['itemId']);
                            }
                        }
                        
                        
                    }
                    
                    array_push($depositos, $info['itemId']);
                    
                    
                    $mlTicket = $info['mlTicket'];
                    $mlItem = $info['itemLocatorId'];
                    $msg = "Monto por ".money_format('%.2n', $info['toPay'])." agregado al pago del item $mlItem por ".$_GET['usn'];
                    
                    if( $isValidated == 0 ){
                        $msg .= " || monto en proceso de validacion.";
                    }
                    
                    if( $confId != 0 ){
                        $msg .= "<br>Correo de solicitud de confirmación a proveedor enviado en ticket $confId";
                    }
                    
                    if( $flagError ){
                        $msg .= "<br><br><span class='color: red'>Hubieron errores en el checkout, revisa logs en base de datos</span>";
                    }
                    
                    $this->zd->saveHistory( $mlTicket, $cti['msg'] ); 
                    $this->zd->saveHistory( $mlTicket, $msg ); 
                    
                    // if($info['monto'] - (moneyVal($info['montoPagado']) + moneyVal($info['toPay'])) <= 0){
                    //     $this->automaticConf($info, $mlTicket);
                    // }

                    
                }else{
                    $flagErrorMonto = true;
                    logError( "checkOut_put", "Montos Update", $this->db->error(), 'Error al modificar tabla de montos ('.$info['itemId'].')', __LINE__, $this );
                }
            }else{
                $flagErrorMonto = true;
                logError( "checkOut_put", "Cash Transactions Insert", $cti['errores'], 'Error al generar la transaccion ('.$info['itemId'].')', __LINE__, $this );
            } 
                

            $x++;
            if($x >= $items){
                $validDepo = $pm->validateDeposit( implode(',',$depositos) );
                $this->fullConf($mlid, 3);
                okResponse('Items Saldados '.($flagError || $flagErrorMonto ? ' Hubieron errores, revisar logs e historial' : ''), 'data', true, $this, 'txs', $ctxs);
            }
        }
        
        

      });
  }

  
  public function automaticConf($i, $t){
      
       switch( $i['itemType'] ){
            case "2":
            case "9":
                $dtCreated = new DateTime();
        
                $this->db->where('itemId', $i['itemId']);
                
                $this->db->set(array('confirm' => 'SYS-'.$i['itemLocatorId'], 'userConfirm' => 'Confirmado automáticamente por el sistema', 'dtConfirm' => $dtCreated->format('Y-m-d H:i:s')));
                $msg = "**** Item: ".$i['itemLocatorId']." confirmado con la clave SYS-".$i['itemLocatorId']." por el sistema ****";
                
                if( $this->db->update('cycoasis_rsv.r_items') ){
                    if( $this->zd->saveHistory( $t, $msg ) ){
                        
                        if($i['monto'] - (moneyVal($i['montoPagado']) + moneyVal($i['toPay'])) <= 0){
                            
                            if( !isset($i['concertEvent']) ){
                                $i['concertEvent'] = null;
                            }
                            
                            switch($i['concertEvent']){
                                case 'diBlassio2019':
                                    $confTicket = $this->cliente_Diblassio($i);
                                    $msg = "Confirmación de item ".$i['itemLocatorId']." enviada en ticket $confTicket";
                                    $this->zd->saveHistory( $t, $msg );
                                    break;
                                case 'nyeGoc2019':
                                    $confTicket = $this->cliente_gala2019($i);
                                    $msg = "Confirmación de item ".$i['itemLocatorId']." enviada en ticket $confTicket";
                                    $this->zd->saveHistory( $t, $msg );
                                    break;
                            }
                        }
                        
                        
                        return true;
                    }else{
                      return false;
                    }
                }else{
                    return false;
                }
                
                return true;
            default:
                return false;
        }
      
  }
  
  public function cancelItem_put(){
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

        $d = $this->put();
        $data = $d['data'];
        $f = $d['flag'];
        $dtCreated = new DateTime();
        
        $fq = $this->db->query("SELECT rsv_cancelMaster FROM userDB a LEFT JOIN cat_profiles b ON a.profile=b.id WHERE asesor_id=".$_GET['usid']);
        $fr = $fq->row_array();
        
        $flag = $fr['rsv_cancelMaster'] == '1';
        
        if( !$f ){
            
            if( $data['isQuote'] == 0 ){
                errResponse('Este item ya no es cotización, su status es de reserva. Solicita a tu gerente la cancelación', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
            }else{
               if( $data['isConfirmable'] == 1 ){
                    errResponse('Este item posiblemente cuenta con pagos aplicados o en validación. Solicita a tu gerente la cancelación', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                } 
            }
            
            if( moneyVal($data['montoPagado']) != 0 ){
                errResponse('Este item ya cuenta con pagos realizados, solicita a tu gerente la cancelación', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
            }
            
            if($this->db->where("itemId", $data['itemId'])->set(array('isCancel' => 1, 'isOpen' => 0, 'isQuote' => 0, 'userCancel' => $_GET['usid'], 'dtCancel' => $dtCreated->format('Y-m-d H:i:s')))->update('cycoasis_rsv.r_items') ){
                if($this->db->where("itemId", $data['itemId'])->set(array('monto' => 0, 'montoParcial' => 0, 'montoPagado' => 0))->update('cycoasis_rsv.r_monto') ){
                    $mlTicket = $data['mlTicket'];
                    $mlItem = $data['itemLocatorId'];
                    $msg = "Item $mlItem cancelado por ".$_GET['usn'].", No se realizaron movimientos monetarios ya que sólo era cotización";
                    $this->zd->saveHistory( $mlTicket, $msg ); 
                }else{
                    errResponse('Error cancelar montos del Item', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
                errResponse('Error cancelar Item', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
                        
            okResponse('Item Cancelado', 'data', true, $this);
        }else{
            
            if( $accsQ = $this->db->select('a.*, operacion as cuenta, monedaAplicada')->from('cycoasis_rsv.p_cashTransaction a')->join('res_ligasPago b', 'a.accountId = b.operacion', 'left')->where('itemId', $data['itemId'])->get() ){
                $accounts = array();
                foreach( $accsQ->result_array() as $index => $ct ){
                    $cash = array(
                            "itemId" => $ct['itemId'],
                            'accountId' => $ct['accountId'],
                            'monto' => moneyVal($ct['monto'])*(-1),
                            'userCreated' => $_GET['usid'],
                            'monedaAplicada' => $ct['monedaAplicada']
                        );
                        
                    $pm = model_pagos();
                    $cti = $pm->setTransaction(array($cash));
                    
                    array_push($ctxs, $cti);
                    
                    if( $cti['err'] ){
                        errResponse('Error al crear transacciones de devolución', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $cti);
                    }
                    
                    if( isset($accounts[$ct['accountId']]) ){
                        $accounts[$ct['accountId']]['monto'] += moneyVal($ct['monto']); 
                    }else{
                        $accounts[$ct['accountId']] = array('operacion' => $ct['cuenta'], 'monto' => moneyVal($ct['monto'])); 
                    }
                }
                
                
                 if($this->db->where("itemId", $data['itemId'])->set(array('isCancel' => 1, 'isQuote' => 0, 'userCancel' => $_GET['usid'], 'dtCancel' => $dtCreated->format('Y-m-d H:i:s')))->update('cycoasis_rsv.r_items') ){
                    if($this->db->where("itemId", $data['itemId'])->set(array('monto' => 0, 'montoParcial' => 0, 'montoPagado' => 0, 'montoEnValidacion' => 0))->update('cycoasis_rsv.r_monto') ){
                        $acTxt = "";
                        foreach($accounts as $acId => $acInf ){
                            $this->db->query("UPDATE res_ligasPago SET montoUsado = montoUsado - ".moneyVal($acInf['monto']).", montoSaldo = monto - montoUsado  WHERE operacion = '$acId'");
                            $acTxt .= " ".$acInf['operacion'].": -$".moneyVal($acInf['monto']).".";
                        }
                        
                        $mlTicket = $data['mlTicket'];
                        $mlItem = $data['itemLocatorId'];
                        $msg = "Item $mlItem cancelado por ".$_GET['usn'].". Los montos devueltos son: $acTxt";
                        $this->zd->saveHistory( $mlTicket, $msg ); 
                        
                        $rsv = model_rsv();
                        $rsv->manageItem(true, $data['itemLocatorId']);
                        $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
                        $data = $itemDataQ->row_array();
                        
                        if($this->fullConf($data['masterLocatorId'], 3, true)){
                            okResponse('Item Cancelado', 'data', true, $this);
                        }
                    }else{
                        errResponse('Error cancelar montos del Item', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }
                }else{
                    errResponse('Error cancelar Item', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
                

            }else{
                errResponse('No fue posible obtener la información de las trasacciones', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
        }
        
      });
  }
  
  

  public function getConfirmList_put(){
      
    //   errResponse('Modulo en mantenimiento', REST_Controller::HTTP_BAD_REQUEST, $this, 'error',array());
      
     $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
         
         $data = $this->put();

        $crA = $this->db->query("SELECT rsv_extranetAll FROM userDB a LEFT JOIN cat_profiles b ON a.profile=b.id WHERE asesor_id=".$_GET['usid']);
        $crR = $crA->row_array();
        $pvrQ = $this->db->select('provider')->from('cycoasis_rsv.usr_providers')->where(array('agentId'=>$_GET['usid'],'activo'=>1))->get();
        
        if( !$data['activas'] ){
            $w = "AND isCancel = 1 AND confirmCancel IS NOT NULL";
        }else{
            $w = "AND isCancel = 0 AND confirm IS NOT NULL";
        }
        
         if( isset($data['dtArrivalFlag']) && $data['dtArrivalFlag'] ){
            $w .= " AND
                    CASE 
            			WHEN itemType=4 THEN t.fecha BETWEEN '".$data['arrival_inicio']."' AND '".$data['arrival_fin']."'
            			WHEN itemType=3 THEN xt.inicio BETWEEN '".$data['arrival_inicio']."' AND '".$data['arrival_fin']."'
            			WHEN itemType=5 THEN x.fecha_in BETWEEN '".$data['arrival_inicio']."' AND '".$data['arrival_fin']."'
            		END";
            // $w .= " having llegada BETWEEN '".$data['arrival_inicio']."' AND '".$data['arrival_fin']."'";
        }
        
        // Fecha de salida
        if( isset($data['dtCreatedFlag']) && $data['dtCreatedFlag'] ){
             $w .= " AND
                    CASE 
            			WHEN itemType=4 THEN t.fecha BETWEEN '".$data['arrival_inicio']."' AND '".$data['arrival_fin']."'
            			WHEN itemType=3 THEN xt.fin BETWEEN '".$data['arrival_inicio']."' AND '".$data['arrival_fin']."'
            			WHEN itemType=5 THEN x.fecha_out BETWEEN '".$data['arrival_inicio']."' AND '".$data['arrival_fin']."'
            		END ";
            // $w .= " having salida BETWEEN '".$data['created_inicio']."' AND '".$data['created_fin']."'";
        }
        
        // Sin Fechas
        if( !(isset($data['dtCreatedFlag']) && $data['dtCreatedFlag']) && !(isset($data['dtArrivalFlag']) && $data['dtArrivalFlag']) ){
            $w .= " AND
                    CASE 
            			WHEN itemType=4 THEN t.fecha BETWEEN ADDDATE(LAST_DAY(CURDATE() - INTERVAL 1 MONTH),1) AND LAST_DAY(CURDATE())
            			WHEN itemType=3 THEN xt.inicio BETWEEN ADDDATE(LAST_DAY(CURDATE() - INTERVAL 1 MONTH),1) AND LAST_DAY(CURDATE())
            			WHEN itemType=5 THEN x.fecha_in BETWEEN ADDDATE(LAST_DAY(CURDATE() - INTERVAL 1 MONTH),1) AND LAST_DAY(CURDATE())
            		END";
            // $w .= " having llegada BETWEEN ADDDATE(LAST_DAY(CURDATE() - INTERVAL 1 MONTH),1) AND LAST_DAY(CURDATE())";
        }
        
        

        // $itQ = $this->manageQueries('item', true, 0, $w);
        
        // $lQ = $this->db->select("itemId, itemLocatorId, dtCreated, proveedor, tipoServicio, categoria, grupo, IF(isCancel=1,confirmCancel,confirm) as confirmation")
        //                 ->select("nombreCliente, llegada, salida, hotel, dtPickUpIn, dtPickUpOut, monto, moneda, creador")
        //                 ->select("IF(isCancel=1,userConfirmCancel,userConfirm) as confirmUser, IF(isCancel=1,dtConfirmCancel,dtConfirm) as confirmDate, Last_Update as dtCreated")
        //                 ->from("items")
        //                 ->order_by('itemLocatorId');
                        
        // if( $crR['rsv_extranetAll'] != '1' ){
        //     $pr = array();
        //     foreach($pvrQ->result_array() as $index => $p){
        //         array_push($pr, $p['provider']);
        //     }
        //     $this->db->where_in('proveedor', $pr);
        // }
        
        // if( $q = $this->db->get() ){
        if( $q = $this->db->query("SELECT 
                                        a.itemId,
                                        itemLocatorId,
                                        IF(isCancel = 0, dtNowIsRsv, dtCancel) AS dtCreated,
                                        title AS tipoServicio, m.grupo,
                                        CASE 
                                    		WHEN itemType = 3 THEN CONCAT(cxt.titulo, ', oficina: ',xt.rqChar01)
                                    		WHEN itemType = 4 THEN ct.titulo
                                    		WHEN itemType = 5 THEN CONCAT(cx.vehiculo,if(isShared=1,' (compartido)',' (privado)'),' ',cx.xferType)
                                    	END as categoria,
                                        IF(isCancel=1,confirmCancel,confirm) as confirmation,
                                        CASE 
                                			WHEN itemType=4 THEN t.fecha
                                			WHEN itemType=3 THEN xt.inicio
                                			WHEN itemType=5 THEN x.fecha_in
                                		END as llegada,
                                		CASE 
                                			WHEN itemType=4 THEN t.fecha
                                			WHEN itemType=3 THEN xt.fin
                                			WHEN itemType=5 THEN x.fecha_out
                                		END as salida,
                                		COALESCE(nombreCliente,'TBA') as nombreCliente, COALESCE(userConfirm,'') as confirmUser, IF(isCancel=0, confirm, confirmCancel) as confirmation, monto, dtConfirm as confirmDate, IF(itemType IN (4,5), COALESCE(dtPickUpOut,COALESCE(dtPickUpIn,'PENDIENTE')),'') as dtPickUpIn, NOMBREASESOR(a.userCreated,2) as creador
                                    FROM
                                        cycoasis_rsv.r_items a
                                            LEFT JOIN
                                        cycoasis_rsv.servicios b ON a.itemType = b.id
                                            LEFT JOIN
                                        cycoasis_rsv.r_xfer x ON a.itemId = x.itemId
                                            LEFT JOIN
                                        cycoasis_rsv.r_xtras xt ON a.itemId = xt.itemId
                                            LEFT JOIN
                                        cycoasis_rsv.r_tour t ON a.itemId = t.itemId
                                    		LEFT JOIN
                                        cycoasis_rsv.cat_xfers cx ON x.xferId = cx.id
                                            LEFT JOIN
                                        cycoasis_rsv.cat_autos cxt ON xt.serviceId = cxt.id
                                            LEFT JOIN
                                        cycoasis_rsv.cat_tours ct ON t.tourId = ct.id
                                    		LEFT JOIN
                                    	cycoasis_rsv.r_monto m ON a.itemId=m.itemId
                                    		LEFT JOIN
                                    	cycoasis_rsv.r_masterlocators ml ON a.masterlocatorid=ml.masterlocatorid
                                    WHERE
                                        isQuote = 0
                                            AND itemType NOT IN (1 , 2) $w ORDER BY itemLocatorId") ){
            
            okResponse('Servicios Obtenidos', 'data', $q->result_array(), $this);
        }else{
            errResponse('No tienes los permisos necesarios para ver esta reservación. Para más información comunícate con el Contact Center', REST_Controller::HTTP_BAD_REQUEST, $this, 'error',array());
        }
                        
        
        
      });
  }
  
  public function getServices_get(){
      $this->db->from('cycoasis_rsv.servicios')->where('active',1)->order_by('order');
      
      if( $q = $this->db->get() ){
          okResponse('Servicios Obtenidos', 'data', $q->result_array(), $this);
        }else{
            errResponse('Error al insertar pago', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
  }
  
  public function th_get(){
      $tkt = $this->uri->segment(3);
       $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$tkt.'/audits.json';
        $response = getUrlContent( $url );
        
        $audits = array();
        foreach($response['data']->{'audits'} as $aud => $a){
            foreach($a->{'events'} as $index => $data){
                if( $data->{'type'} == 'Comment' ){
                    $cun_time = new DateTimeZone('America/Bogota');
                    $dtCreated = new DateTime($a->{'created_at'});
                    $dtCreated->setTimezone($cun_time);
                    $matches = array();
                    preg_match('/ticket ([0-9]+)[\s]*/', $data->{'body'}, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<a href='https://oasishoteles.zendesk.com/agent/tickets/".$matches[1]."' target='_blank'>".$matches[0]."</a>",  $data->{'body'});
                    }else{
                        $msg = $data->{'body'};
                    }
                    array_push($audits, array('Fecha' => $dtCreated->format('Y-m-d H:i:s'), 'msg' => $msg));
                }
            }
        }
      


        okResponse('Datos Obtenidos', 'data', $audits, $this );
  }
  
  public function getRsvHistory_get(){
      $zdId = $this->uri->segment(3);
       
      if( $q = $this->db->query("SELECT 
                                    CONCAT(a.masterlocatorid,' [',
                                    GROUP_CONCAT(DISTINCT c.title),']') AS st,
                                    a.masterlocatorid as ml,
                                    SUM(IF(isCancel = 0 AND isQuote = 0,1,0)) as r,
                                    SUM(IF(isCancel = 0 AND isQuote = 1 AND NOW()<vigencia,1,0)) as q,
                                    SUM(IF(isCancel = 0 AND isQuote = 1 AND NOW()>=vigencia,1,0)) as exp,
                                    SUM(IF(isCancel = 1,1,0)) as xld,
                                    CASE
                                        WHEN SUM(IF(isCancel = 0 AND isQuote = 0,1,0)) >= 1 THEN 'R'
                                        ELSE
                                            CASE
                                                WHEN SUM(IF(isCancel = 0 AND isQuote = 1 AND NOW()<vigencia,1,0)) >= 1 THEN 'Q'
                                                ELSE
                                                CASE
                                                    WHEN SUM(IF(isCancel = 0 AND isQuote = 1 AND NOW()>=vigencia,1,0)) >= 1 THEN 'Exp'
                                                    ELSE 'X'
                                                END
                                            END
                                    END as st,
                                    GROUP_CONCAT(DISTINCT c.title) as services
                                FROM
                                    cycoasis_rsv.r_masterlocators a
                                        LEFT JOIN
                                    cycoasis_rsv.r_items b ON a.masterlocatorid = b.masterlocatorid
                                        LEFT JOIN
                                    cycoasis_rsv.servicios c ON b.itemType = c.id
                                WHERE
                                    zdUserId = $zdId
                                GROUP BY a.masterlocatorid") ){
        okResponse('Datos Obtenidos', 'data', $q->result_array(), $this );  
      }else{
         errResponse('Error al obtener información', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
      }
      


        
  }
  
  public function getHistoryTest_get(){
      $tkt = $this->uri->segment(3);
       $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$tkt.'/audits.json';
        $response = getUrlContent( $url );
        okResponse('Datos Obtenidos', 'data', $response, $this );  
  }
  
  public function getHistory_get(){
      $tkt = $this->uri->segment(3);
       $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$tkt.'/audits.json';
        $response = getUrlContent( $url );
        $arr = json_decode(json_encode($response['data']->{'audits'},true,JSON_UNESCAPED_UNICODE),true);
        $rArr = $arr;
        
        while($response['data']->{'next_page'} != null){
            $response = getUrlContent( $response['data']->{'next_page'} );
            $arr = json_decode(json_encode($response['data']->{'audits'},true,JSON_UNESCAPED_UNICODE),true);
            $rArr = array_merge($rArr,$arr);
        }

        $audits = array();
        foreach($rArr as $aud => $a){
            foreach($a['events'] as $index => $data){
                if( $data['type'] == 'Comment' ){
                    $cun_time = new DateTimeZone('America/Bogota');
                    $dtCreated = new DateTime($a['created_at']);
                    $dtCreated->setTimezone($cun_time);
                    
                    // green item
                    $matches = array();
                    preg_match('/([iI+]tem:* [0-9]+\-[0-9]+)[\s]*/', $data['body'], $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<span class='text-success'>".$matches[1]."</span> ",  $data['body']);
                    }else{
                        $msg = $data['body'];
                    }
                    
                    // green only item
                    $matches = array();
                    preg_match('/[\s]([0-9]{6}\-[0-9]+)[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[1],"<span class='text-success'>".$matches[1]."</span>",  $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // XLD item
                    $matches = array();
                    preg_match('/([cC+]ancelad[aoAO+])[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<span class='text-danger'><b>".$matches[1]."</b></span> ",  $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Reembolso item
                    $matches = array();
                    preg_match('/([rR+]eembols[^\s*]*)[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<span class='text-danger'><b>".$matches[1]."</b></span> ",  $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Confirm item
                    $matches = array();
                    preg_match('/[^e]\s([cC+]onfirm[^\s*]*)[\s][^e]/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[1],"<span class='text-info'><b>".$matches[1]."</b></span> ",  $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Link http
                    $matches = array();
                    preg_match('/http([^\s]+)/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<a href='http".$matches[1]."' target='_blank'>".$matches[0]."</a>",  $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Link tickets
                    $matches = array();
                    preg_match('/ticket ([0-9]+)[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<a href='https://oasishoteles.zendesk.com/agent/tickets/".$matches[1]."' target='_blank'>".$matches[0]."</a>", $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Link tkt
                    $matches = array();
                    preg_match('/tkt ([0-9]+)[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<a href='https://oasishoteles.zendesk.com/agent/tickets/".$matches[1]."' target='_blank'>".$matches[0]."</a>", $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Link tk
                    $matches = array();
                    preg_match('/tk ([0-9]+)[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<a href='https://oasishoteles.zendesk.com/agent/tickets/".$matches[1]."' target='_blank'>".$matches[0]."</a>", $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Link tkt #
                    $matches = array();
                    preg_match('/tkt \#([0-9]+)[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<a href='https://oasishoteles.zendesk.com/agent/tickets/".$matches[1]."' target='_blank'>".$matches[0]."</a>", $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Link tk #
                    $matches = array();
                    preg_match('/tk \#([0-9]+)[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<a href='https://oasishoteles.zendesk.com/agent/tickets/".$matches[1]."' target='_blank'>".$matches[0]."</a>", $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    // Link ticket #
                    $matches = array();
                    preg_match('/ticket \#([0-9]+)[\s]*/', $msg, $matches);
                    if( count($matches) > 0 ){
                        $msg = str_replace($matches[0],"<a href='https://oasishoteles.zendesk.com/agent/tickets/".$matches[1]."' target='_blank'>".$matches[0]."</a>", $msg);
                    }else{
                        $msg = $msg;
                    }
                    
                    
                    array_push($audits, array('Fecha' => $dtCreated->format('Y-m-d H:i:s'), 'msg' => $msg));
                }
            }
        }
      


        okResponse('Datos Obtenidos', 'data', $audits, $this );
  }

    public function notifyProvider( $d ){
        
      
        $w = "it.itemId = $d";
        
        $rsv = model_rsv();
        $rsv->manageItem(true, 0, $w);
        
        // $itQ = $this->manageQueries('item', true, 0, $w);
        $itQQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
        $it = $itQQ->row_array();
        
        $pm = model_pagos();
        
        
        // ASSISTCARD
        if( $it['itemType'] == '10' || $it['itemType'] == '15' ){
            $asc = model_assistcard();
            $assist = $asc->emit($it);
            $pm->validateDeposit( $it['itemId'] );
            return 0;
        }
        
        // VCM XFER
        if( $it['itemType'] == '11' ){
            
            $this->load->model('Vcm_model');
            $vcm = new Vcm_model;
            $result = $vcm->emitTraslado( $it );
            $pm->validateDeposit( $it['itemId'] );
            return 0;
        }
        
        if( $it['proveedor'] == null ){
            return 0;
        }
        
        
        $mq = $this->db->from('cycoasis_rsv.cat_providersMailList')->where(array('providerId'=>$it['proveedor'],'activo'=>1))->order_by('main')->get();
        $mo = $mq->result_array();
        $cc = array();
        
        foreach( $mo as $index => $m ){
            if( $m['main'] == 1 ){
                $main = $m['zdId'];
            }else{
                array_push($cc, array('user_id'=>$m['zdId'], 'action'=>'put'));
            }
        }
        
        $wa = false;
        
        switch( $it['itemType'] ){
            case "2":
                return $this->notifyDayPass($main, $cc, $it);
                break;
            case "3":
            case "4":
            case "5":
                if( $it['grupo'] == 'packSB' ){
                    return 0;
                }
                return $this->notifyVcm($main, $cc, $it);
                break;
            default:
                return 0;
        }


    }
  
    public function notifyProvider_get(){
        $item = $this->uri->segment(3);
        $id = $this->notifyProvider( $item );
        okResponse('Correo Enviado', 'ticket', $id, $this);
    }
    
    public function notifyConfirm( $d ){
        
        // $d['client'];
        // $d['loc'];
        // $d['hotel'];
        // $d['cc'];
        // $d['msg'];
        
        $hotel = array(
                "pyr" => "The Pyramid at Grand Oasis",
                "PYR" => "The Pyramid at Grand Oasis",
                "olite" => "Oasis Cancun Lite",
                "OLITE" => "Oasis Cancun Lite",
                "goc" => "Grand Oasis Cancun",
                "GOC" => "Grand Oasis Cancun",
                "opb" => "Oasis Palm",
                "OPB" => "Oasis Palm",
                "gop" => "Grand Oasis Palm",
                "GOP" => "Grand Oasis Palm",
                "gsc" => "Grand Sens Cancun",
                "GSC" => "Grand Sens Cancun",
                "smart" => "Oasis Smart",
                "SMART" => "Oasis Smart",
                "oh" => "Oh! The Urban by Oasis",
                "OH" => "Oh! The Urban by Oasis",
            );
        
        $newTicket = array("ticket" => array("subject" => "Confirmación ".$hotel[$d['hotel']]." - ".$d['loc'], 
                "requester_id" => $d['client'],
                "submitter_id" => 360005313512,
                "group_id" => 360005313512,
                "assignee_id" => $_GET['zdId'],
                "comment" => array("body" => "Confirmación automática desde CyC", "public" => false, "author_id" => 360005313512)));

        $tkt = json_encode($newTicket);
        $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
        $id = $response['data']->{'ticket'}->{'id'};
        $tags = array('confirmacion', $d['loc']);
        
        switch( $d['hotel'] ){
            case 'pyr':
                $msg = tmpl_pyrConf($d);
                break;
        }
        
        $cc = array();
        
        if( isset($d['cc']) ){
            foreach( $d['cc'] as $index => $m ){
                array_push($cc, array('user_email'=>$m, 'action'=>'put'));
            }
        }
        
        $editTkt = array("ticket" => array(
                "tags" => $tags,
                "submitter_id" => 360005313512, 
                "assignee_id" => 360005313512,
                "email_ccs" => $cc,
                "status" => "solved",
                "comment" => array("html_body" => $msg, "public"=> true, "author_id" => 360005313512)));
        $tkt = json_encode($editTkt);
        
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
        $responseOk = getUrlContent( $url, true, true, $tkt);

        return $id;

    }
    
    public function notifyConfirm_put(){
        $data = $this->put();
        $tkt = $this->notifyConfirm($data);
        okResponse('Correo enviado', 'ticket', $tkt, $this);
    }
  
    public function notifyConfirmDP_put(){
        $data = $this->put();
        $tkt = $this->notifyDayPass($data);
        okResponse('Correo enviado', 'ticket', $tkt, $this);
    }
  
    public function notifyVcm( $main, $cc, $it ){
        $newTicket = array("ticket" => array("subject" => "Nueva Reserva ".$it['itemLocatorId']." - Contact Center Oasis Hoteles / VCM", 
                "requester_id" => $main,
                "submitter_id" => 360005313512,
                "group_id" => 360005313512,
                "assignee_id" => 360005313512,
                "comment" => array("body" => "Notificación de nueva reserva para confirmar generada automáticamente por CyC", "public" => false, "author_id" => 360005313512)));

        $tkt = json_encode($newTicket);
        $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
        $id = $response['data']->{'ticket'}->{'id'};
        $tags = array('confirming', $it['itemLocatorId']);
        
        $msg = confirmingNotify( $it['itemLocatorId'], $it['tipoServicio'], $it['categoria']);
        
        $editTkt = array("ticket" => array(
                "tags" => $tags,
                "requester_id" => $main,
                "submitter_id" => 360005313512, 
                "email_ccs" => $cc,
                "status" => "solved",
                "comment" => array("html_body" => $msg, "public"=> true, "author_id" => 360005313512)));
                
        if( isset($_GET['zdId']) ){
            $editTkt['ticket']['assignee_id'] = $_GET['zdId'];
        }         
                
        $tkt = json_encode($editTkt);
        
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
        $responseOk = getUrlContent( $url, true, true, $tkt);

        return $id;
    }
    
    public function notifyDayPass( $main, $cc, $d ){

        $newTicket = array("ticket" => array("subject" => "Nuevo Daypass vendido desde Contact Center - ".$d['itemLocatorId'], 
                "requester_id" => $main,
                "submitter_id" => 360005313512,
                "group_id" => 360005313512,
                "assignee_id" => 360005313512,
                "comment" => array("body" => "Nuevo Daypass vendido desde Contact Center - ".$d['itemLocatorId'], "public" => false, "author_id" => 360005313512)));

        $tkt = json_encode($newTicket);
        $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
        $id = $response['data']->{'ticket'}->{'id'};
        $tags = array('confirming', $d['itemLocatorId']);
        
        $msg = tmpl_dpConf($d);
        
        $editTkt = array("ticket" => array(
                "tags" => $tags,
                "submitter_id" => 360005313512, 
                "assignee_id" => $_GET['zdId'],
                "email_ccs" => $cc,
                "status" => "solved",
                "comment" => array("html_body" => $msg, "public"=> true, "author_id" => 360005313512)));
        $tkt = json_encode($editTkt);
        
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
        $responseOk = getUrlContent( $url, true, true, $tkt);

        return $id;

    }
    
    public function setFree_put(){
        tokenValidation12( function(){
        
            $data = $this->put();
            $info = $data['item'];
            $x=0;
        
            $confId = 0;
            $cash = array(
                    "itemId" => $info['itemId'],
                    'accountId' => 0,
                    'monto' => $info['monto'],
                    'userCreated' => $_GET['usid'],
                    'txType' => 'cortesia',
                    'monedaAplicada' => 'MXN'
                );
            $monto = array(
                    'montoPagado' => $info['monto'],
                    'montoParcial' => $info['montoParcial'],
                    'isParcial' => 0,
                    'isPagoHotel' => 0
                );
                
            $pm = model_pagos();
            
            
            // VALIDA STATUS ANTES DE MARCARLA COMO CORTESIA
            $status = $pm->getStatus( $info['itemId'] );
            
            if( !$status['err'] ){
                if( $status['data']['isCancel'] == '1' ){
                    errResponse('La reserva se encuentra cancelada, no es posible marcarla como cortesia', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $status['data']);
                }
            }
            
            $ctxs = array();
            $cti = $pm->setTransaction(array($cash));
            
            array_push($ctxs, $cti);
            
            if( !$cti['err'] ){
                if( $this->db->set($monto)->where('itemId', $info['itemId'])->update('cycoasis_rsv.r_monto') ){
                    
                    if( !$this->db->where('itemId', $info['itemId'])->set(array('isQuote'=>0, 'isFree' => 1, 'cieloRelates' => $data['relates']))->update('cycoasis_rsv.r_items') ){
                        errResponse('Error al establecer cotización ('.$info['itemId'].') como reserva', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }else{
                         $confId = $this->notifyProvider($info['itemId']);
                    }

                    $mlTicket = $info['mlTicket'];
                    $mlItem = $info['itemLocatorId'];
                    $msg = "Item $mlItem marcado como \"cortesía\" y se ha relacionado con las reservas de CIELO: ".$data['relates']." por ".$_GET['usn'];
                    $this->zd->saveHistory( $mlTicket, $msg ); 
                    
                    if( $confId != 0 ){
                        $msg = "Correo de solicitud de confirmación a proveedor enviado en ticket $confId";
                        $this->zd->saveHistory( $mlTicket, $msg );
                    }
                    
                    $confTicket = $this->fullConf($info['masterLocatorId'], 3, false, false, true);
                    
                }else{
                    errResponse('Error al modificar tabla de montos ('.$info['itemId'].')', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }   
            }else{
                errResponse('Error al generar la transaccion ('.$info['itemId'].')', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $cti);
            }    
        
            $x++;
        
            okResponse('Items Saldados', 'data', true, $this);
        
        });
    }
    
    public function cliente_gala2019( $d, $isCancel = false ){
        
        $ttl = $isCancel ? 'Cancelación' : 'Confirmación';
        
        $newTicket = array("ticket" => array("subject" => "$ttl Cena de Gala NYE 2019 - ".$d['itemLocatorId'], 
                "requester_id" => $d['clientZd'],
                "submitter_id" => 360005313512,
                "group_id" => 360005313512,
                "assignee_id" => 360005313512,
                "comment" => array("body" => "$ttl de compra - ".$d['itemLocatorId'], "public" => false, "author_id" => 360005313512)));

        $tkt = json_encode($newTicket);
        $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
        $id = $response['data']->{'ticket'}->{'id'};
        $tags = array('confirmacion', $d['itemLocatorId']);
        
        $msg = tmpl_gala2019($d, !$isCancel);
        
        $cc = array();

        $editTkt = array("ticket" => array(
                "tags" => $tags,
                "submitter_id" => 360005313512, 
                "assignee_id" => $d['creatorZdId'],
                "email_ccs" => $cc,
                "status" => "solved",
                "comment" => array("html_body" => $msg, "public"=> true, "author_id" => 360005313512)));
        $tkt = json_encode($editTkt);
        
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
        $responseOk = getUrlContent( $url, true, true, $tkt);

        return $id;

    }
    
    public function cliente_Diblassio( $d, $isCancel = false ){
        
        $ttl = $isCancel ? 'Cancelación' : 'Confirmación';
        
        $newTicket = array("ticket" => array("subject" => "$ttl Evento Raul DiBlassio - ".$d['itemLocatorId'], 
                "requester_id" => $d['clientZd'],
                "submitter_id" => 360005313512,
                "group_id" => 360005313512,
                "assignee_id" => 360005313512,
                "comment" => array("body" => "$ttl de compra - ".$d['itemLocatorId'], "public" => false, "author_id" => 360005313512)));

        $tkt = json_encode($newTicket);
        $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
        $id = $response['data']->{'ticket'}->{'id'};
        $tags = array('confirmacion', $d['itemLocatorId']);
        
        $msg = tmpl_diBlassio($d, !$isCancel);
        
        $cc = array();
        array_push($cc, array('user_email'=>'oasisplusoh@oasishoteles.com', 'action'=>'put'));
        
        $editTkt = array("ticket" => array(
                "tags" => $tags,
                "submitter_id" => 360005313512, 
                "assignee_id" => $d['creatorZdId'],
                "email_ccs" => $cc,
                "status" => "solved",
                "comment" => array("html_body" => $msg, "public"=> true, "author_id" => 360005313512)));
        $tkt = json_encode($editTkt);
        
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
        $responseOk = getUrlContent( $url, true, true, $tkt);

        return $id;

    }
    
    public function getHtmlConf_get(){
        
        $id = $this->uri->segment(3);
        
        if( $itQ = $this->manageQueries('item', true, $id, "it.itemId = '".$id."'") ){
          
          if( $dQ = $this->db->from('items')->get() ){
              $d = $dQ->row_array();
              $msg = printConf($this, $d, true);
              echo $msg;
          }
      }else{
         errResponse('Hubo un error al enviar la confirmación al cliente. No se pudo obtener info del item. Asegúrate de hacérsela llegar de manera manual', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array()); 
      }
    }
    
    
    
    public function sendConf($d, $l=true, $r = false, $pickup = false, $cortesia = false){
        
        if( gettype($d) != "array" ){
            if( $itQ = $this->manageQueries('item', true, $d, "it.itemId = '".$d."'") ){
          
              if( $dQ = $this->db->from('items')->get() ){
                  $d = $dQ->row_array();
              }
          }else{
             return false;
          }
        }
        
        switch( strtolower($d['Servicio']) ){
            case 'hotel':
            case 'daypass':
            case 'tours':
            case 'autos':
                continue;
                break;
            case 'traslados':
                if($d['grupo'] == 'packSB'){
                    return false;
                }
                continue;
                break;
            default:
                return false;
                
        }
        
        $ttl = 'Confirmación';
        
        $newTicket = array("ticket" => array("subject" => "$ttl Reserva Oasis Hotels & Resorts - ".$d['itemLocatorId'], 
                "requester_id" => $d['clientZd'],
                "submitter_id" => 360005313512,
                "group_id" => 360005682532,
                "assignee_id" => $d['creatorZdId'],
                "comment" => array("body" => "$ttl de compra - ".$d['itemLocatorId']." (Creador id: ".$d['creatorZdId'].")", "public" => false, "author_id" => 360005313512)));
        
        if( $d['ccMail'] && $d['ccMail'] != '' ){
            $newTicket['ticket']['email_ccs'] = array(array('user_email'=>$d['ccMail'], 'action'=>'put'));
        }

        $tkt = json_encode($newTicket);
        $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
        $id = $response['data']->{'ticket'}->{'id'};
        $tags = array('confirmacion', $d['itemLocatorId']);
        
        $msg = printConf($this, $d, $l);
        
        if($msg == ''){
            return false;
        }
        
        $editTkt = array("ticket" => array(
                "tags" => $tags,
                "submitter_id" => 360005313512, 
                "status" => "solved",
                "comment" => array("html_body" => $msg, "public"=> true, "author_id" => 360005313512)));
        $tkt = json_encode($editTkt);
        
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
        $responseOk = getUrlContent( $url, true, true, $tkt);

        if( !$r ){
            if( $pickup ){
                $msg = "Envío de confirmación y pickup de item ".$d['itemLocatorId']." enviada en ticket $id. Pickup confirmado por ".$_GET['usn'];
                $this->zd->saveHistory( $d['mlTicket'], $msg );
            }else{
                if( $cortesia ){
                    $msg = "Envío de confirmación (cortesía) de item ".$d['itemLocatorId']." enviada en ticket $id";
                    $this->zd->saveHistory( $d['mlTicket'], $msg );
                }else{
                    $msg = "Confirmación de item ".$d['itemLocatorId']." enviada en ticket $id";
                    $this->zd->saveHistory( $d['mlTicket'], $msg );
                }
            }
        }else{
            $idioma = $d['idioma'] == 'idioma_es' ? 'español' : 'inglés';
            $msg = "Reenvío de confirmación de item ".$d['itemLocatorId']." enviada en idioma $idioma en ticket $id";
            $this->zd->saveHistory( $d['mlTicket'], $msg );
            okResponse('Confirmación enviada', 'msg', $msg, $this);
            return true;
        }
        
        return $msg;
    }
    
    public function sendConf_get(){
        
        $loc = $this->uri->segment(3);
        $lang = $this->uri->segment(4);
        
        // okResponse('Confirmación enviada', 'msg', $loc, $this);
        
        if( isset($lang) ){
            $l = $lang == 1 ? $lang : 3;
        }else{
            $l = 3;
        }
        
        $itQ = $this->manageQueries('item', true, $loc);
        $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
        $data = $itemDataQ->row_array();
        
        $confTicket = $this->sendConf($data, $l, true);
        
    }
    
    public function itemPaymentsV2_get(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
        
            $item = $this->uri->segment(3);
            
            $this->itemPaymentsV2($item);
        });
        
    }
    
    public function itemPayments_get(){
         $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
        
            $item = $this->uri->segment(3);
            
            $this->db->select('SUM(a.monto) as monto, accountId, proveedor, complejo, aut, montoUsado, montoSaldo')->from('cycoasis_rsv.p_cashTransaction a')
                ->join('res_ligasPago b', 'a.accountId = b.operacion', 'left')->order_by('tipoPago');
                
            if( $q = $this->db->get() ){
                    okResponse('Pagos recibidos', 'data', $q->result_array(), $this);

            }else{
                errResponse('Hubo un error al momento de validar los pagos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
         });
    }
    
    private function itemPaymentsV2($item, $flag = true){
        
            $this->db->select('SUM(a.monto) as monto, SUM(a.monto / cambioAplicado) as montoTc, SUM(a.monto) as montoOK, accountId, proveedor, complejo, aut, montoUsado, montoSaldo, montoReembolso, montoReembolsado, paymentValid, cieloTxId, cashTransactionId, monedaAplicada, cambioAplicado')
            ->from('cycoasis_rsv.p_cashTransaction a')
            ->join('res_ligasPago b', 'a.accountId = b.operacion', 'left')->where('itemId',$item)->having('montoOK > 0',null, FALSE)->group_by('accountId');
                
            if( $q = $this->db->get() ){
                if($flag){
                    okResponse('Pagos recibidos', 'data', $q->result_array(), $this);
                }else{
                    return $q->result_array();
                }
            }else{
                errResponse('Hubo un error al momento de validar los pagos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }

    }
    
    public function reactivate_put(){
        tokenValidation12( function(){
            
            $data = $this->put();
        
            $rsv = model_rsv();
            
            $result = $rsv->reactivateExpired($data['itemId']);
            
            if( !$result['err'] ){
                okResp( $result['msg'], 'data', $result['data']);
            }else{
                errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }
        });
    }
    
    public function reactivateTest_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            $data = $this->put();
            
            $query = "SELECT 
                        IF(SUM(COALESCE(blackout,0))>0,1,0) as bckout
                    FROM
                        cat_Fechas a
                            LEFT JOIN
                        cycoasis_rsv.r_hoteles b ON a.Fecha BETWEEN inicio AND ADDDATE(fin, - 1)
                    		LEFT JOIN 
                    	t_blackout c ON a.Fecha=c.Fecha AND b.hotel=c.hotel
                    WHERE
                        itemId = ".$data['itemId'];
                        
            $qbckOUT = $this->db->query($query);
            $qbck = $qbckOUT->row_array();
            
            if( $qbck['bckout'] == 1 ){
                errResponse('No se puede reactivar debido a que las fechas de la cotización ya no cuentan con disponibilidad', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
            }
        
            if( $q = $this->db->query("UPDATE cycoasis_rsv.r_items SET vigencia = ADDTIME(NOW(),'24:00:00') WHERE itemId=".$data['itemId']) ){
                $msg = "Item ".$data['itemLocatorId']." reactivado por ".$_GET['usn'];
                $this->zd->saveHistory( $data['mlTicket'], $msg );
                okResponse('Reserva Reactivada', 'data', true, $this);
            }else{
                errResponse('Hubo un error al momento de validar los pagos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        });
    }
    
    public function getLocsCielo_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            $data = $this->put();
            
            $this->db->select('*')
                    ->from('t_reservations');
                    
            foreach( $data as $f => $r ){
                $this->db->where_in($f,$r);
            }
        
            if( $q = $this->db->get() ){
                okResponse('Reservas Obtenidas', 'data', $q->result_array(), $this);
            }else{
                errResponse('Hubo un error al obtener reservas', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        });
    }
    
    public function setOpen12_put(){
        // tokenValidation12( function(){
          
            $data = $this->put();
            
            $rsv = model_rsv();
            
            $result = $rsv->setToOpenDates($data['itemId']);
            
            if( !$result['err'] ){
                okResp( $result['msg'], 'data', $result['data']);
            }else{
                errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }

        // });
    }

    public function setOpen_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            $data = $this->put();
            $update = array();
            
            switch( intVal($data['tipo']) ){
                case 1:
                    $db = 'cycoasis_rsv.r_hoteles';
                    $update = $data['dates'];
                    break;
                case 2:
                    $db = 'cycoasis_rsv.r_daypass';
                    $update['fecha'] = $data['dates']['inicio'];
                    break;
                case 3:
                case 9:
                    $db = 'cycoasis_rsv.r_xtras';
                    $update['inicio'] = $data['dates']['inicio'];
                    break;
                case 4:
                    $update['fecha'] = $data['dates']['inicio'];
                    $db = 'cycoasis_rsv.r_tour';
                    break;
                case 5:
                    $update['fecha_in'] = $data['dates']['inicio'];
                    $update['fecha_out'] = $data['dates']['salida'];
                    $db = 'cycoasis_rsv.r_xfer';
                    break;
                    
            }
        
            if( $this->db->set($update)->where(array( 'itemId' => $data['itemId'] ))->update($db) ){
                if( $this->db->set(array('isOpen' => 1, 'limite_od' => $data['limit'], 'travelLimit' => $data['limitTravel']))->where(array( 'itemId' => $data['itemId'] ))->update('cycoasis_rsv.r_items') ){
                    $this->zd->saveHistory( $data['mlTicket'], "Item ".$data['item']." se deja como Fechas Abiertas con fecha máxima: ".$data['dates']['inicio']." || Límite para establecer fechas: ".$data['limit'].". Por ".$_GET['usn'] );
                    
                    $itQ = $this->manageQueries('item', true, $data['item']);
                    $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
                    $data = $itemDataQ->row_array();
                    
                    // if($confTicket = $this->sendConf($data, 3, true)){
                    if( $this->fullConf($data['masterLocatorId'], 3, true)){
                        okResponse('Fecha Abierta Exitosa', 'data', true, $this);
                    }
                }else{
                    errResponse('Hubo un error al establecer como abierta', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
                errResponse('Hubo un error al mover fechas', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        });
    }

    public function setDatesForOpen_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            $data = $this->put();
            $update = array();
            
            switch( intVal($data['tipo']) ){
                case 1:
                    $db = 'cycoasis_rsv.r_hoteles';
                    $update = $data['dates'];
                    break;
                case 2:
                    $db = 'cycoasis_rsv.r_daypass';
                    $update['fecha'] = $data['dates']['inicio'];
                    break;
                case 3:
                case 9:
                    $db = 'cycoasis_rsv.r_xtras';
                    $update['inicio'] = $data['dates']['inicio'];
                    break;
                case 4:
                    $update['fecha'] = $data['dates']['inicio'];
                    $db = 'cycoasis_rsv.r_tour';
                    break;
                case 5:
                    $update['fecha_in'] = $data['dates']['inicio'];
                    $update['fecha_out'] = $data['dates']['salida'];
                    $db = 'cycoasis_rsv.r_xfer';
                    break;
                    
            }
        
            if( $this->db->set($update)->where(array( 'itemId' => $data['itemId'] ))->update($db) ){
                if( $this->db->set(array('isOpen' => 0, 'limite_od' => null, 'notasResetOd' => isset($data['notas']) ? $data['notas'] : ''))->where(array( 'itemId' => $data['itemId'] ))->update('cycoasis_rsv.r_items') ){
                    $this->zd->saveHistory( $data['mlTicket'], "Item ".$data['item'].": Nuevas Fechas establecidas: ".$data['dates']['inicio']." || ticket ".$data['ticket'].". Por ".$_GET['usn'] );
                    
                    $itQ = $this->manageQueries('item', true, $data['item']);
                    $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
                    $data = $itemDataQ->row_array();
                    
                    // if($confTicket = $this->sendConf($data, 3, true)){
                    if( $this->fullConf($data['masterLocatorId'])){
                        okResponse('Set de Fecha Abierta Exitosa', 'data', true, $this);
                    }
                    
                }else{
                    errResponse('Hubo un error al establecer como abierta', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
                errResponse('Hubo un error al mover fechas', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        });
    }
    
    public function setChanges_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            $data = $this->put();
            
            if( $this->db->set($data['newData'])->where(array( 'itemId' => $data['itemId'] ))->update('cycoasis_rsv.r_hoteles') ){
                $this->zd->saveHistory( $data['mlTicket'], "Item ".$data['item'].": ".$data['msg'].$_GET['usn'] );

                    okResponse('Cambios Aplicados', 'data', true, $this);

            }else{
                errResponse('Hubo un error al aplicar los cambios', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        });
    }
    
    public function editCCMail_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            $data = $this->put();
            
            if( $this->db->set(array('cc' => $data['cc']))->where(array( 'masterlocatorid' => $data['ml'] ))->update('cycoasis_rsv.r_masterlocators') ){
                
                if( $data['cc'] == null || $data['cc'] == '' ){
                    $msg = "Se eliminó la información de CC para copias de confirmaciones";
                }else{
                    $msg = "Se agregó el correo ".$data['cc']." para copias de confirmaciones";
                }
               
                $this->zd->saveHistory( $data['ticket'], $msg." || Actualización por ".$_GET['usn'] );

                okResponse($msg, 'data', true, $this);

            }else{
                errResponse('Hubo un error al actualizar la información', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        });
    }
    
    public function resetConfirmation_put(){
        tokenValidation12( function(){
            
            $data = $this->put();
            
            // Parametro itemId obtenido
            if( !isset($data['itemId']) ){
                errResp('No se encontro el parametro itemId', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
            }
            
            $rsv = model_rsv();
            
            $result = $rsv->resetConfirm( $data['itemId'] );
            
            if( !$result['err'] ){
                okResp( $result['msg'], 'data', $result['data']);
            }else{
                errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error'] );
            }
        });
    }
    
    public function testModel(){
        return "Prueba correcta";
    }
    
    public function getXldPolicy_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            $data = $this->put();
            
            if( !isset($data['item']['htlNoches']) ){
                $data['item']['htlNoches'] = 1;
            }
            
            $monto = moneyVal(moneyVal($data['item']['montoPagado']) + moneyVal($data['item']['montoEnValidacion'])) + moneyVal(moneyVal($data['item']['pkgItems']['totalValidacion'] ?? 0) + moneyVal($data['item']['pkgItems']['totalPagado'] ?? 0));
            
            $this->db->select("inicioTW,
                        finTW,
                        policyDays,
                        IF(ADDDATE(CURDATE(), policyDays) < '".$data['item']['llegada']."',
                            penalty_2,
                            penalty_1) AS penalty,
                        IF(ADDDATE(CURDATE(), policyDays) < '".$data['item']['llegada']."',
                            isNights_2,
                            isNights_1) AS isNights,
                            isBusy, hs,
                            '".$monto."' as montoPagado,
                        IF(CURDATE()>'".$data['item']['llegada']."',DATEDIFF(CURDATE(),'".$data['item']['llegada']."'),0) as nochesDormidas")
                ->select("ROUND(".$monto."*.1,2) as minimumPenalty", FALSE)
                ->from('cat_politicaXld')
                ->where("isBusy=IF(CURDATE()>'".$data['item']['llegada']."',1,0)","", FALSE)
                ->where("'".$data['item']['llegada']."' BETWEEN inicioTW AND finTW","", FALSE);
                
            $query = $this->db->get_compiled_select();
            
            if( $q = $this->db->query( $query ) ){
                $qr = $q->row_array();
                
                if( $data['item']['itemType'] != 1 ){
                    $qr['penalty'] = 1;
                    $qr['isNights'] = 0;
                }
                
                $qr['minimumPenalty'] = moneyVal( $qr['minimumPenalty'] );
                $qr['penalty'] = intVal( $qr['penalty'] ?? 0 );
                $qr['nochesDormidas'] = moneyVal( $qr['nochesDormidas'] ?? 0);
                
                $qr['montoPagado'] = $monto;
                
                if($data['item']['isNR'] == 1){
                 $qr['penalidad'] = $monto;
                 $qr['penalidadTotal'] = moneyVal($data['item']['monto']) + moneyVal($data['item']['pkgItems']['totalValue']);
                }else{
                    if($qr['isNights'] ?? 0 == 1){
                        
                        if( $data['item']['htlNoches'] < ($qr['nochesDormidas'] + $qr['penalty']) ){
                            $qr['penaltyTotal'] = $data['item']['htlNoches'];
                        }else{
                            $qr['penaltyTotal'] = $qr['nochesDormidas'] + $qr['penalty'];
                        }

                        
                        $qr['penalidadTotal'] = moneyVal($data['item']['monto'] / $data['item']['htlNoches'] * $qr['penaltyTotal']) + (moneyVal($data['item']['monto'] / $data['item']['htlNoches'] * $qr['penaltyTotal']) > 0 ? moneyVal($data['item']['pkgItems']['totalValue']) : 0);
                    }else{
                        $qr['penalidadTotal'] = moneyVal($data['item']['monto'] * ($qr['penalty'])) + ( moneyVal($data['item']['monto'] * ($qr['penalty'])) > 0 ? moneyVal($data['item']['pkgItems']['totalValue']) : 0 );
                        $qr['penaltyTotal'] = $qr['penalty'];
                    }
                    
                    if( $qr['penalidadTotal'] > $monto ){
                        $qr['penalidad'] = $monto;
                    }else{
                        $qr['penalidad'] = $qr['penalidadTotal'];
                    }
                }
                
                okResponse('Penalidades Obtenidas', 'data', $qr, $this,"monto", $monto);

            }else{
                errResponse('Hubo un error al obtener la información', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        });
        
    }
    
    public function verConfirmacionTst_get(){
        
        $ml = $this->uri->segment(3);
        $mail = $this->uri->segment(4);

        $this->tst_fullConf($ml, true, false, false, false, urldecode($mail));
        
    }
     
    public function tst_fullConf($loc, $l=true, $r = false, $pickup = false, $cortesia = false, $print = ''){
        $mlQ = $this->manageQueries('ml', false, $loc);
        $this->manageQueries('item', false, $loc);
                    
        if( $mlDataQ = $this->db->query($mlQ) ){
            if( $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId") ){
                if( $sumQ = $this->db->query("SELECT min(llegada) as llegada, GROUP_CONCAT(DISTINCT grupo) as gposTfas, GROUP_CONCAT(DISTINCT tipoPago) as tiposPago, if(MAX(llegada)>MAX(salida),MAX(llegada),MAX(salida)) as sallida FROM items") ){
                    $sum = $sumQ->row_array();
                    $master =  $mlDataQ->row_array();
                    $master['llegada'] = $sum['llegada'];
                    $master['grupos'] = $sum['gposTfas'];
                    $master['tiposPago'] = $sum['tiposPago'];
                    $master['test'] = 100;
                    $mlQ = $this->db->query("SELECT historyTicket FROM cycoasis_rsv.r_masterlocators WHERE masterlocatorid = $loc");
                    $mlR = $mlQ->row_array();
                    $master['mlTicket'] = $mlR['historyTicket'];
                    
                    // echo json_encode($master);
                    // return;
                    
                    $this->db->select("itemLocatorId,
                            c.monto,
                            c.dtCreated AS fechaCobro,
                            proveedor,
                            complejo,
                            IF(c.accountId='0','cortesía',operacion) as operacion,
                            aut,
                            afiliacion,
                            tarjeta,
                            p.monto AS montoTx,
                            p.moneda")
                            ->from("cycoasis_rsv.r_items i")
                            ->join("cycoasis_rsv.p_cashTransaction c", "i.itemId = c.itemId", "left")
                            ->join("res_ligasPago p", "c.accountId = p.operacion", "left")
                            ->where('masterlocatorid',$loc)
                            ->where('c.itemId IS NOT',' NULL', FALSE);
                
                    if( $pm = $this->db->get() ){
                        $master['payments'] = $pm->result_array();
                    }
                    
                    // okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $itemDataQ->result_array()), $this);
                    
                    // $html = v2_printConf_tst($this, array('master' => $master, 'items' => $itemDataQ->result_array()), 3, $print);
                    
                    if( $print != '' ){
                        return true;
                    }
                    
                    $ttl = 'Confirmación';
        
                    $newTicket = array("ticket" => array("subject" => "$ttl Reserva Oasis Hotels & Resorts - ".$master['masterlocatorid'], 
                            "requester_id" => $master['zdUserId'],
                            "submitter_id" => 360005313512,
                            "group_id" => 360005682532,
                            "assignee_id" => $master['zdCreated'],
                            "comment" => array("body" => "$ttl de compra - ".$master['masterlocatorid']." (Creador id: ".$master['zdCreated'].")", "public" => false, "author_id" => 360005313512)));
                    
                    if( $master['cc'] && $master['cc'] != '' ){
                        $newTicket['ticket']['email_ccs'] = array(array('user_email'=>$master['cc'], 'action'=>'put'));
                    }
            
                    $tkt = json_encode($newTicket);
                    $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
                    $id = $response['data']->{'ticket'}->{'id'};
                    $tags = array('confirmacion', $master['masterlocatorid']);

                    if($html == ''){
                        return false;
                    }
                    
                    $editTkt = array("ticket" => array(
                            "tags" => $tags,
                            "submitter_id" => 360005313512, 
                            "status" => "solved",
                            "comment" => array("html_body" => $html, "public"=> true, "author_id" => 360005313512)));
                    $tkt = json_encode($editTkt);
                    
                    $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
                    $responseOk = getUrlContent( $url, true, true, $tkt);
            
                    if( !$r ){
                        if( $pickup ){
                            $msg = "Envio de confirmación y pickup enviada en ticket $id. Pickup confirmado por ".$_GET['usn'];
                            $this->zd->saveHistory( $master['historyTicket'], $msg );
                        }else{
                            if( $cortesia ){
                                $msg = "Envío de confirmacion de traslado en cortesia enviada en ticket $id";
                                $this->zd->saveHistory( $master['historyTicket'], $msg );
                            }else{
                                $msg = "Confirmacion enviada en ticket $id";
                                $this->zd->saveHistory( $master['historyTicket'], $msg );
                            }
                        }
                        // okResponse('Confirmación enviada', 'msg', $msg, $this);
                        // return true;
                    }else{
                        $idioma = $master['idioma'] == 'idioma_es' ? 'español' : ($master['idioma'] == 'idioma_pt' ? 'portugues' : 'inglés');
                        $msg = "Reenvio de confirmacion enviada en idioma $idioma en ticket $id";
                        $this->zd->saveHistory( $master['historyTicket'], $msg );
                        okResponse('Confirmación enviada', 'msg', $msg, $this);
                        return true;
                    }
                    
                    return $msg;
                }else{
                    errResponse('Error al obtener el resumen '.__LINE__, REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
                errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }else{
            errResponse('Error al obtener el masterlocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
    }
    
    public function testConfSend_get(){
        
            $rsv = model_rsv();
            $mlQ = $rsv->manageMaster(148619);
            $mlDataQ = $this->db->query($mlQ);
            $master =  $mlDataQ->row_array();
            
            okResp('Master', 'data', $master);
    }
    

    public function fullConf($loc, $l=true, $r = false, $pickup = false, $cortesia = false, $print = '', $noResp = false){
        
        $mlQ = $this->manageQueries('ml', false, $loc);
        
        $rsv = model_rsv();
        $rsv->manageItem(false, $loc);
                    
        if( $mlDataQ = $this->db->query($mlQ) ){
            if( $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId") ){
                if( $sumQ = $this->db->query("SELECT min(llegada) as llegada, GROUP_CONCAT(DISTINCT grupo) as gposTfas, GROUP_CONCAT(DISTINCT tipoPago) as tiposPago, if(MAX(llegada)>MAX(salida),MAX(llegada),MAX(salida)) as sallida FROM items") ){
                    $sum = $sumQ->row_array();
                    $master =  $mlDataQ->row_array();
                    $master['llegada'] = $sum['llegada'];
                    $master['grupos'] = $sum['gposTfas'];
                    $master['tiposPago'] = $sum['tiposPago'];
                    $master['test'] = 100;
                    $mlQ = $this->db->query("SELECT historyTicket FROM cycoasis_rsv.r_masterlocators WHERE masterlocatorid = $loc");
                    $mlR = $mlQ->row_array();
                    $master['mlTicket'] = $mlR['historyTicket'];
                    
                    // echo json_encode($master);
                    // return;
                    
                    $this->db->select("itemLocatorId,
                            c.monto,
                            c.dtCreated AS fechaCobro,
                            proveedor,
                            complejo,
                            IF(c.accountId='0','cortesía',operacion) as operacion,
                            aut,
                            afiliacion,
                            tarjeta,
                            p.monto AS montoTx,
                            p.moneda")
                            ->from("cycoasis_rsv.r_items i")
                            ->join("cycoasis_rsv.p_cashTransaction c", "i.itemId = c.itemId", "left")
                            ->join("res_ligasPago p", "c.accountId = p.operacion", "left")
                            ->where('masterlocatorid',$loc)
                            ->where('c.itemId IS NOT',' NULL', FALSE);
                
                    if( $pm = $this->db->get() ){
                        $master['payments'] = $pm->result_array();
                    }
                    
                    // okResponse('Reserva Obtenida', 'data', array('master' => $master, 'items' => $itemDataQ->result_array()), $this);
                    
                    $html = v2_printConf($this, array('master' => $master, 'items' => $itemDataQ->result_array()), 3, $print);
                    
                    if( $print != '' ){
                        return true;
                    }
                    
                    $ttl = 'Confirmación';
        
                    $newTicket = array("ticket" => array("subject" => "$ttl Reserva Oasis Hotels & Resorts - ".$master['masterlocatorid'], 
                            "requester_id" => $master['zdUserId'],
                            "submitter_id" => 360005313512,
                            "group_id" => 360005682532,
                            "assignee_id" => $master['zdCreated'],
                            "comment" => array("body" => "$ttl de compra - ".$master['masterlocatorid']." (Creador id: ".$master['zdCreated'].")", "public" => false, "author_id" => 360005313512)));
                    
                    if( $master['cc'] && $master['cc'] != '' ){
                        $newTicket['ticket']['email_ccs'] = array(array('user_email'=>$master['cc'], 'action'=>'put'));
                    }
            
                    $tkt = json_encode($newTicket);
                    $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
                    
                    if( isset($response['data']->{'ticket'}) ){
                        
                        $id = $response['data']->{'ticket'}->{'id'};
                        $tags = array('confirmacion', $master['masterlocatorid']);
    
                        if($html == ''){
                            return false;
                        }
                        
                        $editTkt = array("ticket" => array(
                                "tags" => $tags,
                                "submitter_id" => 360005313512, 
                                "status" => "solved",
                                "comment" => array("html_body" => $html, "public"=> true, "author_id" => 360005313512)));
                        $tkt = json_encode($editTkt);
                        
                        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
                        $responseOk = getUrlContent( $url, true, true, $tkt);
                
                        if( !$r ){
                            if( $pickup ){
                                $msg = "Envio de confirmación y pickup enviada en ticket $id. Pickup confirmado por ".$_GET['usn'];
                                $this->zd->saveHistory( $master['historyTicket'], $msg );
                            }else{
                                if( $cortesia ){
                                    $msg = "Envío de confirmacion de traslado en cortesia enviada en ticket $id";
                                    $this->zd->saveHistory( $master['historyTicket'], $msg );
                                }else{
                                    $msg = "Confirmacion enviada en ticket $id";
                                    $this->zd->saveHistory( $master['historyTicket'], $msg );
                                }
                            }
                            // okResponse('Confirmación enviada', 'msg', $msg, $this);
                            // return true;
                        }else{
                            $idioma = $master['idioma'] == 'idioma_es' ? 'español' : ($master['idioma'] == 'idioma_pt' ? 'portugues' : 'inglés');
                            $msg = "Reenvio de confirmacion enviada en idioma $idioma en ticket $id";
                            $this->zd->saveHistory( $master['historyTicket'], $msg );
                            
                            if( !$noResp ){
                                okResponse('Confirmación enviada', 'msg', $msg, $this);
                            }else{
                                return true;
                            }
                        }
                        
                        return $msg;
                    }
                    
                    return "No se pudo crear ticket de confirmacion para el cliente. Revisa que el usuario de ZD exista";
                }else{
                    errResponse('Error al obtener el resumen '.__LINE__, REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
                errResponse('Error al obtener el items', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }else{
            errResponse('Error al obtener el masterlocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
    }
    
    function resetBlacklist_put(){
        tokenValidation12( function(){
            
            $data = $this->put();
            $rsv = model_rsv();
            
            $result = $rsv->blacklist( $data['ml'], $data['zdId'], false );
            
            if( !$result['err'] ){
                okResp($result['msg'], 'data', $result['data'] );
            }else{
                errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }
        });
    }
    
    function addBlacklist_put(){
        tokenValidation12( function(){
            
            $data = $this->put();
            $rsv = model_rsv();
            
            $result = $rsv->blacklist( $data['ml'], $data['zdId'], true );
            
            if( !$result['err'] ){
                okResp($result['msg'], 'data', $result['data'] );
            }else{
                errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }
        });
    }
    
    function quotePayment_get(){
        
        $ml = $this->uri->segment(3);
        $itemL = $this->uri->segment(4);
        $itemH = $this->uri->segment(5);
        
        $rsv = model_rsv();
        $rsv->manageItem(true, $itemL);
        if( $itemDataQ = $this->db->query("SELECT a.*, IF(moneda='USD',afiliacionUSD,afiliacionMXN) as afiliacionAlter, complejoOk as cuentaSantenderAlter, ADDDATE(CURDATE(),2) as vigencia FROM items a LEFT JOIN cat_complejo b ON b.hotel='$itemH'") ){
            $ir = $itemDataQ->row_array();
            
            $db = array(
                    "afiliacion" => $ir['cuentaSantander'] == 'VCM' ? $ir['afiliacionAlter'] : $ir['afiliacion'],
                    "cuenta" => $ir['cuentaSantander'] == 'VCM' ? $ir['cuentaSantenderAlter'] : $ir['cuentaSantander'],
                    "itemId" => $ir['itemId'],
                    "moneda" => $ir['moneda'],
                    "monto" => floatVal($ir['montoSaldoPrepago']),
                    "promo" => $ir['promo'],
                    "reference" => $ml."-".date("U"),
                    "vigencia" => $ir['vigencia'],
                );
            
            $link = array(
                    "amount" => floatVal($ir['montoSaldoPrepago']),
                    "fh_vigencia" => date("d/m/Y", strtotime($ir['vigencia'])),
                    "mail_cliente" => $ir['mailCreador'],
                    "moneda" => $ir['moneda'],
                    "omitir_notif_default" => 0,
                    "promociones" => $ir['promo'],
                    "reference" => $ml."-".date("U"),
                    "st_correo" => 0,
                );
                
            $alinksQ = $this->db->query("SELECT DISTINCT reference FROM cycoasis_rsv.r_pLinks WHERE itemId=".$ir['itemId']." AND active=1");
            $alinks = $alinksQ->result_array();
            
            if( $alinksQ->num_rows() > 0 ){
                
                $arefs = array();
                foreach($alinks as $i => $ref){
                    array_push($arefs, $ref['reference']);
                }
                $this->db->where_in('reference', $arefs)->set('active',0)->update('cycoasis_rsv.r_pLinks');
            }
            
            
           okResponse('Reserva Obtenida', 'data', array("item"=>$ir, "db" => $db, "link" => $link), $this); 
        }else{
            errResponse('Error al obtener el resumen '.__LINE__, REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
        }
        
        
        
        
    }
    
    function hasTransfer_put(){
        tokenValidation12( function(){
            $data = $this->put();
            
            $rsv = model_rsv();
            
            $result = $rsv->xferCortesia( $data['loc'], $data['flag'] );
            
            if( !$result['err'] ){
                okResp( $result['msg'], 'data', $result['data']);
            }else{
                errRes( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
            }
            
            
        });
    }
    
    function mlExist_get(){
        $mlQ = $this->db->select('masterLocatorId')->where('masterLocatorId', $this->uri->segment(3))->from('cycoasis_rsv.r_items')->get();
        if( $mlQ->num_rows() > 0 ){
            okResponse('MasterLocator', 'data', true, $this);
        }else{
            errResponse('No existen reservas con esta confirmacion', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
        }
    }
    
    function searchMlByConf_get(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            
            $this->db->select('masterLocatorId')->like('confirm', $this->uri->segment(3))->order_by('isCancel')->limit(1)->from('cycoasis_rsv.r_items');
            
            if( $mlQ = $this->db->get() ){
                
                $mlR = $mlQ->row_array();
                $ml = $mlR['masterLocatorId'];
                
                if( $ml != null ){
                    okResponse('MasterLocator', 'data', $ml, $this);
                }else{
                    errResponse('No existen reservas con esta confirmacion', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }
                
            }else{
                errResponse('Error en la busqueda', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
            
        });
    }
    
    function saveBeFreeCert_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
            $data = $this->put();
            
            $t_res = array(
                    'rp_char03' => $data['item']['itemLocatorId'],
                    'rp_num04' => 1
                );
                
            if( $this->db->set($t_res)->where('id', $data['cert']['id'])->update('t_reservations') ){
                
                $item = array(
                        'isQuote' => 0,
                        'certificado' => $data['cert']['id'] 
                    );
                    
                     if( $this->db->set($item)->where('itemId', $data['item']['itemId'])->update('cycoasis_rsv.r_items') ){
                         
                        $origCert = $data['item']['notas']." || Certificado origen rsva ".$data['cert']['hotel']." ".$data['cert']['rsva']." con folio ".$data['cert']['id'];
                         
                        $this->db->set('notasHotel', $origCert)->where('itemId', $data['item']['itemId'])->update('cycoasis_rsv.r_hoteles');
                
                        $msg = "Certificado con folio ".$data['cert']['id']." apicado al item ".$data['item']['itemLocatorId']." por ".$_GET['usn'];
                        $this->zd->saveHistory( $data['item']['mlTicket'], $msg );
                        $this->fullConf($data['item']['masterLocatorId'],3, false, false, false, '', true);
                        
                        okResponse('Certificado Guardadp', 'data', true, $this); 
                        
                    }else{
                        errResponse('Error al obtener el masterlocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }
                
            }else{
                errResponse('Error al obtener el masterlocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            

                
         });
         
        
    }
    
    function chgAgent_put(){
        tokenValidation12( function(){
            
            $data = $this->put();
            
            $rsv = model_rsv();
            
            $result = $rsv->changeCreator($data['ml'], $data['newUser'], $data['newName'], $data['oldName']);
            
            
            if( !$result['err'] ){
                
                okResp( $result['msg'], 'data', $result['data'] ); 
                        
            }else{
                errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error'] );
            }
            
         });
         
        
    }
    
    public function confToken_get(){
        
        $l = $this->uri->segment(3);
        
        $jwt = $this->confToken($l);
        
        okResponse('Token obtenido', 'data', $jwt, $this); 
    }
    
    private function confToken($l){
        
        $jwtQ = $this->db->query("SELECT b.dtCreated, b.masterlocatorid, b.correoCliente FROM `cycoasis_rsv`.`r_items` it LEFT JOIN `cycoasis_rsv`.`r_masterlocators` b ON `it`.`masterlocatorid` = `b`.`masterlocatorid` WHERE it.masterLocatorId = $l GROUP BY b.masterlocatorid");
        $jwtR = $jwtQ->row_array();
        
        $jwt = generateToken( $jwtR, 'cycConf2021' );
        
        return $jwt;
        
    }
    
    // ********** EXTRANET **********
    
    public function listConfirm_put(){
      
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){

            $crA = $this->db->query("SELECT rsv_extranetAll FROM userDB a LEFT JOIN cat_profiles b ON a.profile=b.id WHERE asesor_id=".$_GET['usid']);
            $crR = $crA->row_array();
            if( $q = $this->db->query("SELECT 
                                        a.itemId,
                                        itemLocatorId,
                                        CASE
                                    		WHEN itemType = 5 THEN  fecha_in
                                    		WHEN itemType = 11 THEN  COALESCE(IF(llegada_date='0000-00-00',null,llegada_date),salida_date)
                                    		WHEN itemType = 4 THEN  t.fecha
                                    		WHEN itemType = 3 THEN  xt.inicio
                                        END as dtCreated,
                                        -- IF(isCancel = 0, dtNowIsRsv, dtCancel) AS dtCreated,
                                        title AS tipoServicio,
                                        isCancel
                                    FROM
                                        cycoasis_rsv.r_items a
                                            LEFT JOIN
                                        cycoasis_rsv.servicios b ON a.itemType = b.id
                                            LEFT JOIN
                                        cycoasis_rsv.r_xfer x ON a.itemId = x.itemId
                                            LEFT JOIN
                                        cycoasis_rsv.r_tour t ON a.itemId = t.itemId
                                            LEFT JOIN
                                        cycoasis_rsv.r_xtras xt ON a.itemId = xt.itemId
                                            LEFT JOIN
                                        cycoasis_rsv.r_xfer_vcm xfv ON a.itemId = xfv.itemId
                                    WHERE
                                        isQuote = 0
                                            AND ((isCancel = 0 AND confirm IS NULL)
                                            OR (isCancel = 1 AND confirmCancel IS NULL AND confirm IS NOT NULL))
                                            AND itemType NOT IN (1 , 2)
                                            AND CASE
                                    		WHEN itemType = 5 THEN  fecha_in
                                    		WHEN itemType = 11 THEN  COALESCE(IF(llegada_date='0000-00-00',null,llegada_date),salida_date)
                                    		WHEN itemType = 4 THEN  t.fecha
                                    		WHEN itemType = 3 THEN  xt.inicio
                                        END >= '20210901'
                                    ORDER BY isCancel , dtCreated") ){
                
                $result = array('confirm' => array(), 'cancel' => array());
                
                foreach( $q->result_array() as $ind => $c ){
                    if($c['isCancel'] == '1'){
                        array_push($result['cancel'], $c);
                    }else{
                        array_push($result['confirm'], $c);
                    }
                }
                
                
                okResponse('Servicios Obtenidos', 'data', $result, $this);
            }else{
                errResponse('No tienes los permisos necesarios para ver esta reservación. Para más información comunícate con el Contact Center', REST_Controller::HTTP_BAD_REQUEST, $this, 'error',array());
            }

        });
    }
    
    private function notify_email_vcm( $type, $item ){
        
        $mailListQ = $this->db->from('cycoasis_rsv.cat_providersMailList')->where(array('providerId'=>5,'activo'=>1))->order_by('main')->get();
        $mo = $mailListQ->result_array();
        $cc = array();
        
        foreach( $mo as $index => $m ){
            if( $m['main'] == 1 ){
                $main = $m['zdId'];
            }else{
                array_push($cc, array('user_id'=>$m['zdId'], 'action'=>'put'));
            }
        }
        
        $title = $type == 'xld' ? ("Cancelacion de Reserva ".$item['itemLocatorId']." (CRM: ".$item['confirmOk']." // ORACLE: ".$item['vcm_confirm'].")") : ("Nueva Reserva ".$item['itemLocatorId']." - Contact Center Oasis Hoteles / VCM");
        $openMsg = $type == 'xld' ? ("Notificación de cancelación de reserva para confirmar generada automáticamente por CyC") : ("Notificación de nueva reserva para confirmar generada automáticamente por CyC");
        
        $newTicket = array("ticket" => array("subject" => $title, 
                "requester_id" => $main,
                "submitter_id" => 360005313512,
                "group_id" => 360005313512,
                "assignee_id" => 360005313512,
                "comment" => array("body" => $openMsg, "public" => false, "author_id" => 360005313512)));

        $tkt = json_encode($newTicket);
        $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
        
        $id = $response['data']->{'ticket'}->{'id'};
        $tags = array('confirming', $item['itemLocatorId']);
        
        $msg = confirming_notify_v12( $type, $item );
        
        $editTkt = array("ticket" => array(
                "tags" => $tags,
                "requester_id" => $main,
                "submitter_id" => 360005313512, 
                "email_ccs" => $cc,
                "status" => "solved",
                "comment" => array("html_body" => $msg, "public"=> true, "author_id" => 360005313512)));
                
        if( isset($_GET['zdId']) ){
            $editTkt['ticket']['assignee_id'] = $_GET['zdId'];
        }         
                
        $tkt = json_encode($editTkt);
        
        $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$id.'.json';
        $responseOk = getUrlContent( $url, true, true, $tkt);

        return $id;
    }
    
    // ********** EXTRANET **********
    
    
    
    
    // ********** START CANCELLATIONS **********
    
    public function cancelItemV2_put(){
        $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
     
            $d = $this->put();
            
            if( isset($d['data']['packs']) && count($d['data']['packs']) > 0 ){
                
                $penalidadOG = $d['data']['penalidad'];
                $penalidad = $penalidadOG;
                foreach( $d['data']['packs'] as $pP => $itemP ){
                    $penalidad -= $d['data']['penalidad'] > 0 ? $itemData['monto'] : 0;
                }
                
                if( $penalidad < 0 ){
                    errResp( 'La penalidad ingresada no cubre el monto de los paquetes incluidos. La penalidad debe ser igual o mayor al monto de los paquetes', REST_Controller::HTTP_BAD_REQUEST, 'error', array());
                }
                
                $d['data']['penalidad'] = $penalidad;
                
                $hotelCancel = $this->cancelItemV2($d, false, false);
                
                if( $hotelCancel['err'] ){
                    errResp($hotelCancel['err']['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $hotelCancel['error']);
                }
                
                $x = 0;
                foreach( $d['data']['packs'] as $p => $item ){
                    $item['keep'] = $item['keep'] ?? false;
                    if( !$item['keep'] ){
                        if( $item['itemType'] == '15' || $item['itemType'] == '10' ){
                            
                            if( $item['confirmOK'] != null ){
                                $cancel = $this->cancelaAssist($item['confirm']);
                                
                                if( $cancel['err'] ){
                                    errResponse($cancel['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                                }else{
                                    $this->db->query("UPDATE cycoasis_rsv.r_assist SET status=0, fecBaja=NOW() WHERE codigo='".$item['confirm']."'");
                                }
                            }
                                   
                            $itemData = $item;
                            $itemData['xldType'] = $d['data']['xldType'];
                            $itemData['penalidad'] = 0;
                                    
                            $insCancel = $this->cancelItemV2(array('data' => $itemData, 'policies' => $d['policies'], 'flag' => $d['flag']), false, false);
                    
                            if( $insCancel['err'] ){
                                errResp($insCancel['err']['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $insCancel['error']);
                            }
                                        
                        }else{
                            $itemData = $item;
                            $itemData['xldType'] = $d['data']['xldType'];
                            $itemData['penalidad'] = 0;
                                    
                            $insCancel = $this->cancelItemV2(array('data' => $itemData, 'policies' => $d['policies'], 'flag' => $d['flag']), false, false);
                    
                            if( $insCancel['err'] ){
                                errResp($insCancel['err']['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $insCancel['error']);
                            }
                        }
                    }
                    
                    $x++;
                    if( $x >= count($d['data']['packs']) ){
                        okResp('Items Cancelados', 'data', true);
                    }
                }
            }else{
                $this->cancelItemV2($d);
            }
        
        });
    }
    
    private function returnResp( $flag, $type, $msg, $c1, $d1, $c2 = 'default', $d2 = null){
        if( $flag ){
            if( $type == 'error' ){
                errResp($msg, REST_Controller::HTTP_BAD_REQUEST, $c1, $d1, $c2, $d2);
            }else{
                okResponse($msg, $c1, $d1, $this, $c2, $d2);
            }
        }else{
            return array('err' => $type == 'error', 'msg' => $msg, $c1 => $d1, $c2 => $d2);
        }
    }
  
    private function cancelItemV2($d, $skipValid = false, $rFlag = true){

        $data = $d['data'];
        $policies = $d['policies'] ?? array();
        $flag = $d['flag'];
        $dtCreated = new DateTime();
        
        $related = $this->itemPaymentsV2($data['itemId'],false);
        
        if( !$skipValid ){
            $fq = $this->db->query("SELECT IF(allmighty=1,1,rsv_cancelMaster) as rsv_cancelMaster FROM userDB a LEFT JOIN cat_profiles b ON a.profile=b.id WHERE asesor_id=".$_GET['usid']);
            $fr = $fq->row_array();
            
            $f = $fr['rsv_cancelMaster'] == '1';
        }else{
            $f = true;
        }
        
        
        
        if( $f == false || $flag == true ){
            

                if( $data['isQuote'] == 0 && $_GET['usid'] != 29 ){
                    return $this->returnResp( $rFlag, 'error', 'Este item ya no es cotización, su status es de reserva. Solicita a tu gerente la cancelación', 'error', array() );
                    // errResponse('Este item ya no es cotización, su status es de reserva. Solicita a tu gerente la cancelación', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }
                
                if( $data['isConfirmable'] == 1 && $_GET['usid'] != 29 ){
                    return $this->returnResp( $rFlag, 'error', 'Este item posiblemente cuenta con pagos en validacion. Solicita a tu gerente la cancelación', 'error', array() );
                    // errResponse('Este item posiblemente cuenta con pagos en validacion. Solicita a tu gerente la cancelación', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }
                
                if( $data['montoPagado'] != 0 && $_GET['usid'] != 29 ){
                    return $this->returnResp( $rFlag, 'error', 'Este item ya cuenta con pagos realizados, solicita a tu gerente la cancelación', 'error', array() );
                    // errResponse('Este item ya cuenta con pagos realizados, solicita a tu gerente la cancelación', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }


            
            if($this->db->where("itemId", $data['itemId'])->set(array('isCancel' => 1, 'isOpen' => 0, 'isQuote' => 0, 'userCancel' => $_GET['usid'], 'dtCancel' => $dtCreated->format('Y-m-d H:i:s')))->update('cycoasis_rsv.r_items') ){
                if($this->db->where("itemId", $data['itemId'])->set(array('montoPagado' => 0))->update('cycoasis_rsv.r_monto') ){
                    $mlTicket = $data['mlTicket'];
                    $mlItem = $data['itemLocatorId'];
                    $msg = "Item $mlItem cancelado por ".$_GET['usn'].", No se realizaron movimientos monetarios ya que sólo era cotización";
                    $this->zd->saveHistory( $mlTicket, $msg ); 
                }else{
                    return $this->returnResp( $rFlag, 'error', 'Error cancelar montos del Item', 'error', $this->db->error());
                    // errResponse('Error cancelar montos del Item', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }else{
                return $this->returnResp( $rFlag, 'error', 'Error cancelar Item', 'error', $this->db->error());
                // errResponse('Error cancelar Item', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
                        
            return $this->returnResp( $rFlag, 'success', 'Item Cancelado', 'data', true);
            // okResponse('Item Cancelado', 'data', true, $this);
        }else{
            
            if( $data['certificado'] != null ){
                $t_res = array(
                    'rp_num04' => 0,
                    'rp_char03' => null
                );
                $this->db->set($t_res)->where('id', $data['certificado'])->update('t_reservations');
                $this->zd->saveHistory( $data['mlTicket'], "Certificado ".$data['certificado']." desligado de rsva ".$data['itemLocatorId']." por ".$_GET['usn'] );
            }
            
            $isR = $data['xldType'] == 'reembolso';
            $ajustes = $this->ajusteMonto_devolucion( $data, $data['penalidad'], $isR );
            
            $this->db->set(array('montoPenalidad' => $data['penalidad'], 'montoPagado' => $data['penalidad'], 'montoEnValidacion' => 0, 'montoParcial' => $data['penalidad']))->where('itemId', $data['itemId'])->update('cycoasis_rsv.r_monto');  
            $itemUpdt = array('isCancel' => 1, 'isConfirmable' => 0, 'isQuote' => 0, 'userCancel' => $_GET['usid'], 'dtCancel' => $dtCreated->format('Y-m-d H:i:s'));
            $this->db->set($itemUpdt)->where('itemId', $data['itemId'])->update('cycoasis_rsv.r_items');  
            
            $mlTicket = $data['mlTicket'];
            $mlItem = $data['itemLocatorId'];
            $msg = "Item $mlItem cancelado por ".$_GET['usn'].". Monto a favor del cliente: ".($ajustes['montoFavor'] ?? 'n/a');
            
            if( $isR ){
                $msg .= " || solicitado como <b>reembolso</b>";
            }else{
                $msg .= " || saldo para uso como <b>traspaso</b>";
            }
            
            
            if( $data['itemType'] == 1 ){
                $msg .= "\n\nPenalidad aplicada: ".$data['penalidad']." (La penalidad por política aplicable al momento de la cancelación: ".$policies['penalidadTotal'];
                
                if( $policies['isNights'] ?? 0 == '1' ){
                    $msg .= " [".$policies['penalty']." noches + ".$policies['nochesDormidas']." noches dormidas])";
                }else{
                    $msg .= ")";
                }
            }
            
            if( $data['itemType'] == 11 ){
                $this->load->model('Vcm_model');
                $vcm = new Vcm_model;
                
                $ct = $vcm->cancelTraslado( $data );
                
                $msg .= " || ".$ct['msg'];
            }
            
            $txmsg = "";
            
            // foreach( $ctxs as $txs => $ctm ){
            //     $txmsg .= $ctm['msg']."<br>";
            // }
            
            $this->zd->saveHistory( $mlTicket, $txmsg ); 
            $this->zd->saveHistory( $mlTicket, $msg ); 
            
            $rsv = model_rsv();
            $rsv->manageItem(true, $data['itemLocatorId']);
            $itemDataQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
            $data = $itemDataQ->row_array();
            
            // if($confTicket = $this->sendConf($data, 3, true)){
            if($rFlag){
                if( $this->fullConf($data['masterLocatorId'], 3, true, false, false, '', true)){
                    
                    okResponse('Item Cancelado', 'data', true, $this);
                }
                
                errResponse('No fue posible obtener la información de las trasacciones', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }else{
                return $this->returnResp( $rFlag, 'success', 'Item Cancelado', 'data', true);
            }

        }

    }
    
    // ********** END CANCELLATIONS **********
    
    
    
    
    // ********** START RSV CHECKOUT **********
    
        public function saveRsv12_put(){
            $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
                $data = $this->put();
                
                $this->saveRsv12($data);
            });
        }
        
    
    
        private function saveRsv12($data, $otlc = false){

                // EVALUATE IF NEW LOC
                $masterFlag = $data['newMaster'];
                
                // FOR NEW MASTERLOCATORS
                if( $masterFlag ){
                    
                    $insertMaster = $data['masterdata'];
                    $insertMaster['userCreated'] = $_GET['usid'] ?? 103;
                    
                    // CREATE MASTERLOCATOR
                    if( $this->db->set($insertMaster)->insert('cycoasis_rsv.r_masterlocators') ){
                        $data['masterloc'] = $this->db->insert_id();
                        
                        // CREATE ML HISTORY TICKET
                        $mlTicket = $this->createHistoryTicket( $data['masterloc'] );
                        
                        $this->db->where('masterlocatorid',$data['masterloc'])->set(array('historyTicket' => $mlTicket))->update('cycoasis_rsv.r_masterlocators');
                    }else{
                        errResponse('No se pudo crear el masterLocator', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }
                    
                    // SET ITEM INDEX TO 1
                    $i = 1;
                
                }
                
                // FOR EXISTING MASTERLCOATORS
                else{
                    
                    $insertMaster = $data['masterdata'];
                    $this->db->where('masterlocatorid',$data['masterloc'])->set($insertMaster)->update('cycoasis_rsv.r_masterlocators');
                
                    $mlQ = $this->db->query("SELECT historyTicket, languaje, hasTransfer  FROM cycoasis_rsv.r_masterlocators WHERE masterlocatorid = ".$data['masterloc']);
                    $mlR = $mlQ->row_array();
                    $mlTicket = $mlR['historyTicket'];
                    $lang = $mlR['languaje'];
                    
                    $mlupdt = array();
                    
                    if( $data['rsvType'] == 'xfer' ){
                         $mlupdt['hasTransfer'] = 0;
                    }
                    
                    if( $lang == null ){
                        $mlupdt['languaje'] = $data['master']['languaje'];
                    }
                    
                    if( count($mlupdt) > 0 ){
                        $this->db->where('masterlocatorid',$data['masterloc'])->set($mlupdt)->update('cycoasis_rsv.r_masterlocators');
                    }
                    
                    // SET CONSECUTIVE ITEM INDEX
                    $iq = $this->db->query("SELECT COUNT(*) as items FROM cycoasis_rsv.r_items WHERE masterlocatorId=".$data['masterloc']);
                    $ir = $iq->row_array();
                    $i = intval($ir['items']) + 1;
                }
                
                if( $data['zdTicket'] != '' ){
                    $data['zdChannel'] = $this->linkedTicket($data['zdTicket'], $data['masterloc']);
                    $msgTicket = " Se ligó el ticket ".$data['zdTicket'];
                }else{
                    $data['zdChannel'] = 'undefined';
                    $msgTicket = "";
                }
                
                $data['zdChannel'] = $data['zdTicket'] == '' ? 'undefined' : $this->linkedTicket($data['zdTicket'], $data['masterloc']);
                
                $data['mlData'] = array(
                        "ml" => $data['masterloc'],
                        "zdTicket" => $data['zdTicket'],
                        "zdChannel" => $data['zdChannel'],
                        'msg' => $msgTicket
                    );
                
                
                
                // CALL METHOD
                switch($data['rsvType']){
                    case 'hotel':
                        $this->hotelRsv12($data, $i, $mlTicket, $otlc);
                        break;
                    default:
                        $itemsCreados = array();
                        $msg = "Item ";
                        
                        $itemId = $this->rsvItemBuild( $data['rsvType'], $data['data'][$data['rsvType']], $data['mlData'], $i );
                        array_push($itemsCreados,$itemId);
                        
                        if( $msg != "Item " ){
                            $msg .= ", ";
                        }
                        
                        $msg .= $data['masterloc']."-$i";
                        $msg .= " creado por ".$_GET['usn'].".".$msgTicket;
                        $this->zd->saveHistory( $mlTicket, $msg ); 
                    
                        okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterloc'], 'items'=>$itemsCreados), $this);
                    break;
                }

        }
        
        private function hotelRsv12($data, $i, $mlTicket, $otlc = false){
            $itemsCreados = array();
            $msg = "Item(s) ";
            
            foreach( $data['data'] as $index => $info ){
                
                $itemId = $this->rsvItemBuild( 'hotel', $info['hotel'], $data['mlData'], $i );
                array_push($itemsCreados,$itemId);
                
                if( $msg != "Item(s) " ){
                    $msg .= ", ";
                }
                
                $msg .= $data['masterloc']."-$i";
                $hotelLoc = $data['masterloc']."-$i";
    
                $i++;
                
                $hasInclusions = $data['hasInclusionIns'] ?? false;
                
                if( $hasInclusions ){
                    
                    $itemLocRelated = $i;
                    
                    foreach( $info as $label  => $ins ){
                        if (preg_match("/insurance_inclusion/", $label)) {
                            
                            $iRelated = $itemId;
                    
                            $ins['item']['pkgItemId'] = $itemId;
                            
                            $itemIdNew = $this->rsvItemBuild( 'insurance', $ins, $data['mlData'], $i, true );
                            array_push($itemsCreados,$itemIdNew);
                            
                            if( $msg != "Item(s) " ){
                                $msg .= ", ";
                            }
                            
                            $msg .= $data['masterloc']."-$i";
                
                            $i++;
                            
                        } 
                    }
                
                    
                }
                
                if( $data['hasInsurance'] ){
                    
                    $iRelated = $itemId;
                    
                    $info['insurance']['insurance']['sg_itemRelated'] = $hotelLoc;
                    $info['insurance']['item']['parentItem'] = $itemId;
                    
                    $itemId = $this->rsvItemBuild( 'insurance', $info['insurance'], $data['mlData'], $i );
                    array_push($itemsCreados,$itemId);
                    
                    $this->db->where("itemId", $iRelated)->set(array('insuranceRelated' => $itemId))->update('cycoasis_rsv.r_items');
                    
                    if( $msg != "Item(s) " ){
                        $msg .= ", ";
                    }
                    
                    $msg .= $data['masterloc']."-$i";
        
                    $i++;
                }
                
                    
            }
            
            $msg .= " creado(s) por ".($_GET['usn'] ?? 'robotCyc').".".$data['mlData']['msg'];
            $this->zd->saveHistory( $mlTicket, $msg );
            
            if( $otlc ){
                $mail = model_mailing();
                $conf = $mail->sendFull($data['masterloc'], true, true);
            }
        
            okResponse('Reserva Creada', 'data', array('masterlocator'=>$data['masterloc'], 'items'=>$itemsCreados), $this);
      
            
        }
        
        private function createHistoryTicket( $loc ){
            $newTicket = array("ticket" => array(
                        "subject" => "Historial para localizador $loc",
                            "requester_id" => 373644140032,
                            "submitter_id" => 373644140032, 
                        "group_id" => 360006241112,
                        "assignee_id" => 373644140032,
                        "status" => "solved",
                        "tags" => array("rsv_history", "rsva-$loc"),
                        "comment" => array("body" => "****** Inicio de Historial, reserva creada en ComeyCome. Masterlocator: $loc ******", "public" => false, "author_id" => 373644140032)));
            
            $tkt = json_encode($newTicket);
            $response = getUrlContent( 'https://oasishoteles.zendesk.com/api/v2/tickets.json', true, false, $tkt);
    
            return $response['data']->{'ticket'}->{'id'};
        }
    
        private function rsvItemBuild( $type, $iData, $mlData, $i, $pkg = false ){
            
            $data = $iData['item'];
            
            $data["masterlocatorid"]    = $mlData['ml'];
            $data["itemNumber"]         = $i;
            $data["itemLocatorId"]      = $mlData['ml']."-$i";
            $data["userCreated"]        = $_GET['usid'] ?? 103;
            $data["zdTicket"]           = $mlData['zdTicket'];
            $data["zdChannel"]          = $mlData['zdChannel'];
                
            if( $this->db->insert('cycoasis_rsv.r_items', $data) ){
                return $this->rsvMontoBuild( $type, $iData, $this->db->insert_id(), $i );
            }else{
                errResponse('Error al crear item '.$mlData['ml']."-$i", REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        }
        
        private function rsvMontoBuild( $type, $iData, $itemId, $i ){
            
            $data = $iData['monto'];
            $data["itemId"] = $itemId;
                
            if( $this->db->insert('cycoasis_rsv.r_monto', $data) ){
                return $this->rsvProductBuild( $type, $iData, $itemId, $i );
            }else{
                errResponse('Error al guardar informacion de montos de '.$mlData['ml']."-$i", REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
        }
        
        private function rsvProductBuild( $type, $iData, $itemId, $i ){
            
            $tables = array(
                    "hotel" => "cycoasis_rsv.r_hoteles",
                    "tour" => "cycoasis_rsv.r_hoteles",
                    "xfer" => "cycoasis_rsv.r_xfer_vcm",
                    "insurance" => "cycoasis_rsv.r_seguros",
                );
            
            $data = $iData[$type];
            $data["itemId"] = $itemId;
                
            if( $this->db->insert($tables[$type], $data) ){
                return $itemId;
            }else{
                errResponse('Error al guardar informacion de $type de '.$mlData['ml']."-$i", REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
            
        }
        
        private function linkedTicket( $ticket, $ml ){
    
            $newTicket = array("ticket" => array(
                    "comment" => array("body" => "Reserva creada en ComeyCome. Masterlocator: $ml", "public" => false, "author_id" => 373644140032)));
    
            $tkt = json_encode($newTicket);
            $url = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$ticket.'.json';
                
            if( $responseOk = getUrlContent( $url, true, true, $tkt) ){
                
                $urlTkt = 'https://oasishoteles.zendesk.com/api/v2/tickets/'.$ticket.'.json';
                
                if( $responseTkt = getUrlContent( $urlTkt ) ){
                    $chan = $responseTkt['data']->ticket->via->channel;
                    
                    if( $chan == 'any_channel' ){
                        $tags = $responseTkt['data']->ticket->tags;
                        
                        if( in_array("asksuite_origin", $tags) ){
                            $chan = 'asksuite';
                        }
                    }
                    
                    return $chan;
                }
                
                return 'undefined';
            }else{
              return 'undefined';
            }
    
        }
        
        public function manualValidate_put(){
            $data = $this->put();
            
            $rltQ = $this->manageQueries('item', true, 0, "it.itemLocatorId = '".$data['localizador']."'");
            $rltQQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
            $rlt = $rltQQ->row_array();
            
            $pm = model_pagos();
            
            $response = $pm->validateDeposit( $rlt['itemId'], $rlt['mlTicket'], true );
            
            if( $response['err'] ){
                errResp( $response['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $response['error'] );
            }
        
            okResp( 'Validation Ran...', 'data', $response['data'] );
            
        }
        
        public function manualPaymentApplyTest_put(){
        
            $pm = model_pagos();
            
            $test = $pm->saveTxToCielo( array() );
            
            okResp( $test['msg'], 'data', $test );
        }
        
        public function manualPaymentApply_put(){
            
            $pm = model_pagos();
            
            // Pasar en put el itemId o itemLocatorId
            $data = $this->put();
            if( isset($data['itemLocatorId']) ){
                $itq = $pm->getId($data['itemLocatorId'], 'itemlocatorid');
                
                if( $itq['err'] ){
                    errResp( $itq['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $itq['error']);
                }
                
                $data['itemId'] = $itq['data'];
            }
            
            
            if( !isset($data['itemId']) ){
                errResp('No se encontro la variable itemId en los parametros',REST_Controller::HTTP_BAD_REQUEST, 'error', array());
            }
            
            // Cambiar a true cuando este en produccion la aplicacion en CIELO
            $cieloReady = true;
            
            // Se activa cuando el complemento ya fue capturado... falta desarrollar esta parte
            $isComplemento = false;

            $insert = $pm->applyPayments( $data['itemId'], $isComplemento, $cieloReady );
            
            if( $insert['err'] ){
                errResp( $insert['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $insert['errores'] );
            }else{
                $this->zd->saveHistory( array($data['itemId'], 'itemId'), $insert['msg'] );
                okResp( $insert['msg'], 'tx', $insert['correctas'], 'errores', $insert['errores'] );
            }
            
        }
        
        public function applyPaymentsCielo_put(){
            tokenValidation12( function(){
               
                $data = $this->put();
                
                // VALIDA QUE LOS PARAMETROS PASADOS SEAN CORRECTOS
                if( !isset($data['txId']) ){
                    errResp('No se encontro el valor del txId en los parametros. Asegurate de agregarlo', REST_Controller::HTTP_BAD_REQUEST, 'error', $data);
                }
                
                $pm = model_pagos();
                
                $result = $pm->applyPayments_sgl( $data['txId'], false, true );
        
                if( $result['err'] ){
                    errResp( $result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', array('txCorrectas' => $result['correctas'], 'txErrores' => $result['errores']));
                }else{
                    okResp($result['msg'],'data', array('txCorrectas' => $result['correctas'], 'txErrores' => $result['errores']));
                }
               
                
            });
        }
    
    // ********** END RSV CHECKOUT **********
    
    
    

    // ********** START TRASLADOS **********
    
        public function manualEmit_get(){
            
            $rltQ = $this->manageQueries('item', true, 0, "it.itemLocatorId = '".$this->uri->segment(3)."'");
            $rltQQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
            $rlt = $rltQQ->row_array();
            
            if( $rlt['itemType'] != '11' ){
                errResponse('El item enviado no es un traslado para emitir',REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
            }
            
            $this->load->model('Vcm_model');
            $vcm = new Vcm_model;
            
            $result = $vcm->emitTraslado( $rlt );
            // $result = $this->emitTraslado( $rlt );
            
            
            okResponse('Sent Data', 'data', $result, $this);
        }
        

    // ********** END TRASLADOS **********
    
    
    
    // ********** START SEGUROS **********
    
        private function cancelaAssist($voucher){
            
            $asc = model_assistcard();
    
            $poliza = $asc->consultaConf($voucher);
    
            //Create a date object out of a string (e.g. from a database):
            $inicio = date_create_from_format('Y-m-d', substr($poliza['data']['voucher']['fecVigInic'],0,10));
    
            //Create a date object out of today's date:
            $hoy = date_create_from_format('Y-m-d', date('Y-m-d', strtotime('today')));
    
            if( $inicio < $hoy ){
                return array('err' => true, 'msg' => "La fecha de inicio de vigencia a iniciado (".date_format($inicio, 'd-m-Y')."), esta poliza ya no es cancelable");
                // errResponse("La fecha de inicio de vigencia a iniciado (".date_format($inicio, 'd-m-Y')."), esta poliza ya no es cancelable", REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
            }
    
            $response = $asc->cancelVoucher($voucher); 
            
            if( $response['err'] == true ){
                return array('err' => true, 'msg' => $response['description']);
            }else{
                return array('err' => false, 'msg' => $response['data']['description']);
            }
    
            
        }
        
        private function mdfXldInsurance( $data, $traspaso, $original ){
            $related = $this->itemPaymentsV2($data['seguro']['itemId'],false);
                
            $d = array(
                'data' => $data['seguro'],
                'related' => $related,
                'policies' => null,
                'flag' => false
                );
            $d['data']['xldType'] = $traspaso ? 'traspaso' : 'reembolso';
            $d['data']['penalidad'] = 0;
            
            // CANCELL IF ALREADY EMITTED
            if( $original['confirm'] != null ){
            
                $cancel = $this->cancelaAssist($original['confirm']);
                
                if( $cancel['err'] ){
                    errResponse($cancel['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                }else{
                    $this->db->query("UPDATE cycoasis_rsv.r_assist SET status=0, fecBaja=NOW() WHERE codigo='".$original['confirm']."'");
                }
                
            }
            
            // CANCELL ON DB
            if( $cancelItem = $this->cancelItemV2($d, true, false)){
                    
                if ( $this->db->query("UPDATE cycoasis_rsv.r_items SET insuranceRelated = NULL WHERE itemId=".$data['itemId'] ) ){
                    return true;
    
                }else{
                    return false;
                }
            }else{
                errResponse('Error al cancelar item', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $cancelItem);
            }
        }
        
        private function mdfIns( $data, $flag = false ){
            $asc = model_assistcard();
            
            $traspaso = isset($data['traspaso']) ? $data['traspaso'] : false;
            
            // GET ORIGINAL INSURANCE ITEM
            $insItemQ = $this->db->from('cycoasis_rsv.r_items')->where('itemId',$data['seguro']['itemId'])->get();
            $insItemR = $insItemQ->row_array();
            
            // CANCELACION
            if( $data['item']['sg_cobertura'] == '' ){
                
                if( $this->mdfXldInsurance( $data, $traspaso, $insItemR ) ){
                    if( $flag ){
                        return true;
                    }else{
                        okResponse('Seguro Cancelado','data', true,$this,'itemId', $data['itemId']);
                    }
                }else{
                    errResponse('Error al quitar relacion de item con seguro', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }
            }
            
            // AJUSTE
            
                $insMontoQ = $this->db->from('cycoasis_rsv.r_monto')->where('itemId',$data['seguro']['itemId'])->get();
                $insMontoR = $insMontoQ->row_array();
            
                $data['monto']['lv'] = 1;
                $data['monto']['isPagoHotel'] = 0;
                $data['monto']['montoOriginal'] = $data['monto']['monto'];
                $data['monto']['montoParcial'] = $data['monto']['monto'];
                $data['monto']['grupo'] = 'assistCard';
                
                $emited = false;
                $montoFavor = false;
                
                // ADJUST IF ALREADY EMITTED
                if( $insItemR['confirm'] != null ){
                    
                    if( $this->mdfXldInsurance( $data, true , $insItemR ) ){
                        $emited = true;
                        
                        $newIns = array(
                                "item" => $data['item'],
                                "itemId" => $data['itemId'],
                                "monto" => $data['monto'],
                                "type" => "seguro",
                                "master" => array(
                                        "languaje" => $data['seguro']['idioma']
                                    ),
                                "masterLoc" => $data['masterLoc']
                            );
                        unset( $newIns['item']['itemId'] );
                        unset( $newIns['monto']['itemId'] );
                        
                        $this->saveRsvFnc( $newIns );
                        
                    }else{
                        errResponse($cancel['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array());
                    }
                    
                    
                }else{
                // ADJUST IF NOT EMITTED
                    
                    if ( $this->db->set($data['item'])->where('itemId',$data['seguro']['itemId'])->update('cycoasis_rsv.r_seguros') ){
                        
                        // ADJUST WITH NO PAYMENT
                        if( $insMontoR['montoPagado'] == 0 ){
                            if ( !$this->db->set($data['monto'])->where('itemId',$data['seguro']['itemId'])->update('cycoasis_rsv.r_monto') ){
                                errResponse('Error al modificar item con seguro', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                            }
                            $montoFavor = true;
                        }else{
                            
                            $montoResponse = $this->editMontoTotal(array('comment' => "Monto modificado automaticamente por el gestor de seguros", 'isR' => !$traspaso,'newMonto' => $data['monto']['monto'],'original' => $data['seguro']), false);
                            if( $montoResponse['err'] ){
                                errResponse($montoResponse['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $montoResponse['error']);
                            }
                            
                            if( $data['monto']['monto'] < $data['seguro']['montoPagado'] ){
                                $assist = $asc->emit($data['seguro']);
                            }else{
                                $montoFavor = true;
                            }
                        }
                        
                       
                        if( $montoFavor ){
                            $this->db->query("UPDATE cycoasis_rsv.r_items SET isCancel=0, isQuote=1, isOpen=0, vigencia=ADDDATE(NOW(),2) WHERE itemId=".$data['seguro']['itemId'] );
                            if( $flag ){
                                return true;
                            }else{
                                okResponse('Seguro Ajustado. Aun hay un saldo pendiente por lo que no se ha emitido','data', true,$this); 
                            }
                        }else{
                            $this->db->query("UPDATE cycoasis_rsv.r_items SET isCancel=0, isQuote=0, isOpen=0, vigencia=ADDDATE(NOW(),2) WHERE itemId=".$data['seguro']['itemId'] );
                            if( $flag ){
                                return true;
                            }else{
                                okResponse('Seguro Ajustado.','data', true,$this); 
                            }
                        }
                        
                    }else{
                        errResponse('Error al modificar item con seguro', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }
                }
        }
      
        public function modifyInsurance_put(){
            $result = validateToken( $_GET['token'], $_GET['usn'], $func = function(){
             
                $data = $this->put();
                $this->mdfIns( $data );
    
            });
        }
        
        public function setInsurancePh_put(){
            $data = $this->put();
            
            $noEmit = isset( $data['noEmit'] ) && $data['noEmit'] == true;
            
            $this->setInsurancePh( $data['localizador'], false, $noEmit, true);
            
        }
        
        private function setInsurancePh( $i, $isId = false, $noEmit = true, $finalResponse = false ){
            
            $asc = model_assistcard();
            $rsv = model_rsv();
            $pm = model_pagos();
            
            $query = $isId ? "it.itemId = '$i'" : "it.itemLocatorId = '$i'";
            
            $rltQ = $rsv->manageItem( true, 0, $query );
            $rltQQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
            $rlt = $rltQQ->row_array();
            
            if( $rlt['itemType'] != 10 && $rlt['itemType'] != 15 ){
                if( $finalResponse ){
                    errResponse('El item ingresado no es un seguro', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $rlt);
                }else{
                    logError( "setInsurancePh (Rsv)", "manualSetPh", 'El item ingresado no es un seguro', "Error al establecer como PH (item ".$rlt['itemLocatorId'].")", __LINE__, $this );
                    return array("err" => true, "msg" => 'El item ingresado no es un seguro');
                }
            }
            
            if( $rlt['montoPagado'] > 0 ){
                if( $finalResponse ){
                    errResponse('La reserva ya cuenta con pagos, no es posible establecerla como pago en hotel', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $rlt);
                }else{
                    logError( "setInsurancePh (Rsv)", "manualSetPh", 'La reserva ya cuenta con pagos, no es posible establecerla como pago en hotel', "Error al establecer como PH (item ".$rlt['itemLocatorId'].")", __LINE__, $this );
                    return array("err" => true, "msg" => 'La reserva ya cuenta con pagos, no es posible establecerla como pago en hotel');
                }
            }
            
            if( $rlt['isQuote'] == 0 || $rlt['isConfirmable'] == 1 || $rlt['isCancel'] == 1 ){
                if( $finalResponse ){
                    errResponse('La reserva no cuenta con los criterios para ser pago en hotel', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $rlt);
                }else{
                    logError( "setInsurancePh (Rsv)", "manualSetPh", 'La reserva no cuenta con los criterios para ser pago en hotel', "Error al establecer como PH (item ".$rlt['itemLocatorId'].")", __LINE__, $this );
                    return array("err" => true, "msg" => 'La reserva no cuenta con los criterios para ser pago en hotel');
                }
            }
            
            $update = array(
                    'isParcial' => 0,
                    'isPagoHotel' => 1,
                    'montoParcial' => 0,
                );
            
            if( $this->db->where('itemId',$rlt['itemId'])->set($update)->update('cycoasis_rsv.r_monto') ){
                if( $this->db->where('itemId',$rlt['itemId'])->set(array('isQuote' => 0))->update('cycoasis_rsv.r_items') ){
                    
                    $this->zd->saveHistory( $rlt['mlTicket'], "Item de seguro ".$rlt['itemLocatorId']." establecido como pago en hotel desde api manual" );
                    
                    if( !$noEmit ){
                        $result = $asc->emit($rlt);
                
                        if( $result['err'] ){
                            if( $finalResponse ){
                                errResponse($result['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $result['data']);
                            }else{
                                logError( "setInsurancePh (Rsv)", "manualSetPh", $result['msg'], "Error al establecer como PH (item ".$rlt['itemLocatorId'].")", __LINE__, $this );
                                return array("err" => true, "msg" => $result['msg']);
                            }
                        }else{
                            $pm->validateDeposit( $rlt['itemId'] );
                            if( $finalResponse ){
                                okResponse($result['msg'], 'data', true, $this);
                            }else{
                                return array("err" => false, "msg" => $result['msg']);
                            }
                        }
                    }else{
                        if( $finalResponse ){
                            okResponse('Seguro como pago en hotel, sin emitir aun', 'data', true, $this);
                        }else{
                            return array("err" => false, "msg" =>'Seguro como pago en hotel, sin emitir aun');
                        }
                    }
                    
                    
                }else{
                    if( $finalResponse ){
                        errResponse('Error al guardar pago en hotel en tabla de montos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                    }else{
                        logError( "setInsurancePh (Rsv)", "manualSetPh", 'Error al guardar pago en hotel en tabla de montos', "Error al establecer como PH (item ".$rlt['itemLocatorId'].")", __LINE__, $this );
                        return array("err" => true, "msg" => 'Error al guardar pago en hotel en tabla de montos');
                    }
                }
            }else{
                if( $finalResponse ){
                    errResponse('Error al guardar pago en hotel en tabla de montos', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
                }else{
                    logError( "setInsurancePh (Rsv)", "manualSetPh", 'Error al guardar pago en hotel en tabla de montos', "Error al establecer como PH (item ".$rlt['itemLocatorId'].")", __LINE__, $this );
                    return array("err" => true, "msg" => 'Error al guardar pago en hotel en tabla de montos');
                }
            }
            
        }
        
        public function manualAssistEmit_put(){
            $data = $this->put();
            $rsv = model_rsv();
            $asc = model_assistcard();
            $pm = model_pagos();
            
            $rltQ = $rsv->manageItem( true, 0, "it.itemLocatorId = '".$data['localizador']."'" );
            $rltQQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
            $rlt = $rltQQ->row_array();
            
            $this->db->query("SELECT sg_inicio < CURDATE(), ADDDATE(sg_inicio,DATEDIFF(CURDATE(),sg_inicio)), ADDDATE(sg_fin,DATEDIFF(CURDATE(),sg_inicio)) 
                                INTO @flag, @inicio, @fin
                                FROM cycoasis_rsv.r_seguros WHERE itemId=".$rlt['itemId']);
                                
            $this->db->query("UPDATE cycoasis_rsv.r_seguros SET
                                sg_inicio = IF(@flag=1,@inicio,sg_inicio),
                                sg_fin = IF(@flag=1,@fin,sg_fin)
                                WHERE itemId=".$rlt['itemId']);
            
            $rltQ = $rsv->manageItem( true, 0, "it.itemLocatorId = '".$data['localizador']."'" );
            $rltQQ = $this->db->query("SELECT * FROM items ORDER BY itemId");
            $rlt = $rltQQ->row_array();
            
            $result = $asc->emit($rlt);
            
            if( $result['err'] ){
                errResponse($result['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $result['error']);
            }else{
                $pm->validateDeposit( $rlt['itemId'] );
                okResponse($result['msg'], 'data', true, $this);
            }
        }
        
        public function manualAssistCancel_put(){
            $data = $this->put();
            
            $result = $this->cancelaAssist($data['confirm']);
            
            if( $result['err'] ){
                errResponse($result['msg'], REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $result);
            }else{
                okResponse($result['msg'], 'data', true, $this);
            }
        }
        
        public function relatedIns($ml, $il){
            $itQ = $this->manageQueries('item', true, 0, "it.masterlocatorid = $ml");
            $itQQ = $this->db->query("SELECT * FROM items WHERE sg_itemRelatedStatus='".$il."-0'");
            $it = $itQQ->row_array();
            
            return $it;
        }
        
        public function recoverAssist_put(){
            $data = $this->put();
            $asc = model_assistcard();
            
            if( !$sq = $this->db->select('b.itemLocatorId')->from('cycoasis_rsv.r_items a')->join('cycoasis_rsv.r_items b','b.insuranceRelated=a.itemId','left')->where('a.itemLocatorId',$data['loc'])->get() ){
                errResponse('Error al obtener Parent Item',  REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error() );
            }
            
            $seguro = $sq->row_array();

            $voucher = $asc->consultaVoucher($seguro['itemLocatorId']);
            
            if( $voucher['ERR'] ){
                errResponse($voucher['msg'],  $voucher['request'], $this, 'error', $voucher['error'] );
            }else{
                
                if( !isset($voucher['data']['data']['voucher']) ){
                    errResponse('No se encontro ninguna reserva emitida',  REST_Controller::HTTP_BAD_REQUEST, $this, 'error', array() );
                }
                
                $insData = array(
                        "codigo"                    => $voucher['data']['data']['voucher']['codigo'],
                        "agenciaNombreComercial"    => $voucher['data']['data']['agenciaNombreComercial'],
                        "tarifaEmitida"             => $voucher['data']['data']['voucher']['tarifaEmitida'],
                        "taxEmitida"                => $voucher['data']['data']['voucher']['taxEmitida'],
                        "remesaEmitida"             => $voucher['data']['data']['voucher']['remesaEmitida'],
                        "cambioDolar"               => $voucher['data']['data']['voucher']['cambioDolar'],
                        "fechaEmision"              => $voucher['data']['data']['voucher']['fechaEmision'],
                        "fecVigInic"                => $voucher['data']['data']['voucher']['fecVigInic'],
                        "fecVigFin"                 => $voucher['data']['data']['voucher']['fecVifFin'],
                        "cliente"                   => $voucher['data']['data']['voucher']['cliente'],
                        "agencia"                   => $voucher['data']['data']['voucher']['agencia'],
                        "producto"                  => $voucher['data']['data']['producto']['codigo'],
                        "codTarifa"                 => $voucher['data']['data']['voucher']['codTarifa'],
                        "cantDias"                  => $voucher['data']['data']['voucher']['cantDias'],
                        "status"                    => 1
                    );
                
                if( $this->db->insert('cycoasis_rsv.r_assist', $insData) ){
                    if( $this->db->set(array('confirm' => $voucher['data']['data']['voucher']['codigo']))->where('itemLocatorId',$data['loc'])->update('cycoasis_rsv.r_items') ){
                        okResponse('Seguro actualizado y confirmado', 'data',$insData, $this);
                    }else{
                       errResponse('Error al insertar info de emision',  REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error() ); 
                    }
                }else{
                   errResponse('Error al insertar info de emision',  REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error() ); 
                }
                
            }
            
            
        }
    
    // ********** END SEGUROS **********
    
    
    // ********** START PAGOS ROIBACK **********
    
    public function rsvasRoiback_get(){
        
        // tokenValidation12( $func = function(){
        
            $data = array(
                    "inicio" => $this->uri->segment(4),
                    "table" => $this->uri->segment(3) ?? 0
                );
                
            $data['table'] = $data['table'] == "1";
            
            $inicio = isset($data['inicio']) ? "'".$data['inicio']."'" : "CURDATE()";
            
            $query = "SELECT 
                        a.*,
                        COUNT(b.voucher) AS complementos,
                        GROUP_CONCAT(DISTINCT rsva) AS CIELO,
                        CONCAT('[',
                                GROUP_CONCAT(DISTINCT CONCAT('{',
                                            '\"operacion\":\"',
                                            operacion,
                                            '\",\"aut\":\"',
                                            aut,
                                            '\",\"monto\":',
                                            p.monto,
                                            ',\"moneda\":\"',
                                            p.moneda,
                                            '\",\"referencia\":\"',
                                            referencia,
                                            '\"}')),
                                ']') AS pago
                    FROM
                        cycoasis_rsv.rb_rsvas a
                            LEFT JOIN
                        t_complementos b ON a.rb_voucher = b.voucher
                            LEFT JOIN
                        res_ligasPago p ON p.referencia LIKE CONCAT('%', rb_voucher, '%')
                    WHERE
                        inicio >= $inicio
                            AND (paquetes > 0 OR traslados != '[]')
                            AND st != 'CANC'
                    GROUP BY rb_voucher
                    ORDER BY inicio";
                    
            if( $result = $this->db->query($query) ){
                $rows = $result->result_array();
                
                foreach($rows as $i => $r){
                    $rows[$i]['habitaciones'] = json_decode($r['habitaciones']);    
                    $rows[$i]['tours'] = json_decode($r['tours']);    
                    $rows[$i]['traslados'] = json_decode($r['traslados']);    
                    $rows[$i]['pago'] = $r['pago'] == null ? array() : json_decode($r['pago']);    
                    
                    $toPay = 0;
                    $host = 0;
                    $complements = 0;
                    $totComp = 0;
                    
                    foreach( $rows[$i]['tours'] as $ti => $t ){
                        $toPay += $t->Precio;
                        $complements += $t->Precio;
                        $totComp += $t->Cantidad;
                    }
                    
                    foreach( $rows[$i]['traslados'] as $xi => $x ){
                        $toPay += $x->{'Precio complemento'};
                        $complements += $x->{'Precio complemento'};
                        $totComp++;
                    }
                    
                    foreach( $rows[$i]['habitaciones'] as $hi => $h ){
                        $toPay += ($h->Precio - $complements) / $h->Noches;
                        $host += ($h->Precio - $complements) / $h->Noches;
                    }
                    
                    
                    
                    $rows[$i]['toPay'] = round($toPay * 100) / 100 ;
                    $rows[$i]['notes'] = "Hospedaje: $".(round($host * 100) / 100)." // Complementos: $".(round($complements * 100) / 100) ;
                    
                    if( $totComp != $rows[$i]['complementos'] ){
                        $rows[$i]['error'] = true;
                    }else{
                        $rows[$i]['error'] = false;
                    }
                }
                
                if( isset($data['table']) && $data['table'] ){
                    $xls = model_xls();
                    $xls->printTable($rows);
                }else{
                    okResp('Resultados obtenidos', 'data', $rows );
                }
            }else{
                errResp('Error al obtener informacion', REST_Controller::HTTP_BAD_REQUEST, 'error', $this->db->error());
            }
                    
            
            
        // });
        
    }
    
    
    // ********** END PAGOS ROIBACK **********
    
    public function getTc_get(){
        $date = $this->uri->segment(3) ?? date('Y-m-d');
        
        $pm = model_pagos();
        
        $result = $pm->getTC($date);
        
        if( $result['err'] ){
            errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
        }else{
            okResp($result['msg'], 'data', $result['data'] );
        }
    }
    
    public function tipoCambio_get(){
        $date = $this->uri->segment(3) ?? date('Y-m-d');
        
        $pm = model_pagos();
        
        $result = $pm->getTC($date);
        
        if( $result['err'] ){
            errResp($result['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $result['error']);
        }else{
            echo "Tipo de cambio del dia ".$result['data']->fecha."<br>".$result['data']->dato;
        }
    }
    
    private function ca_base(){
        return $this->db->select("b.itemLocatorId,
                                    b.itemLocatorId,
                                    a.*,
                                    h.hotel,
                                    inicio,
                                    gpoTfa,
                                    CASE
                                        WHEN b.itemType = 1 THEN b.confirm
                                        WHEN b.itemType IN (10,15) THEN c.confirm
                                    END as confirmation,
                                    b.itemType,
                                    p.proveedor,
                                    tipo,
                                    p.complejo as complejoPago,
                                    b.isCancel,
                                    cp.complejo", FALSE)
                    ->from('cycoasis_rsv.p_cashTransaction a')
                    ->join('res_ligasPago p','a.accountId = p.operacion','left')
                    ->join('cycoasis_rsv.r_items b','a.itemId = b.itemId','left')
                    ->join('cycoasis_rsv.r_items c','COALESCE(b.parentItem,b.pkgItemId) = c.itemId','left')
                    ->join('cycoasis_rsv.r_hoteles h','CASE
                                                            WHEN b.itemType = 1 THEN b.itemId
                                                            WHEN b.itemType IN (10,15) THEN c.itemId
                                                        END = h.itemId','left', FALSE)
                    ->join('cat_complejo cp','h.hotel=cp.hotel','left')
                    ->where(array( 'cieloApplied' => 0, 'accountId !=' => 0, 'p.complejo !=' => 'vcm', 'paymentValid' => 1))
                    ->where('h.inicio >=', 'CURDATE()', FALSE)
                    ->where('a.monto !=', 0, FALSE)
                    ->where('b.isOpen', 0)
                    ->order_by('itemId')
                    ->order_by('a.dtCreated')
                    ->limit(500);
    }
    
    public function cieloApplyPendings_put(){
        tokenValidation12( $func = function(){
            
            $data = $this->put();
            
            $this->ca_base();
                    
            if( isset( $data['complejo'] ) ){
                $this->db->where('cp.complejo', $data['complejo']);
            } 
                    
            if( isset( $data['fechaPago_inicio'] ) ){
                $this->db->where("p.dtCreated BETWEEN '", $data['fechaPago_inicio']."' AND ADDDATE('".$data['fechaPago_fin']."',1)", false);
            } 
                    
            if( isset( $data['fechaAplicacion_inicio'] ) ){
                $this->db->where("a.dtCreated BETWEEN '", $data['fechaAplicacion_inicio']."' AND ADDDATE('".$data['fechaAplicacion_fin']."',1)", false);
            } 
                    
            if( isset( $data['fechaInicio_inicio'] ) ){
                $this->db->where("inicio BETWEEN '", $data['fechaInicio_inicio']."' AND '".$data['fechaInicio_fin']."'", false);
            } 
              
            $rquery = $this->db->get_compiled_select();      
            if( $rQ = $this->db->query( $rquery ) ){
                
                $itemIds = array();
                
                foreach( $rQ->result_array() as $i => $tx ){
                    array_push($itemIds, $tx['itemId']);
                }
                
                $this->ca_base();
                $this->db->where_in('a.itemId', $itemIds);
                
                $rQA = $this->db->get();
                
                okResponse('Listado obtenido', 'data', $rQA->result_array(), $this);
            }else{
                errResponse('Error al obtener listado', REST_Controller::HTTP_BAD_REQUEST, $this, 'error', $this->db->error());
            }
        });
    }
    
    
    

  
}