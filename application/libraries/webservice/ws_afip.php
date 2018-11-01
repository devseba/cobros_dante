<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

define ("URL_LIB_WS", "application/libraries/webservice");
define ("CuitE", "30518967009");


include('ta/wsaa-client.php');

	function xmlstr_to_array($xmlstr) { 
	  $doc = new DOMDocument();
	  $doc->loadXML($xmlstr);
	  return domnode_to_array($doc->documentElement);
	}
	
	function domnode_to_array($node) { 
	  $output = array();
	  switch ($node->nodeType) 
	  {
		   case XML_CDATA_SECTION_NODE:
		   case XML_TEXT_NODE:
		    $output = trim($node->textContent);
		   break;
		   case XML_ELEMENT_NODE:
		    for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) { 
		     $child = $node->childNodes->item($i);
		     $v = domnode_to_array($child);
		     if(isset($child->tagName)) {
		       $t = $child->tagName;
		       if(!isset($output[$t])) {
		        $output[$t] = array();
		       }
		       $output[$t][] = $v;
		     }
		     elseif($v) {
		      $output = (string) $v;
		     }
		    }
		    if(is_array($output)) {
		     if($node->attributes->length) {
		      $a = array();
		      foreach($node->attributes as $attrName => $attrNode) {
		       $a[$attrName] = (string) $attrNode->value;
		      }
		      $output['@attributes'] = $a;
		     }
		     foreach ($output as $t => $v) {
		      if(is_array($v) && count($v)==1 && $t!='@attributes') {
		       $output[$t] = $v[0];
		      }
		     }
		    }
		   break;
	  }
	  return $output;
	}



class ws_afip {
	/*************************************************************************************************
	********************************** Funciones Varias **********************************************
	*************************************************************************************************/
	

	function roundUpToAny($n,$x) {
	    return (round($n)%$x === 0) ? round($n) : round(($n+$x/2)/$x)*$x;
	}



