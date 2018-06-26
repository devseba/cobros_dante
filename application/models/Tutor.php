<?php 

	class Tutor extends ActiveRecord\Model
	{
		static $belongs_to = array(
			array('city')
		);
		
		static $has_many = array(
			array('family'),
			array('student', 'through' => 'family')
		);
		
		static $validates_presence_of = array(
			array('nombre', 'message' => '<span class="ferror">El nombre no puede estar vacío</span>'),
			array('apellido', 'message' => '<span class="ferror">El apellido no puede estar vacío</span>'),
			array('sexo', 'message' => '<span class="ferror">Debe indicar el sexo</span>'),
			//array('fecha_nacimiento', 'message' => '<span class="ferror">La fecha no puede estar vacía</span>'),
			array('tipo_documento', 'message' => '<span class="ferror">Indique el tipo de documento</span>'),
			//array('nro_documento', 'message' => '<span class="ferror">Indique el número de documento</span>'),
			array('domicilio', 'message' => '<span class="ferror">Debe indicar el domicilio</span>'),
			array('nacionalidad', 'message' => '<span class="ferror">Debe indicar la nacionalidad</span>'),
			array('telefono_fijo', 'message' => '<span class="ferror">Debe indicar al menos un número de teléfono</span>'),
			//array('ocupacion', 'message' => '<span class="ferror">Debe indicar la ocupación</span>'),
			array('city_id', 'message' => '<span class="ferror">Debe indicar la ciudad</span>')
		);
		
		static $validates_uniqueness_of = array(
			array('nro_documento', 'message' => '<span class="ferror">El número de documento ya existe, compruebe si el tutor ya existe en el sistema.</span>')
		);
		
		static $before_save = array('uppercase');
		
		function uppercase()
		{
			$this->apellido = strtoupper($this->apellido);
			$this->nombre = strtoupper($this->nombre);
		}	
	}
