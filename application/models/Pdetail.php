<?php 

	class Pdetail extends ActiveRecord\Model
	{
		static $belongs_to = array(
			array('ptype'),
			array('payment'),
			array('bank'),
			array('creditcard')
    );
			
		static $validates_presence_of = array(
			array('payment_id', 'message' => '<span class="ferror">Debe indicar el pago a detallar</span>'),
			array('ptype_id', 'message' => '<span class="ferror">Debe seleccionar una forma de pago</span>'),
			array('importe', 'message' => '<span class="ferror">Debe indicar el importe</span>'),
		);
		
		static $validates_numericality_of = array(
			array('payment_id', 'greater_than_or_equal_to' => 1, 'message' => '<span class="ferror">Debe indicar el pago a detallar</span>'),
			array('ptype_id', 'greater_than_or_equal_to' => 1, 'message' => '<span class="ferror">Debe indicar la forma de pago</span>'),
			array('importe', 'greater_than_or_equal_to' => 1, 'message' => '<span class="ferror">Debe indicar el importe a detallar</span>')
		);
	}
