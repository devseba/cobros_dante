<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Cobranza extends CI_Controller {
	public function index()
	{
		//I'm just using rand() function for data example
		//45636348
		$campo[] = "0101";
		$campo[] = "45636348";
		$campo[] = "00001";
		$campo[] = "082016";
		$campo[] = "000113500";
		$campo[] = "150816";
		$campo[] = "000124850";
		$campo[] = "200916";
		$campo[] = "000136200";
		$campo[] = "1";

		$temp = "";
		foreach ($campo as $key => $value) {
			$temp .= $value;
		}
		$this->set_barcode($temp);
	}
	/*
	private function set_barcode($code)
	{
		//load library
		$this->load->library('zend');
		//load in folder Zend
		$this->zend->load('Zend/Barcode');
		//generate barcode
		Zend_Barcode::render('code25', 'image', array('text'=>$code), array());
	}*/

	private function set_barcode($code){		
		$this->load->library("Barcode/src/BarcodeGenerator");
		$this->load->library("Barcode/src/BarcodeGeneratorPNG");
		$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
		echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode('081231723897', $generator::TYPE_CODE_128)) . '">';
	}

	public function listado_cobranza_por_tutor(){
		//obtengo los hijos asignados del tutor logueado

		//a traves del documento obtengo las cuotas que debe el alumno

		//apartir de estos datos genero el codigo de barra
	}
}