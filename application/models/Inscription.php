<?php 

	class Inscription extends ActiveRecord\Model
	{
		static $belongs_to = array(
			array('student'),
			array('course'),
			array('division')
		);
		
		static $validates_presence_of = array(
			array('student_id', 'message' => '<span class="ferror">Debe seleccionar un estudiante</span>'),
			array('course_id', 'message' => '<span class="ferror">Debe elegir un curso</span>'),
			array('division_id', 'message' => '<span class="ferror">Debe elegir una división</span>'),
			array('ciclo_lectivo', 'message' => '<span class="ferror">Debe indicar el ciclo lectivo</span>')
		);
		
		static $validates_numericality_of = array(
			array('student_id', 'greater_than_or_equal_to' => 1, 'message' => 'Debe indicar el estudiante'),
			array('course_id', 'greater_than_or_equal_to' => 1, 'message' => 'Debe indicar el curso'),
			array('division_id', 'greater_than_or_equal_to' => 1, 'message' => 'Debe indicar la división'),
			array('ciclo_lectivo', 'greater_than_or_equal_to' => 1, 'message' => 'Debe indicar el ciclo lectivo')
		);
	}