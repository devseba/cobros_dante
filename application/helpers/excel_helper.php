<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
//header("Content-type: application/vnd.ms-excel; charset=UTF-16LE");
header("Content-type: application/vnd.ms-excel; charset=UTF-8");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
header ( "Pragma: no-cache" );
header ( "Content-Type: application/force-download; name=\"excel_".date('d-m-Y').".xls\"");
header("Content-Disposition: attachment; filename=\"excel_".date('d-m-Y').".xls\"");

/*
* Excel library for Code Igniter applications iso-8859-1
* Author: Derek Allard, Dark Horse Consulting, www.darkhorse.to, April 2006
*/

function to_excel($query, $titulo=''){
     $headers = $titulo."\t\n\n"; 
     $data = ''; 
     
     $obj =& get_instance();
     
     if (sizeof($query) == 0) {
          $data .= '<h3>No hay información para mostrar.</h3>';
     } else {
		for($i = 0; $i < sizeof($query);$i++){
			 if($i==0){
				foreach(array_keys($query[$i]) as $field){
					$field = str_replace('"', '""', $field);
					//$headers .= '"'.strtoupper(iconv("UTF-8","iso-8859-1",$field)).'"' . "\t";
					$headers .= strtoupper(iconv("UTF-8","iso-8859-1",$field)). "\t";
				}
				$headers = trim($headers)."\n";
			}

		 $line = '';
            foreach($query[$i] as $value) {
				if ((!isset($value)) OR ($value == "")) {
					 $value = "\t";
				} else {
					if(!is_numeric($value)){
					 $value = str_replace('"', '""', $value);
					// $value = '"' . strtoupper(iconv("UTF-8","iso-8859-1", $value)) . '"' . "\t";
					 $value = '"' . strtoupper($value) . '"';
				 }
				}
				$line .= $value. "\t";
		   }
		   $data .= trim($line)."\n";
		}
          
          $data = str_replace("\r","",$data);  
     }
      
     echo "$headers\n$data";
}
?> 
