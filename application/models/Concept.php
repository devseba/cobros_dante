<?php 

	class Concept extends ActiveRecord\Model
	{
		static $has_many = array(
			array('amounts')
		);
		
		static $validates_presence_of = array(
			array('concepto', 'message' => '<span class="ferror">El Concepto no puede estar vacio</span>')
		);
		
		static $validates_uniqueness_of = array(
			array(array('concepto'), 'message' => '<span class="ferror">El concepto ya existe.</span>')
		);
		
		static $before_save = array('uppercase');
		
		function uppercase()
		{
			$this->concepto = strtoupper($this->concepto);
		}
	}
