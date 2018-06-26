<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Descuentos extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->helper('date');
		if(!$this->session->userdata('id')) redirect('auth/login');
	}
	
	function test()
	{
		$becas = Scolarship::all();
		foreach($becas as $b)
		{
			echo $b->amount->id;
		}
	}
	
	public function index($offset = 0)
	{
		if(!$offset)
			$this->session->unset_userdata('filtros_descuentos');
			
		$datos = $this->session->all_userdata();
		
		$estudiante = isset($datos['filtros_descuentos']['string'])?$datos['filtros_descuentos']['string']:'%%'; 
		$tutor = isset($datos['filtros_descuentos']['nombre'])?$datos['filtros_descuentos']['nombre']:'%%'; 
		$concepto = isset($datos['filtros_descuentos']['concepto'])?$datos['filtros_descuentos']['concepto']:0; 
				
		$condiciones = '';
		$valores = array();
		
		if($estudiante !== '%%'){
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ? )';
			$valores['string'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores[] = $tutor;
			$valores['nombre'] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " concept_id = ?";
			$valores['concepto'] = $concepto;
			}
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = ' INNER JOIN students ON students.id = scolarships.student_id';
		$joins .= ' INNER JOIN amounts ON amounts.id = scolarships.amount_id';
		$joins .= ' INNER JOIN families ON families.student_id = students.id';
		$joins .= ' INNER JOIN tutors ON tutors.id = families.tutor_id';
		
		$config['base_url'] = site_url('descuentos/index');
		$config['total_rows'] = Scolarship::count(array('conditions'=>$conditions, 'joins'=>$joins));
		$config['per_page'] = '20';  
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$descuentos = Scolarship::all(array('conditions'=>$conditions, 'joins'=>$joins, 'limit' => $config['per_page'], 'offset' => $offset) );
		
		$this->table->set_heading('Alumno','Concepto','Descuento','Acciones');
		foreach($descuentos as $d)
		{
			$this->table->add_row(
				$d->student->apellido.' '.$d->student->nombre,
				$d->amount->concept->concepto.' '.$d->amount->ciclo_lectivo,
				$d->porcien_descuento.'%',
				anchor('descuentos/editar/'.$d->student_id.'/'.$d->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Modificar"').' '.
				anchor('descuentos/eliminar/'.$d->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular"')
			);
		}
		
		$data['descuentos'] = $this->table->generate();
		$data['conceptos'] = Concept::all();
		$data['filtros']= array($estudiante, $tutor, $concepto);
		$data['pagination'] = $this->pagination->create_links();
		$this->session->set_flashdata('next',base_url('descuentos'));
		$this->template->write_view('content', 'descuentos/index',$data);
		$this->template->render();
	}
	
	public function filters($offset=0){
		$estudiante = '%'.str_replace(' ', '%',$this->input->post('estudiante')).'%';
		$tutor = '%'.str_replace(' ', '%',$this->input->post('tutor')).'%';
		$concepto = $this->input->post('concepto_id');
				
		$condiciones = '';
		$valores = array();
		
		if($estudiante !== '%%'){
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ? )';
			$valores['string'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores[] = $tutor;
			$valores['nombre'] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " concept_id = ?";
			$valores['concepto'] = $concepto;
			}
		
		$this->session->set_userdata('filtros_descuentos', $valores);
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = ' INNER JOIN students ON students.id = scolarships.student_id';
		$joins .= ' INNER JOIN amounts ON amounts.id = scolarships.amount_id';
		$joins .= ' INNER JOIN families ON families.student_id = students.id';
		$joins .= ' INNER JOIN tutors ON tutors.id = families.tutor_id';
		
		$config['base_url'] = site_url('descuentos/index');
		$config['total_rows'] = Scolarship::count(array('joins'=>$joins, 'conditions' => $conditions));
		$config['per_page'] = '20';  
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$descuentos = Scolarship::all(array('joins'=>$joins,'conditions' => $conditions, 'limit' => $config['per_page'], 'offset' => $offset) );
			
		$this->table->set_heading('Alumno','Concepto','Descuento','Acciones');
		foreach($descuentos as $d)
		{
			$this->table->add_row(
				$d->student->apellido.' '.$d->student->nombre,
				$d->amount->concept->concepto.' '.$d->amount->ciclo_lectivo,
				$d->porcien_descuento.'%',
				anchor('descuentos/editar/'.$d->student_id.'/'.$d->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Modificar"').' '.
				anchor('descuentos/eliminar/'.$d->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular"')
			);
		}
		$this->session->set_flashdata('next',$this->agent->referrer());
		echo $this->table->generate();

		echo '<div class="pagination">';
		echo $this->pagination->create_links();
		echo '</div>';
	}
	
	public function reporte(){
		$estudiante = '%'.str_replace(' ', '%',$this->input->post('estudiante')).'%';
		$tutor = '%'.str_replace(' ', '%',$this->input->post('tutor')).'%';
		$concepto = $this->input->post('concepto_id');
				
		$condiciones = '';
		$valores = array();
		
		if($estudiante !== '%%'){
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ? )';
			$valores['string'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores[] = $tutor;
			$valores['nombre'] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones != '') $condiciones .=" AND ";
			$condiciones .= " concept_id = ?";
			$valores['concepto'] = $concepto;
			}
		
		$this->session->set_userdata('filtros_descuentos', $valores);
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = ' INNER JOIN students ON students.id = scolarships.student_id';
		$joins .= ' INNER JOIN amounts ON amounts.id = scolarships.amount_id';
		$joins .= ' INNER JOIN families ON families.student_id = students.id';
		$joins .= ' INNER JOIN tutors ON tutors.id = families.tutor_id';
		
		$config['base_url'] = site_url('descuentos/index');
		$config['total_rows'] = Scolarship::count(array('joins'=>$joins, 'conditions' => $conditions));
		$config['per_page'] = '20';  
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$descuentos = Scolarship::all(array('joins'=>$joins,'conditions' => $conditions));
			
		$this->table->set_heading('Alumno','Concepto','Descuento','Acciones');
		foreach($descuentos as $d)
		{
			$this->table->add_row(
				$d->student->apellido.' '.$d->student->nombre,
				$d->amount->concept->concepto.' '.$d->amount->ciclo_lectivo,
				$d->porcien_descuento.'%',
				anchor('descuentos/editar/'.$d->student_id.'/'.$d->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Modificar"').' '.
				anchor('descuentos/eliminar/'.$d->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular"')
			);
		}

		$data['titulo'] = "Reporte de descuentos";
		$data['reporte'] = $this->table->generate();
	
		$this->session->set_flashdata('next',$this->agent->referrer());
		$this->template->set_template('reporte');
		$this->template->write_view('content', 'pagos/reporte',$data);
		$this->template->render();
	}
	
	public function agregar($alumno)
	{				
		if ( $_POST )
		{	$campos = $_POST;
			foreach($_POST['amount_id'] as $amount)
			{
				$beca = array(
					'student_id' => $alumno,
					'amount_id' => $amount,
					'porcien_descuento' => $campos['porcien_descuento']
				);
				
				Scolarship::create($beca);
			}
			$this->session->set_flashdata( 'msg','<div class="success">El descuento se guardó correctamente.</div>' );
			redirect('alumnos/ver/'.$alumno); //redirect($this->session->flashdata('next'));
		}
		
		$conditions = array(
			'conditions' => array(
				'student_id = ? AND pagado = 0 AND amount_id NOT IN (
												SELECT amount_id
												FROM scolarships
												WHERE student_id = ? )',
				$alumno, $alumno
			)
		);
		
		$this->session->set_flashdata('next',$this->agent->referrer());
		
		$data['deudas'] = Debt::all($conditions);
		$data['alumno'] = $alumno;
		$data['titulo'] = "Aplicar descuento";
		$data['action'] = "descuentos/agregar";
		
		$this->template->write_view('content', 'descuentos/agregar',$data);
		$this->template->render();
	}
	
	public function editar( $alumno, $id )
	{	
		if(!$id)
		{
			$this->session->set_flashdata( 'msg','<div class="error">El descuento solicitado no existe.</div>' );
		}
		elseif ( $_POST )
		{
			$this->load->library('Utils');
			$beca = Scolarship::find($id);
			$beca->update_attributes(elements(array('porcien_descuento', 'amount_id'), $_POST ));
			$beca->save();
			$this->session->set_flashdata('msg','<div class="success">El descuento se guardó correctamente.</div>' );
			redirect($this->session->flashdata('next'));			
		}
		
		$data['a'] = Scolarship::find($id);
		
		$conditions = array(
			'conditions' => array(
				'student_id = ? AND pagado = 0',
				$alumno
			)
		);
		
		if($id) $data['id'] = $id;
		
		$this->session->set_flashdata('next',$this->agent->referrer());
		
		$data['deudas'] = Debt::all($conditions);
		$data['alumno'] = $alumno;
		$data['titulo'] = "Editar descuento";
		$data['action'] = "descuentos/editar/".$id;
		
		$this->template->write_view('content', 'descuentos/agregar',$data);
		$this->template->render();
	}
	
	function eliminar($id)
	{
		try{
			$a = Scolarship::find($id);
			$a->delete();
			$this->session->set_flashdata('msg','<div class="success">El descuento fué eliminado correctamente.</div>');
		}
		catch( \Exception $e){
			$this->session->set_flashdata('msg','<div class="success">El descuento no puede ser eliminado.</div>');
		}
			
		redirect($this->agent->referrer());
	}
}
