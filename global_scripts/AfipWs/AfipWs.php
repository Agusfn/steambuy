<?php
/*
Clase que genera una solicitud de acceso a la AFIP (si no se solicitó una) y permite realizar operaciones de diferentes web services. 
(Sólo WSFE y WSAA contemplados)

TRA: Ticket de solicitud de acceso, TA: Ticket de Acceso.

$entity: Persona a nombre de la cual se hacen las solicitudes. 1: Agustín (CUIT 20396674182), 2: Tomás (CUIT 20375378117)
$testing: Modo debug o test. FALSE: Producción, TRUE: Testing. Testing genera archivos en /debug_data/

REVISAR FUNCION firmarTRA CUANDO ESTO SE USE EN UN SERVIDOR (sacar $_SERVER['DOCUMENT_ROOT'])

--- COD. ERRORES ---
solicitarTA(); 1=Error firmando el TRA, 2=Error solicitando el TA en SOAP (ver error_text), 3=Error guardando el TA

*/


ini_set("soap.wsdl_cache_enabled", "0");

class AfipWsfe
{
	const TESTING = FALSE; 
	
	const request_files_dir = "global_scripts/AfipWs/temp_request_files/";
	const debug_files_dir = "global_scripts/AfipWs/debug_data/";
	
	const wsaa_dir = "https://wsaa.afip.gov.ar/ws/services/LoginCms";
	const wsaa_dir_test = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms";
	const wsfe_dir = "https://servicios1.afip.gov.ar/wsfev1/service.asmx";
	const wsfe_dir_test = "https://wswhomo.afip.gov.ar/wsfev1/service.asmx";
	
	public $CUIT = array("1"=>20396674182, "2"=>20375378117);
	public $IIBB = array("1"=>"20-39667418-2", "2"=>"20-37537811-7");
	private $token = array("1"=>"", "2"=>"");
	private $sign = array("1"=>"", "2"=>"");
	private $auth = array("1"=>"", "2"=>"");
	
	private $client;
	
	public $error_text; // Usado en: ultimoNroCbteAut(); detallesCbte(); generarCbte();

	function __construct($entities = array("1","2")) {

		foreach($entities as $entity) {
			if(!$this->TAVigente($entity)) {
				$solicitud = $this->solicitarTA($entity);
				if(!$solicitud["result"]) {
					throw new Exception("Error AfipWsfe: No se pudo solicitar el TA para entity ".$entity.". solicitarTA(); Error code: ".$solicitud["error_code"]." Texto: ".$solicitud["error_text"]);
					return;
				}
			}
			if(!$this->cargarTA($entity)) {
				throw new Exception("Error AfipWsfe: No se pudo cargar el TA para entity ".$entity.". cargarTA();");
				return;
			}
		}
		//Ejecutar cliente WSFE
		$wsfe_dir = (self::TESTING ? self::wsfe_dir_test : self::wsfe_dir)."?wsdl";
		if($headers = @get_headers($wsfe_dir)) {
			$this->client = new SoapClient($wsfe_dir, array(
				'soap_version'   => SOAP_1_2,
				'location'       => self::TESTING ? self::wsfe_dir_test : self::wsfe_dir,
				'trace'          => 1,
				'exceptions'     => 0
			));	
			if(!$this->checkDummy())	 {
				throw new Exception("Error AfipWsfe: Los servidores del ws WSFE de AFIP no están funcionando.");
				return;
			}
		} else {
			throw new Exception("Error AfipWsfe: No se pudo cargar la URL del WSDL en ".(self::TESTING ? "homologación" : "producción"));
			return;
		}
	}
	
	// --- Funciones del WSFE ---

