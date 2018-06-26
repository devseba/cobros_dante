<?php 

	class Course extends ActiveRecord\Model
	{
		static $belongs_to = array(
			array('level')
		);
		
		static $validates_presence_of = array(
			array('course', 'message' => '<span class="ferror">Debe ingresar una Ciudad</span>'),
			array('level_id', 'message' => '<span class="ferror">Seleccionar una Division</span>')
		);
			
		static $validates_uniqueness_of = array(
			array(array('course','level_id'), 'message' => '<span class="ferror">El Curso ya existe</span>')
		);
	}
