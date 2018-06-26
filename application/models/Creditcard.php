<?php 

	class Creditcard extends ActiveRecord\Model
	{
		static $before_save = array('uppercase');
		
		function uppercase()
		{
			$this->nombre = strtoupper($this->nombre);
		}
	}