	public function generarCbte($entity, $cbtetipo, $importe) {
		
		$return = array("CAE"=>"", "vtoCAE"=>"", "nro"=>"", "fecha"=>"");
		
		if(!is_numeric($cbtetipo) || !is_numeric($importe)) {
			$this->error_text = "Tipo de cbte. o precio no numérico.";
			return false;	
		}
		
		$ultCbte = $this->ultimoNroCbteAut($entity);
		if($ultCbte === false) return false; // $this->error_text queda guardado
		$sgteCbte = $ultCbte + 1;

		$today = date("Ymd");
		$datos = array(
		"Auth"=>$this->auth[$entity],
		'FeCAEReq' => array(
			'FeCabReq' => array('CantReg' => 1,'PtoVta' => 2,'CbteTipo' => $cbtetipo),  
            'FeDetReq' => array(
                'FECAEDetRequest' => array(
                    array(
                        'Concepto' => 1,
                        'DocTipo' => 99, 
                        'DocNro' => "0",
                        'CbteDesde' => $sgteCbte,
                        'CbteHasta' => $sgteCbte,
                        'CbteFch' => $today,
                        'ImpTotal' => $importe,
                        'ImpTotConc' => "0.00",
                        'ImpNeto' => $importe,
                        'ImpOpEx' => "0.00",
                        'ImpTrib' => "0.00",
                        'ImpIVA' => "0.00",
                        'MonId' => "PES",
                        'MonCotiz' => "1",
                    ),
                )   
            )   
        ));
		$result = $this->client->FECAESolicitar($datos);

		if(!is_soap_fault($result)) {
			if($result->FECAESolicitarResult->FeCabResp->Resultado == "A") {
				$return["CAE"] = $result->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE;
				$return["vtoCAE"] = $result->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto;
				$return["nro"] = $sgteCbte;
				$return["fecha"] = $today;
				return $return;
			} else {
				file_put_contents("debug_data/FECAESolicitar_rejects.txt", date("d/m/Y H:i:s")."\r\n".print_r($result->FECAESolicitarResult, true)."\r\n\r\n\r\n", FILE_APPEND);
				$this->error_text = "Autorización AFIP rechazada. .".print_r($result->FECAESolicitarResult, true).".  Log guardado en debug_data/FECAESolicitar_rejects.txt ".date("d/m/Y H:i:s");
				return false;
			}
		}
		else {
			$ultCbte2 = $this->ultimoNroCbteAut($entity);
			if($ultCbte2 !== false) {
				if($ultCbte2 == $sgteCbte) {
					$detalles = $this->detallesCbte($entity, $cbtetipo, $ultCbte2);
					if($detalles !== false) {
						$return["CAE"] = $detalles["CodAutorizacion"];
						$return["vtoCAE"] = $detalles["FchVto"];
					}
					return $return;
				} else {
					$this->error_text = "SOAP Error: ".$result->faultcode." -- ".$result->faultstring;
					return false;
				}
			} else {
				$this->error_text = "Error SOAP FECAESolicitar. Luego error intentando obtener el último número de cbte para verificar.";
				return false;
			}
		}	
	}
	
	

	public function ultimoNroCbteAut($entity) {	
		$result = $this->client->FECompUltimoAutorizado(array("Auth" => $this->auth[$entity],"PtoVta" => 2,"CbteTipo" => 11));
		if(is_soap_fault($result)) {
			$this->error_text = "SOAP Error: ".$result->faultcode." -- ".$result->faultstring;
			return false;
		} else return $result->FECompUltimoAutorizadoResult->CbteNro;
	}

	public function detallesCbte($entity, $cbtetipo, $cbtenro) {
		$result = $this->client->FECompConsultar(array(
		"Auth" => $this->auth[$entity],
		"FeCompConsReq"=>array(
			"CbteTipo"=>$cbtetipo,
			"CbteNro"=>$cbtenro,
			"PtoVta"=>"2")
		));
		if(is_soap_fault($result)) {
			$this->error_text = "SOAP Error: ".$result->faultcode." -- ".$result->faultstring;
			return false;
		} else return (array)$result->FECompConsultarResult->ResultGet;
	}
	
	public function checkDummy() {
		$result = $this->client->FEDummy();
		if(is_soap_fault($result)) return false;
		else {
			$status = $result->FEDummyResult;
			if($status->AppServer == "OK" && $status->DbServer == "OK" && $status->AuthServer == "OK") return true;
			else return false;
		}
	}
	
	public function test($entity) {
		$result = $this->client->FEParamGetTiposMonedas(array('Auth' => $this->auth[$entity]));
		if(is_soap_fault($result)) {
			$this->error_text = "SOAP Error: ".$result->faultcode." -- ".$result->faultstring;
			return false;
		} else return $result;	
	}
	
	
	// Funciones de WSAA ticket de acceso
	
