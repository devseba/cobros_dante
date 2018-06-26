<?php 
	
	class Amount extends ActiveRecord\Model
	{
		static $belongs_to = array(
			array('concept'),
			array('course')
		);
		
		static $has_many = array(
			array('scolarship'),
			array('debt')
		);
		
		static $validates_presence_of = array(
			array('concept_id', 'message' => '<span class="ferror">Debe seleccionar un concepto</span>'),
			array('course_id', 'message' => '<span class="ferror">Debe seleccionar un curso</span>'),
			array('fecha', 'message' => '<span class="ferror">La fecha no puede estar vacía</span>'),
			array('ciclo_lectivo', 'message' => '<span class="ferror">El ciclo lectivo no puede estar vacío</span>'),
			array('importe', 'message' => '<span class="ferror">El importe no puede estar vacio</span>')
		);
		
	/*	static $validates_uniqueness_of = array(
			array(array('concept_id', 'course_id','ciclo_lectivo'), 'message' => '<span class="ferror">Ya hay un importe para este concepto, curso y ciclo lectivo.</span>')
		);	
	*/
	}
