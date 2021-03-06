<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Importes extends CI_Controller {

	function __construct(){
		parent::__construct();
		$this->load->helper('date');
		if(!$this->session->userdata('id')) redirect('auth/login');
	}
	
	public function index($offset = 0){
		if(!$offset)
			$this->session->unset_userdata('filtros_importes');
			
		$datos = $this->session->all_userdata();
		
		$fecha_desde = isset($datos['filtros_importes']['fecha_desde'])?$datos['filtros_importes']['fecha_desde']:date('Y-m-d', mktime(0,0,0,1,1,date('Y')));
		$fecha_hasta = isset($datos['filtros_importes']['fecha_hasta'])?$datos['filtros_importes']['fecha_hasta']:date('Y-m-d', mktime(0,0,0,12,31,date('Y')));
		$concepto = isset($datos['filtros_importes']['concepto'])?$datos['filtros_importes']['concepto']:'0';
		$nivel = isset($datos['filtros_importes']['nivel'])?$datos['filtros_importes']['nivel']:'0';
		$curso = isset($datos['filtros_importes']['curso'])?$datos['filtros_importes']['curso']:'0';

		$condiciones = '';
		$valores = array();
		
		if($fecha_desde != ''){
			$condiciones .= " fecha >= ?";
			$valores['fecha_desde'] = $fecha_desde;
			}
		
		if($fecha_hasta != ''){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " fecha <= ?";
			$valores['fecha_hasta'] = $fecha_hasta;
			}
		
		if($concepto > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " concept_id = ?";
			$valores['concepto'] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " level_id = ?";
			$valores['nivel'] = $nivel;
			}
		
		if($curso > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " courses.id = ?";
			$valores['curso'] = $curso;
			}
		
		$conditions = array_merge(array($condiciones), $valores);
		$joins = '	INNER JOIN courses ON courses.id = amounts.course_id ';
		
		$config['base_url'] = site_url('importes/index');
		$config['total_rows'] = Amount::count(array('joins'=>$joins, 'conditions' => $conditions));
		$config['per_page'] = '20';  
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$importes = Amount::all(array(	'joins'=>$joins,
										'conditions' => $conditions, 
										'limit' => $config['per_page'], 
										'offset' => $offset, 
										'order'=>'amounts.fecha ASC, amounts.concept_id ASC, amounts.id ASC') );
		
		$this->table->set_heading('Orden','Concepto', 'Ciclo Lectivo', 'Curso','Fecha Vto','Importe','Acciones');
		foreach($importes as $importe){
			$this->table->add_row(
				$importe->id,
				$importe->concept->concepto,
				$importe->ciclo_lectivo,
				$importe->course->course.' '.$importe->course->level->nivel,
				$importe->fecha->format('d/m/Y'),
				$importe->importe,
				($importe->pago_parcial==1)?'Si':'No',
				anchor('importes/editar/'.$importe->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('importes/eliminar/'.$importe->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		
		$data['importes'] = $this->table->generate();
		$data['pagination'] = $this->pagination->create_links();
		$data['conceptos'] = Concept::all(array('order'=>'id'));
		$data['filtros'] = $valores;
		$data['niveles'] = Level::all(array('conditions'=>array('id != 1')));
		$data['cursos'] = Course::find('all', array('select' => 'id,level_id,course'));
				
		$this->template->write_view('content', 'importes/index',$data);
		$this->template->render();
	}
	
	public function filters($offset=0){
		$fecha_desde = $this->input->post('fecha_desde');
		$fecha_hasta = $this->input->post('fecha_hasta');
		$concepto = $this->input->post('concepto_id');
		$nivel = $this->input->post('level_id');
		$curso = $this->input->post('course_id');
				
		$condiciones = '';
		$valores = array();
		
		if($fecha_desde != ''){
			$condiciones .= " fecha >= ?";
			$valores['fecha_desde'] = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
		}
		
		if($fecha_hasta != ''){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " fecha <= ?";
			$valores['fecha_hasta'] = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
		
		if($concepto > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " concept_id = ?";
			$valores['concepto'] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " level_id = ?";
			$valores['nivel'] = $nivel;
			}
		
		if($curso > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " courses.id = ?";
			$valores['curso'] = $curso;
			}
		
		$this->session->set_userdata('filtros_importes', $valores);
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = '	INNER JOIN courses ON courses.id = amounts.course_id ';
		
		$config['base_url'] = site_url('importes/index');
		$config['total_rows'] = Amount::count(array('joins'=>$joins, 'conditions' => $conditions));
		$config['per_page'] = '20';  
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$importes = Amount::all(array(	'joins'=>$joins,
										'conditions' => $conditions, 
										'limit' => $config['per_page'], 
										'offset' => $offset, 
										'order'=>'amounts.fecha ASC, amounts.concept_id ASC, amounts.id ASC') );
			
		$this->table->set_heading('Orden','Concepto', 'Ciclo Lectivo','Curso','Fecha Vto','Importe','Pago Parcial','Acciones');
		foreach($importes as $importe){
			$this->table->add_row(
				$importe->id,
				$importe->concept->concepto,
				$importe->ciclo_lectivo,
				$importe->course->course.' '.$importe->course->level->nivel,
				$importe->fecha->format('d/m/Y'),
				$importe->importe,
				($importe->pago_parcial==1)?'Si':'No',
				anchor('importes/editar/'.$importe->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('importes/eliminar/'.$importe->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		echo $this->table->generate();
		
		echo '<div class="pagination">';
		echo $this->pagination->create_links();
		echo '</div>';
	}
	
	public function agregar(){				
		$data = array();
		if ( $_POST ){
			$i = Amount::connection();
			try{
				$i->transaction();
				$campos = $_POST;
							
				list($dia,$mes,$anio) = explode('/',$_POST['fecha']);
				$fecha = mdate('%Y-%m-%d', normal_to_unix($_POST['fecha']));
				
				if(!isset($_POST['pago'])){
					$campos['pago_parcial'] = 0;
					$campos['pago_eventual'] = 0;
				}
				elseif($_POST['pago']){
					$campos['pago_parcial'] = 0;
					$campos['pago_eventual'] = 1;
					}
					else{
						$campos['pago_parcial'] = 1;
						$campos['pago_eventual'] = 0;
					}
				
				foreach($_POST['concept_id'] as $concept){
					$campos['concept_id'] = $concept;
					
					foreach($_POST['course_id'] as $course){
						$campos['course_id'] = $course;
						$campos['fecha']= ($concept>2 && $concept<13)?date('Y-m-d', mktime(0,0,0, $concept, $dia,$anio)):$fecha;
						
						$recargo = false;
							
						if($concept == 2){
							$recargo = Amount::find(array('conditions'=>array('concept_id = ? AND course_id = ?', $concept , $course)));						
						}
							
						if($recargo){
							$recargo->update_attributes(array('ciclo_lectivo'=>$campos['ciclo_lectivo'], 'fecha'=>$fecha, 'pago_parcial'=>$campos['pago_parcial'], 'pago_eventual' =>$campos['pago_eventual'], 'importe'=>$campos['importe']));
							$recargo->save();
									 
							$imp_id = $recargo->id;
						}
						else{
							Amount::create( 
								elements( array('concept_id', 'course_id', 'fecha','importe','ciclo_lectivo','pago_parcial','pago_eventual'), $campos)
								);
								
							$imp_id = $i->insert_id();
						}
							
						if(!$campos['pago_eventual']){
							$conditions = array('course_id = ? AND ciclo_lectivo = ?', $course, $campos['ciclo_lectivo']);
								
							$inscriptos = Inscription::all(array('conditions'=>$conditions));
								
							foreach($inscriptos as $insc){
								$d = Debt::find(array('joins'=>'join amounts on debts.amount_id = amounts.id','conditions'=>array('debts.student_id = ? AND amounts.concept_id = ?', $insc->student_id, 2 )));
								if($d){
									$d->update_attributes(array('amount_id'=>$imp_id,'inscription_id'=>$insc->id));
									$d->save();
									}
								else{
									$deuda = array(
												'student_id' => $insc->student_id,
												'amount_id' => $imp_id,
												'inscription_id' => $insc->id
											);
									Debt::create($deuda);
								}
							}
						}
					} // fin for curso
				}
				//die;
				$this->session->set_flashdata('msg','<div class="success">El importe se agregó correctamente.</div>');
				$i->commit();
				redirect('importes');
			}
			catch (\Exception $e)
			{
				$i->rollback();
				$this->session->set_flashdata('msg','<div class="error">Hubo un error al agregar el importe, intente nuevamente. Verifique que el importe no exista previamente</div>');
				redirect('importes');
			}
		}
		
		$data['titulo'] = "Agregar Importe";
		$data['action'] = "importes/agregar";
		$data['conceptos']= Concept::all(array('order'=>'concepts.id ASC'));
		$data['cursos']=Course::all(array('joins'=>array('level'),'conditions'=>array('courses.id != 1')));
		
		$this->template->write_view('content', 'importes/agregar',$data);
		$this->template->render();
	}
	
	public function editar( $id ){	
		if(!$id){
			$this->session->set_flashdata( 'msg','<div class="error">El Importe solicitado no existe.</div>' );
			redirect('importes');
		}
		elseif ( $_POST ){
			$this->load->library('Utils');
					
			$importe = Amount::find($id);
			
			$campos = $_POST;
			$campos['fecha']= mdate('%Y-%m-%d', normal_to_unix($_POST['fecha']));
			
			if(!isset($_POST['pago'])){
					$campos['pago_parcial'] = 0;
					$campos['pago_eventual'] = 0;
				}
				elseif($_POST['pago']){
					$campos['pago_parcial'] = 0;
					$campos['pago_eventual'] = 1;
					}
					else{
						$campos['pago_parcial'] = 1;
						$campos['pago_eventual'] = 0;
					}
			
			$importe->update_attributes(elements(array('concept_id', 'course_id', 'fecha','importe','pago_parcial', 'pago_eventual','ciclo_lectivo'), $campos ));
			
			if( $importe->is_valid( ) ){
				if($importe->save()){
					$this->session->set_flashdata( 'msg','<div class="success">El Importe se guardó correctamente.</div>' );
					redirect('importes');
				}
				else{
					$this->session->set_flashdata( 'msg','<div class="error">Hubo un error al guardar los datos.</div>' );
					redirect($this->agent->referrer());
				}
			}
			else{
				$data['errors'] = $importe->errors;
			}
		}
		else $data['a'] = Amount::find($id);
		
		$data['titulo'] = "Editar Importe";
		$data['action'] = "importes/editar/".$id;
		$data['conceptos']= Concept::all();
		$data['cursos']=Course::all(array('joins'=>array('level'),'conditions'=>array('courses.id != 1')));
		
		$this->template->write_view('content', 'importes/agregar',$data);
		$this->template->render();
	}
	
	function eliminar($id){
		try{
			$a = Amount::find($id);
			$a->delete();
			$this->session->set_flashdata('msg','<div class="success">El Importe fué eliminado correctamente.</div>');
		}
		catch (\Exception $e){
			$this->session->set_flashdata('msg','<div class="error">El importe ya ha sido utilizado no puede ser borrado.</div>');			
		}
		redirect('importes');
	}
}