	public function TAVigente($entity) {
		$TAFile = (self::TESTING) ? "TA-test.xml" : "TA.xml";
		$TAFileDir = ROOT_LEVEL."global_scripts/AfipWs/resources/TA/".$this->CUIT[$entity]."/".$TAFile;
		if(file_exists($TAFileDir)) {
			$TAxml = simplexml_load_file($TAFileDir);
			if(isset($TAxml->header->expirationTime) && isset($TAxml->credentials->token) && isset($TAxml->credentials->sign)) {
				$expTime = strtotime($TAxml->header->expirationTime);
				$currentTime = date("U");
				if(intval($expTime - $currentTime) > 0) return true; 
				else return false;
			} else return false;
		} else return false;
	}
	
	
	public function solicitarTA($entity) {
		$return = array("result"=>null, "error_code" => 0, "error_text"=>"");
		
		$this->crearTRA();
		if($CMS = $this->firmarTRA($entity)) {
			$TA = $this->llamarWSAA($CMS);
			if($TA["result"] == true) {
				$TAFileDir = ROOT_LEVEL."global_scripts/AfipWs/resources/TA/".$this->CUIT[$entity]."/".(self::TESTING ? "TA-test.xml" : "TA.xml");
				if(!file_put_contents($TAFileDir, $TA["return_text"])) {
					$return["result"] = false;
					$return["error_code"] = 3;
					return $return;	
				} else {
					$return["result"] = true;
					return $return;
				}
			} else if($TA["result"] == false) {
				$return["result"] = false;
				$return["error_code"] = 2;
				$return["error_text"] = $TA["return_text"];
				return $return;		
			}
		} else {
			$return["result"] = false;
			$return["error_code"] = 1;
			return $return;	
		}

	}
	
	
	public function cargarTA($entity) {
		$TAFileDir = ROOT_LEVEL."global_scripts/AfipWs/resources/TA/".$this->CUIT[$entity]."/".(self::TESTING ? "TA-test.xml" : "TA.xml");
		if(file_exists($TAFileDir)) {
			if($TAxml = simplexml_load_file($TAFileDir)) {
				$this->token[$entity] = $TAxml->credentials->token;
				$this->sign[$entity] = $TAxml->credentials->sign;
				$this->auth[$entity] = array("Token" => $this->token[$entity], "Sign" => $this->sign[$entity], "Cuit" => $this->CUIT[$entity]);
				return true;
			} else return false;
		} else return false;
	}

	
	// Funciones internas para solicitud de TA por medio de WSAA
	
	private function crearTRA()
	{
		$TRA = new SimpleXMLElement(
			'<?xml version="1.0" encoding="UTF-8"?>' .
			'<loginTicketRequest version="1.0">'.
			'</loginTicketRequest>');
	  	$TRA->addChild("header");
	  	$TRA->header->addChild("uniqueId",date("U"));
	  	$TRA->header->addChild("generationTime",date("c",date("U")-60));
	  	$TRA->header->addChild("expirationTime",date("c",date("U")+60));
	  	$TRA->addChild('service', "wsfe");
	  	$TRA->asXML(ROOT_LEVEL.self::request_files_dir."TRA.xml");
	}
	
	private function firmarTRA($entity)
	{
		$cert_location = ROOT_LEVEL."global_scripts/AfipWs/resources/certificates/".$this->CUIT[$entity]."/".(self::TESTING ? "test" : "prod")."/";
		
		$sign = openssl_pkcs7_sign(realpath(ROOT_LEVEL.self::request_files_dir."TRA.xml"), ROOT_LEVEL.self::request_files_dir."TRA.tmp", "file://".realpath($cert_location."certificado.crt"), array("file://".realpath($cert_location."clave.key"), "xxxxx"), array(), !PKCS7_DETACHED);
	  	if (!$sign) {
			unlink(ROOT_LEVEL.self::request_files_dir."TRA.xml");
			return false;
		}
		$signed = file_get_contents(ROOT_LEVEL.self::request_files_dir."TRA.tmp");
		$CMS = preg_replace("/^(.*\n){5}/", "", $signed);
		unlink(ROOT_LEVEL.self::request_files_dir."TRA.xml");
	  	unlink(ROOT_LEVEL.self::request_files_dir."TRA.tmp");
		return $CMS;
	}
	
	
	private function llamarWSAA($CMS) {
		$return = array("result"=>null, "return_text"=>"");
		
		$wsdl_dir = self::TESTING ? self::wsaa_dir_test."?wsdl" : self::wsaa_dir."?wsdl";
		$client = new SoapClient($wsdl_dir, array(
			'soap_version'   => SOAP_1_2,
			'location'       => self::TESTING ? self::wsaa_dir_test : self::wsaa_dir,
			'trace'          => 1,
			'exceptions'     => 0
		)); 
		$results = $client->loginCms(array('in0' => $CMS));
		if(self::TESTING) {
			file_put_contents(ROOT_LEVEL.self::debug_files_dir."request-loginCms.xml",$client->__getLastRequest());
	  		file_put_contents(ROOT_LEVEL.self::debug_files_dir."response-loginCms.xml",$client->__getLastResponse());
		}
	  	if (is_soap_fault($results)) {
			$return["result"] = false;
			$return["return_text"] = "SOAP Fault--".$results->faultcode."--".$results->faultstring;
			return $return;
		}
		$return["result"] = true;
		$return["return_text"] = $results->loginCmsReturn;
	  	return $return;
	}
	
	
}

?>