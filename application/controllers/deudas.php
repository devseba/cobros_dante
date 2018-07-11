<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Deudas extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
		$this->load->helpers('date');
	}

	public function index($offset = 0){
		if(!$offset)
			$this->session->unset_userdata('fs_deudas');
			
		$datos = $this->session->all_userdata();
		
		$estudiante = isset($datos['fs_deudas']['estudiante'])?$datos['fs_deudas']['estudiante']:'%%'; 
		$tutor = isset($datos['fs_deudas']['tutor'])?$datos['fs_deudas']['tutor']:'%%'; 
		$concepto = isset($datos['fs_deudas']['concepto'])?$datos['fs_deudas']['concepto']:0; 
		$nivel = isset($datos['fs_deudas']['nivel'])?$datos['fs_deudas']['nivel']:0; 
		$curso = isset($datos['fs_deudas']['curso'])?$datos['fs_deudas']['curso']:0; 
		$fecha_desde = isset($datos['fs_deudas']['fecha_desde'])?$datos['fs_deudas']['fecha_desde']:date('Y-m-d', mktime(0,0,0,1,1,date('Y'))); 
		$fecha_hasta = isset($datos['fs_deudas']['fecha_hasta'])?$datos['fs_deudas']['fecha_hasta']:date('Y-m-d', mktime(0,0,0,date('m'),28,date('Y'))); 
		
		$condiciones = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$valores[] = 0;
		$valores[] = 2;
		$valores[] = 0;
		
		$cond = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$val[] = 0;
		$val[] = 2;
		$val[] = 0;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;				
		
		$this->session->set_userdata('fs_deudas', $valores);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id AND students.baja = 0
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		$todos = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		if(sizeof($todos)){
			$config['base_url'] = site_url('deudas/index');
			$config['total_rows'] = sizeof($todos);
			$config['per_page'] = '20';  
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->load->library('pagination', $config);
			
			$deudas = array();
			$deudas = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'order'=>'amounts.course_id ASC, students.apellido asc, students.nombre asc, amounts.fecha asc', 'group'=>'concepts.id, students.id','limit' => $config['per_page'], 'offset' => $offset) );
			
			$students = array();
			
			foreach($todos as $e){
				$students[] = $e->student_id;
				$ids_deudas[]= $e->id;
			}
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " debts.student_id IN (?)";
			$val[] = array_unique($students);
			
			$condition = array_merge(array($cond),$val);
				
			$deuda_filtros = Debt::find(array(
							'select'=> 'IF(SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5) IS NULL,0, SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5)) AS deuda_total',
							'joins'=>'	LEFT JOIN students ON students.id = debts.student_id 
										LEFT JOIN amounts ON amounts.id = debts.amount_id 
										LEFT JOIN courses ON courses.id = amounts.course_id 
										LEFT JOIN scolarships ON scolarships.amount_id = debts.amount_id AND scolarships.student_id = debts.student_id', 'conditions'=>$condition));
			
			$pagos_deudas = Detail::find(array('select'=>'SUM(details.importe) AS pagos_total',
												'joins'=> 'LEFT JOIN payments ON payments.id = details.payment_id',
												'conditions'=>array('anulado = ? AND debt_id IN (?)',0,array_unique($ids_deudas))));
						
			$total_deuda = 0;
			$this->table->set_heading('Fecha', 'Estudiante', 'Concepto','Curso','Importe');
			foreach($deudas as $deuda){
				$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
				$this->table->add_row(
					$deuda->amount->fecha->format('d/m/Y'),
					$deuda->student->apellido.' '.$deuda->student->nombre,
					$deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo,
					$deuda->amount->course->course,
					'$'.($imp_deuda-$pago)
				);
				$total_deuda += ($imp_deuda-$pago);
			}
			
			$data['deudas'] = $this->table->generate();
			$data['total_deuda'] = '<h3 align="right"><strong><em>El total de la deuda: $'.($deuda_filtros->deuda_total-$pagos_deudas->pagos_total).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</em></strong></h3>';
			$data['pagination'] = $this->pagination->create_links();
		}
		else{
			$data['deudas'] = "No hay resultados a mostrar";
		}
		
		$data['conceptos'] = Concept::all();
		$data['niveles'] = Level::all(array('conditions'=>array('id != 1')));
		$data['cursos'] = Course::find('all', array('select' => 'id,level_id,course'));		
		$data['filtros']= $valores;
		$this->session->set_flashdata('next',base_url('deudas'));
		$this->template->write_view('content', 'deudas/index',$data);
		$this->template->render();
	}
	
	public function deudasTodas($offset = 0){
		$datos = $this->session->all_userdata();
		
		$estudiante = isset($datos['fs_deudas']['estudiante'])?$datos['fs_deudas']['estudiante']:'%%'; 
		$tutor = isset($datos['fs_deudas']['tutor'])?$datos['fs_deudas']['tutor']:'%%'; 
		$concepto = isset($datos['fs_deudas']['concepto'])?$datos['fs_deudas']['concepto']:0; 
		$nivel = isset($datos['fs_deudas']['nivel'])?$datos['fs_deudas']['nivel']:0; 
		$curso = isset($datos['fs_deudas']['curso'])?$datos['fs_deudas']['curso']:0; 
		$fecha_desde = date('Y-m-d', mktime(0,0,0,1,1,'2000')); 
		$fecha_hasta = isset($datos['fs_deudas']['fecha_hasta'])?$datos['fs_deudas']['fecha_hasta']:date('Y-m-d',mktime(0,0,0,12,31,date('Y')));
		
		$condiciones = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$valores[] = 0;
		$valores[] = 2;
		$valores[] = 0;
		
		$cond = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$val[] = 0;
		$val[] = 2;
		$val[] = 0;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;				
		
		$this->session->set_userdata('fs_deudas', $valores);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id AND students.baja = 0
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		
		$todos = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		if(sizeof($todos)){
			$config['base_url'] = site_url('deudas/index');
			$config['total_rows'] = sizeof($todos);
			$config['per_page'] = '20';  
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->load->library('pagination', $config);
			
			$deudas = array();
			$deudas = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'order'=>'amounts.course_id ASC, students.apellido asc, students.nombre asc, amounts.fecha asc', 'group'=>'concepts.id, students.id','limit' => $config['per_page'], 'offset' => $offset) );
			
			$students = array();
			
			foreach($todos as $e){
				$students[] = $e->student_id;
				$ids_deudas[]= $e->id;
				}
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " debts.student_id IN (?)";
			$val[] = array_unique($students);
			
			$condition = array_merge(array($cond),$val);
				
			$deuda_filtros = Debt::find(array(
							'select'=> 'IF(SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5) IS NULL,0, SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5)) AS deuda_total',
							'joins'=>'	LEFT JOIN students ON students.id = debts.student_id 
										LEFT JOIN amounts ON amounts.id = debts.amount_id 
										LEFT JOIN courses ON courses.id = amounts.course_id 
										LEFT JOIN scolarships ON scolarships.amount_id = debts.amount_id AND scolarships.student_id = debts.student_id', 'conditions'=>$condition));
			
			$pagos_deudas = Detail::find(array('select'=>'SUM(details.importe) AS pagos_total',
												'joins'=> 'LEFT JOIN payments ON payments.id = details.payment_id',
												'conditions'=>array('anulado = ? AND debt_id IN (?)',0,array_unique($ids_deudas))));
						
			$total_deuda = 0;
			$this->table->set_heading('Fecha', 'Estudiante', 'Concepto','Curso','Importe');
			foreach($deudas as $deuda){
				$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
				$this->table->add_row(
					$deuda->amount->fecha->format('d/m/Y'),
					$deuda->student->apellido.' '.$deuda->student->nombre,
					$deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo,
					$deuda->amount->course->course,
					'$'.($imp_deuda-$pago)
				);
				$total_deuda += ($imp_deuda-$pago);
			}
			
			$data['deudas'] = $this->table->generate();
			$data['total_deuda'] = '<h3 align="right"><strong><em>El total de la deuda: $'.($deuda_filtros->deuda_total-$pagos_deudas->pagos_total).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</em></strong></h3>';
			$data['pagination'] = $this->pagination->create_links();
		}
		else{
			$data['deudas'] = "No hay resultados a mostrar";
		}
		
		$data['conceptos'] = Concept::all();
		$data['niveles'] = Level::all(array('conditions'=>array('id != 1')));
		$data['cursos'] = Course::find('all', array('select' => 'id,level_id,course'));		
		$data['filtros']= $valores;		
		$this->session->set_flashdata('next',base_url('deudas'));
		$this->template->write_view('content', 'deudas/index2',$data);
		$this->template->render();
	}

	public function filters($offset = 0){
		$estudiante = '%'.str_replace(' ', '%',$this->input->post('estudiante')).'%';
		$tutor = '%'.str_replace(' ', '%',$this->input->post('tutor')).'%';
		$fecha_desde = ($this->input->post('fecha_desde')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_desde'))):'2011-01-01';
		$fecha_hasta = ($this->input->post('fecha_hasta')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_hasta'))):date('Y-m-d');
		$concepto = $this->input->post('concepto_id');
		$nivel = $this->input->post('level_id');
		$curso = $this->input->post('course_id');
		
		$condiciones = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$valores[] = 0;
		$valores[] = 2;
		$valores[] = 0;
		
		
		$cond = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$val[] = 0;
		$val[] = 2;
		$val[] = 0;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;				
		
		$this->session->set_userdata('fs_deudas', $valores);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id 
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		
		$todos = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		if(sizeof($todos)){
			$config['base_url'] = site_url('deudas/index');
			$config['total_rows'] = sizeof($todos);
			$config['per_page'] = '20';  
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->load->library('pagination', $config);
			
			$deudas = array();
			$deudas = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'order'=>'amounts.course_id ASC, students.apellido asc, students.nombre asc, amounts.fecha asc', 'group'=>'concepts.id, students.id','limit' => $config['per_page'], 'offset' => $offset) );
			
			$students = array();
			
			foreach($todos as $e){
				$students[] = $e->student_id;
				$ids_deudas[]= $e->id;
				}
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " debts.student_id IN (?)";
			$val[] = array_unique($students);
			
			$condition = array_merge(array($cond),$val);
				
			$deuda_filtros = Debt::find(array(
							'select'=> 'IF(SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5) IS NULL,0, SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5)) AS deuda_total',
							'joins'=>'	LEFT JOIN students ON students.id = debts.student_id
										LEFT JOIN amounts ON amounts.id = debts.amount_id 
										LEFT JOIN courses ON courses.id = amounts.course_id 
										LEFT JOIN scolarships ON scolarships.amount_id = debts.amount_id AND scolarships.student_id = debts.student_id', 'conditions'=>$condition));
			
			$pagos_deudas = Detail::find(array('select'=>'SUM(details.importe) AS pagos_total',
												'joins'=> 'LEFT JOIN payments ON payments.id = details.payment_id',
												'conditions'=>array('anulado = ? AND debt_id IN (?)',0,array_unique($ids_deudas))));
						
			$total_deuda = 0;
			$this->table->set_heading('Fecha', 'Estudiante', 'Concepto','Curso','Importe');
			foreach($deudas as $deuda){
				$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
				$this->table->add_row(
					$deuda->amount->fecha->format('d/m/Y'),
					$deuda->student->apellido.' '.$deuda->student->nombre,
					$deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo,
					$deuda->amount->course->course,
					'$'.($imp_deuda-$pago)
				);
				$total_deuda += ($imp_deuda-$pago);
			}

			$this->session->set_flashdata('next',$this->agent->referrer());
			echo $this->table->generate();
			echo '<h3 align="right"><strong><em>El total de la deuda: $'.($deuda_filtros->deuda_total-$pagos_deudas->pagos_total).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;</em></strong></h3>';
			echo '<div class="pagination">';
			echo $this->pagination->create_links();
			echo '</div>';
		}
		else{
			echo "No hay resultados a mostrar";
			}
	}
	
	public function reporte(){
		$estudiante = '%'.str_replace(' ', '%',$this->input->post('estudiante')).'%';
		$tutor = '%'.str_replace(' ', '%',$this->input->post('tutor')).'%';
		$fecha_desde = ($this->input->post('fecha_desde')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_desde'))):'';
		$fecha_hasta = ($this->input->post('fecha_hasta')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_hasta'))):date('Y-m-d');
		$concepto = $this->input->post('concepto_id');
		$nivel = $this->input->post('level_id');
		$curso = $this->input->post('course_id');
		
		$condiciones = 'pagado = ? AND concept_id!=?';
		$valores[] = 0;
		$valores[] = 2;
		
		$cond = 'pagado = ? AND concept_id!=?';
		$val[] = 0;
		$val[] = 2;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;				
		
		$this->session->set_userdata('fs_deudas', $valores);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		
		$todos = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		if(sizeof($todos)){
			$config['base_url'] = site_url('deudas/index');
			$config['total_rows'] = sizeof($todos);
			$config['per_page'] = '20';  
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->load->library('pagination', $config);
			
			$deudas = array();
			$deudas = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'order'=>'amounts.course_id ASC, students.apellido asc, students.nombre asc, amounts.fecha asc', 'group'=>'concepts.id, students.id') );
			
			$students = array();
			
			foreach($todos as $e){
				$students[] = $e->student_id;
				$ids_deudas[] = $e->id;
				}
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " debts.student_id IN (?)";
			$val[] = array_unique($students);
			
			$condition = array_merge(array($cond),$val);
		
			$deuda_filtros = Debt::find(array(
							'select'=> 'IF(SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5) IS NULL,0, SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5)) AS deuda_total',
							'joins'=>'	LEFT JOIN amounts ON amounts.id = debts.amount_id 
										LEFT JOIN courses ON courses.id = amounts.course_id 
										LEFT JOIN scolarships ON scolarships.amount_id = debts.amount_id AND scolarships.student_id = debts.student_id', 'conditions'=>$condition));
			
			$pagos_deudas = Detail::find(array('select'=>'SUM(details.importe) AS pagos_total',
												'joins'=> 'LEFT JOIN payments ON payments.id = details.payment_id',
												'conditions'=>array('anulado = ? AND debt_id IN (?)',0,array_unique($ids_deudas))));
						
			$total_deuda = 0;
			$this->table->set_heading('Fecha', 'Estudiante', 'Concepto','Curso','Importe');
			foreach($deudas as $deuda){
				$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
				$this->table->add_row(
					$deuda->amount->fecha->format('d/m/Y'),
					$deuda->student->apellido.' '.$deuda->student->nombre,
					$deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo,
					$deuda->amount->course->course,
					'$'.($imp_deuda-$pago)
				);
				//$total_deuda += ($imp_deuda-$pago);
			}
			$total_deuda = $deuda_filtros->deuda_total-$pagos_deudas->pagos_total;
			$data['titulo'] = "Reporte de deudas";
			$data['reporte'] = $this->table->generate();
			$data['total'] = $total_deuda;
		
			$this->session->set_flashdata('next',$this->agent->referrer());
			$this->template->set_template('reporte');
			$this->template->write_view('content', 'pagos/reporte',$data);
			$this->template->render();
		}
	}
	
	function export(){
		$this->load->helper('excel');
		
		$estudiante = '%'.str_replace(' ', '%',$this->input->post('estudiante')).'%';
		$tutor = '%'.str_replace(' ', '%',$this->input->post('tutor')).'%';
		$fecha_desde = ($this->input->post('fecha_desde')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_desde'))):'';
		$fecha_hasta = ($this->input->post('fecha_hasta')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_hasta'))):date('Y-m-d');
		$concepto = $this->input->post('concepto_id');
		$nivel = $this->input->post('level_id');
		$curso = $this->input->post('course_id');
		
		$condiciones = 'pagado = ? AND concept_id!=?';
		$valores[] = 0;
		$valores[] = 2;
		
		$cond = 'pagado = ? AND concept_id!=?';
		$val[] = 0;
		$val[] = 2;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;				
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		
		$a = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		
		$res = array();
		$i = 0;
		$total = 0;
		foreach($a as $deuda){
			$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
		
			$total += ($imp_deuda-$pago);
			$res[$i]['Fecha'] = $deuda->amount->fecha->format('d/m/Y');
			$res[$i]['Estudiante'] = $deuda->student->apellido.' '.$deuda->student->nombre;
			$res[$i]['Concepto'] = $deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo;
			$res[$i]['Course'] = $deuda->amount->course->course;
			$res[$i++]['Importe'] = ($imp_deuda-$pago);
		}
		
		to_excel($res, "Total Deudas \t".$this->input->post('fecha_desde')." - ".$this->input->post('fecha_hasta')."\t $ ".$total); 
	}
	
	function exportTodo(){
		$this->load->helper('excel');
		
		$estudiante = '%'.str_replace(' ', '%',$this->input->post('estudiante')).'%';
		$tutor = '%'.str_replace(' ', '%',$this->input->post('tutor')).'%';
		$fecha_desde = ($this->input->post('fecha_desde')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_desde'))):'';
		$fecha_hasta = ($this->input->post('fecha_hasta')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_hasta'))):date('Y-m-d',mktime(0,0,0,12,31,date('Y')));
		$concepto = $this->input->post('concepto_id');
		$nivel = $this->input->post('level_id');
		$curso = $this->input->post('course_id');
		
		$condiciones = 'pagado = ? AND concept_id!=?';
		$valores[] = 0;
		$valores[] = 2;
		
		$cond = 'pagado = ? AND concept_id!=?';
		$val[] = 0;
		$val[] = 2;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;				
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		
		$a = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		
		$res = array();
		$i = 0;
		$total = 0;
		foreach($a as $deuda){
			$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
		
			$total += ($imp_deuda-$pago);
			$res[$i]['Deuda'] = $deuda->id;
			$res[$i]['Fecha'] = $deuda->amount->fecha->format('d/m/Y');
			//$res[$i]['Estudiante'] = $deuda->student->apellido.' '.$deuda->student->nombre;
			$res[$i]['Documento'] = $deuda->student->nro_documento;
			$res[$i]['Concepto'] = $deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo;
			$res[$i]['Concepto_id'] = $deuda->amount->concept_id;
			//$res[$i]['Course'] = utf8_encode($deuda->amount->course->course);
			$res[$i++]['Importe'] = ($imp_deuda-$pago);
		}
		//to_excel($res); 
	}
	
	function agregar(){
		redirect('deudas');
	}
	
	function eliminar($id){
		redirect('deudas');
	}

	public function refresh_master($offset = 0){
		if(!$offset)
			$this->session->unset_userdata('fs_deudas');
			
		$datos = $this->session->all_userdata();
		
		$estudiante = isset($datos['fs_deudas']['estudiante'])?$datos['fs_deudas']['estudiante']:'%%'; 
		$tutor = isset($datos['fs_deudas']['tutor'])?$datos['fs_deudas']['tutor']:'%%'; 
		$concepto = isset($datos['fs_deudas']['concepto'])?$datos['fs_deudas']['concepto']:0; 
		$nivel = isset($datos['fs_deudas']['nivel'])?$datos['fs_deudas']['nivel']:0; 
		$curso = isset($datos['fs_deudas']['curso'])?$datos['fs_deudas']['curso']:0; 
		$fecha_desde = isset($datos['fs_deudas']['fecha_desde'])?$datos['fs_deudas']['fecha_desde']:date('Y-m-d', mktime(0,0,0,1,1,date('Y'))); 
		$fecha_hasta = isset($datos['fs_deudas']['fecha_hasta'])?$datos['fs_deudas']['fecha_hasta']:date('Y-m-d', mktime(0,0,0,date('m'),28,date('Y'))); 
		
		$condiciones = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$valores[] = 0;
		$valores[] = 2;
		$valores[] = 0;
		
		$cond = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$val[] = 0;
		$val[] = 2;
		$val[] = 0;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;				
		
		if($condiciones!='')
			$condiciones .= ' AND ';
		$condiciones.= "estado_pago_link = ?";
		$valores['estado_pago_link'] = 0;

		$this->session->set_userdata('fs_deudas', $valores);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id AND students.baja = 0
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		
		$todos = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		if(sizeof($todos)){
			$config['base_url'] = site_url('deudas/refresh_master');
			$config['total_rows'] = sizeof($todos);
			$config['per_page'] = '20';  
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->load->library('pagination', $config);
			
			$deudas = array();
			$deudas = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'order'=>'amounts.course_id ASC, students.apellido asc, students.nombre asc, amounts.fecha asc', 'group'=>'concepts.id, students.id','limit' => $config['per_page'], 'offset' => $offset) );
			
			$students = array();
			
			foreach($todos as $e){
				$students[] = $e->student_id;
				$ids_deudas[]= $e->id;
				}
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " debts.student_id IN (?)";
			$val[] = array_unique($students);
			
			$condition = array_merge(array($cond),$val);
				
			$deuda_filtros = Debt::find(array(
							'select'=> 'IF(SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5) IS NULL,0, SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5)) AS deuda_total',
							'joins'=>'	LEFT JOIN students ON students.id = debts.student_id 
										LEFT JOIN amounts ON amounts.id = debts.amount_id 
										LEFT JOIN courses ON courses.id = amounts.course_id 
										LEFT JOIN scolarships ON scolarships.amount_id = debts.amount_id AND scolarships.student_id = debts.student_id', 'conditions'=>$condition));
			
			$pagos_deudas = Detail::find(array('select'=>'SUM(details.importe) AS pagos_total',
												'joins'=> 'LEFT JOIN payments ON payments.id = details.payment_id',
												'conditions'=>array('anulado = ? AND debt_id IN (?)',0,array_unique($ids_deudas))));
						
			$total_deuda = 0;
			$this->table->set_heading('Fecha', 'Estudiante', 'Concepto','Curso','Importe');
			foreach($deudas as $deuda){
				$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
				$this->table->add_row(
					$deuda->amount->fecha->format('d/m/Y'),
					$deuda->student->apellido.' '.$deuda->student->nombre,
					$deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo,
					$deuda->amount->course->course,
					'$'.($imp_deuda-$pago)
				);
				$total_deuda += ($imp_deuda-$pago);
			}
			
			$data['deudas'] = $this->table->generate();
			$data['total_deuda'] = '<h3 align="right"><strong><em>El total de la deuda: $'.($deuda_filtros->deuda_total-$pagos_deudas->pagos_total).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</em></strong></h3>';
			$data['pagination'] = $this->pagination->create_links();
		}
		else{
			$data['deudas'] = "No hay resultados a mostrar";
			}
		
		$data['conceptos'] = Concept::all();
		$data['niveles'] = Level::all(array('conditions'=>array('id != 1')));
		$data['cursos'] = Course::find('all', array('select' => 'id,level_id,course'));		
		$data['filtros']= $valores;
		$this->session->set_flashdata('next',base_url('deudas/refresh_master'));
		$this->template->write_view('content', 'deudas/refresh_master',$data);
		$this->template->render();

	}	

	public function filters_refresh($offset = 0){
		$estudiante = '%'.str_replace(' ', '%',$this->input->post('estudiante')).'%';
		$tutor = '%'.str_replace(' ', '%',$this->input->post('tutor')).'%';
		$fecha_desde = ($this->input->post('fecha_desde')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_desde'))):'2011-01-01';
		$fecha_hasta = ($this->input->post('fecha_hasta')!='')?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_hasta'))):date('Y-m-d');
		$concepto = $this->input->post('concepto_id');
		$nivel = $this->input->post('level_id');
		$curso = $this->input->post('course_id');
		
		$condiciones = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$valores[] = 0;
		$valores[] = 2;
		$valores[] = 0;
		
		
		$cond = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$val[] = 0;
		$val[] = 2;
		$val[] = 0;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;

		if($condiciones!='')
			$condiciones .= ' AND ';
		$condiciones.= "estado_pago_link = ?";
		$valores['estado_pago_link'] = 0;	
		
		$this->session->set_userdata('fs_deudas', $valores);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id 
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		
		$todos = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		if(sizeof($todos)){
			$config['base_url'] = site_url('deudas/index');
			$config['total_rows'] = sizeof($todos);
			$config['per_page'] = '20';  
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->load->library('pagination', $config);
			
			$deudas = array();
			$deudas = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'order'=>'amounts.course_id ASC, students.apellido asc, students.nombre asc, amounts.fecha asc', 'group'=>'concepts.id, students.id','limit' => $config['per_page'], 'offset' => $offset) );
			
			$students = array();
			
			foreach($todos as $e){
				$students[] = $e->student_id;
				$ids_deudas[]= $e->id;
				}
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " debts.student_id IN (?)";
			$val[] = array_unique($students);
			
			$condition = array_merge(array($cond),$val);
				
			$deuda_filtros = Debt::find(array(
							'select'=> 'IF(SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5) IS NULL,0, SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5)) AS deuda_total',
							'joins'=>'	LEFT JOIN students ON students.id = debts.student_id
										LEFT JOIN amounts ON amounts.id = debts.amount_id 
										LEFT JOIN courses ON courses.id = amounts.course_id 
										LEFT JOIN scolarships ON scolarships.amount_id = debts.amount_id AND scolarships.student_id = debts.student_id', 'conditions'=>$condition));
			
			$pagos_deudas = Detail::find(array('select'=>'SUM(details.importe) AS pagos_total',
												'joins'=> 'LEFT JOIN payments ON payments.id = details.payment_id',
												'conditions'=>array('anulado = ? AND debt_id IN (?)',0,array_unique($ids_deudas))));
						
			$total_deuda = 0;
			$this->table->set_heading('Fecha', 'Estudiante', 'Concepto','Curso','Importe');
			foreach($deudas as $deuda){
				$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
				$this->table->add_row(
					$deuda->amount->fecha->format('d/m/Y'),
					$deuda->student->apellido.' '.$deuda->student->nombre,
					$deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo,
					$deuda->amount->course->course,
					'$'.($imp_deuda-$pago)
				);
				$total_deuda += ($imp_deuda-$pago);
			}

			$this->session->set_flashdata('next',$this->agent->referrer());
			echo $this->table->generate();
			echo '<h3 align="right"><strong><em>El total de la deuda: $'.($deuda_filtros->deuda_total-$pagos_deudas->pagos_total).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;</em></strong></h3>';
			echo '<div class="pagination">';
			echo $this->pagination->create_links();
			echo '</div>';
		}
		else{
			echo "No hay resultados a mostrar";
			}
	}

	public function generar_archivo_refresh($offset = 0){
		if(!$offset)
			$this->session->unset_userdata('fs_deudas');
		//Obtenemos el mes actual en hexadecimal
		$mes_hex = dechex((int)date("m"));
		//obtengo la raiz del proyecto para establecer la ruta de los archivos
        $raiz = $this->utils->getPathRootProject();
        $ruta_archivos = $raiz."files/link/";
        $num_volumen = 9;
		//Obtenemos el dia actual
		$dia = date("d");
		//Formamos el nombre del archivo
		$archivo ='PDKT'.$num_volumen.strtoupper($mes_hex).$dia;
		//creamos y abrimos el archivo
		$txt= fopen($ruta_archivos.$archivo, 'w+') or die ('Problemas al crear el archivo');
		//creamos el header para el archivo
		$header_file = "HRFACTURACIONDKT".date("ymd")."00001";
		$header_file .= str_pad("",104," ",STR_PAD_LEFT);
		fwrite($txt, $header_file);	
		//Archivo de control
		//Formamos el nombre del archivo
		$archivo2 ='CDKT'.$num_volumen.strtoupper($mes_hex).$dia;
		//creamos y abrimos el archivo
		$txt2= fopen($ruta_archivos.$archivo2, 'w+') or die ('Problemas al crear el archivo');
		//creamos el header para el archivo
		$header_file2 = "HRPASCTRL".date("Ymd")."DKT".$archivo;
			

		$datos = $this->session->all_userdata();
		$datos2 = $_POST;
		$estudiante = isset($datos['fs_deudas']['estudiante'])?$datos['fs_deudas']['estudiante']:'%%'; 
		$tutor = isset($datos['fs_deudas']['tutor'])?$datos['fs_deudas']['tutor']:'%%'; 
		$concepto = isset($datos['fs_deudas']['concepto'])?$datos['fs_deudas']['concepto']:0; 
		$nivel = isset($datos['fs_deudas']['nivel'])?$datos['fs_deudas']['nivel']:0; 
		$curso = isset($datos['fs_deudas']['curso'])?$datos['fs_deudas']['curso']:0;
		$datos2['fecha_desde'] = isset($datos2['fecha_desde'])?$datos2['fecha_desde']:"01-01-2018";
		$datos2['fecha_hasta'] = isset($datos2['fecha_hasta'])?$datos2['fecha_hasta']:"31-12-2018";
		$fecha_desde = isset($datos2['fecha_desde'])?$this->utils->fecha_formato_mysql($datos2['fecha_desde']):date('Y-m-d', mktime(0,0,0,1,1,date('Y'))); 
		$fecha_hasta = isset($datos2['fecha_hasta'])?$this->utils->fecha_formato_mysql($datos2['fecha_hasta']):date('Y-m-d', mktime(0,0,0,12,31,date('Y'))); 
		
		$condiciones = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$valores[] = 0;
		$valores[] = 2;
		$valores[] = 0;
		
		$cond = 'pagado = ? AND concept_id!=? AND students.baja = ?';
		$val[] = 0;
		$val[] = 2;
		$val[] = 0;
		
		if($estudiante != '%%'){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ?)';
			$valores['estudiante'] = $estudiante;
			$valores[] = $estudiante;
		}
		
		if($tutor !== '%%'){
			if($condiciones != ''){
				$condiciones .= " AND ";
			}
			$condiciones .= ' (CONCAT_WS(" ",tutors.apellido, tutors.nombre) LIKE ? OR CONCAT_WS(" ",tutors.nombre, tutors.apellido) LIKE ? )';
			$valores['tutor'] = $tutor;
			$valores[] = $tutor;
		}
		
		if($concepto > 0){
			if($condiciones!='')
				$condiciones .= ' AND ';
			$condiciones .= ' concepts.id = ?';
			$valores['concepto'] = $concepto;
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= ' concept_id = ?';
			$val[] = $concepto;
			}
		
		if($nivel > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' level_id = ?';
			$valores['nivel'] = $nivel;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' level_id = ?';
			$val[] = $nivel;
		}
		
		if($curso > 0){
			if($condiciones!=''){
				$condiciones .= ' AND ';
			}
			
			$condiciones .= ' course_id = ?';
			$valores['curso'] = $curso;
			
			if($cond!='')
				$cond .= ' AND ';
				
			$cond .= ' course_id = ?';
			$val[] = $curso;
		}
		
		if($condiciones!='')
			$condiciones .= ' AND ';
			
		$condiciones .= " fecha BETWEEN ? AND ?";
		$valores['fecha_desde'] = $fecha_desde;
			
		if($cond!='')
			$cond .= ' AND ';
			
		$cond .= " fecha BETWEEN ? AND ?";
		$val[] = $fecha_desde;	
			
		if($fecha_hasta != ''){
			$fecha_hasta = $fecha_hasta;
		}
		else
			$fecha_hasta = date('Y-m-d');
			
		$valores['fecha_hasta'] = $fecha_hasta;					
		$val[] = $fecha_hasta;

		if($condiciones!='')
			$condiciones .= ' AND ';
		$condiciones.= "estado_pago_link = ?";
		$valores['estado_pago_link'] = 0;					
		
		$this->session->set_userdata('fs_deudas', $valores);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$joins = 'LEFT JOIN students ON students.id = debts.student_id AND students.baja = 0
				  LEFT JOIN amounts ON amounts.id = debts.amount_id
				  LEFT JOIN concepts ON concepts.id = amounts.concept_id
				  LEFT JOIN courses ON courses.id = amounts.course_id
				  LEFT JOIN families ON families.student_id = students.id
				  LEFT JOIN tutors ON tutors.id = families.tutor_id';
		
		
		$todos = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'group'=>'concepts.id, students.id'));
		if(sizeof($todos)){
			$config['base_url'] = site_url('deudas/refresh_master');
			$config['total_rows'] = sizeof($todos);
			$config['per_page'] = '20';  
			$config['num_links'] = '2'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->load->library('pagination', $config);
			
			$deudas = array();
			$deudas = Debt::all(array('joins'=>$joins,'conditions' => $conditions, 'order'=>'amounts.course_id ASC, students.apellido asc, students.nombre asc, amounts.fecha asc', 'group'=>'concepts.id, students.id'));
			
			$students = array();
			
			foreach($todos as $e){
				$students[] = $e->student_id;
				$ids_deudas[]= $e->id;
				}
			
			if($cond!='')
				$cond .= ' AND ';
			$cond .= " debts.student_id IN (?)";
			$val[] = array_unique($students);
			
			$condition = array_merge(array($cond),$val);
				
			$deuda_filtros = Debt::find(array(
							'select'=> 'IF(SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5) IS NULL,0, SUM(ceil((amounts.importe - (amounts.importe*(IF(scolarships.porcien_descuento IS NULL,0,scolarships.porcien_descuento)/100)))/5)*5)) AS deuda_total',
							'joins'=>'	LEFT JOIN students ON students.id = debts.student_id 
										LEFT JOIN amounts ON amounts.id = debts.amount_id 
										LEFT JOIN courses ON courses.id = amounts.course_id 
										LEFT JOIN scolarships ON scolarships.amount_id = debts.amount_id AND scolarships.student_id = debts.student_id', 'conditions'=>$condition));
			
			$pagos_deudas = Detail::find(array('select'=>'SUM(details.importe) AS pagos_total',
												'joins'=> 'LEFT JOIN payments ON payments.id = details.payment_id',
												'conditions'=>array('anulado = ? AND debt_id IN (?)',0,array_unique($ids_deudas))));
						
			$total_deuda = 0;
			$this->table->set_heading('Fecha', 'Estudiante', 'Concepto','Curso','Importe');

			$lotes[0]["numero"] = 1;
			$cantidad_registros = 0;//asignamos 2 porque ya estamos contando el header y footer
			$acum_1venc = 0;
			$acum_2venc = 0;
			$acum_3venc = 0;
			$array_id_deudas = array();
			$registros = "";
			foreach($deudas as $deuda){
				$pagado = Detail::all(array('conditions'=>array('debt_id = ?',$deuda->id)));
				$pago =0;
				foreach($pagado as $p){
					$pago += $p->importe;
				}
				
				$beca = Scolarship::find(array('conditions'=>array('amount_id = ? AND student_id=?',$deuda->amount_id, $deuda->student_id)));
				$imp_deuda = $deuda->amount->importe;
				if($beca){
					$imp_deuda = ceil(($imp_deuda - ($imp_deuda*($beca->porcien_descuento/100)))/5)*5;
				}
				$this->table->add_row(
					$deuda->amount->fecha->format('d/m/Y'),
					$deuda->student->apellido.' '.$deuda->student->nombre,
					$deuda->amount->concept->concepto.' '.$deuda->amount->ciclo_lectivo,
					$deuda->amount->course->course,
					'$'.($imp_deuda-$pago)
				);
				$total_deuda += ($imp_deuda-$pago);

				//escribir en el archivo
				$id_concepto = $deuda->amount->concept->codigo_link;
				if($id_concepto != null){
					$debe = $imp_deuda-$pago;
					if($debe > 0){
						//$id_deuda = str_pad($deuda->id, 5, "0", STR_PAD_LEFT);
						$id_deuda = date("0my",strtotime($deuda->amount->fecha));
						$id_usuario = $deuda->student->id;
						$id_usuario = str_replace(".", "", $id_usuario);
						$id_usuario = str_replace(" ", "", $id_usuario);
						$id_usuario = str_pad($id_usuario, 15,0, STR_PAD_LEFT);
						$id_usuario = str_pad($id_usuario, 19," ", STR_PAD_RIGHT);
						$fecha1 = date("ym15",strtotime($deuda->amount->fecha));//Fecha 1er vto 6 dig
						$acum_1venc += $debe;
						$importe1 = number_format($debe,"2","","");
						$importe1 = str_pad($importe1, 12,"0",STR_PAD_LEFT);
						$fecha2 = date("ym20",strtotime($deuda->amount->fecha));//Fecha 2er vto 6 dig
						$acum_2venc += ($debe * 1.10);
						$importe2 = number_format($debe * 1.10,"2","","");
						$importe2 = str_pad($importe2, 12,"0",STR_PAD_LEFT);
						$acum_3venc += ($debe * 1.15);
						$fecha3 = date("ymd",strtotime("31-12-2018"));//Fecha 3er vto 6 dig
						$importe3 = number_format($debe * 1.15,"2","","");
						$importe3 = str_pad($importe3, 12,"0", STR_PAD_LEFT);
						$otro = str_pad("", 50," ",STR_PAD_LEFT);

						$fila = $id_deuda.
								$id_concepto.
								$id_usuario.
								$fecha1.
								$importe1.
								$fecha2.
								$importe2.
								$fecha3.
								$importe3.
								$otro;
						$cod_pago_link = $id_deuda.$id_concepto.$id_usuario;
						//me fijo si ya esta la cadena "id_deuda+id_concepto+id_usuario" en el archivo
						$pos = strpos($registros, $cod_pago_link);
						if($pos === false){//busco en la base de datos
							$deuda_p_link = $this->utils->get_deuda_p_codigo_link($cod_pago_link);
							if($deuda_p_link != 0){
								$pos = true;
							}
						}
						$num = 1;
						while ($pos != false) {
							echo $id_deuda."<br>";
							//vuelvo a crear el id de deuda
							$id_deuda = date($num."my",strtotime($deuda->amount->fecha));
							//vuelvo a asignar la fila
							$fila = $id_deuda.
									$id_concepto.
									$id_usuario.
									$fecha1.
									$importe1.
									$fecha2.
									$importe2.
									$fecha3.
									$importe3.
									$otro;
							$cod_pago_link = $id_deuda.$id_concepto.$id_usuario;
							//reasigno pos para comparar en el while
							$pos = strpos($registros, $cod_pago_link);
							if($pos === false){//busco en la base de datos
								$deuda_p_link = $this->utils->get_deuda_p_codigo_link($cod_pago_link);
								if($deuda_p_link != 0){
									$pos = true;
								}
							}							
							$num++;
						}
						$registros .=$fila;
						fwrite($txt, $fila );
						$array_id_deudas[] = $deuda->id;
						$array_registros[$deuda->id] = $fila; 
						$cantidad_registros ++;

					}
				}
			}
			$cantidad_registros = $cantidad_registros + 2;
			$lotes[0]["cantidad"] = $cantidad_registros;
			$lotes[0]["total1"] = $acum_1venc; 
			$lotes[0]["total2"] = $acum_2venc;
			$lotes[0]["total3"] = $acum_3venc;

			//lotes
			$totales = array("total1" => 0, "total2" => 0, "total3" => 0, "cantidad" => 0);

			$footer_file = "TRFACTURACION".
							str_pad($cantidad_registros,8,"0",	STR_PAD_LEFT).
							str_pad(number_format($acum_1venc,2,"",""),18,"0",STR_PAD_LEFT).
							str_pad(number_format($acum_2venc,2,"",""),18,"0",STR_PAD_LEFT).
							str_pad(number_format($acum_3venc,2,"",""),18,"0",STR_PAD_LEFT).
							str_pad("", 56," ",STR_PAD_LEFT);
			fwrite($txt, $footer_file );
			fclose($txt);

			//CONTINUO CON EL ARCHIVO DE CONTROL
			$size_file2 = ($cantidad_registros * strlen($header_file));
			//$size_file2 = filesize($archivo);
			$header_file2 .= str_pad($size_file2,10,"0",STR_PAD_LEFT);
			$header_file2 .= str_pad("",37," ",STR_PAD_LEFT);
			fwrite($txt2, $header_file2);


			foreach ($lotes as $key => $value) {
				$lote = "LOTES";
				$lote .= str_pad($value["numero"],5,"0",STR_PAD_LEFT);
				$lote .= str_pad($value["cantidad"],8,"0",STR_PAD_LEFT);
				$lote .= str_pad(number_format($value["total1"],2,"",""),18,"0",STR_PAD_LEFT);
				$lote .= str_pad(number_format($value["total2"],2,"",""),18,"0",STR_PAD_LEFT);
				$lote .= str_pad(number_format($value["total3"],2,"",""),18,"0",STR_PAD_LEFT);
				$lote .= str_pad("",3," ",STR_PAD_LEFT);
				fwrite($txt2, $lote);
				$totales["total1"] += $value["total1"];
				$totales["total2"] += $value["total2"];
				$totales["total3"] += $value["total3"];
				$totales["cantidad"] += $value["cantidad"];
			}			

			//FOOTER ARCHIVO CONTROL
			$footer_file2 = "FINAL";
			$footer_file2 .= str_pad($totales["cantidad"],8,0,STR_PAD_LEFT);
			$footer_file2 .= str_pad(number_format($totales["total1"],2,"",""),18,"0",STR_PAD_LEFT);
			$footer_file2 .= str_pad(number_format($totales["total2"],2,"",""),18,"0",STR_PAD_LEFT);
			$footer_file2 .= str_pad(number_format($totales["total3"],2,"",""),18,"0",STR_PAD_LEFT);
			$footer_file2 .= "20181231";
			fwrite($txt2, $footer_file2);
            		
			fclose($txt2);
			$data['deudas'] = $this->table->generate();
			$data['total_deuda'] = '<h3 align="right"><strong><em>El total de la deuda: $'.($deuda_filtros->deuda_total-$pagos_deudas->pagos_total).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</em></strong></h3>';
			$data['pagination'] = $this->pagination->create_links();
		}
		else{
			$data['deudas'] = "No hay resultados a mostrar";
		}

        //header ("Content-Disposition: attachment; filename=".$archivo); 
        //header ("Content-Type: application/octet-stream"); 
        //header ("Content-Length: ".filesize($archivo)); 
        //readfile($archivo);
        //$this->utils->crear_zip($archivo,$archivo2);
        //$this->utils->crear_zip($archivo2);

        if(count($array_id_deudas) > 0){

			//Guardo en la bd un registro del archivo subido
			$file_db = array('name' => $archivo,
							'path' => $ruta_archivos.$archivo,
							"fecha_subida" => date("Y-m-d"),
							"medio" => 2);
			$f = new File($file_db);
			$file_id = 0;
			$f->save();
			if(!$f->is_valid()){
				echo "Error al crear archivo refresh<br>";
			}
			else{
				$file_id = $f->id;
				echo "Se creo el archivo refresh<br>";	
			}
			

			//Guardo en la bd un registro del archivo subido
			$file_db2 = array('name' => $archivo2,
							'path' => $ruta_archivos.$archivo2,
							"fecha_subida" => date("Y-m-d"),
							"medio" => 2);
			$f2 = new File($file_db2);
			$f2->save();
			if(!$f2->is_valid()){
				echo "Error al crear archivo control<br>";
			}
			else{
				$file_id2 = $f2->id;
				echo "Se creo el archivo control<br>";	
			}

	        $this->db->where_in("id", $array_id_deudas);
	        $this->db->set(array("pago_link"=>1,"estado_pago_link"=>1,"file_id"=>$file_id));
	        $this->db->update("debts");
	        foreach ($array_id_deudas as $key => $value){
		        $this->db->where("id", $value);
		        $this->db->set(array("registro_link"=>$array_registros[$value]));
		        $this->db->update("debts");
	        }
        	print_r(implode(",", $array_id_deudas));
        }
        else{
        	echo "No hay deudas para generar archivo de pagos link";
        }

		$data['conceptos'] = Concept::all();
		$data['niveles'] = Level::all(array('conditions'=>array('id != 1')));
		$data['cursos'] = Course::find('all', array('select' => 'id,level_id,course'));		
		$data['filtros']= $valores;
		echo "<br><br>";
		echo "<a href='".site_url("deudas/refresh_master")."'>Volver</a>";
		/*$this->session->set_flashdata('next',base_url('deudas/refresh_master'));
		$this->template->write_view('content', 'deudas/refresh_master',$data);
		$this->template->render();*/
	}

	function getIdDeudaPagosLink(){
		$d = Debt::all(array("conditions"=>array("pago_link",1)));
		$IDDeudas = array();
		foreach ($d as $key => $value) {
			$IDDeuda = substr($value->registro_link, 0,5);
			if(!in_array($IDDeuda, $IDDeudas)){
				$IDDeudas[] = $IDDeuda;
			}
		}
		//print_r($IDDeudas);
		foreach ($IDDeudas as $key => $value) {
			echo $value.",";
		}
	}


	function verificar_archivos_refresh(){
		$sql = "SELECT * FROM files WHERE medio = 2 AND name LIKE 'PDKT%'";
		$f = $this->db->query($sql);
		foreach ($f->result() as $key => $value) {
			echo "File: ".$value->name."<br>";
			$fp = fopen($value->path, 'r') or die ('Problemas al abrir el archivo');
			$size  = filesize($value->path);
			$cant_line = 131;
			$count = $size / $cant_line;
			$linea = fgets($fp);
			for($i = 1; $i < ($count-1); $i++){
				$reg = substr($linea, $i * $cant_line,$cant_line);
				$sql2 = "SELECT * FROM debts WHERE registro_link LIKE '".$reg."'";
				$d = $this->db->query($sql2);
				if($d->num_rows() == 0){
					print_r($d->row()->registro_link);
					echo "<br>";
				}
			}
			fclose($fp);
			echo "*******************************************************************************************<br>";		
		}
	}
}
