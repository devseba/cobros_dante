<?php 

	class Payment extends ActiveRecord\Model
	{
		static $has_many = array(
			array('detail'),
			array('pdetail'),
			array('debt', 'through' => 'detail')
		);
		
		static $belongs_to = array(
			array('student'),
			array('user')
		);
		
		static $validates_numericality_of = array(
			array('student_id', 'greater_than' => 0),
			array('user_id', 'greater_than' => 0),
			array('importe', 'greater_than' => 0),
		);
	}