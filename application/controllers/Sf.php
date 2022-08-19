<?php
//defined('BASEPATH') OR exit('No direct script access allowed');
require( APPPATH.'/libraries/REST_Controller.php');
// use REST_Controller;


class Sf extends REST_Controller {

    protected $sf;
    
    public function __construct(){
        parent::__construct();
        $this->load->helper('json_utilities');
        $this->load->helper('jwt');
        $this->load->helper('validators');
        $this->load->helper('model_loader');   
        $this->load->database();
        
        $this->sf = model_salesforce();
    }
  
    public function catalogs_get(){
    
        $update = $this->sf->updateSfCatalogs();
        
        if( $update['err'] ){
            errResp($update['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $update['error'] );
        }else{
            okResp($update['msg'], 'data', $update['data']);
        }
    }
    
    public function searchOportunity_post(){
        $data = $this->post();
        
        $ops = $this->sf->searchSfOportunity( $data );
        
        if( $ops['err'] ){
            errResp($ops['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $ops['error'] );
        }else{
            okResp($ops['msg'], 'data', $ops['data']);
        }
        
    }
    
    public function searchUserOportunities_post(){
        $data = $this->post();
        
        $ops = $this->sf->searchSfUsertOportunities( $data );
        
        if( $ops['err'] ){
            errResp($ops['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $ops['error'] );
        }else{
            okResp($ops['msg'], 'data', $ops['data']);
        }
        
    }
    
    
    public function updateOportunity_post(){
        $data = $this->post();
        
        $ops = $this->sf->updateSfOportunity( $data );
        
        if( $ops['err'] ){
            errResp($ops['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $ops['error'] );
        }else{
            okResp($ops['msg'], 'data', $ops['data'], 'extra', $ops['extra'] ?? null);
        }
        
    }
    
    public function sendRoomOportunity_put(){
        $data = $this->put();
        
        $rooms = $data['rooms'];
        $op = $data['oportunidad'];
        
        $base64 = base64_encode($this->printPdf( array("op" => $op ), $rooms ));
        $rooms['base64'] = $base64;
        
        $bin = base64_decode($base64, true);

        # Perform a basic validation to make sure that the result is a valid PDF file
        # Be aware! The magic number (file signature) is not 100% reliable solution to validate PDF files
        # Moreover, if you get Base64 from an untrusted source, you must sanitize the PDF contents
        if (strpos($bin, '%PDF') !== 0) {
          errResp('Missing the PDF file signature', REST_Controller::HTTP_BAD_REQUEST, 'error', array());
        }
        
        $room = $this->sf->sendRoom( $rooms );
        
        if( $room['err'] ){
            errResp($room['msg'], REST_Controller::HTTP_BAD_REQUEST, 'error', $room['error'] );
        }else{
            // okResp($room['msg'], 'data', $room['data']);
            okResp($room['msg'], 'data', $room['data'], 'extra', $room['extra'] ?? null);
        }
        
    }
    
    private function printPdf( $d, $r ){
        
        $op = $d['op'];
      
        $pdf = model_pdf( array("mt" =>39, "mb" => 38) );
        
        // Crear una imagen de 100*30
        $im = imagecreate(100, 30);
        
        // Fondo blanco y texto azul
        $fondo = imagecolorallocate($im, 255, 255, 255);
        $color_texto = imagecolorallocate($im, 0, 0, 255);
        
        // Escribir la cadena en la parte superior izquierda
        imagestring($im, 5, 0, 0, 'Hello world!', $color_texto);
        
        // Imprimir la imagen
        // header('Content-type: image/png');
        imagepng($im, $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/img/logos/tmpTable.png' );
        
        //Add a new page
        $pdf->pdf->AddPage();
          
        $generales = array(
                "Las tarifas anteriores son netas en pesos (MXN) por habitación por noche, no comisionables, con base en ocupación seleccionada.",
                "Tarifas vigentes para llegadas anticipadas y salidas posteriores con 3 días anteriores y 3 días después de las fechas señaladas, sujeto a disponibilidad, con previa reservación.",
                "El servicio todo incluido se sirve en las áreas, horarios y procedimientos que el hotel establece.",
                "El programa todo incluido no admite reservaciones para grupos en los restaurantes.",
                "Todo evento privado sin excepción, está sujeto a cargo adicional por concepto de servicio y se cotiza por separado.",
                "La ubicación de la habitación está sujeta a la categoría y al hotel contratado.",
                "En el caso de 1 adulto y 2 menores (de 0 a 12 años) se considera la tarifa de habitación doble.",
                "Ocupación máxima por habitación: 3 adultos (en PYR) o 4 adultos (en GOC) o 2 adultos y 2 menores acomodados en camas existentes.",
            );
        $incluye = array(
                "Alojamiento. Las habitaciones pueden tener 2 camas matrimoniales o 1 cama King size sujeta a disponibilidad (no   se garantiza un tipo de cama en específico).",
                "Todos los alimentos, en los restaurantes correspondientes a cada categoría.",
                "Bebidas nacionales e importadas, correspondientes a cada categoría.",
                "Mini-bar en las habitaciones. Se surte con cervezas, refrescos y agua (servicio y periodicidad dependen de la categoría seleccionada). ",
                "Actividades organizadas por nuestro equipo de Animación dentro de la programación semanal del Hotel.",
                "Entretenimiento en vivo diurno y nocturno dentro de la programación semanal del Hotel.",
                "1 código de internet WIFI por persona para 1 dispositivo.",
                "Propinas.",
                "Impuestos (IVA e ISH).",
                "Uso de salones de acuerdo al tamaño del grupo (los servicios adicionales son con costo extra y se cotizan por separado).",
            );
        $noIncluye = array(
            "Llamadas telefónicas.",
            "Uso de SPA.",
            "Deportes acuáticos motorizados.",
            "Lavandería y tintorería.",
            "Servicio de pago por evento.",
            "Caja de seguridad (únicamente incluida en categoría The Pyramid).",
            "Contratación de servicios de eventos.",
            "Room Service. Servicio exclusivo para huéspedes The Pyramid con cargo por entrega.",
            "Late check out.",
            );
        $noIncluye_2 = array(
            "Impuesto de Saneamiento Ambiental ($28.86 MXN por habitación por noche de ocupación)",
            );
        $gratuidades = array(
            "Coctel de bienvenida sin alcohol durante el registro del grupo, siempre y cuando el grupo llegue a la misma hora.",
            "Check in privado, siempre y cuando el grupo llegue a la misma hora.",
            "Se otorga una habitación en cortesía cada 15 pagadas (en temporadas altas aplica una habitación en cortesía cada 25 pagadas). El máximo de cortesías a otorgar es de 8 habitaciones (en la misma categoría, ocupación y fechas contratadas por el promedio del grupo).",
            "Menores de 0 a 12 años son sin cargo compartiendo habitación con sus padres, de 13 años en adelante se cobra precio de adulto en all inclusive.",
            );

        setlocale(LC_ALL,"es_ES");
        $fecha = strftime("%d de %B del %Y");

        // ENCABEZADO
        $pdf->align("Cancún, Quintana Roo; a $fecha", 'R', array("size" => 10, "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        
        $data = array(
                "tratamiento" => "",
                "nombre" => $op['NombreContact']." ".$op['ApellidosContact'],
                "tel" => $op['TelefonoContact'],
                "cia" => $op['NombreAccount'],
                "email" => $op['EmailContact'],
                "referencia" => "",
                "fecha_inicio" => $op['FechaBoda'] ?? $op['FechaInicioEstancia'],
                "fecha_fin" => $op['FechaBoda'] ?? $op['FechaFinEstancia'],
                "no_habs" => count($r['rooms']),
                "habs" => array($op['HotelEvento'])
            );
        
        // SALUDO
        $pdf->write($data['tratamiento'] ?? 'C.', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write(" ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write($data['nombre'] ?? 'A quien corresponda', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4));
        $pdf->saltoLinea(6);
        $pdf->write("Teléfono: ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write($data['tel'] ?? 'N/A', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4));
        $pdf->saltoLinea(6);
        $pdf->write("Compañía: ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write($data['cia'] ?? 'N/A', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4));
        $pdf->saltoLinea(6);
        $pdf->write("E-mail: ", array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4));
        $pdf->write($data['email'] ?? 'N/A', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4));
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        
        // REFERENCIAS
        $pdf->align("", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 105));
        $pdf->align("Referencia:", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 40));
        $pdf->align($data['referencia'] ?? 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1, "w" => 50));
        $pdf->align("", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 105));
        $pdf->align("Fehas:", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 40));
        $pdf->align( isset($data['fecha_inicio']) ? ($data['fecha_fin']." a ".$data['fecha_fin']) : 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1, "w" => 50));
        $pdf->align("", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 105));
        $pdf->align("No. habs probables:", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 40));
        $pdf->align($data['no_habs'] ?? 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1, "w" => 50));
        $pdf->align("", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 105));
        $pdf->align("Hoteles:", 'R', array("size" => 10, "style" => 'B', "font" => 'Arial', 'h' => 4, 'ln' => 0, 'w' => 40));
        if( isset($data['habs']) ){
            $pdf->align($data['habs'][0] ?? 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1, "w" => 50));
            array_splice($data['habs'],0,1);
            $habs = implode(", ", $data['habs']);
        }
        if( $habs != "" ){
            $pdf->align(isset($habs) ? ($habs == '' ? 'N/A' : $habs) : 'N/A', 'R', array("size" => 10, "style" => '', "font" => 'Arial', 'h' => 4, 'ln' => 1));
        }
        $pdf->saltoLinea(6);
        
        // INICIO
        $pdf->write("Estimado ".$data['nombre'].":", array("size" => 10, "style" => 'B', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        $saludo = "Agradeciendo su preferencia y de acuerdo con su amable solicitud, le presento la siguiente propuesta de hospedaje para su grupo, esperando que sea de su agrado y teniendo el placer de servirle cubriendo sus necesidades:";
        $pdf->addParagraph($saludo, array("size" => 10, "style" => '', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        
        // Add logo to page
        // $pdf->pdf->Image($_SERVER['CONTEXT_DOCUMENT_ROOT'].'/img/logos/tmpTable.png',null,null,130);
        for($i=0;$i<4;$i++){
            $pdf->pdf->Row(array("col1", "col2", "col3", "col4"));
        }
        $pdf->saltoLinea(6);
        
        // CUERPO
        $pdf->addBullets($generales, array("size" => 10, "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->addParagraph("La Tarifa incluye", array("size" => 10, "style" => 'BU', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->addBullets($incluye, array("size" => 10, "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->addParagraph("La Tarifa no incluye", array("size" => 10, "style" => 'BU', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->addBullets($noIncluye, array("size" => 10, "font" => 'Arial'));
        $pdf->addBullets($noIncluye_2, array("size" => 10, "style" => 'B', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->addParagraph("Gratuidades e inclusiones", array("size" => 10, "style" => 'BU', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->addBullets($gratuidades, array("size" => 10, "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $t = "Esta cotización no bloquea el número de habitaciones requeridas, ";
        $pdf->write($t, array("size" => 10, "style" => '', "font" => 'Arial'));
        $t = "tiene una vigencia de 10 días a partir de la fecha de envío y se debe reconfirmar disponibilidad una vez aceptada esta propuesta. ";
        $pdf->write($t, array("size" => 10, "style" => 'B', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        $t = "Precios sujetos a cambio sin previo aviso. ";
        $pdf->write($t, array("size" => 10, "style" => 'BU', "font" => 'Arial'));
        $t = "En caso de bajar la garantía de 10 habitaciones, se tendrá que re cotizar y no se podrán confirmar las tarifas otorgadas en esta cotización.";
        $pdf->write($t, array("size" => 10, "style" => '', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        $t = "Ésta es sólo una cotización y no implica responsabilidad de disponibilidad para el Hotel al momento de confirmar.";
        $pdf->addParagraph($t, array("size" => 10, "style" => '', "font" => 'Arial'));
        $pdf->saltoLinea(6);
        $pdf->saltoLinea(6);
        
        $name = $op['SocioComercialNombre'];
        $puesto = "Coordinador de Grupos y Convenciones";
        $hoteles = array($op['HotelEvento']);
        $cel = "99 82 40 94 71";
        $pdf->addSignature( $name, $puesto, $hoteles, $cel, 10);
        
        // return the generated output
        return $pdf->pdf->Output('S');
  }
    
    
  
}
