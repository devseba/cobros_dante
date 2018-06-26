<?php 

	class Ptype extends ActiveRecord\Model
	{
		static $has_many = array(
			array('pdetail')
		);
		
		static $before_save = array('uppercase');
		
		function uppercase()
		{
			$this->tipo = strtoupper($this->tipo);
		}
	}