	function dummy($urlafip)
	{   

		$xml = '<soapenv:Envelope
				xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:ar="http://ar.gov.afip.dif.FEV1/">
				 <soapenv:Header/>
				 <soapenv:Body>
				 <ar:FEDummy/>
				 </soapenv:Body>
				</soapenv:Envelope>';


		$headers = array(
			"POST  HTTP/1.1",
			"Host: hostname",
			"Content-type: text/xml",
			"charset: UTF-8",
			//"SOAPAction: FEDummy",
			"urn: FEDummy",
			//"urn: consultarPuntosVentaCAE",
			"Content-length: ".strlen($xml)
		);
		 
		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL, $urlafip);
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $xml);
		//curl_setopt($soap_do, CURLOPT_USERPWD, $username . ":" . $password);
		 
		$result = curl_exec($soap_do);
		$err = curl_error($soap_do);
		curl_close($soap_do);
		
	       	
		file_put_contents(URL_LIB_WS."/dummyResponse.xml",$result);
		

		if(isset($result))
		{	
			$arre = xmlstr_to_array($result);  
			//print_r($arre);  die();
			if( isset($arre['soap:Body']['FEDummyResponse']['FEDummyResult']) )	
			{
				$arre = $arre['soap:Body']['FEDummyResponse']['FEDummyResult'];
			
				$ban=0;
				foreach ($arre as $key => $valor) {
					//echo "<br>".$key.": ".$valor;
					if($valor != "OK") 
						$ban=1;
				}

				//echo "bandera: ".$ban;

				if($ban == 1)
					//echo "caido";
					return 0;
				else 
					//echo "activo";
					return 1;
			}
			else return 0;
		}
		else 
			//echo "caido";
			return 0;
	}

	function obtener_token()
	{   
		$token = '';
		$sign = '';
		
		 //<expirationTime>2016-11-12T07:10:22.312-03:00</expirationTime>

		if (file_exists(URL_LIB_WS.'/ta/TA.xml')) {
		      $xml = simplexml_load_file(URL_LIB_WS.'/ta/TA.xml');
		      
		      $expiration = $xml->header->expirationTime;
		      $expiration = explode(".", $expiration); 
		      $expiration = str_replace("T", " ", $expiration[0]);
		     
		      $fechaA = date('Y-m-d H:i:s');

		      if($expiration < $fechaA)
		      {  
		        
		        get_TA(); //solo actualizo token, cuando ya expiró
		        $xml = simplexml_load_file(URL_LIB_WS.'/ta/TA.xml');
		      }

		     //print_r($xml);
		      $token = $xml->credentials->token;
		      $sign = $xml->credentials->sign;
		} else {
		    
		    get_TA(); //sino existe archivo TA, lo creo
		    $xml = simplexml_load_file(URL_LIB_WS.'/ta/TA.xml');
		    $token = $xml->credentials->token;
		    $sign = $xml->credentials->sign;
		}

		$datos['token'] = $token;
		$datos['sign'] = $sign;
		return $datos;
	}


	function generar_comprobante($urlafip,$xml,$numFact) //peticion curl
	{

		//echo "adentro";
		$headers = array(
			"POST  HTTP/1.1",
			"Host: hostname",
			"Content-type: text/xml",
			"charset: UTF-8",
			"SOAPAction: ",
			"urn: FECAESolicitar",
			"Content-length: ".strlen($xml)
		);
		 
		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL, $urlafip );
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $xml);
		//curl_setopt($soap_do, CURLOPT_USERPWD, $username . ":" . $password);
		 
		$result = curl_exec($soap_do);
		$err = curl_error($soap_do);
		curl_close($soap_do);

		//var_dump($result);


		$arre = xmlstr_to_array($result);    
		/*var_dump($arre);
		echo "<br><br>";
		die();*/
		
		$rutaarchivo = URL_LIB_WS."/request/facturaResponse_".$numFact.".xml";
		if ( file_exists($rutaarchivo)) 
			$rutaarchivo = URL_LIB_WS."/request/facturaResponse_".$numFact."-dup.xml";

		file_put_contents($rutaarchivo,$result);

		

		$resultado = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['FeCabResp']['Resultado'];
		
		if(isset($resultado))
		{	
			if($resultado == 'A') //aprobado
			{
				$CAE = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['FeDetResp']['FECAEDetResponse']['CAE']; 
				$vencCAE = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['FeDetResp']['FECAEDetResponse']['CAEFchVto'];
				
				$data['CAE']= $CAE;
				$data['vencCAE']=$vencCAE;
			}
			else 
				if($resultado == 'R') //Reprobado
				{
					if(isset($arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['Errors']))
					{
						$errores = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['Errors'];
						$data['errores']=$errores;
					}
					else $data['errores']= $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult'];
				
				}
				else  
					if($resultado == 'O') //observado
					{
						$obs = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['FeDetResp']['Obs']['Observaciones']; //['codigoDescripcion']
						//var_dump($errores);
						$data['observaciones']=$obs;

						$CAE = $arre['soapenv:Body']['ns1:autorizarComprobanteResponse']['comprobanteResponse']['CAE'];
						$vencCAE = $arre['soapenv:Body']['ns1:autorizarComprobanteResponse']['comprobanteResponse']['fechaVencimientoCAE'];
						$data['CAE']= $CAE;
						$data['vencCAE']=$vencCAE;
					}
					else $data="";
		}
		else $data['falla'] = $arre['soap:Body']['soap:Fault'];
		
		
		return $data;

	}


	function generar_comprobante_NC($urlafip,$xml,$numFact) //peticion curl NC
	{
		$headers = array(
			"POST  HTTP/1.1",
			"Host: hostname",
			"Content-type: text/xml",
			"charset: UTF-8",
			"SOAPAction: ",
			"urn: FECAESolicitar",
			"Content-length: ".strlen($xml)
		);
		 
		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL, $urlafip );
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $xml);
		//curl_setopt($soap_do, CURLOPT_USERPWD, $username . ":" . $password);
		 
		$result = curl_exec($soap_do);
		$err = curl_error($soap_do);
		curl_close($soap_do);

		//var_dump($result);


		$arre = xmlstr_to_array($result);    
		/*var_dump($arre);
		echo "<br><br>";
		die();*/
		

		$rutaarchivo = URL_LIB_WS."/requestNC/facturaResponse_".$numFact.".xml";
		if ( file_exists($rutaarchivo)) 
			$rutaarchivo = URL_LIB_WS."/requestNC/facturaResponse_".$numFact."-dup.xml";

		file_put_contents($rutaarchivo,$result);


		

		$resultado = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['FeCabResp']['Resultado'];
		
		if(isset($resultado))
		{	
			if($resultado == 'A') //aprobado
			{
				$data['CAE'] = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['FeDetResp']['FECAEDetResponse']['CAE']; 
				$data['vencCAE'] = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['FeDetResp']['FECAEDetResponse']['CAEFchVto'];
			}
			else 
				if($resultado == 'R') //Reprobado
				{
					if(isset($arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['Errors']))
					{
						$data['errores'] = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['Errors'];
					}
					else $data['errores']= $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult'];
				
				}
				else  
					if($resultado == 'O') //observado
					{
						$obs = $arre['soap:Body']['FECAESolicitarResponse']['FECAESolicitarResult']['FeDetResp']['Obs']['Observaciones']; //['codigoDescripcion']
						//var_dump($errores);
						$data['observaciones']=$obs;

						$data['CAE'] = $arre['soapenv:Body']['ns1:autorizarComprobanteResponse']['comprobanteResponse']['CAE'];

						$data['vencCAE'] = $arre['soapenv:Body']['ns1:autorizarComprobanteResponse']['comprobanteResponse']['fechaVencimientoCAE'];
					}
					else $data="";
		}
		else $data['falla'] = $arre['soap:Body']['soap:Fault'];
		
		
		return $data;

	}


	function peticion_swafip($xml, $metodo, $urlafip) //peticion curl generica (a cualquier funcion)
	{
		//global $urlafip;
		

		//echo "adentro";
		$headers = array(
			"POST  HTTP/1.1",
			"Host: hostname",
			"Content-type: text/xml",
			"charset: UTF-8",
			"SOAPAction: ",
			"urn: $metodo",
			"Content-length: ".strlen($xml)
		);
		 
		$soap_do = curl_init();
		curl_setopt($soap_do, CURLOPT_URL, $urlafip );
		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($soap_do, CURLOPT_POST,           true );
		curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $xml);
		//curl_setopt($soap_do, CURLOPT_USERPWD, $username . ":" . $password);
		 
		$result = curl_exec($soap_do);
		$err = curl_error($soap_do);
		curl_close($soap_do);

		//$rutaarchivo = URL_LIB_WS."/request/ultimoResponse.xml";
		//file_put_contents($rutaarchivo,$result);
		//print_r($result); echo "FIN"; die();
		return $result;
		
	}


	function request_sw_afip_simular($datos)
	{	
		$salida['cae'] = '1234567891011'; 
		$salida['venccae'] = '20161220';  
		$salida['codigo'] = '123456789101112345678910111234567891011'; 	

		return $salida;
	}


	function request_sw_afip($datos) //prepara el xml. IMPORTANTE!
	{	
		$urlafip = "https://servicios1.afip.gov.ar/wsfev1/service.asmx";
	
		if( $this->dummy($urlafip) ) //solo si el web service está levantado
		//if( 1) 
		{	

		$ta = $this->obtener_token();


		//$cuitE = $datos['cuitE']; //30641334096; //el cuit del colegio! no del representante

		$auth=' <ar:Auth>
	            	<ar:Token>'.$ta['token'].'</ar:Token>
	      			<ar:Sign>'.$ta['sign'].'</ar:Sign>
	      			<ar:Cuit>'.CuitE.'</ar:Cuit>
	        	</ar:Auth>
	        ';

		if($ta['token']!='')
		//if( 1) 
		{	
			
				$fechaactTemp = $datos['fecha']; 	
				
				//$puntoVenta="0003";  
				$puntoVenta = $datos['puntoV'];
				$concepto = 2; //servicios
				//$umedida=7; //unidades
				//$codiva=5; //21%

				$month = date('m');
				$year = date('Y');
			    $day = date("d", mktime(0,0,0, $month+1, 0, $year));						 
			    $peri2 = date('Y-m-d', mktime(0,0,0, $month, $day, $year)); //ultimo dia del mes actual
				$peri1 = date('Y-m-d', mktime(0,0,0, $month, 1, $year)); //primer dia del mes actual

				//$fechaVTemp =  date('Y-m-d',strtotime('+10 days', strtotime($peri1)));
				$fechaVTemp =  $peri2;


				/*$dia = date('l',strtotime($fechaVTemp));
				if($dia == "Saturday") //si es sabado sumo dos dias
				  $fechaVTemp =  date('Y-m-d',strtotime('+2 days', strtotime($fechaVTemp)));
				else 
				  if($dia == "Sunday") //si es domingo sumo 1 dia
				    $fechaVTemp =  date('Y-m-d',strtotime('+1 days', strtotime($fechaVTemp)));*/

				
				/*echo $peri1."<br>";
				echo $peri2."<br>";
				echo $fechaVTemp."<br>";
				die();*/

				$dni = $datos['dni'];
				if($dni != 0)
					$coddoc = 96; //'DNI';
				else $coddoc = 99;//otros

				$tipoFact = "C";
				$codigoTipoFact = "15"; //15=recibo C; 11=factura C
				//$iva=0.21;
					
				$numFact = (int) $datos['numFact'];

				//$subTotal = number_format($subTotal,2,".","");
				$Total = number_format($datos['total'],2,".","");


					
				/*****************************WEB SERVICE****************************/	

				$fechaactX = str_replace('-', '', $fechaactTemp);
				$fechaVX = str_replace('-', '', $fechaVTemp);
				$peri1X = str_replace('-', '', $peri1);
				$peri2X = str_replace('-', '', $peri2);


				if($concepto==2)
				$servicio = '
							 <ar:FchServDesde>'.$peri1X.'</ar:FchServDesde>
							 <ar:FchServHasta>'.$peri2X.'</ar:FchServHasta>
							 <ar:FchVtoPago>'.$fechaVX.'</ar:FchVtoPago>
									 ';
				else $servicio = '';


				/*switch ($coddoc) {
					case 'CUIT': $coddoc = 80;
						break;
					case 'CUIL':$coddoc = 86;
						break;
					case 'DNI': $coddoc = 96;
						break;
					default: $coddoc = 80;
						break;
				}*/
															
				

				$xml='<soapenv:Envelope
						xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
						xmlns:ar="http://ar.gov.afip.dif.FEV1/">
						 <soapenv:Header/>
						 <soapenv:Body>
						 <ar:FECAESolicitar>
							 '.$auth.'
							 <ar:FeCAEReq>
								 <ar:FeCabReq>
									 <ar:CantReg>1</ar:CantReg>
									 <ar:PtoVta>'.$puntoVenta.'</ar:PtoVta>
									 <ar:CbteTipo>'.$codigoTipoFact.'</ar:CbteTipo>
								 </ar:FeCabReq>
								 <ar:FeDetReq>
									 <ar:FECAEDetRequest>
										 <ar:Concepto>'.$concepto.'</ar:Concepto>
										 <ar:DocTipo>'.$coddoc.'</ar:DocTipo>
										 <ar:DocNro>'.$dni.'</ar:DocNro>

										 <ar:CbteDesde>'.$numFact.'</ar:CbteDesde> 
										 <ar:CbteHasta>'.$numFact.'</ar:CbteHasta>
										 <ar:CbteFch>'.$fechaactX.'</ar:CbteFch>
										 						 
										 <ar:ImpTotal>'.$Total.'</ar:ImpTotal>
										 <ar:ImpTotConc>0</ar:ImpTotConc>
										
										 <ar:ImpNeto>'.$Total.'</ar:ImpNeto>
										 <ar:ImpOpEx>0</ar:ImpOpEx>

										 <ar:ImpIVA>0</ar:ImpIVA>

										 '.$servicio.'

										 <ar:MonId>PES</ar:MonId>
										 <ar:MonCotiz>1</ar:MonCotiz>
										
									 </ar:FECAEDetRequest>
								 </ar:FeDetReq>
							 </ar:FeCAEReq>

						</ar:FECAESolicitar>
					</soapenv:Body>
				</soapenv:Envelope>
									';	//

		
				file_put_contents(URL_LIB_WS."/request/facturaRequest_".$numFact.".xml",$xml);
				$webservice = $this->generar_comprobante($urlafip, $xml, $numFact); //descomentar!!
	

				/*$xmlmetodos	='
							<soapenv:Envelope
								xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
								xmlns:ar="http://ar.gov.afip.dif.FEV1/">
								 	<soapenv:Header/>
								 	<soapenv:Body>
								 		<ar:FEParamGetTiposDoc>
											 '.$auth.'
								 		</ar:FEParamGetTiposDoc>
								 	</soapenv:Body>
								</soapenv:Envelope>
								';
				peticion_swafip($xmlmetodos, 'FEParamGetTiposDoc');
				die();*/
				
				/************************** Fin Web Service *******************************/		
				/*	A: Aprobado, 
					O: Observado, 
					R: Rechazado*/
				

				
				if( isset($webservice['CAE']) )
				//if( 1 )
				{	
					//$webservice['CAE']='66666666666666'; //14 DIG
					//$webservice['vencCAE'] = '20161010';
					
					$webservice['vencCAE2'] = $webservice['vencCAE'];

					$anioCae = substr($webservice['vencCAE'], 0, 4);
					$mesCae = substr($webservice['vencCAE'], 4, 2);
					$diaCae = substr($webservice['vencCAE'], 6, 2);
					$webservice['vencCAE'] = $anioCae."-".$mesCae."-".$diaCae;


					
					$numFact4d = str_pad($numFact, 8, "0", STR_PAD_LEFT); //relleno con ceros a las izq, hasta completar 4 caracteres
				
					
					
							 			    
				    //CODIGO BARRAS		    
				    $code_number = CuitE.$codigoTipoFact.$puntoVenta.$webservice['CAE'].$webservice['vencCAE2'];
				    $code_numberArre = str_split($code_number);			    
				    $pares=0; $impares=0;
				    for ($i=1; $i <=  count($code_numberArre); $i++) { //<= porque empieza en 1
				    	if ($i%2==0)
						    $pares+=(int)$code_numberArre[$i-1];
						else
						    $impares+=(int)$code_numberArre[$i-1];
				    }
				    $impares=$impares*3;
				    $dig_verif= $impares + $pares; 		        
					$dig_verif = $this->roundUpToAny($dig_verif,10) - $dig_verif; //obtiene el multimplo de 10, mayor y mas proximo
					$code_number .= $dig_verif;
						
					$data['cae'] = $webservice['CAE'];
					$data['venccae'] = $webservice['vencCAE'];
					$data['num'] = $puntoVenta.'-'.$numFact4d;
					$data['codigoBarras'] = $code_number;
					//$data['fecha'] = date('Y-m-d');

						
					

					//**********tratar Observaciones del web service	********************
					if( isset($webservice['observaciones']) && count($webservice['observaciones'])>0 )
					{
						$data['Obs'] = array();
						foreach ($webservice['observaciones'] as $key)
						{
							$motivo = $key['Code'].": ".$key['Msg'];
							$data['Obs'][] = $motivo;
						}
					}


				}
				else {//**********tratar errores del web service	********************
						
						
						if( isset($webservice['errores']) && count($webservice['errores'])>0 )
						{
							/*$data['error'] = array();
							foreach ($webservice['errores'] as $key)
							{
								//foreach ($key as $row)
								foreach ($key as $row => $value)
								{
									//$motivo = $row['codigo'].": ".utf8_decode($row['descripcion']);
									$motivo = $row.": ".utf8_decode($value);
									//$motivo[]=$value;

									
								}	
								$data['error'][] = $motivo;
							}*/

							$data['error'] = $webservice['errores'];
						}

						if( isset($webservice['falla']) && count($webservice['falla'])>0 )
						{
							/*$motivo = $webservice['falla']['faultcode'].": ".utf8_decode($webservice['falla']['faultstring']);
							$data['error'][] = $motivo;*/
							$data['error'] = $webservice['falla'];
						}

						if( isset($webservice['observaciones']) && count($webservice['observaciones'])>0 )
						{
							$data['Obs'] = $webservice['observaciones'];
						}
					}
					
			

		}
		else $data['error'] = "No se obtuvo el TA!";

		}
		else $data['error'] = "web service caido!!";

		return $data;

	}


	function request_sw_afip_notacredito($datos) //prepara el xml NC. 
	{	
		$urlafip = "https://servicios1.afip.gov.ar/wsfev1/service.asmx";
	
		if( $this->dummy($urlafip) ) //solo si el web service está levantado
		//if( 1) 
		{	

		$ta = $this->obtener_token();


		//$cuitE = $datos['cuitE']; //30641334096; //el cuit del colegio! no del representante

		$auth=' <ar:Auth>
	            	<ar:Token>'.$ta['token'].'</ar:Token>
	      			<ar:Sign>'.$ta['sign'].'</ar:Sign>
	      			<ar:Cuit>'.CuitE.'</ar:Cuit>
	        	</ar:Auth>
	        ';

		if($ta['token']!='')
		//if( 1) 
		{		
				$fechaactTemp = $datos['fecha']; 	
				
				//$puntoVenta="0003";  
				$puntoVenta = $datos['puntoV'];
				$concepto = 2; //servicios
				//$umedida=7; //unidades
				//$codiva=5; //21%


				$dni = $datos['dni'];
				if($dni != 0)
					$coddoc = 96; //'DNI';
				else $coddoc = 99;//otros

				$tipoFact = "C";
				$codigoTipoFact = "13"; //15=recibo C; 11=factura C; 12: 
				// Nota de Débito C   //13: Nota de Crédito C
				
				//$iva=0.21;
		
				$numFact = (int) $datos['numFact'];

				$puntoVasoc = $datos['puntoVasoc'];
				$numFactAsoc = $datos['numFactAsoc'];  

				//$subTotal = number_format($subTotal,2,".","");
				$Total = number_format($datos['total'],2,".","");


					
				/*****************************WEB SERVICE****************************/	

				$fechaactX = str_replace('-', '', $fechaactTemp);
				/*$fechaVX = str_replace('-', '', $fechaVTemp);
				$peri1X = str_replace('-', '', $peri1);
				$peri2X = str_replace('-', '', $peri2);*/


				if($concepto==2)
					$servicio = '
						 <ar:FchServDesde>'.$fechaactX.'</ar:FchServDesde>
						 <ar:FchServHasta>'.$fechaactX.'</ar:FchServHasta>
						 <ar:FchVtoPago>'.$fechaactX.'</ar:FchVtoPago>
								 ';
				else $servicio = '';

																		

				$xml='<soapenv:Envelope
						xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
						xmlns:ar="http://ar.gov.afip.dif.FEV1/">
						 <soapenv:Header/>
						 <soapenv:Body>
						 <ar:FECAESolicitar>
							 '.$auth.'
							 <ar:FeCAEReq>
								 <ar:FeCabReq>
									 <ar:CantReg>1</ar:CantReg>
									 <ar:PtoVta>'.$puntoVenta.'</ar:PtoVta>
									 <ar:CbteTipo>'.$codigoTipoFact.'</ar:CbteTipo>
								 </ar:FeCabReq>
								 <ar:FeDetReq>
									 <ar:FECAEDetRequest>
										 <ar:Concepto>'.$concepto.'</ar:Concepto>
										 <ar:DocTipo>'.$coddoc.'</ar:DocTipo>
										 <ar:DocNro>'.$dni.'</ar:DocNro>

										 <ar:CbteDesde>'.$numFact.'</ar:CbteDesde> 
										 <ar:CbteHasta>'.$numFact.'</ar:CbteHasta>
										 <ar:CbteFch>'.$fechaactX.'</ar:CbteFch>
										 						 
										 <ar:ImpTotal>'.$Total.'</ar:ImpTotal>
										 <ar:ImpTotConc>0</ar:ImpTotConc>
										
										 <ar:ImpNeto>'.$Total.'</ar:ImpNeto>
										 <ar:ImpOpEx>0</ar:ImpOpEx>

										 <ar:ImpIVA>0</ar:ImpIVA>

										 '.$servicio.'

										 <ar:MonId>PES</ar:MonId>
										 <ar:MonCotiz>1</ar:MonCotiz>

										 <ar:CbtesAsoc>
											 <ar:CbteAsoc>
												 <ar:Tipo>11</ar:Tipo>
												 <ar:PtoVta>'.$puntoVasoc.'</ar:PtoVta>
												 <ar:Nro>'.$numFactAsoc.'</ar:Nro>
											 </ar:CbteAsoc>
										 </ar:CbtesAsoc>
										
									 </ar:FECAEDetRequest>
								 </ar:FeDetReq>
							 </ar:FeCAEReq>

						</ar:FECAESolicitar>
					</soapenv:Body>
				</soapenv:Envelope>
				';	

				
		
				file_put_contents(URL_LIB_WS."/requestNC/facturaRequest_".$numFact.".xml",$xml);

				// $this->peticion_swafip ($xml,'FECAESolicitar',$urlafip);
				
				$webservice = $this->generar_comprobante_NC($urlafip, $xml, $numFact); //NOTA DE CREDITO
	

				/************************** Fin Web Service *******************************/	
				/*	A: Aprobado, 
					O: Observado, 
					R: Rechazado*/
			
				
				if( isset($webservice['CAE']) )
				//if( 1 )
				{	
					$webservice['vencCAE2'] = $webservice['vencCAE'];

					$anioCae = substr($webservice['vencCAE'], 0, 4);
					$mesCae = substr($webservice['vencCAE'], 4, 2);
					$diaCae = substr($webservice['vencCAE'], 6, 2);
					$webservice['vencCAE'] = $anioCae."-".$mesCae."-".$diaCae;

					
					$numFact4d = str_pad($numFact, 8, "0", STR_PAD_LEFT); //relleno con ceros a las izq, hasta completar 4 caracteres
									
						
					$data['cae'] = $webservice['CAE'];
					$data['venccae'] = $webservice['vencCAE'];
						

					//**********tratar Observaciones del web service	********************
					if( isset($webservice['observaciones']) && count($webservice['observaciones'])>0 )
					{
						$data['Obs'] = array();
						foreach ($webservice['observaciones'] as $key)
						{
							$motivo = $key['Code'].": ".$key['Msg'];
							$data['Obs'][] = $motivo;
						}
					}

				}
				else {//**********tratar errores del web service	********************
											
						if( isset($webservice['errores']) && count($webservice['errores'])>0 )
						{
							

							$data['error'] = $webservice['errores'];
						}

						if( isset($webservice['falla']) && count($webservice['falla'])>0 )
						{
							
							$data['error'] = $webservice['falla'];
						}

						if( isset($webservice['observaciones']) && count($webservice['observaciones'])>0 )
						{
							$data['Obs'] = $webservice['observaciones'];
						}
					}
					
			

		}
		else $data['error'] = "No se obtuvo el TA!";

		}
		else $data['error'] = "web service caido!!";

		return $data;

	}

	function request_ultimo_comprobante($datos)
	{	
		$urlafip = "https://servicios1.afip.gov.ar/wsfev1/service.asmx";
	
		if( $this->dummy($urlafip) ) //solo si el web service está levantado
		//if( 1) 
		{	

		$ta = $this->obtener_token();


		$auth=' <ar:Auth>
	            	<ar:Token>'.$ta['token'].'</ar:Token>
	      			<ar:Sign>'.$ta['sign'].'</ar:Sign>
	      			<ar:Cuit>'.CuitE.'</ar:Cuit>
	        	</ar:Auth>
	        ';

		if($ta['token']!='')
		//if( 1) 
		{		
				$puntoVenta = $datos['puntoV']; 
				$codigoTipoFact = $datos['codigoTipoFact']; 

				
				$concepto = 2; //servicios
				//$umedida=7; //unidades
				//$codiva=5; //21%

				//$tipoFact = "C";
				//$codigoTipoFact = "15"; //15=recibo C; 11=factura C
				//$iva=0.21;
					
				/*****************************WEB SERVICE****************************/	

				$xmlmetodos = '
						<soapenv:Envelope
							xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
							xmlns:ar="http://ar.gov.afip.dif.FEV1/">
							 <soapenv:Header/>
							 <soapenv:Body>
								 <ar:FECompUltimoAutorizado>
									  '.$auth.'
									 <ar:PtoVta>'.$puntoVenta.'</ar:PtoVta>
									 <ar:CbteTipo>'.$codigoTipoFact.'</ar:CbteTipo>
								 </ar:FECompUltimoAutorizado>
							 </soapenv:Body>
						</soapenv:Envelope>
				';
				
				$result = $this->peticion_swafip($xmlmetodos, 'FECompUltimoAutorizado',$urlafip);

				$arre = xmlstr_to_array($result);  

				$numero = $arre['soap:Body']['FECompUltimoAutorizadoResponse']['FECompUltimoAutorizadoResult']['CbteNro'];

				//print_r($arre);
				
				if($numero) 
					$data['numero'] = $numero;
				else $data['numero'] = 0;
				//print_r($data);
				// die();

		}
		else $data['error'] = "No se obtuvo el TA!";

		}
		else $data['error'] = "web service caido!!";

		return $data;
	}


	function prepare_request($data){
		$CI = & get_instance();

		$entrada['puntoV'] = 4;
		$entrada['codigoTipoFact'] = 15;  
		$salida1 = $this->request_ultimo_comprobante($entrada);


		$entrada['numFact'] = $salida1['numero'] + 1;
		//$entrada['numFact'] = 16;
		$entrada['dni'] = $data['dnitutor'];
		$entrada['total'] = $data['total'];
		$entrada['fecha'] = $data['fecha'];
		
		$salida = $this->request_sw_afip($entrada);
		//print_r($salida);

		/*$salida  = Array ( 
			'cae' => '68449646108644', 
			'venccae' => '2018-11-08', 
			'num'=> '0004-00000002',
			'codigoBarras' => '3051896700915468449646108644201811089' 
		);*/
		//print_r($salida);


		$datos_factura = array(
			'punto_v' => $entrada['puntoV'],
			'numero' => $entrada['numFact'],
			'fecha' => $data['fecha'],
			'total' => $data['total'],
			'tipoComp' => $entrada['codigoTipoFact'],
			'cae' => $salida['cae'],
			'fcae' => $salida['venccae'],
			'codigobarras' => $salida['codigoBarras'],
			'payment_id' => $data['pagoId'],
		);
		//print_r($datos_factura);
		$factura = new Factura($datos_factura);
		if($factura->is_valid()){
			$factura->save();
			return true;
		}
		else{
			return false;
		}
		//$resultORM = $CI->db->insert("facturas",$datos_factura);

		//var_dump($CI->db->last_query());
	}

	function prepare_request_nc($data){
		$CI = & get_instance();

		$entrada['puntoV'] = 4;
		$entrada['codigoTipoFact'] = 13;  //15=recibo C; 11=factura C; 12: 
				// Nota de Débito C   //13: Nota de Crédito C
		$salida1 = $this->request_ultimo_comprobante($entrada);
		//print_r($salida1); die();

		$entrada['numFact'] = $salida1['numero'] + 1;
		$entrada['dni'] = $data['dnitutor'];
		$entrada['total'] = $data['total'];
		$entrada['fecha'] = date('Y-m-d'); 
		$entrada['puntoVasoc'] = $data['puntoVasoc'];
		$entrada['numFactAsoc'] = $data['numFactAsoc'];

		$salida = $this->request_sw_afip_notacredito($entrada);
		//print_r($salida);

		$datos_factura = array(
			'punto_v' => $entrada['puntoV'],
			'numero' => $entrada['numFact'],
			'total' => $entrada['total'],
			'tipoComp' => $entrada['codigoTipoFact'],		
			'fecha' => $entrada['fecha'],
			'cae' => $salida['cae'],
			'fcae' => $salida['venccae'],
			'factura_id' => $data['facturaId'],
		);
		//print_r($datos_factura);
		$resultORM = $CI->db->insert("notacreditos",$datos_factura);

	}

	// function request_sw_afip_general() //no se usa
	// {	
	// 	$urlafip = "https://servicios1.afip.gov.ar/wsfev1/service.asmx";
	
	// 	if( $this->dummy($urlafip) ) //solo si el web service está levantado
	// 	//if( 1) 
	// 	{	

	// 	$ta = $this->obtener_token();


	// 	$auth=' <ar:Auth>
	//             	<ar:Token>'.$ta['token'].'</ar:Token>
	//       			<ar:Sign>'.$ta['sign'].'</ar:Sign>
	//       			<ar:Cuit>'.CuitE.'</ar:Cuit>
	//         	</ar:Auth>
	//         ';

	// 	if($ta['token']!='')
	// 	//if( 1) 
	// 	{		
	// 			$puntoVenta="0003";  
				
	// 			$concepto = 2; //servicios
	// 			//$umedida=7; //unidades
	// 			//$codiva=5; //21%

				

	// 			$tipoFact = "C";
	// 			$codigoTipoFact = "11"; //15=recibo C; 11=factura C
	// 			//$iva=0.21;

					
	// 			/*****************************WEB SERVICE****************************/	

	
	// 			/*$xmlmetodos	='
	// 						<soapenv:Envelope
	// 							xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
	// 							xmlns:ar="http://ar.gov.afip.dif.FEV1/">
	// 							 	<soapenv:Header/>
	// 							 	<soapenv:Body>
	// 							 		<ar:FEParamGetTiposDoc>
	// 										 '.$auth.'
	// 							 		</ar:FEParamGetTiposDoc>
	// 							 	</soapenv:Body>
	// 							</soapenv:Envelope>
	// 							';*/
	// 			/*$xmlmetodos = '
	// 					<soapenv:Envelope
	// 						xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
	// 						xmlns:ar="http://ar.gov.afip.dif.FEV1/">
	// 						 <soapenv:Header/>
	// 						 <soapenv:Body>
	// 							 <ar:FECompUltimoAutorizado>
	// 								  '.$auth.'
	// 								 <ar:PtoVta>3</ar:PtoVta>
	// 								 <ar:CbteTipo>13</ar:CbteTipo>
	// 							 </ar:FECompUltimoAutorizado>
	// 						 </soapenv:Body>
	// 					</soapenv:Envelope>
	// 			';*/

	// 			$xmlmetodos = '
	// 					<soapenv:Envelope
	// 					xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
	// 					xmlns:ar="http://ar.gov.afip.dif.FEV1/">
	// 					 <soapenv:Header/>
	// 					 <soapenv:Body>
	// 						 <ar:FECompConsultar>
	// 							   '.$auth.'
	// 							 <ar:FeCompConsReq>
	// 								 <ar:CbteTipo>11</ar:CbteTipo>
	// 								 <ar:CbteNro>807</ar:CbteNro>
	// 								 <ar:PtoVta>3</ar:PtoVta>
	// 							 </ar:FeCompConsReq>
	// 						</ar:FECompConsultar>
	// 					 </soapenv:Body>
	// 					</soapenv:Envelope>
	// 			';
	// 			$this->peticion_swafip($xmlmetodos, 'FECompConsultar',$urlafip);
			

	// 	}
	// 	else $data['error'] = "No se obtuvo el TA!";

	// 	}
	// 	else $data['error'] = "web service caido!!";

	// }
	// function request_sw_afip_consultar($codigoTipoFact=11,$puntoVenta="0003", $numComprobante) //no se usa
	// {	
	// 	$urlafip = "https://servicios1.afip.gov.ar/wsfev1/service.asmx";
	
	// 	if( $this->dummy($urlafip) ) //solo si el web service está levantado
	// 	//if( 1) 
	// 	{	

	// 	$ta = $this->obtener_token();

	// 	$auth=' <ar:Auth>
	//             	<ar:Token>'.$ta['token'].'</ar:Token>
	//       			<ar:Sign>'.$ta['sign'].'</ar:Sign>
	//       			<ar:Cuit>'.CuitE.'</ar:Cuit>
	//         	</ar:Auth>
	//         ';

	// 	if($ta['token']!='')
	// 	//if( 1) 
	// 	{		
	// 			//$puntoVenta="0003";  
				
	// 			$concepto = 2; //servicios
	// 			//$umedida=7; //unidades
	// 			//$codiva=5; //21%

				

	// 			$tipoFact = "C";
	// 			$codigoTipoFact = "11"; //15=recibo C; 11=factura C
	// 			//$iva=0.21;


	// 			$xmlmetodos = '
	// 					<soapenv:Envelope
	// 					xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
	// 					xmlns:ar="http://ar.gov.afip.dif.FEV1/">
	// 					 <soapenv:Header/>
	// 					 <soapenv:Body>
	// 						 <ar:FECompConsultar>
	// 							   '.$auth.'
	// 							 <ar:FeCompConsReq>
	// 								 <ar:CbteTipo>'.$codigoTipoFact.'</ar:CbteTipo>
	// 								 <ar:CbteNro>'.$numComprobante.'</ar:CbteNro>
	// 								 <ar:PtoVta>'.$puntoVenta.'</ar:PtoVta>
	// 							 </ar:FeCompConsReq>
	// 						</ar:FECompConsultar>
	// 					 </soapenv:Body>
	// 					</soapenv:Envelope>
	// 			';
	// 			$data['respuesta'] = $this->peticion_swafip($xmlmetodos, 'FECompConsultar',$urlafip);
	// 			file_put_contents(URL_LIB_WS."/pruebas/consultaRequest_".$numComprobante.".xml",$data['respuesta']);

	// 	}
	// 	else $data['error'] = "No se obtuvo el TA!";

	// 	}
	// 	else $data['error'] = "web service caido!!";

	// 	return $data;

	// }



	// function generar_key_csr(){

	// 	echo "inicio";

	// 	$keysize = 3072;
	// 	$res = openssl_pkey_new (array('private_key_bits' => $keysize));


	// 	// Extract the private key from $res to $privKey
	// 	openssl_pkey_export($res, $privKeystring);
	// 	file_put_contents("ClavePrivada.key",$privKeystring);
	// 	var_dump( $privKeystring ) ; 
	// 	echo "<br><br>";

	// 	//$salida = exec('openssl req -new -key ClavePrivada.key -subj "/C=AR/O=Colegio San Pablo S.A./CN=Colegio San Pablo AEL/serialNumber=CUIT 23079385239" -out CobrosSanPAblo');

	// 	$dn = array(
	// 	    "C" => "AR",
	// 	    "O" => "Colegio Dante Alighieri",
	// 	    "CN" => "Colegio Dante Alighieri AEL",
	// 	    "serialNumber" => "CUIT 27052820559"
	// 	);


	// 	/*MERLINO PAULINA
	// 	CUIL: 27-05282055-9
	// 	clave: pauli0559*/


	// 	// Generar una petición de firma de certificado
	// 	$csr = openssl_csr_new($dn, $res);

	// 	openssl_csr_export($csr, $csrout) and var_dump($csrout);

	// 	file_put_contents("ClavePrivada.csr",$csrout);

	// 	echo "fin";
	// }
	
	
	


}// fin Mis_funciones



?>
