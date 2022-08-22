<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cotizador_model extends CI_Model{
    
    protected $i;
    
    function __construct() {
        parent::__construct();
        $this->i = get_instance();
    }
    
    public function hospedaje( $start, $end, $grupo, $noRest, $bw = '', $comercial = false ){
        
        $totalAdlts = 0;
        $totalPax = 0;
        $grupo = $grupo;
        
        $lcom = $comercial ? '(1-((1-n1)*(1-0.05)))' : 'n2';
        // $lcom = $comercial ? 'n2' : 'n2';
        
        
        // SET RESTRICTIONS
        $noReestrictions = $noRest == true ? 1 : 0;
        
        
        // SET USER
        $usid = isset($_GET['usid']) ? $_GET['usid'] : 66;
        
        
        // SET query variables
        $this->i->db->query("SET @inicio = CAST('$start' as DATE)");
        $this->i->db->query("SET @fin = ADDDATE(CAST('$end' as DATE),-1)");
        
        $this->i->db->query("SET @bdate = ".($bw != '' ? "CAST('$bw' as DATE)" : "CURDATE()"));

        $this->i->db->query("SELECT tipoCambio INTO @tc FROM tarifas_tipocambio WHERE dtStart<=@bdate ORDER BY dtStart DESC LIMIT 1;");
        $this->i->db->query("SELECT COALESCE(fixedTC,@tc) INTO @tc FROM tarifas_grupos WHERE grupo='$grupo';");
          
        
        // SKIP ACTIVE GROUPS IF USER DEFINED
        if( $usid == 29 OR $usid == 63 OR $usid == 31 OR $usid == 72 OR $usid == 70 ){
            $activeGroup = '';
        }else{
            $activeGroup = "AND g.activo=1 
                                    AND CURDATE() BETWEEN bwInicio AND bwFin";
        }
        
        // RAW PRICE TABLE
            
        $query = "SELECT 
                	d.hotel, cmpl.sf_map as complejoSF, ch.hotelName, ch.sf_map as hotelSF, habCode, tarifa_pp,
                    CONCAT(\"{\",GROUP_CONCAT(CONCAT(\"\\\"\",f.Fecha,\"\\\":{\",
                    \"\\\"n1\\\":{\",
                		\"\\\"name\\\":\\\"\",l1_name,\"\\\",\",
                		\"\\\"code\\\":\\\"\",COALESCE(code1,'_'),\"\\\",\",
                        \"\\\"descuento\\\":\",n1,\",\",
                        \"\\\"active\\\":\",l1_active,\",\",
                        \"\\\"allEnabled\\\":\",l1_allEnabled,
                		\"},\"
                	\"\\\"n2\\\":{\",
                		\"\\\"name\\\":\\\"\",l2_name,\"\\\",\",
                		\"\\\"code\\\":\\\"\",COALESCE(code2,'_'),\"\\\",\",
                        \"\\\"descuento\\\":\",$lcom,\",\",
                        \"\\\"active\\\":\",l2_active,\",\",
                        \"\\\"allEnabled\\\":\",l2_allEnabled,
                		\"},\"
                	\"\\\"n3\\\":{\",
                		\"\\\"name\\\":\\\"\",l3_name,\"\\\",\",
                		\"\\\"code\\\":\\\"\",COALESCE(code3,'_'),\"\\\",\",
                        \"\\\"descuento\\\":\",n3,\",\",
                        \"\\\"active\\\":\",l3_active,\",\",
                        \"\\\"allEnabled\\\":\",l3_allEnabled,
                		\"},\"
                	\"\\\"n4\\\":{\",
                		\"\\\"name\\\":\\\"\",l4_name,\"\\\",\",
                		\"\\\"code\\\":\\\"\",COALESCE(code4,'_'),\"\\\",\",
                        \"\\\"descuento\\\":\",n4,\",\",
                        \"\\\"active\\\":\",l4_active,\",\",
                        \"\\\"allEnabled\\\":\",l4_allEnabled,
                		\"},\"
                    \"\\\"n5\\\":{\",
                		\"\\\"name\\\":\\\"\",COALESCE(l5_name,''),\"\\\",\",
                		\"\\\"code\\\":\\\"\",COALESCE(code5,'_'),\"\\\",\",
                        \"\\\"descuento\\\":\",COALESCE(n5,0),\",\",
                        \"\\\"active\\\":\",COALESCE(l5_active,0),\",\",
                        \"\\\"allEnabled\\\":\",COALESCE(l5_allEnabled,0),
                		\"},\"
                    \"\\\"precio\\\":{\",
                		\"\\\"pax1\\\":\",pax1,\",\",
                		\"\\\"pax2\\\":\",pax2,\",\",
                		\"\\\"pax3\\\":\",pax3,\",\",
                		\"\\\"pax4\\\":\",pax4,\",\",
                		\"\\\"paxMenor\\\":\",paxMenor,
                		\"},\"
                    \"\\\"precio_m\\\":{\",
                		\"\\\"pax1\\\":\",COALESCE(pax1_m,0),\",\",
                		\"\\\"pax2\\\":\",COALESCE(pax2_m,0),\",\",
                		\"\\\"pax3\\\":\",COALESCE(pax3_m,0),\",\",
                		\"\\\"pax4\\\":\",COALESCE(pax4_m,0),\",\",
                		\"\\\"paxMenor\\\":\",COALESCE(paxMenor_m,0),
                		\"},\",
                	 \"\\\"isClosed\\\":\",IF(COALESCE(blackout,0)=1 OR COALESCE(isClosed,0)=1,1,0),
                    \"}\")),\"}\") as jsonData,
                    habName, maxOcc, maxAdults, maxChild, isNR, isCC, tipoCamas, COALESCE(fixedTC,@tc) as tipoCambio, 
                    LOWER(CONCAT('https://cyc-oasishoteles.com/assets/img/logos/logo_',d.hotel,'.jpg')) as hotelUrl,
                    COALESCE(n.noches,1) as minNights
                FROM
                    cat_Fechas f
                        JOIN
                    tarifas_grupos g
                        LEFT JOIN
                    tarifas_descuentos d ON g.discountCode = d.code
                        AND f.Fecha BETWEEN d.inicio AND d.fin
                        AND @bdate BETWEEN dsc_bw_inicio AND dsc_bw_fin
                        LEFT JOIN
                	(SELECT 
                		Fecha, hotel, cat, grupo,
                		pax1 * if(tarifa_pp=1, 1, 1) as pax1,
                		pax2 * if(tarifa_pp=1, 2, 1) as pax2,
                		pax3 * if(tarifa_pp=1, 3, 1) as pax3,
                		pax4 * if(tarifa_pp=1, 4, 1) as pax4,
                        paxMenor,
                        pax1_m * if(tarifa_pp=1, 1, 1) as pax1_m,
                		pax2_m * if(tarifa_pp=1, 2, 1) as pax2_m,
                		pax3_m * if(tarifa_pp=1, 3, 1) as pax3_m,
                		pax4_m * if(tarifa_pp=1, 4, 1) as pax4_m,
                        paxMenor_m,h.*
                	FROM 
						Fechas f
					LEFT JOIN
                		tarifas_netas t on f.Fecha BETWEEN tw_inicio AND tw_fin
                	LEFT JOIN 
                	    cat_habitaciones h ON t.cat=h.habCode AND t.hotel=h.hotelCode
                    WHERE 
                        Fecha BETWEEN @inicio AND @fin AND @bdate BETWEEN netas_bw_inicio AND netas_bw_fin AND isCC=1 GROUP BY grupo, Fecha, hotel, cat) t ON d.grupo_netas=t.grupo AND f.Fecha=t.fecha AND d.hotel=t.hotel 
                    
                    LEFT JOIN 
                        t_blackout b ON f.Fecha=b.Fecha AND d.hotel=b.hotel
                    LEFT JOIN
                        cat_hoteles ch ON d.hotel=ch.code
                    LEFT JOIN
                        cat_complejo cmpl ON d.hotel=cmpl.hotel
                    LEFT JOIN
                        t_room_blackout br ON d.hotel = bo_hotel AND habCode = bo_room AND f.fecha BETWEEN br.inicio AND br.fin
                    LEFT JOIN
    					tarifas_noches n ON @inicio BETWEEN n.inicio AND n.fin AND t.hotel=n.hotel AND n.grupo=g.grupo
                WHERE
                    g.grupo = '$grupo'
                        AND f.Fecha BETWEEN @inicio AND @fin
                        AND d.habs LIKE CONCAT('%',habCode,'%')
                GROUP BY
                d.hotel, t.cat
                ORDER BY
                f.Fecha,displayOrder, d.hotel, pax1";
                
        if( $rawDataQ = $this->i->db->query($query) ){
            $rawData = $rawDataQ->result_array();
            
            // INFO GRUPOS
            $gQ = $this->i->db->from('tarifas_grupos')->where('grupo', $grupo)->get();
            $groupInfo = $gQ->row_array();
            
            return modelResp( false, "Tarifaro Obtenido", 'data', $rawData, 'extra', array('grupo' => $groupInfo) );
        }else{
            return modelResp( true, "Error al obtener tarifario", 'error', $this->i->db->error());
        }

    }
    
    public function filterOtlc( $cotizacion ){
        
        if( $cotizacion['extra']['grupo']['isLocal'] != 1 ){
            
            $cotizacion['extra']['grupo']['code2'] = 'OTLC';
            $cotizacion['extra']['grupo']['l2_name'] = 'OTLC';
            
            foreach($cotizacion['data'] as $i => $h ){
                
                $jd = json_decode($h['jsonData'], true);
                foreach( $jd as $id => $hd ){
                    // unset($jd[$id]['n3'], $jd[$id]['n4'], $jd[$id]['n5']);
                    $jd[$id]['n3']['active'] = '0';
                    $jd[$id]['n4']['active'] = '0';
                    $jd[$id]['n5']['active'] = '0';
                    $jd[$id]['n2']['name'] = 'OTLC';
                    $jd[$id]['n2']['code'] = '2 OTLC';
                    $jd[$id]['n2']['descuento'] = 1-((1-$jd[$id]['n1']['descuento'])*(1-.2));
                }
                $cotizacion['data'][$i]['jsonData'] = json_encode($jd);
            }
        }
        
        $cotizacion['extra']['grupo']['grupo'] = 'OTLC';
        $cotizacion['extra']['grupo']['p1'] = 'C';
        $cotizacion['extra']['grupo']['p2'] = 'C';
        $cotizacion['extra']['grupo']['p3'] = 'C';
        $cotizacion['extra']['grupo']['p4'] = 'C';
        $cotizacion['extra']['grupo']['p5'] = 'C';
        $cotizacion['extra']['grupo']['l3_active'] = '0';
        $cotizacion['extra']['grupo']['l4_active'] = '0';
        $cotizacion['extra']['grupo']['l5_active'] = '0';
        $cotizacion['extra']['grupo']['l3_allEnabled'] = '0';
        $cotizacion['extra']['grupo']['l4_allEnabled'] = '0';
        $cotizacion['extra']['grupo']['l5_allEnabled'] = '0';
        $cotizacion['extra']['grupo']['isOR'] = '0';
        $cotizacion['extra']['grupo']['hasPaq'] = '0';
        $cotizacion['extra']['grupo']['freeTransfer'] = '0';
        
        return $cotizacion;
    }
    
    public function filterHotel( $cotizacion, $hotel ){
        
        if( $hotel == null ){
            return $cotizacion;
        }
        
        foreach($cotizacion['data'] as $i => $h ){
            if( strtolower($h['hotel']) != strtolower($hotel) ){
                unset( $cotizacion['data'][$i] );
            }
        }
        
        $x = 0;
        $result = array();
        foreach($cotizacion['data'] as $i => $h ){
            // $result[$x] = $h;
            unset($cotizacion['data'][$i]);
            $cotizacion['data'][$x] = $h;
            $x++;
        }
        // unset($cotizacion['data']);
        // $cotizacion['data'] = $result;
            
        return $cotizacion;
    }
    
    public function cambioHospedaje( $itemId, $start = null, $end = null ){
        
        $this->i->load->model('Assistcard_model');
        $asc = new Assistcard_model;
        
        $this->i->db->query("SET @inicio = ".($start != null ? "'$start'" : "CURDATE()"));
        $this->i->db->query("SET @fin = ".($end != null ? "'$end'" : "CURDATE()"));
        
        $qdate = $start != null ? "@inicio" : "inicio";
        $qdate .= " as inicio, ";
        $qdate .= $end != null ? "@fin" : "fin";
        $qdate .= " as fin, ";
        
        $aq = $this->i->db->select("$qdate 1 as pax, sg_mdo as mdo, sg_cobertura as cobertura", false)->from('cycoasis_rsv.r_hoteles a')
                    ->join('cycoasis_rsv.r_items b','a.itemId = b.itemId', 'left')
                    ->join('cycoasis_rsv.r_seguros s','b.insuranceRelated = s.itemId', 'left')->where('a.itemId',$itemId)->get();
        $ad = $aq->row_array();
        $assist = $asc->cotizacion( $ad );
        
        if( $ad['mdo'] != null ){
            $seguro = array('price' => $assist['prices'][$ad['mdo']][$ad['cobertura']]['publico_ci'], 'tipocambio' => $assist['prices'][$ad['mdo']][$ad['cobertura']]['tipoCambio']);
        }else{
            $seguro = array('price' => 0, 'tipocambio' => 0);
        }
        
        $this->i->db->query("SELECT 
            hotel, categoria, if(gpoCC='OTLC',t.grupo,gpoCC), CAST(i.dtCreated as DATE) as created, inicio, fin, SUBSTRING(lv,1,1),
            b.moneda, monto, montoPagado+montoEnValidacion, CONCAT(adultos,'.',juniors,'.',menores), a.noches, adultos+juniors+menores,
            if(gpoCC='OTLC',1,0)
        INTO
        	@hotel, @cat, @gpo, @created, @inicio, @fin, @lv, @moneda, @montoOriginal, @montoPagadoTotal, @occOriginal, @noches, @paxOriginal, @isOtlc
        FROM
        	cycoasis_rsv.r_items i 
        		LEFT JOIN
            cycoasis_rsv.r_hoteles a ON i.itemId=a.itemId
                LEFT JOIN
            cycoasis_rsv.r_monto b ON a.itemId = b.itemId
                LEFT JOIN
            tarifas_grupos t ON b.grupo=t.cieloUSD
        WHERE
            i.itemId = $itemId
        GROUP BY i.itemId");
            
        if( $start != null ){
            $this->i->db->query("SET @inicio = '$start'");
        }
        
        if( $end != null ){
            $this->i->db->query("SET @fin = '$end'");
        }
        
        $this->i->db->query("SET @noches = DATEDIFF(@fin,@inicio)");
            
        $this->i->db->query("SELECT tipoCambio INTO @tc FROM tarifas_tipocambio WHERE dtStart<=@created ORDER BY dtStart DESC LIMIT 1;");
        $this->i->db->query("SELECT COALESCE(fixedTC,@tc) INTO @tc FROM tarifas_grupos WHERE grupo=@gpo;");
        
        $this->i->db->query("SET @dlv = CONCAT('n',@lv)"); 
         
        $this->i->db->query("SET @sql = CONCAT(\"SELECT 
        	b.hotel, c.cat, a.grupo as grupoTfas, @lv as lv, @moneda as moneda, @montoOriginal as montoOriginal, 
            @montoPagadoTotal as montoPagadoTotal, @occOriginal as occOriginal, @noches as noches, @paxOriginal as paxOriginal,
            IF(COUNT(DISTINCT Fecha) != @noches OR c.grupo IS NULL,0,1) as validFlag,
            ROUND(SUM(pax1 * IF(tarifa_pp=1,1,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2) as pax1,
            ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2) as pax2,
            ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)) + SUM(paxMenor * (1-n2) * IF(@moneda='MXN',@tc,1)),2) as pax23,
            ROUND(SUM(pax3 * IF(tarifa_pp=1,3,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2) as pax3,
            ROUND(SUM(pax4 * IF(tarifa_pp=1,4,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2) as pax4,
            ROUND((SUM(pax1 * IF(tarifa_pp=1,1,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))) - @montoOriginal,2) as difPax1,
            ROUND((SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))) - @montoOriginal,2) as difPax2,
            ROUND((SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))) + SUM(paxMenor * (1-n2) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2) as difPax23,
            ROUND((SUM(pax3 * IF(tarifa_pp=1,3,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))) - @montoOriginal,2) as difPax3,
            ROUND((SUM(pax4 * IF(tarifa_pp=1,4,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))) - @montoOriginal,2) as difPax4,
            maxOcc, maxAdults, maxChild
        FROM
            tarifas_grupos a
                LEFT JOIN
            tarifas_descuentos b ON a.grupo = b.code
                LEFT JOIN
            (SELECT Fecha, b.* FROM Fechas a LEFT JOIN tarifas_netas b ON a.Fecha BETWEEN tw_inicio AND tw_fin WHERE Fecha BETWEEN @inicio AND ADDDATE(@fin,-1) AND @created BETWEEN netas_bw_inicio AND netas_bw_fin) c 
                ON b.grupo_netas = c.grupo AND b.hotel=c.hotel AND c.Fecha BETWEEN b.inicio AND b.fin
        		LEFT JOIN
        	cat_habitaciones h ON b.hotel=h.hotelCode AND c.cat=h.habCode
        WHERE
            a.grupo = @gpo 
            AND @created BETWEEN dsc_bw_inicio AND dsc_bw_fin 
            AND b.hotel=@hotel AND c.cat=@cat
            AND c.Fecha BETWEEN @inicio AND ADDDATE(@fin,-1)\")");
        
        $r = $this->i->db->query("SELECT @sql");
        $qr = $r->row_array();
        $qt = $str=str_replace("\n","",str_replace("\t","",$qr['@sql']));
        
        if( $q = $this->i->db->query($qt) ){

            $qR = $q->row_array();
            
            if( $qR['validFlag'] == '1' ){
                return modelResp( false, "Cotizacion obtenida", 'data', $qR, 'assist', $seguro );
            }else{
                return modelResp( true, "No es posible cotizar desde CYC, se debe realizar la consulta desde CIELO", 'error', $qt);
            }
        }else{
            return modelResp( true, "Error al realizar consulta", 'error', $this->i->db->error());
        }
    }
    
    public function cambioHotel12( $itemId, $params ){
        
        $this->i->load->model('Assistcard_model');
        $asc = new Assistcard_model;
        
        $this->i->db->query("SET @inicio = CURDATE()");
        $this->i->db->query("SET @fin = CURDATE()");
        
        $start = $params['inicio'];
        $end = $params['fin'];
        $occup = $params['adultos'].".".$params['juniors'].".".$params['menores'];
        $totOc = $params['adultos']+$params['juniors']+$params['menores'];
        
        $qdate = "'$start' as inicio, '$end' as fin, ";
        
        $aq = $this->i->db->select("$qdate $totOc as pax, sg_mdo as mdo, sg_cobertura as cobertura", false)->from('cycoasis_rsv.r_hoteles a')
                    ->join('cycoasis_rsv.r_items b','a.itemId = b.itemId', 'left')
                    ->join('cycoasis_rsv.r_seguros s','b.insuranceRelated = s.itemId', 'left')->where('a.itemId',$itemId)->get();
        $ad = $aq->row_array();
        $assist = $asc->cotizacion( $ad );
        
        if( $ad['mdo'] != null ){
            $seguro = array('price' => $assist['prices'][$ad['mdo']][$ad['cobertura']]['publico_ci'], 'tipocambio' => $assist['prices'][$ad['mdo']][$ad['cobertura']]['tipoCambio']);
        }else{
            $seguro = array('price' => 0, 'tipocambio' => 0);
        }
        
        $this->i->db->query("SELECT 
            hotel, categoria, if(gpoCC='OTLC',t.grupo,gpoCC), CAST(i.dtCreated as DATE) as created, inicio, fin, SUBSTRING(COALESCE(lv_goalCode,lv),1,1),
            b.moneda, COALESCE(lv_originalRate,monto), montoPagado+montoEnValidacion, CONCAT(adultos,'.',juniors,'.',menores), a.noches, adultos+juniors+menores,
            if(gpoCC='OTLC',1,0)
        INTO
        	@hotel, @cat, @gpo, @created, @inicio, @fin, @lv, @moneda, @montoOriginal, @montoPagadoTotal, @occOriginal, @noches, @paxOriginal, @isOtlc
        FROM
        	cycoasis_rsv.r_items i 
        		LEFT JOIN
            cycoasis_rsv.r_hoteles a ON i.itemId=a.itemId
                LEFT JOIN
            cycoasis_rsv.r_monto b ON a.itemId = b.itemId
                LEFT JOIN
            tarifas_grupos t ON b.grupo=t.cieloUSD
        WHERE
            i.itemId = $itemId
        GROUP BY i.itemId");
            
        if( $start != null ){
            $this->i->db->query("SET @inicio = '$start'");
        }
        
        if( $end != null ){
            $this->i->db->query("SET @fin = '$end'");
        }
        
        $this->i->db->query("SET @noches = DATEDIFF(@fin,@inicio)");
            
        $this->i->db->query("SELECT tipoCambio INTO @tc FROM tarifas_tipocambio WHERE dtStart<=@created ORDER BY dtStart DESC LIMIT 1;");
        $this->i->db->query("SELECT COALESCE(fixedTC,@tc) INTO @tc FROM tarifas_grupos WHERE grupo=@gpo;");
        
        $this->i->db->query("SET @dlv = CONCAT('n',@lv)"); 
         
        $this->i->db->query("SET @sql = CONCAT(\"SELECT 
        	b.hotel, c.cat, a.grupo as grupoTfas, @hotel as hotelOriginal, @cat as catOriginal, @lv as lv, @moneda as moneda, @montoOriginal as montoOriginal, 
            @montoPagadoTotal as montoPagadoTotal, '$occup' as occQuote, @occOriginal as occOriginal, @noches as noches, @paxOriginal as paxOriginal,
            IF(COUNT(DISTINCT Fecha) != @noches OR c.grupo IS NULL,0,1) as validFlag,
            CASE
                WHEN $totOc > maxOcc OR ".$params['adultos']." > maxAdults OR ".($params['menores'] + $params['juniors'])." > maxChild THEN null
                WHEN '$occup' = '1.0.0' THEN ROUND(SUM(pax1 * IF(tarifa_pp=1,1,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '1.0.1' THEN ROUND(SUM(pax1 * IF(tarifa_pp=1,1,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '1.0.2' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '2.0.0' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '2.0.1' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '2.0.2' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '2.1.2' THEN ROUND(SUM((pax2 * IF(tarifa_pp=1,2,1) + COALESCE(paxMenor,0)) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '3.0.0' THEN ROUND(SUM(pax3 * IF(tarifa_pp=1,3,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '3.0.1' THEN ROUND(SUM(pax3 * IF(tarifa_pp=1,3,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                WHEN '$occup' = '4.0.0' THEN ROUND(SUM(pax4 * IF(tarifa_pp=1,4,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
            END as newAmmount,
            CASE
                WHEN $totOc > maxOcc OR ".$params['adultos']." > maxAdults OR ".($params['menores'] + $params['juniors'])." > maxChild THEN null
                WHEN '$occup' = '1.0.0' THEN ROUND(SUM(pax1 * IF(tarifa_pp=1,1,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '1.0.1' THEN ROUND(SUM(pax1 * IF(tarifa_pp=1,1,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '1.0.2' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '2.0.0' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '2.0.1' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '2.0.2' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '2.1.2' THEN ROUND(SUM((pax2 * IF(tarifa_pp=1,2,1) + COALESCE(paxMenor,0)) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '3.0.0' THEN ROUND(SUM(pax3 * IF(tarifa_pp=1,3,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '3.0.1' THEN ROUND(SUM(pax3 * IF(tarifa_pp=1,3,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                WHEN '$occup' = '4.0.0' THEN ROUND(SUM(pax4 * IF(tarifa_pp=1,4,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
            END as difAmmount,
            maxOcc, maxAdults, maxChild
        FROM
            tarifas_grupos a
                LEFT JOIN
            tarifas_descuentos b ON a.grupo = b.code
                LEFT JOIN
            (SELECT Fecha, b.* FROM Fechas a LEFT JOIN tarifas_netas b ON a.Fecha BETWEEN tw_inicio AND tw_fin WHERE Fecha BETWEEN @inicio AND ADDDATE(@fin,-1) AND @created BETWEEN netas_bw_inicio AND netas_bw_fin) c 
                ON b.grupo_netas = c.grupo AND b.hotel=c.hotel AND c.Fecha BETWEEN b.inicio AND b.fin
        		LEFT JOIN
        	cat_habitaciones h ON b.hotel=h.hotelCode AND c.cat=h.habCode
        WHERE
            a.grupo = @gpo 
            AND @created BETWEEN dsc_bw_inicio AND dsc_bw_fin 
            AND b.hotel='".$params['hotel']."' AND c.cat='".$params['cat']."'
            AND c.Fecha BETWEEN @inicio AND ADDDATE(@fin,-1)\")");
        
        $r = $this->i->db->query("SELECT @sql");
        $qr = $r->row_array();
        $qt = $str=str_replace("\n","",str_replace("\t","",$qr['@sql']));
        
        if( $q = $this->i->db->query($qt) ){

            $qR = $q->row_array();
            
            if( $qR['validFlag'] == '1' ){
                return modelResp( false, "Cotizacion obtenida", 'data', $qR, 'assist', $seguro );
            }else{
                return modelResp( true, "No es posible cotizar desde CYC, se debe realizar la consulta desde CIELO", 'error', $qt);
            }
        }else{
            return modelResp( true, "Error al realizar consulta", 'error', $this->i->db->error());
        }
    }
    
    public function empaquetarHospedaje( $itemId, $start = null, $end = null ){
        
        $this->i->db->query("SET @inicio = ".($start != null ? "'$start'" : "CURDATE()"));
        $this->i->db->query("SET @fin = ".($end != null ? "'$end'" : "CURDATE()"));
        
        $qdate = $start != null ? "@inicio" : "inicio";
        $qdate .= " as inicio, ";
        $qdate .= $end != null ? "@fin" : "fin";
        $qdate .= " as fin, ";
        
        $aq = $this->i->db->select("$qdate 1 as pax, sg_mdo as mdo, sg_cobertura as cobertura", false)->from('cycoasis_rsv.r_hoteles a')
                    ->join('cycoasis_rsv.r_items b','a.itemId = b.itemId', 'left')
                    ->join('cycoasis_rsv.r_seguros s','b.insuranceRelated = s.itemId', 'left')->where('a.itemId',$itemId)->get();
        $ad = $aq->row_array();
        
        $this->i->db->query("SELECT 
            hotel, categoria, if(gpoCC='OTLC',t.grupo,gpoCC), CAST(i.dtCreated as DATE) as created, inicio, fin, SUBSTRING(lv,1,1),
            b.moneda, monto, montoPagado+montoEnValidacion, CONCAT(adultos,'.',juniors,'.',menores), a.noches, adultos+juniors+menores,
            if(gpoCC='OTLC',1,0), adultos,juniors + menores, masterlocatorid
        INTO
        	@hotel, @cat, @gpo, @created, @inicio, @fin, @lv, @moneda, @montoOriginal, @montoPagadoTotal, @occOriginal, @noches, @paxOriginal, @isOtlc, @adlts, @menores, @masterloc
        FROM
        	cycoasis_rsv.r_items i 
        		LEFT JOIN
            cycoasis_rsv.r_hoteles a ON i.itemId=a.itemId
                LEFT JOIN
            cycoasis_rsv.r_monto b ON a.itemId = b.itemId
                LEFT JOIN
            tarifas_grupos t ON b.grupo=t.cieloUSD
        WHERE
            i.itemId = $itemId
        GROUP BY i.itemId");
            
        if( $start != null ){
            $this->i->db->query("SET @inicio = '$start'");
        }
        
        if( $end != null ){
            $this->i->db->query("SET @fin = '$end'");
        }
        
        $this->i->db->query("SET @noches = DATEDIFF(@fin,@inicio)");
            
        $this->i->db->query("SELECT tipoCambio INTO @tc FROM tarifas_tipocambio WHERE dtStart<=@created ORDER BY dtStart DESC LIMIT 1;");
        $this->i->db->query("SELECT COALESCE(fixedTC,@tc) INTO @tc FROM tarifas_grupos WHERE grupo=@gpo;");
        
        $levelQuotes = array();
        
        for( $lvx = 1; $lvx <= 5; $lvx++ ){
            
            $this->i->db->query("SET @dlv = CONCAT('n',$lvx)"); 
             
            $this->i->db->query("SET @sql = CONCAT(\"SELECT 
            	b.hotel, c.cat, a.grupo as grupoTfas, @dlv as nameLevel, $lvx as lv, @lv as lvOriginal, @moneda as moneda, @montoOriginal as montoOriginal, 
                @montoPagadoTotal as montoPagadoTotal, @occOriginal as occOriginal, @noches as noches, @paxOriginal as paxOriginal,
                IF(COUNT(DISTINCT Fecha) != @noches OR c.grupo IS NULL,0,1) as validFlag,
                CASE
                    WHEN @occOriginal = '1.0.0' THEN ROUND(SUM(pax1 * IF(tarifa_pp=1,1,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                    WHEN @occOriginal = '1.0.1' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                    WHEN @occOriginal = '1.0.2' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                    WHEN @occOriginal = '2.0.0' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                    WHEN @occOriginal = '2.0.1' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                    WHEN @occOriginal = '2.0.2' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                    WHEN @occOriginal = '3.0.0' THEN ROUND(SUM(pax3 * IF(tarifa_pp=1,3,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                    WHEN @occOriginal = '3.0.1' THEN ROUND(SUM(pax4 * IF(tarifa_pp=1,4,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1)),2)
                END as newAmmount,
                CASE
                    WHEN @occOriginal = '1.0.0' THEN ROUND(SUM(pax1 * IF(tarifa_pp=1,1,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                    WHEN @occOriginal = '1.0.1' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                    WHEN @occOriginal = '1.0.2' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                    WHEN @occOriginal = '2.0.0' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                    WHEN @occOriginal = '2.0.1' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                    WHEN @occOriginal = '2.0.2' THEN ROUND(SUM(pax2 * IF(tarifa_pp=1,2,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                    WHEN @occOriginal = '3.0.0' THEN ROUND(SUM(pax3 * IF(tarifa_pp=1,3,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                    WHEN @occOriginal = '3.0.1' THEN ROUND(SUM(pax4 * IF(tarifa_pp=1,4,1) * (1-if(@isOtlc = 0, \",@dlv,\", (1-((1-n1)*(1-0.2))))) * IF(@moneda='MXN',@tc,1))- @montoOriginal,2)
                END as difAmmount,
                maxOcc, maxAdults, maxChild, @tc as tc, @gpo as gpoTfas, @inicio as inicio, $itemId as itemId, @masterloc as masterlocator
            FROM
                tarifas_grupos a
                    LEFT JOIN
                tarifas_descuentos b ON a.grupo = b.code
                    LEFT JOIN
                (SELECT Fecha, b.* FROM Fechas a LEFT JOIN tarifas_netas b ON a.Fecha BETWEEN tw_inicio AND tw_fin WHERE Fecha BETWEEN @inicio AND ADDDATE(@fin,-1) AND @created BETWEEN netas_bw_inicio AND netas_bw_fin) c 
                    ON b.grupo_netas = c.grupo AND b.hotel=c.hotel AND c.Fecha BETWEEN b.inicio AND b.fin
            		LEFT JOIN
            	cat_habitaciones h ON b.hotel=h.hotelCode AND c.cat=h.habCode
            WHERE
                a.grupo = @gpo 
                AND @created BETWEEN dsc_bw_inicio AND dsc_bw_fin 
                AND b.hotel=@hotel AND c.cat=@cat
                AND c.Fecha BETWEEN @inicio AND ADDDATE(@fin,-1)\")");
            
            $r = $this->i->db->query("SELECT @sql");
            $qr = $r->row_array();
            $qt = $str=str_replace("\n","",str_replace("\t","",$qr['@sql']));
            
            if( $q = $this->i->db->query($qt) ){
            
                $qR = $q->row_array();
                
                if( $qR['validFlag'] == '1' ){
                    array_push( $levelQuotes, $qR);
                }else{
                    return modelResp( true, "No es posible cotizar desde CYC, se debe realizar la consulta desde CIELO", 'error', $qt);
                }
                
                
            }else{
                return modelResp( true, "Error al realizar consulta", 'error', $this->i->db->error());
            }
        }
        
        $packs = new Package_model();
        $quote = $packs->calcPackage( $levelQuotes );

        return modelResp( false, "Cotizacion obtenida", 'data', $levelQuotes, 'quote', $quote );
    }
    
    public function daypass( $data ){
        
        // VALIDACION DE DATOS
        $defParms = array('fecha');
        foreach( $defParms as $i => $f ){
            if( !isset( $data[$f] ) ){
                return modelResp( true, "No se recibio el parametro $f dentro de los datos", 'error', $data );
            }
        } 
        
        $params = array(
            "fecha" => date( "Y-m-d", strtotime( $data['fecha'] )),
            "dow" => date( "N", strtotime( $data['fecha'] )),
        );
        
        $quote = array();
        
        $this->i->db->query("SET @fecha = ".$data['fecha']);
        
        $this->i->db->from("cycoasis_rsv.cat_daypass12")
            ->where("CURDATE() BETWEEN dp_bw_inicio AND dp_bw_fin", NULL, FALSE)
            ->where("@fecha BETWEEN dp_tw_inicio AND dp_tw_fin", NULL, FALSE);
            
        if( $q = $this->i->db->get() ){
            
            if( $q->num_rows() == 0){
                return modelResp( true, 'No hay tarifas disponibles para esta fecha', 'error', array() ); 
            }else{
                
                $tarifas = $q->result_array();
                
                foreach( $tarifas as $hotel => $info ){
                    
                    $quote[$info['dp_hotel']] = $info;
                    $quote[$info['dp_hotel']]['isEvent'] = 0;
                    $quote[$info['dp_hotel']]['isSpecial'] = 0;
                    $quote[$info['dp_hotel']]['isBo'] = 0;
                    
                    foreach( $info as $field => $datos ){
                        
                        // DECODE JSON DATA
                        if( $field == 'dp_default' || $field == 'dp_event' || $field == 'dp_special' || $field == 'dp_local' || $field == 'dp_event_dates' || $field == 'dp_special_dow' || $field == 'dp_blackouts' ){
                            
                            $quote[$info['dp_hotel']][$field] = json_decode($datos);
                            
                            // DECODE DATES
                            if( $field == 'dp_event_dates' || $field == 'dp_blackouts' ){
                                foreach( $quote[$info['dp_hotel']][$field] as $i => $x ){
                                    $tmpDate = date( "Y-m-d", strtotime( $x ) );
                                    $quote[$info['dp_hotel']][$field][$i] = $tmpDate;
                                    
                                    // DETECT EVENTS AND BOs
                                    if( $tmpDate == $params['fecha'] ){
                                        if( $field == 'dp_event_dates' ){
                                            $quote[$info['dp_hotel']]['isEvent'] = 1;
                                        }else{
                                            $quote[$info['dp_hotel']]['isBo'] = 1;
                                        }
                                    }
                                }
                            }
                            
                            // DETECT SPECIALS
                            
                            if( $field == 'dp_special_dow' && in_array( $params['dow'], $quote[$info['dp_hotel']][$field] ) ){
                                $quote[$info['dp_hotel']]['isSpecial'] = 1;
                            }
                            
                        }
                    }
                }
                
                return modelResp( false, 'Cotizacion Obtenida', 'data', $quote );
            }
            
            
        }else{
           return modelResp( true, 'Error al obtener informacion', 'error', $this->i->db->error());  
        }
    }
    
}

class Package_model extends Cotizador_model{
    
    protected $insPaxRate = 6.5 * 1.16;
    
    function __construct() {
        parent::__construct();
    }
    
    protected function calcPackage( $data ){
        
        $ref = $data[0];
        
        $cl = intVal($ref['lvOriginal']);
        $t = floatval($ref['tc']);
        $usd = strtolower($ref['moneda']) == 'usd';
        $grupo = $ref['gpoTfas'];
        
        $days = $ref['noches'] + 1;
        $insQ = ceil( $days / 8 );
        $insRate = $this->insPaxRate * $insQ * ( $usd ? 1 : $t + 1);
        
        if( $cl == 4 ){
          return array();
        }
        
        $result = array(
          "level"       => 'n1',
          "lnumber"     => 1,
          "rate"        => 0,
          "hotelRate"   => 0,
          "tipoCambio"  => ($t + 1),
          "insRate"     => 0,
          "pax"         => 0,
          "total"       => 0,
          "dif"         => 0,
          "cRate"       => 0,
          "paqs"        => $insQ,
          "days"        => $days
        );
        
        // Nivel a comparar
        $cLevel = .05;
        $lName = $cl == 1 ? 'neta' : 'n' + ($cl-1);
        $lCompare = $ref['montoOriginal'];
    
        // Define ocupacion
        $pax = $ref['paxOriginal'];
        
        // Evalua minimo de pax para seguro o descuento
        $startPax = $pax > 4 ? $pax : 4;
        for( $p = $startPax; $p >= $pax; $p -= 2 ){
        
        
          $result = array(
            "ogLevel"       => $cl,
            "level"         => 'n' + $this->levelCorrect(($insRate * $p) / $lCompare + (.05 * ($cl-1)), $cl),
            "lnumber"       => $this->levelCorrect(($insRate * $p) / $lCompare + (.05 * ($cl-1)), $cl),
            "rate"          => ($this->hotelVal($data[$this->levelCorrect(($insRate * $p) / $lCompare + (.05 * ($cl-1)), $cl) - 1 ?? 0], $t, $usd)),
            "hotelRate"     => ($this->hotelVal($data[$cl-1], $t, $usd) - ($p * $insRate)),
            "tipoCambio"    => ($t + 1),
            "insRate"       => ($this->insPaxRate * $p * ($usd ? 1 : $t + 1)),
            "levelRate"     => ($data[$this->levelCorrect(($insRate * $p) / $lCompare + (.05 * ($cl-1)), $cl) - 1]['newAmmount']),
            "pax"           => $p,
            "total"         => ($this->hotelVal($data[$this->levelCorrect(($insRate * $p) / $lCompare + (.05 * ($cl-1)), $cl) - 1 ?? 0], $t, $usd) + $p * $insRate),
            "dif"           => ($this->hotelVal($data[$cl-1], $t, $usd) - ($this->hotelVal($data[$this->levelCorrect(($insRate * $p) / $lCompare + (.05 * ($cl-1)), $cl) - 1 ?? 0], $t, $usd) + $p * $insRate)),
            "cRate"         => ($this->hotelVal($data[$cl-1], $t, $usd)),
            "paqs"          => $insQ,
            "days"          => $days
          );
          
          
    
          if( ($insRate * $p) / $lCompare <= $cLevel ){
    
            return $this->buildCorrectionPack($result, $grupo, $ref);
          }
    
        }

        
        return $this->buildCorrectionPack($result, $grupo, $ref);
    }
    
    private function buildCorrectionPack( $result, $g, $ref, $flag = false ){
        
        $gpoQ = $this->i->db->from('tarifas_grupos')->where('grupo', $g)->get();
        $gpo = $gpoQ->row_array();
        
        $monto = array(
                "lv" => $gpo['code'.$result['level']],
                "lv_name" => $gpo['l'.$result['level'].'_name'],
                "monto" => $result['hotelRate'],
                "lv_goalCode" => $gpo['code'.$result['ogLevel']],
                "lv_goalName" => $gpo['l'.$result['ogLevel'].'_name'],
                "lv_originalRate" => $result['cRate'],
                "importeManual" => 1
            );
        
        $insurance = array();
        
        for( $ins = 1; $ins <= $result['paqs']; $ins++ ){
            
            $tmpIns =  $this->insuranceBuilder( $ref, $result, $ins );
            
            array_push($insurance, $tmpIns);
        }
        
        return array('quote' => $result, 'monto' => $monto, 'insurance' => $insurance);
    }
    
    public function insuranceBuilder( $data, $insurance, $i = 1000 ){
        
        // $i != 1000 es inclusion
        
        $date=date_create($data['inicio']);
        if( $i != 1000 ){
            date_add($date,date_interval_create_from_date_string((($i-1) * 8)." days"));
        }
        $start = date_format($date,"Y-m-d");
        
        $date=date_create($data[$i != 1000 ? 'inicio' : 'fin']);
        if( $i != 1000 ){
            date_add($date,date_interval_create_from_date_string(($i * 8 - 1)." days"));
        }
        $end = date_format($date,"Y-m-d");
        
        $tmpIns = array(
                "item" => array(
                        "sg_cobertura"      => $i != 1000 ? "inclusion" : $insurance['cobertura'],
                        "sg_fin"            => $end,
                        "sg_hotel"          => $data['hotel'],
                        "sg_inicio"         => $start,
                        "sg_mdo"            => $i != 1000 ? "nacional" : $insurance['mdo'],
                        "sg_pax"            => $insurance['pax'],
                    ),
                "itemId" => $data['itemId'],
                "masterLoc" => $data['masterlocator'],
                "type" => $i != 1000 ? "seguro-i" : 'seguro',
                "monto" => array(
                        "moneda" => $data['moneda'],
                        "monto" => $insurance['insRate']
                    )
            );

        if( $i == 1000 ){
            $tmpIns['item']['sg_itemRelated'] = $data['related'];
        }           
        return $tmpIns;
    }
    
    private function levelCorrect( $d, $cl ){

    $levels = array(.05, .1, .15, .2);
    $i = 2;

    foreach( $levels as $index => $l ){
      if( $d <= $l ){
        if( $i == 3 && $cl == 2 && $d <= .07 ){
          return 5;
        }

        return $i;
      }

      $i++;
    }

    return 4;

  }
  
  protected function  hotelVal( $m, $t, $usd ){

    if( $usd ){
      return +( $m['montoOriginal'] );
    }else{
      return +( 1 * $m['montoOriginal'] );
    }
  }
}

class CotizadorOtlc_model extends Cotizador_model{
    
    function __construct() {
        parent::__construct();
    }
    
    private function getPriceFromQuote( $habs, $d, $nr, $selected ){
        
        $result = array();
        
        $filter = $selected['hotel'] != null && $selected['cat'] != null;
        
        if( $filter && ($selected['hotel'] != $d['hotel'] || $selected['cat'] != $d['habCode']) ){
            return false;
        }
        
        $json = json_decode($d['jsonData'], true);
            
        foreach( $habs as $hi => $h ){
            
            $tmpRslt = array(
                    "lv" => '',
                    "monto" => 0,
                    "montoOriginal" => 0,
                    "err" => false,
                    "obs" => "",
                    'selected' => $selected,
                    'flag' => $filter && $selected['hotel'] == $d['hotel'] && $selected['cat'] == $d['habCode'],
                    // 'json' => $json
                );
                
            if( intVal($d['minNights']) > count($json) ){
                $tmpRslt['err'] = true;
                $tmpRslt['obs'] = "Estancia minima de ".$d['minNights']." noches requerida || ";
            }
            
            if( ($h['adultos'] ?? 0) > intval($d['maxAdults']) || (($h['menores'] ?? 0) + ($h['juniors'] ?? 0)) > intval($d['maxChild']) || (($h['adultos'] ?? 0) + ($h['menores'] ?? 0) + ($h['juniors'] ?? 0)) > intval($d['maxOcc']) ){
                $tmpRslt['err'] = true;
                $tmpRslt['obs'] = "Ocupacion no válida || ";
            }
            
                
            $pax = 'pax1';
            $xm = false;
            $occup = ($h['adultos'] ?? 0).".".($h['juniors'] ?? 0).".".($h['menores'] ?? 0);
            switch( $occup ){
                case '1.0.0':
                    $pax = 'pax1';
                    break;
                case '1.0.1':
                case '1.0.2':
                case '1.0.3':
                case '2.0.0':
                case '2.0.1':
                case '2.0.2':
                    $pax = 'pax2';
                    break;
                case '2.1.2':
                    $pax = 'pax2';
                    $xm = true;
                    break;
                case '3.0.0':
                case '3.0.1':
                case '3.0.2':
                    $pax = 'pax3';
                    break;
                case '3.1.2':
                    $pax = 'pax3';
                    $xm = true;
                    break;
                case '4.0.0':
                case '4.0.1':
                case '4.0.2':
                    $pax = 'pax4';
                    break;
                case '4.1.2':
                    $pax = 'pax4';
                    $xm = true;
                    break;
            }
            
            
            
            foreach( $json as $f => $date ){
                if( $date['isClosed'] == 1 ){
                    $tmpRslt['err'] = true;
                    $tmpRslt['obs'] = "Fecha $f Cerrada || ";
                }
                $tmpRslt['montoOriginal'] += $date['precio'][$pax] + ($xm ? $date['precio']['paxMenor'] : 0);
                $tmpRslt['monto'] += ($date['precio'][$pax] + ($xm ? $date['precio']['paxMenor'] : 0) )* (1 - $date['n1']['descuento']) * .75;
                $tmpRslt['lv'] = $date['n2']['code'];
            }
            
            $result[$hi] = $tmpRslt;
        }
        
        return $result;
        
    }
    
    private function getZdUser( $data ) {
        
        $zd = model_zendesk();
        
        $filter = str_replace(' ', '%20', $data['correo']);
        $response = $zd->getData( 'https://oasishoteles.zendesk.com/api/v2/users/search.json?query='.$filter);
        
        $result = json_decode(json_encode($response['data']->{'users'}),true);
        
        if( count($result) > 0 ){
            return $result[0];
        }else{
            
            $url = 'https://oasishoteles.zendesk.com/api/v2/users/create_or_update.json';
            $arr = array(
                "user" => array(
                    "name" => $data['nombre']." ".$data['apellido'], 
                    "email" => $data['correo'], 
                    "phone" => '',
                    "user_fields" => array(
                            "id_pais"          => 1,
                            "idioma_cliente"   => ($data['isEnglish'] ?? false) ? 'idioma_en' : 'idioma_es',
                            "nacionalidad"     => "nacional",
                            "pais"             => "MEXICO",
                            "whatsapp"         => "",
                        ),
                    "verified" => true));
                    
            $json = json_encode($arr);
            $response = $zd->postData( $url, $json );
            
            $result = json_decode(json_encode($response['data']->{'user'}),true);
            
            return $result;

        }
 
    }
    
    private function prepareRsv( $q, $data ){
        
        // var_dump($data);
        
        // $data['rsvData']
        
        if( !isset($data['rsvData']) ){
          return modelResp(true, 'No se obtuvo información para generar la reserva', 'error', $data['rsvData']);
        }
        if( !isset($data['rsvData']['nombre']) ){
         return modelResp(true, 'No se obtuvo el nombre del titular para generar la reserva', 'error', $data['rsvData']);
        }
        if( !isset($data['rsvData']['apellido']) ){
         return modelResp(true, 'No se obtuvo el apellido del titular para generar la reserva', 'error', $data['rsvData']);
        }
        if( !isset($data['rsvData']['correo']) ){
         return modelResp(true, 'No se obtuvo el correo electrónico para generar la reserva', 'error', $data['rsvData']);
        }
        
        $zdUser = $this->getZdUser($data['rsvData']);
        
        $rsvForm = array(
                "nacionalidad"      =>    'nacional',
                "isUSD"             =>    $data['isUsd'],
                "hasInsurance"      =>    false,
                "hasInclusionIns"   =>    false,
                "hasTransfer"       =>    false,
                "newMaster"         =>    true,
                "masterloc"         =>    'noLoc',
                "zdTicket"          =>    null,
                "rsvType"           =>    'hotel',
                "data"              =>    array(),
                "masterdata"        =>    array(
                    "nombreCliente"     =>    $data['rsvData']['nombre']." ".$data['rsvData']['apellido'], 
                    "telCliente"        =>    '', 
                    "celCliente"        =>    '', 
                    "waCliente"         =>    '', 
                    "correoCliente"     =>    $data['rsvData']['correo'], 
                    "zdUserId"          =>    $zdUser['id'] ?? 'ND', 
                    "esNacional"        =>    true, 
                    "languaje"          =>    ($data['rsvData']['isEnglish'] ?? false) ? 'idioma_en' : 'idioma_es', 
                    "hasTransfer"       =>    false, 
                    "xldPol"            =>    $q['extra']['grupo']['xldPolicy'], 
                    "orId"              =>    '', 
                    "orLevel"           =>    '', 
                    "clientePaisId"     =>    1, 
                    "clientePais"       =>    'MEXICO', 
                    ),
            );
            
        $habs = array();
        foreach( $q['data'][0]['prices'] as $h => $hab ){
            $htl = $q['data'][0];
            $gp = $q['extra']['grupo'];
            
            $date1 = new DateTime($data['inicio']);
            $date2 = new DateTime($data['fin']);
            $nights = $date2->diff($date1)->format("%a"); 
            
            $habs[$h] = array(
                    "hotel" =>        array(
                        "item"  =>  array(
                          "itemType"    =>      1,
                          "isQuote"     =>      1,
                          "zdTicket"    =>      '',
                          "external"    =>      1
                        ),
                        "hotel" =>  array(
                          "hotel"           =>  $htl['hotel'],
                          "categoria"       =>  $htl['habCode'],
                          "mdo"             =>  $gp['mayorista'],
                          "agencia"         =>  $htl['tarifa_pp'] == '1' ? ($data['isUsd'] ? 'IZATLC' : 'IZAMTLC' ) : ($data['isUsd'] ? 'IZAMTLCEP' : 'IZAMTLEP' ),
                          "gpoTfa"          =>  ($data['isUsd'] ? $gp['cieloUSD'] : $gp['cieloMXN'] ),
                          "adultos"         =>  $data['habitaciones'][$h]['adultos'] ?? 0,
                          "juniors"         =>  $data['habitaciones'][$h]['juniors'] ?? 0,
                          "menores"         =>  $data['habitaciones'][$h]['menores'] ?? 0,
                          "inicio"          =>  $data['inicio'],
                          "fin"             =>  $data['fin'],
                          "noches"          =>  $nights,
                          "notasHotel"      =>  'Reserva Miembro OTLC',
                          "isLocal"         =>  0,
                          "isNR"            =>  0,
                          "gpoCC"           =>  'OTLC',
                          "bedPreference"   =>  '',
                          "titular"         =>  $data['rsvData']['nombre']." ".$data['rsvData']['apellido'],
                          "htl_nombre_1"    =>  $data['rsvData']['nombre'],
                          "htl_apellido_1"  =>  $data['rsvData']['apellido'],
                        ),
                        "monto" =>  array(
                                "monto"             =>  moneyVal($hab['monto'] * ($data['isUsd'] ? 1 : floatval($htl['tipoCambio']))),
                                "montoOriginal"     =>  moneyVal($hab['montoOriginal'] * ($data['isUsd'] ? 1 : floatval($htl['tipoCambio']))),
                                "montoParcial"      =>  moneyVal($hab['monto'] * ($data['isUsd'] ? 1 : floatval($htl['tipoCambio']))),
                                "moneda"            =>  $data['isUsd'] ? 'USD' : 'MXN',
                                "lv"                =>  $hab['lv'],
                                "lv_name"           =>  'OTLC',
                                "grupo"             =>  ($data['isUsd'] ? $gp['cieloUSD'] : $gp['cieloMXN'] ),
                                "grupoTfas"         =>  $gp['grupoCielo'],
                                "promo"             =>  'C',
                                "tipoCambio"        =>  moneyVal(floatval($htl['tipoCambio'])),
                                "lv_goalCode"       =>  $hab['lv'],
                                "lv_goalName"       =>  'OTLC',
                                "lv_originalRate"   =>  moneyVal($hab['monto'] * ($data['isUsd'] ? 1 : floatval($htl['tipoCambio']))),
                                "importeManual"     =>  0,
                            )
                      ),
                      "pax" =>      ($data['habitaciones'][$h]['adultos'] ?? 0) + ($data['habitaciones'][$h]['juniors'] ?? 0) + ($data['habitaciones'][$h]['menores'] ?? 0),
                );
        }
        
        $rsvForm['data'] = $habs;
        
        return modelResp(false, 'Data rsva', 'data', $rsvForm, 'extra', array('isNewRsv' => true));
    }
    
    public function getQuote( $data ){
        
        $isOtlc = false;
        $isRsv = false;
            
        $bw = isset($data['bwDate']) ? ($data['bwDate'] == null ? '' : $data['bwDate']) : '';
    
        $comercial = isset( $data['comercial'] ) ? $data['comercial'] : false;
        
        // IF OTLC
        if( $data['grupo']['grupo'] == 'OTLC' ){
            $isOtlc = true;
            $comercial = false;
            if( $qotlc = $this->i->db->query("SELECT grupo FROM tarifas_grupos WHERE activo=1 AND mainCampaign=1 AND CURDATE() BETWEEN bwInicio AND bwFin ORDER BY isLocal DESC LIMIT 1") ){
                if( $qotlc->num_rows() > 0 ){
                    $otg = $qotlc->row_array();
                    $data['grupo']['grupo'] = $otg['grupo'];
                }else{
                    return modelResp(true, "No existen campañas activa para OTLC", 'error', array());
                }
            }else{
                return modelResp(true, "Error al encontrar campaña activa para OTLC", 'error', $this->i->db->error());
            }
        }
        
        $cotizacion = $this->hospedaje( $data['inicio'], $data['fin'], $data['grupo']['grupo'], $data['noRestrict'], $bw, $comercial );
        $cotizacion = $this->filterHotel( $cotizacion, $data['hotel'] ?? null );
        
        // IF OTLC
        if( $isOtlc ){
            $cotizacion = $this->filterOtlc( $cotizacion );
        }
        
        foreach( $cotizacion['data'] as $i => $c ){
            $cotizacion['data'][$i]['prices'] = $this->getPriceFromQuote( $data['habitaciones'], $c, $data['noRestrict'], array( 'hotel' => $data['hotel'] ?? null, 'cat' => $data['cat'] ?? null ) );
            unset($cotizacion['data'][$i]['jsonData']);
            
            if( !$cotizacion['data'][$i]['prices'] ){
                unset($cotizacion['data'][$i]);
            }else{
                if( $cotizacion['data'][$i]['prices']['hab1']['flag'] ){
                    $isRsv = true;
                    continue;
                }
            }
            
            
        }
        
        if( $isRsv ){
            return $this->prepareRsv( $cotizacion, $data ); 
        }
          
        
        if( !$cotizacion['err'] ){    
            return modelResp(false, $cotizacion['msg'], 'data', $cotizacion['data'], 'extra', $cotizacion['extra']);
        }else{
            return modelResp(true, $cotizacion['msg'], 'error', $cotizacion['error']);
        }
    }
}