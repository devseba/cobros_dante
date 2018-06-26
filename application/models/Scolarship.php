<?php 

	class Scolarship extends ActiveRecord\Model
	{
		static $belongs_to = array(
			array('student'),
			array('amount')
		);
	}