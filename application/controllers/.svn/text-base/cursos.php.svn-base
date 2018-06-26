<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cursos extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
	}

	public function index($offset = 0)
	{
		if(!$offset)
			$this->session->unset_userdata('filtros_cursos');
			
		$datos = $this->session->all_userdata();
		
		$string = isset($datos['filtros_cursos']['curso'])?$datos['filtros_cursos']['curso']:'%%';
		$c = isset($datos['filtros_cursos']['nivel'])?$datos['filtros_cursos']['nivel']:0;
		
		$condiciones = '';
		$valores = array();
		
		if($string != '%%'){
			$condiciones .= " course LIKE ?";
			$valores[] = $string;
			}
		
		if($c > 0){
			if($condiciones != ''){
				$condiciones .= " AND ";
				}
			$condiciones .= " level_id = ?";
			$valores[] = $c;
		}
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$config['base_url'] = site_url('cursos/index');
		$config['total_rows'] = Course::count(array('joins'=>array('level'),'conditions' => $conditions));
		$config['per_page'] = '20'; 
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$cursos = Course::all(array('joins'=>array('level'),'conditions' => $conditions, 'limit' => $config['per_page'], 'offset' => $offset) );
		
		$this->table->set_heading('Curso','Nivel', 'Acciones');
		foreach($cursos as $curso)
		{
			$this->table->add_row(
				$curso->course,
				$curso->level->nivel,
				($curso->id==1)?'':anchor('cursos/editar/'.$curso->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('cursos/eliminar/'.$curso->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		
		$data['cursos'] = $this->table->generate();
		$data['pagination'] = $this->pagination->create_links();
		$data['niveles']= Level::all();
		$data['filtros']= array($string, $c);
		
		$this->template->write_view('content', 'cursos/index',$data);
		$this->template->render();
	}
	
	public function filters($offset=0)
	{
		$string = '%'.$this->input->post('curso').'%';
		$c = $this->input->post('level_id');
		
		$condiciones = '';
		$valores = array();
		
		if($string != '%%'){
			$condiciones .= " course LIKE ?";
			$valores['curso'] = $string;
			}
		
		if($c > 0){
			if($condiciones != ''){
				$condiciones .= " AND ";
				}
			$condiciones .= " level_id = ?";
			$valores['nivel'] = $c;
		}
		
		$this->session->set_userdata('filtros_cursos', $valores);
		$conditions = array_merge(array($condiciones), $valores);
		
		$config['base_url'] = site_url('cursos/index');
		$config['total_rows'] = Course::count(array('joins'=>array('level'),'conditions' => $conditions));
		$config['per_page'] = '20'; 
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$cursos = Course::all(array('joins'=>array('level'),'conditions' => $conditions, 'limit' => $config['per_page'], 'offset' => $offset) );
		
		$this->table->set_heading('Curso','Nivel', 'Acciones');
		foreach($cursos as $curso)
		{
			$this->table->add_row(
				$curso->course,
				$curso->level->nivel,				
				($curso->id==1)?'':anchor('cursos/editar/'.$curso->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('cursos/eliminar/'.$curso->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		echo $this->table->generate();
		
		echo '<div class="pagination">';
		echo $this->pagination->create_links();
		echo '</div>';
	}
	
	public function agregar()
	{				
		$data = array();
		if ( $_POST )
		{
			$curso = new Course( 
				elements( array('course','level_id'), $_POST )
			);
			if( $curso->is_valid( ) )
			{
				$curso->save();
				$this->session->set_flashdata( 'msg','<div class="success">La curso se guardó correctamente.</div>' );
				redirect('cursos');
			}
			else
			{
				$data['errors'] = $curso->errors;
			}
		}
		
		$data['niveles']= Level::all();
		$data['titulo'] = "Agregar Curso";
		$data['action'] = "cursos/agregar";
		
		$this->template->write_view('content', 'cursos/agregar',$data);
		$this->template->render();
	}
	
	public function editar( $id )
	{	
		if(!$id)
		{
			$this->session->set_flashdata( 'msg','<div class="error">La curso solicitada no existe.</div>' );
			redirect('cursos');
		}
		elseif ( $_POST )
		{
			$this->load->helper('date');
			$this->load->library('Utils');
					
			$curso = Course::find($id);
			
			$curso->update_attributes(elements( array('course', 'level_id' ), $_POST ));
			
			if( $curso->is_valid( ) )
			{
				if($curso->save())
				{
					$this->session->set_flashdata( 'msg','<div class="success">La curso se guardó correctamente.</div>' );
					redirect('cursos');
				}
				else
				{
					$this->session->set_flashdata( 'msg','<div class="error">Hubo un error al guardar los datos.</div>' );
					redirect('cursos/editar/'.$id);
				}
			}
			else
			{
				$data['errors'] = $curso->errors;
			}
		}
		else $data['a'] = Course::find($id);
		
		$data['niveles']= Level::all();
		$data['titulo'] = "Editar Curso";
		$data['action'] = "cursos/editar/".$id;
		
		$this->template->write_view('content', 'cursos/agregar',$data);
		$this->template->render();
	}
	
	function eliminar($id)
	{
		$a = Course::find($id);
		$a->delete();
		$this->session->set_flashdata('msg','<div class="success">El curso se eliminó correctamente.</div>');
		redirect('cursos');
	}
}
