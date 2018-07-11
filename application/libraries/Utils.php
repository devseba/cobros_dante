<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Utils {
		
		function fecha_formato( $formato = "%Y-%m-%d", $fecha )
		{
			if(!empty($fecha))
			{
				$f = mdate( $formato , normal_to_unix( $fecha ) );
				return $f;
			}
			else return FALSE;
		}

		function fecha_formato_mysql($fecha){
			$res=substr($fecha,6,4)."-".substr($fecha,3,2)."-".substr($fecha,0,2);
			return $res;			
		}

		function agregar_caracter_fecha($fecha, $c){
			return substr($fecha, 0,2).$c.substr($fecha, 2,2).$c.substr($fecha, 4,4);
		}

		/*
		** Transforma el importe para el barcode
		*/
		function importe_barcode($numero, $enteros, $decimal){
			$numero = round($numero,$decimal);
			$numero = str_replace(",", ".", $numero);
			$numero = number_format($numero,"2",",","");
			$array = explode(",", $numero);
			$rellenar_entero = ($enteros - strlen($array[0]));
			
			//echo "-->".$enteros. "-" . strlen($array[0])." = ".$rellenar_entero."<br>";
			$importe = "";
			for ($i=0; $i < $rellenar_entero; $i++) {
				$importe .= "0";
			}
			$importe .= $array[0];

			$rellenar_decimal = "";
			if(isset($array[1])){
				$rellenar_decimal = $decimal - strlen($array[1]);
			}
			else{
				$rellenar_decimal = $decimal;
				$array[1] = "";
			}
			for ($i=0; $i < $rellenar_decimal; $i++) {
				$array[1] .= "0";
			}

			return $importe.$array[1];
		}

		function digito_verificador($codigo){
			$lenght = strlen($codigo);
			$par = 0;
			$impar = 0;
			//Hago la suma de los impares y pares por separado
			for($i=0;$i<$lenght;$i++){
				if(($i%2)==0){//posicion impar en el codigo de barras
					$impar = ($impar + $codigo[$i]);
				}
				else{//posicion par en el codigo de barras
					$par = ($par + $codigo[$i]);
				}
			}
			//echo $impar;
			//echo "<br>";
			//echo $par;
			//echo "<br>";
			//Multiplico por 3 el impar y se lo suma al par
			//Despues le aplico el modulo 10
			//Por ultimo resto 10 menos el resultado anterior
			$resultado = ($par + ($impar * 3)) % 10;
			//echo $resultado;
			if($resultado == 0){
				return 0;
			}
			else{
				return 10 - $resultado;
			}
			return $resultado;
		}

		/**
		 * Calcula el saldo para una deuda de un alumno
		 * @param d Objeto obtenido de un registro de la tabla debts
		 * @return saldo
		*/
		function calcular_saldo($d){
			$descuento = 0;
			foreach($d->amount->scolarship as $s){
				if(($s->student_id == $d->student_id) AND ($s->amount_id == $d->amount_id)){
					$descuento = $s->porcien_descuento;
				}
			}
			$pagado = 0;
			$ban = 0;
			foreach($d->detail as $dt){					
				if($dt->payment->anulado == 0)
					$pagado += $dt->importe;
			}
			
			$saldo = 0;
			$pagar = 0;
			$pagar = ceil(($d->amount->importe - ($d->amount->importe*($descuento/100)))/5)*5;
			$saldo = $pagar - $pagado;

			return $saldo;
		}

		function calcular_ultimo_dia_mes($fecha){
			$fecha->modify('last day of this month');
			return $fecha->format('d');			
		}

		function crear_zip($file_name, $file_name2)
		{
			// Creamos un instancia de la clase ZipArchive
			$zip = new ZipArchive();
			// Creamos y abrimos un archivo zip temporal
			$open = $zip->open($file_name.".zip",ZipArchive::CREATE);
			// Añadimos un archivo en la raid del zip.
			$zip->addFile($file_name);
			// Una vez añadido los archivos deseados cerramos el zip.
			$zip->close();
			// Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
			header("Content-type: application/octet-stream");
			header("Content-disposition: attachment; filename=".$file_name.".zip");
			// leemos el archivo creado
			readfile($file_name.'.zip');
			// Por último eliminamos el archivo temporal creado
			unlink($file_name.'.zip');//Destruye el archivo temporal

			// Creamos un instancia de la clase ZipArchive
			$zip = new ZipArchive();
			// Creamos y abrimos un archivo zip temporal
			$open = $zip->open($file_name2.".zip",ZipArchive::CREATE);
			// Añadimos un archivo en la raid del zip.
			$zip->addFile($file_name2);
			// Una vez añadido los archivos deseados cerramos el zip.
			$zip->close();
			// Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
			header("Content-type: application/octet-stream");
			header("Content-disposition: attachment; filename=".$file_name2.".zip");
			// leemos el archivo creado
			readfile($file_name2.'.zip');
			// Por último eliminamos el archivo temporal creado
			unlink($file_name2.'.zip');//Destruye el archivo temporal			
		}

		/**
		 * FUNCIONES PARA PAGOS LINK
		 */

		/**
		 * Sirve para verificar que este el link generado
		 * @param $cod_pago_link 
		 * @return 0 o deuda_id
		 * @author Sebastian Avila
		 */
		function get_deuda_p_codigo_link($cod_pago_link){
			$CI = & get_instance();
			$sql = "SELECT id FROM debts WHERE registro_link LIKE '".$cod_pago_link."%'";
			$debts = $CI->db->query($sql);
			if($debts->num_rows())
				return $debts->row()->id;
			else
				return 0;
		}

		function getPathRootProject(){
			return $_SERVER['DOCUMENT_ROOT']."/cobros/";
		}
	}