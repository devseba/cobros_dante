<?php 

	class Family extends ActiveRecord\Model
	{
		static $belongs_to = array(
			array('student'),
			array('tutor')
		);
		
		static $validates_presence_of = array(
			array('student_id', 'message' => 'Debe indicar el estudiante.'),
			array('tutor_id', 'message' => 'Debe indicar el tutor.')
		);
		
		static $validates_numericality_of = array(
			array('student_id', 'greater_than' => 0, 'message' => 'Debe indicar el estudiante.'),
			array('tutor_id', 'greater_than' => 0, 'message' => 'Debe indicar el tutor.')
		);
	}