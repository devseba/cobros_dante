<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tutores extends CI_Controller {

	function __construct(){
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
	}

	public function asignar(){
		$insert['student_id'] = $this->input->post('student_id');
		$insert['tutor_id'] = $this->input->post('tutor_id');
		$f = new Family($insert);
		if($f->is_valid()){
			$f->save();
			$this->session->set_flashdata('msg','<div class="success">Se agregó el tutor correctamente</div>');
		}
		else{
			$this->session->set_flashdata('msg','<div class="error">No se pudo asignar el tutor: '.$f->errors.'</div>');
		}
		redirect('alumnos/ver/'.$insert['student_id']);
	}
	
	public function buscar(){
		$string = $_POST['term'].'%';
		$ttr = Tutor::all(array('conditions' => array('nro_documento LIKE ? OR CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ", tutors.nombre,tutors.apellido) LIKE ?', $string, $string, $string), 'limit' => 5));
		$tt = array();
		foreach($ttr as $t){
			$tt[] = array('id' => $t->id, 'value' => $t->apellido.', '.$t->nombre.' - '.$t->nro_documento);
		}
		echo json_encode($tt);
	}

	public function index($offset = 0){
		if(!$offset)
			$this->session->unset_userdata('filtros_tutores');
			
		$datos = $this->session->all_userdata();
				
		$string = isset($datos['filtros_tutores']['string'])?str_replace(' ','%',$datos['filtros_tutores']['string']):'%%'; 
		$estudiante = isset($datos['filtros_tutores']['estudiante'])?str_replace(' ','%',$datos['filtros_tutores']['estudiante']):'%%'; 
		
		$condiciones = '';
		$valores = array();
		
		if($string != '%%'){
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ?)';
			$valores['string'] = $string;
			$valores[] = $string;
			}
			
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= " AND ";
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
			}
		
		$conditions = array_merge(array($condiciones), $valores);
	
		$join = 'LEFT JOIN families ON (tutors.id = families.tutor_id)
				LEFT JOIN students ON (families.student_id = students.id)';
				
		$config['total_rows'] = Tutor::count(array('joins'=>$join,'conditions' => $conditions));
		if($config['total_rows']>0){
			$config['base_url'] = site_url('tutores/index');
			$config['per_page'] = '15'; 
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'Último &rarr;';
			$this->load->library('pagination', $config);
			
			$tutores = Tutor::all(array('order'=>'apellido, nombre ASC', 'select' => 'DISTINCT tutors.*', 'joins'=>$join,'conditions' => $conditions, 'limit' => $config['per_page'], 'offset' => $offset) );
			
			$this->table->set_heading('Apellido','Nombre', 'Telefono','Celular','Email','Acciones');
			foreach($tutores as $al){
				$this->table->add_row(
					$al->apellido,
					$al->nombre,
					$al->telefono_fijo,
					$al->celular,
					$al->email,
					anchor('tutores/editar/'.$al->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar tutor"').' '.
					anchor('tutores/eliminar/'.$al->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar tutor"')
				);
			}
			
			$data['tutores'] = $this->table->generate();
			$data['pagination'] = $this->pagination->create_links();
		}
		else{
			$data['tutores'] = 'No hay resultados para mostrar.';
			$data['pagination'] = '';
			}
		$data['filtros'] = $valores; 
		$this->template->write_view('content', 'tutores/index',$data);
		$this->template->render();
	}
	
	public function filters($offset=0){
		$string = '%'.str_replace(' ','%',$this->input->post('string')).'%';
		$estudiante = '%'.str_replace(' ','%',$this->input->post('estudiante')).'%';
				
		$condiciones = '';
		$valores = array();
		
		if($string != '%%'){
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ?)';
			$valores['string'] = $string;
			$valores[] = $string;
			}
			
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= " AND ";
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
			}
		
		$this->session->set_userdata('filtros_tutores', $valores);	
		$conditions = array_merge(array($condiciones), $valores);
	
		$join = 'LEFT JOIN families ON (tutors.id = families.tutor_id)
				LEFT JOIN students ON (families.student_id = students.id)';
				
		$config['total_rows'] = Tutor::count(array('joins'=>$join,'conditions' => $conditions));
		if($config['total_rows']>0){
			$config['base_url'] = site_url('tutores/index');
			$config['per_page'] = '15'; 
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'Último &rarr;';
			$this->load->library('pagination', $config);
			
			$tutores = Tutor::all(array('order'=>'apellido, nombre ASC', 'select' => 'DISTINCT tutors.*', 'joins'=>$join,'conditions' => $conditions, 'limit' => $config['per_page'], 'offset' => $offset) );
			
			$this->table->set_heading('Apellido','Nombre', 'Telefono','Celular','Email','Acciones');
			foreach( $tutores as $al){
				$this->table->add_row(
					$al->apellido,
					$al->nombre,				
					$al->telefono_fijo,
					$al->celular,
					$al->email,
					anchor('tutores/editar/'.$al->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar tutor"').' '.
					anchor('tutores/eliminar/'.$al->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar tutor"')
				);
			}
			echo $this->table->generate();
			
			echo '<div class="pagination">';
			echo $this->pagination->create_links();
			echo '</div>';
		}
		else{
			echo 'No hay resultados para mostrar';
			}
	}
	
	
	public function agregar(){				
		$data = array();
		if ( $_POST ){
			$this->load->helper('date');
			$this->load->library('Utils');
			$insert = $_POST;
			$insert['fecha_nacimiento'] = $this->utils->fecha_formato('%Y-%m-%d', $insert['fecha_nacimiento']);
			
			$tutor = new Tutor( 
				elements( array(
					'city_id',
					'nombre',
					'apellido',
					'relacion',
					'email',
					'fecha_nacimiento',
					'tipo_documento',
					'nro_documento',
					'domicilio',
					'nacionalidad',
					'telefono_fijo',
					'telefono_trabajo',
					'celular',
					'ocupacion',
					'sexo'
				), $insert )
			);
			
			if( $tutor->is_valid( ) ){
				$tutor->save();
				$this->session->set_flashdata( 'msg','<div class="success">El tutor se guardó correctamente.</div>' );
				redirect($_POST['ir_a']);
			}
			else{
				$data['errors'] = $tutor->errors;
				$data['ir_a'] = isset($_POST['ir_a'])?$_POST['ir_a']:$_SERVER['HTTP_REFERER'];
			}
		}
		$data['ir_a'] = isset($data['ir_a'])?$data['ir_a']:$_SERVER['HTTP_REFERER'];
		$data['paises'] = Country::all();
		$data['provincias'] = State::all();
		$data['ciudades'] = City::all();
		$data['titulo'] = "Agregar Tutor";
		$data['action'] = "tutores/agregar";
		
		$this->template->write_view('content', 'tutores/agregar',$data);
		$this->template->render();
	}
	
	public function editar( $id ){	
		if(!$id){
			$this->session->set_flashdata( 'msg','<div class="error">El tutor solicitado no existe.</div>' );
			redirect('tutores');
		}
		elseif ( $_POST ){
			
			$this->load->helper('date');
			$this->load->library('Utils');
			$insert = $_POST;
			$insert['fecha_nacimiento'] = $this->utils->fecha_formato('%Y-%m-%d', $insert['fecha_nacimiento']);
			
			$tutor = Tutor::find($id);
			
			$tutor->update_attributes(elements( array(
					'city_id',
					'nombre',
					'apellido',
					'relacion',
					'email',
					'fecha_nacimiento',
					'tipo_documento',
					'nro_documento',
					'domicilio',
					'nacionalidad',
					'telefono_fijo',
					'telefono_trabajo',
					'celular',
					'ocupacion',
					'sexo'					
				), $insert )
			);
			
			if( $tutor->is_valid()){
				if($tutor->save()){
					$this->session->set_flashdata( 'msg','<div class="success">El tutor se guardó correctamente.</div>' );
					redirect($_POST['ir_a']);
				}
				else{
					$this->session->set_flashdata( 'msg','<div class="error">Hubo un error al guardar los datos.</div>' );
					redirect('tutores/editar/'.$id);
				}
			}
			else{
				$data['errors'] = $tutor->errors;
				$data['ir_a'] = isset($_POST['ir_a'])?$_POST['ir_a']:$_SERVER['HTTP_REFERER'];
			}
		}
		else {
			$data['a'] = Tutor::find($id);
			$data['ir_a'] = $_SERVER['HTTP_REFERER'];
		}
		
		$data['paises'] = Country::all();
		$data['provincias'] = State::all();
		$data['ciudades'] = City::all();
		$data['titulo'] = "Editar Tutor";
		$data['action'] = "tutores/editar/".$id;
		
		$this->template->write_view('content', 'tutores/agregar',$data);
		$this->template->render();
	}
	
	function eliminar($id){
		try{
			$a = Tutor::find($id);
			$a->delete();
			$this->session->set_flashdata('msg','<div class="success">El tutor fué eliminado correctamente.</div>');
		}
		catch( \Exception $e){
			$this->session->set_flashdata('msg','<div class="success">El tutor no se puede eliminar.</div>');
			}
			
		redirect('tutores');
	}
}
