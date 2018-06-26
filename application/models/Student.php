<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	class Student extends ActiveRecord\Model {
		
		static $has_many = array(
			array('inscription'),
			array('debt', 'conditions' => array('pagado = ?', 0)),
			array('scolarship'),
			array('family'),
			array('detail', 'through' => 'debt'),
			array('tutor', 'through' => 'family'),
			array('payment', 'conditions' => array('anulado = ?', 0))
		);
		
		static $belongs_to = array(
			array('city')
		);
		
		static $validates_presence_of = array(
			array('nombre', 'message' => '<span class="ferror">El nombre no puede estar vacío</span>'),
			array('apellido', 'message' => '<span class="ferror">El apellido no puede estar vacío</span>'),
			array('fecha_nacimiento', 'message' => '<span class="ferror">La fecha no puede estar vacía</span>'),
			array('sexo', 'message' => '<span class="ferror">Debe elegir una opción</span>'),
			array('tipo_documento', 'message' => '<span class="ferror">Indique el tipo de documento</span>'),
			array('nro_documento', 'message' => '<span class="ferror">Indique el número de documento</span>'),
			array('domicilio', 'message' => '<span class="ferror">Debe indicar el domicilio</span>'),
			array('nacionalidad', 'message' => '<span class="ferror">Debe indicar la nacionalidad</span>'),
			array('telefono', 'message' => '<span class="ferror">Debe indicar al menos un número de teléfono</span>'),
			array('fecha_inscripcion', 'message' => '<span class="ferror">Debe indicar la fecha de inscripción</span>'),
			array('city_id', 'message' => '<span class="ferror">Debe indicar la ciudad</span>'),
			array('grupo_sanguineo', 'message' => '<span class="ferror">Debe indicar el grupo sanguíneo</span>')
		);
		
		static $validates_uniqueness_of = array(
			array('nro_documento', 'message' => '<span class="ferror">El número de documento ya existe, compruebe si el alumno ya existe en el sistema.</span>')
		);
		
		static $before_save = array('uppercase');
		
		function uppercase()
		{
			$this->apellido = strtoupper($this->apellido);
			$this->nombre = strtoupper($this->nombre);
		}	
	}
