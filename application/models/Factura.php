<?php 

class Factura extends ActiveRecord\Model{

		static $belongs_to = array(
			array('payment')
		);
		
		
		/*function insert_factura($datos){
			$this->load->database();

			$query = $this->db->insert("facturas",$datos);
	        return $query;
		}*/

}