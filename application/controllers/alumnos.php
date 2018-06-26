<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Alumnos extends CI_Controller {

	function __construct(){
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
	}
	
	public function buscar(){
		$string = '%'.$_POST['term'].'%';
		$ttr = Student::all(array('conditions' => array('nro_documento LIKE ?', $string)));
		$tt = array();
		foreach($ttr as $t)
		{
			$tt[] = array('id' => $t->id, 'value' => $t->apellido.', '.$t->nombre.' - '.$t->nro_documento);
		}
		echo json_encode($tt);
	}

	public function index($offset = 0){
		if(!$offset)
			$this->session->unset_userdata('filtros_alumnos');
			
		$datos = $this->session->all_userdata();
				
		$string = isset($datos['filtros_alumnos']['string'])?str_replace(' ','%',$datos['filtros_alumnos']['string']):'%%'; 
		$nivel = isset($datos['filtros_alumnos']['nivel'])?$datos['filtros_alumnos']['nivel']: 0; 
		$curso = isset($datos['filtros_alumnos']['curso'])?$datos['filtros_alumnos']['curso']: 0;
		$division = isset($datos['filtros_alumnos']['division'])?$datos['filtros_alumnos']['division']: 0; 
		$mostrar = (isset($datos['filtros_alumnos']['nivel'])&&($datos['filtros_alumnos']['nivel']==1))?1:0;
		$ciclo_lectivo = (isset($datos['filtros_alumnos']['ciclo_lectivo']))?$datos['filtros_alumnos']['ciclo_lectivo']:date('Y');
		
		$condiciones = 'baja = ? AND egresado = ?';
		$valores = array('baja'=>0, 'egresado'=>$mostrar);
		
		$joins = ' LEFT JOIN inscriptions ON students.id = inscriptions.student_id ';
		
		if($nivel > 1){
			$condiciones .= ' AND inscriptions.ciclo_lectivo LIKE ? ';
			$valores['ciclo_lectivo']=$ciclo_lectivo;	
		}
		
		if($string !== '%%'){
			$condiciones .= ' AND ((CONCAT_WS(" ",apellido, nombre) LIKE ? OR CONCAT_WS(" ",nombre, apellido) LIKE ? )';
			$valores['string'] = $string;
			$valores['nombre'] = $string;
			$condiciones .= " OR nro_documento LIKE ? ";
			$condiciones .= " OR telefono LIKE ? )";
			$valores['documento'] = $string;
			$valores['telefono'] = $string;
		}
		
		if(($nivel >1)||($division >1)){
			$joins .= '
					  LEFT JOIN courses ON inscriptions.course_id = courses.id
					  LEFT JOIN levels ON courses.level_id = levels.id
					  LEFT JOIN divisions ON inscriptions.division_id = divisions.id
					  ';
					  
			if($nivel >1){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " levels.id = ?";
				$valores['nivel'] = $nivel;
			}
			
			if($curso > 0){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " courses.id = ?";
				$valores['curso'] = $curso;
			}
			
			if($division > 0){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " divisions.id = ?";
				$valores['division'] = $division;
			}
		}
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$this->load->library('pagination');
		$todos = Student::all(array('group' => 'students.id', 'joins'=>$joins,'conditions' => $conditions));
		if(sizeof($todos)>0){
			$config['base_url'] = site_url('alumnos/index');
			$config['total_rows'] = sizeof($todos); 
			$config['per_page'] = '20'; 
			$config['num_links'] = '1'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->pagination->initialize($config);
			
			$a = Student::all(array('group' => 'students.id', 'joins'=>$joins,'conditions' => $conditions, 'order' => 'apellido ASC, nombre ASC', 'limit' => $config['per_page'], 'offset' => $offset) ); 
			
			$this->table->set_heading('&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;','Apellido', 'Nombre', 'Documento','Telefono','Año','Acciones',form_checkbox(array('id' => 'select-all', 'class' => 'check')));
			$orden = $offset+1;
			foreach($a as $al){	
				$pant='';
				if($al->egresado==1){
					$pant = 'EGRESADO';
				}
				else{
					$insc = Inscription::last(array('conditions' => array('student_id = ?',$al->id)));
					$pant = ($insc)?$insc->course->course .' '.$insc->division->division:''; 
					}
				$this->table->add_row(
					$orden++,
					$al->apellido,
					$al->nombre,
					$al->tipo_documento.' '.$al->nro_documento,
					$al->telefono,
					$pant,
					anchor('alumnos/ver/'.$al->id,img('static/img/icon/doc_lines.png'), 'class="tipwe" title="Ver detalles de alumno"').' '.
					anchor('alumnos/editar/'.$al->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar alumno"').' '.
					anchor('alumnos/eliminar/'.$al->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar alumno"'),
					($insc)?form_checkbox(array('name' => 'inscriptions[]', 'class' => 'check', 'value' => $insc->id)):''
				);
			}
			
			$data['alumnos'] = "<br/><div align='right'>Total de Alumnos ".sizeof($todos)."</div><br/>".$this->table->generate();
			$data['pagination'] = $this->pagination->create_links();
		}
		else{
			$data['alumnos'] = "No hay resultados para mostrar";
			$data['pagination'] = '';
			}
			
		$data['niveles'] = Level::all();
		$data['cursos'] = Course::find('all', array('select' => 'id,level_id,course'));
		$data['divisiones'] = Division::all(array('conditions' => 'id > 1'));
		$data['ciclo_lectivos']= Inscription::all(array('select'=>'DISTINCT ciclo_lectivo' ));
		$data['filtros']= array($string, $nivel, $curso, $division, $ciclo_lectivo);
		
		$this->session->set_flashdata('next',site_url('alumnos'));
		$this->template->write_view('content', 'alumnos/index',$data);
		$this->template->render();
	}
	
	public function filter($offset = 0){
		$string = '%'.str_replace(' ','%',$this->input->post('string')).'%';
		$nivel = $this->input->post('level_id');
		$curso = $this->input->post('course_id');
		$division = $this->input->post('division_id');
		$mostrar = ($nivel==1)?1:0; //es para que no muestre los egresados siempre
		$ciclo = $this->input->post('ciclo_lectivo');
				
		$condiciones = 'baja = ? AND egresado = ?';
		$valores = array('baja'=>0, 'egresado'=>$mostrar);
		
		$joins = 'LEFT JOIN inscriptions ON students.id = inscriptions.student_id ';
		
		if($nivel>1){
			$condiciones .= ' AND inscriptions.ciclo_lectivo LIKE ? ';
			$valores['ciclo_lectivo']=$ciclo;
		}
		
		if($string !== '%%'){
			$condiciones .= ' AND ((CONCAT_WS(" ",apellido, nombre) LIKE ? OR CONCAT_WS(" ",nombre, apellido) LIKE ? )';
			$valores['string'] = $string;
			$valores['nombre'] = $string;
			$condiciones .= " OR nro_documento LIKE ? ";
			$condiciones .= " OR telefono LIKE ? )";
			$valores['documento'] = $string;
			$valores['telefono'] = $string;
		}
		
		if(($nivel >1)||($division >1)){
			$joins .= '	LEFT JOIN courses ON inscriptions.course_id = courses.id
						LEFT JOIN levels ON courses.level_id = levels.id
						LEFT JOIN divisions ON inscriptions.division_id = divisions.id
					  ';
					  
			if($nivel >1){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " levels.id = ?";
				$valores['nivel'] = $nivel;
			}
			
			if($curso > 0){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " courses.id = ?";
				$valores['curso'] = $curso;
			}
			
			if($division > 0){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " divisions.id = ?";
				$valores['division'] = $division;
			}
		}

		$this->session->set_flashdata('next',$this->agent->referrer());
		$this->session->set_userdata('filtros_alumnos', $valores);
		$conditions = array_merge(array($condiciones), $valores);
		
		$todos = Student::all(array('group' => 'students.id', 'joins'=>$joins,'conditions' => $conditions));
		if(sizeof($todos)){
			$config['base_url'] = site_url('alumnos/index/');
			$config['total_rows'] = sizeof($todos); 
			$config['per_page'] = '20'; 
			$config['num_links'] = '1'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->load->library('pagination', $config);
			
			$a = Student::all(array('group'=>'students.id', 'joins'=>$joins,'conditions' => $conditions, 'order' => 'apellido ASC, nombre ASC', 'limit' => $config['per_page'], 'offset' => $offset) );
					
			$this->table->set_heading('&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;','Apellido', 'Nombre', 'Documento','Telefono','Año','Acciones',form_checkbox(array('id' => 'select-all', 'class' => 'check')));
			$orden = $offset+1;
			foreach($a as $al){	
				$pant='';
				if($al->egresado==1){
					$pant = 'EGRESADO';
					$insc = NULL;
				}
				else{
					$insc = Inscription::find(array('conditions' => array('student_id = ? AND ciclo_lectivo LIKE ?',$al->id, $ciclo)));
					$pant = ($insc)?$insc->course->course .' '.$insc->division->division:''; 
					}
				
				$this->table->add_row(
					$orden++,
					$al->apellido,
					$al->nombre,
					$al->tipo_documento.' '.$al->nro_documento,
					$al->telefono,
					$pant,
					anchor('alumnos/ver/'.$al->id,img('static/img/icon/doc_lines.png'), 'class="tipwe" title="Ver detalles de alumno"').' '.
					anchor('alumnos/editar/'.$al->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar alumno"').' '.
					anchor('alumnos/eliminar/'.$al->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar alumno"'),
					($insc)?form_checkbox(array('name' => 'inscriptions[]', 'class' => 'check', 'value' => $insc->id)):''
				);
			}
			
			echo "<br/><div align='right'>Total de Alumnos ".sizeof($todos)."</div><br/>".$this->table->generate();
			
			echo '<div class="pagination">';
			echo $this->pagination->create_links();
			echo '</div>';
		}
		else{
			echo 'No hay resultados para mostrar';
			}
	}
	
	public function reporte(){		
		$a = $this->_obtener_datos($_POST);
			
		$this->table->set_heading('&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;','Apellido y Nombre', 'Documento','Lugar de Nacimiento','Fecha de Nacimiento','Telefono');
		$orden = 1;
		foreach($a as $al){	
			$this->table->add_row(
			$orden++,
			$al->apellido.', '.$al->nombre,
			$al->tipo_documento.' '.$al->nro_documento,
			'&nbsp;&nbsp;'.$al->lugar_nacimiento,
			($al->fecha_nacimiento)?$al->fecha_nacimiento->format('d/m/Y'):'',
			$al->telefono
			);
		}
			
		$data['titulo'] = "Alumnos";
		if(isset($_POST['level_id']) && $_POST['level_id'] > 0) 
			$data['nivel'] = Level::find($_POST['level_id'])->nivel;
		if(isset($_POST['course_id']) && $_POST['course_id'] >0) 
			$data['curso'] = Course::find($_POST['course_id'])->course;
		if(isset($_POST['division_id']) && $_POST['division_id'] >0) 
			$data['division'] = Division::find($_POST['division_id'])->division;
			
		$data['reporte'] = $this->table->generate();
						
		$this->template->set_template('reporte');
		$this->template->write_view('content', 'pagos/reporte',$data);
		$this->template->render();		
	}
	
	function export(){
		$this->load->helper('excel');
		$a = $this->_obtener_datos($_POST);
		$res = array();
		$i = 0;
		foreach($a as $al){
			$pant='';
			if($al->egresado==1){
				$pant = 'EGRESADO';
				$insc = NULL;
			}
			else{
				$insc = Inscription::find(array('conditions' => array('student_id = ? AND ciclo_lectivo LIKE ?',$al->id, $this->input->post('ciclo_lectivo'))));
				$pant = ($insc)?$insc->course->course .' '.$insc->division->division:''; 
				}
			$res[$i]['Matricula'] = str_pad($al->id, 3, '0', STR_PAD_LEFT);
			$res[$i]['Nombre y Apellido'] = utf8_decode($al->apellido).', '.utf8_decode($al->nombre);
			$res[$i]['Documento'] = $al->tipo_documento.' '.$al->nro_documento;
			$res[$i]['Fecha Nacimiento'] = ($al->fecha_nacimiento)?$al->fecha_nacimiento->format('d/m/Y'):'';
			$res[$i]['Lugar Nacimiento'] = utf8_decode($al->lugar_nacimiento);
			$res[$i]['Nacionalidad'] = utf8_decode($al->nacionalidad);
			$res[$i]['Domicilio'] = utf8_decode($al->domicilio);
			$res[$i]['Fecha Inscripcion'] = ($al->fecha_inscripcion)?$al->fecha_inscripcion->format('d/m/Y'):'';
			$res[$i]['Telefono'] = $al->telefono;
			$res[$i++]['Curso'] = utf8_decode($pant);
		}
		to_excel($res, 'Alumnos'); 
	}
	
	function _obtener_datos($datos){
		$string = isset($datos['string'])?'%'.str_replace(' ','%',$datos['string']).'%':'%';
		$nivel = isset($datos['level_id'])?$datos['level_id']:0;
		$curso = isset($datos['course_id'])?$datos['course_id']:0;
		$division = isset($datos['division_id'])?$datos['division_id']:0;
		$mostrar = ($nivel==1)?1:0; //es para que no muestre los egresados siempre
		$ciclo = isset($datos['ciclo_lectivo'])?$datos['ciclo_lectivo']:date('Y');
		
		$condiciones = 'baja = ? AND egresado = ?';
		$valores = array('baja'=>0, 'egresado'=>$mostrar);
		
		$joins = ' LEFT JOIN inscriptions ON students.id = inscriptions.student_id ';
		
		//if($nivel > 1){
			$condiciones .= ' AND inscriptions.ciclo_lectivo LIKE ? ';
			$valores['ciclo_lectivo']=$ciclo;	
			$data['ciclo_lectivo'] = $ciclo;
		//}
		
		if($string !== '%%'){
			$condiciones .= ' AND ((CONCAT_WS(" ",apellido, nombre) LIKE ? OR CONCAT_WS(" ",nombre, apellido) LIKE ? )';
			$valores['string'] = $string;
			$valores['nombre'] = $string;
			$condiciones .= " OR nro_documento LIKE ? ";
			$condiciones .= " OR telefono LIKE ? )";
			$valores['documento'] = $string;
			$valores['telefono'] = $string;
		}
		
		//if(($nivel >1)||($division >1)){
			$joins .= '
					  LEFT JOIN courses ON inscriptions.course_id = courses.id
					  LEFT JOIN levels ON courses.level_id = levels.id
					  LEFT JOIN divisions ON inscriptions.division_id = divisions.id
					  ';
					  
			if($nivel >1){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " levels.id = ?";
				$valores['nivel'] = $nivel;
				$data['nivel'] = Level::find($nivel)->nivel;
			}
			
			if($curso > 0){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " courses.id = ?";
				$valores['curso'] = $curso;
				$data['curso'] = Course::find($curso)->course;
				
			}
			
			if($division > 0){
				if($condiciones != '')
					$condiciones .= " AND ";
				$condiciones .= " divisions.id = ?";
				$valores['division'] = $division;
				$data['division'] = Division::find($division)->division;
			}
		//}
		
		$conditions = array_merge(array($condiciones), $valores);
			
		return Student::all(array('group' => 'students.id', 'joins'=>$joins,'conditions' => $conditions, 'order' => 'apellido ASC, nombre ASC') );
	}
	
	public function ver($id = FALSE){
		if($id){
			$data['a'] = Student::find($id);
			$deudas = $data['a']->debt;
			$pagos = $data['a']->payment;
			$becas = Scolarship::all(array('conditions' => array('student_id = ?',$id)));
			
			$this->table->set_heading('# Deuda','Concepto', 'Vencimiento', 'Importe', 'Desc.','A pagar','Pagado','Pendiente','Saldo','Link','');
			
			foreach($deudas as $d){
				$descuento = 0;
				foreach($d->amount->scolarship as $s){
					if(($s->student_id == $id) AND ($s->amount_id == $d->amount_id)){
						$descuento = $s->porcien_descuento;
					}
				}
				$pagado = 0;
				$pendiente = 0;
				$ban = 0;
				foreach($d->detail as $dt){					
					if($dt->payment->anulado == 0)
						if($dt->estado != 0)//si el pago no esta pendiente
							$pagado += $dt->importe;
						else{
							$ban = 1;
							$pendiente += $dt->importe;
						}
				}
				
				$saldo = 0;
				$pagar = 0;
				$pagar = ceil(($d->amount->importe - ($d->amount->importe*($descuento/100)))/5)*5;
				$saldo = $pagar - $pagado - $pendiente;
				$class_pend = ($pendiente > 0)?"pendiente":"";
				if($this->session->userdata('grupo') == 'alumno'){
					if($saldo > 0 || $ban == 1){
						$this->table->add_row(
							$d->id,
							$d->amount->concept->concepto.' '.$d->amount->ciclo_lectivo,
							$d->amount->fecha->format('d/m/Y'),
							'$'.$d->amount->importe,
							$descuento ? $descuento.'%' : 'No',
							'$'.$pagar,
							'$'.$pagado,
							"<div class='".$class_pend."'>".'$'.$pendiente."</div>",
							'$'.$saldo,
							form_hidden('saldo['.$d->id.']', $saldo).' '.form_input(array('name' => 'parcial['.$d->id.']', 'class' => 'small', 'max' => $saldo, 'min' => 1,'readonly'=>'true')).' '.form_checkbox(array('name' => 'suma', 'class' => 'check', 'value' => $saldo))
						);
					}				
				}
				else{
					if($saldo > 0 || $ban == 1){
						$atts = array(
						  'width'      => '800',
						  'height'     => '600',
						  'scrollbars' => 'yes',
						  'status'     => 'yes',
						  'resizable'  => 'yes',
						  'screenx'    => '0',
						  'screeny'    => '0'
						);						
						$this->table->add_row(
							$d->id,
							$d->amount->concept->concepto.' '.$d->amount->ciclo_lectivo,
							$d->amount->fecha->format('d/m/Y'),
							'$'.$d->amount->importe,
							$descuento ? $descuento.'%' : 'No',
							'$'.$pagar,
							'$'.$pagado,
							"<div class='".$class_pend."'>".'$'.$pendiente."</div>",
							'$'.$saldo,
							form_hidden('saldo['.$d->id.']', $saldo).' '.form_input(array('name' => 'parcial['.$d->id.']', 'class' => 'small', 'max' => $saldo, 'min' => 1)).' '.form_checkbox(array('name' => 'suma', 'class' => 'check', 'value' => $saldo)),
							anchor_popup('pagos/ver_codigo_link/'.$d->id,img('static/img/icon/print.png'), $atts,'class="tipwe" title="Imprimir recibo"')
						);
					}		
				}
				
				if($d->amount->concept_id==2 ){
					$this->table->add_row(
						$d->id,
						$d->amount->concept->concepto,
						date('d/m/Y'),
						'$'.$d->amount->importe,
						$descuento ? $descuento.'%' : 'No',
						'$'.$d->amount->importe,
						'$'.$d->amount->importe,
						"<div class='".$class_pend."'>".'$'.$pendiente."</div>",
						'$'.$d->amount->importe,
						form_hidden('saldo['.$d->id.']', $d->amount->importe).' '.form_input(array('name' => 'parcial['.$d->id.']', 'class' => 'small', 'max' => '', 'min' => 0))
					);
				}
			}
			
			$data['deudas'] = $this->table->generate(); // TABLA DEUDAS
		

			if($this->session->userdata('grupo') == 'alumno'){//esto es para el tutor
				$this->table->set_heading('# Comprobante','Concepto','Fecha de pago','Importe'/*,'Acciones'*/);
				$count = 0;
				foreach($pagos as $p){	
					if(!$p->anulado){
						$atts = array(
						  'width'      => '800',
						  'height'     => '600',
						  'scrollbars' => 'yes',
						  'status'     => 'yes',
						  'resizable'  => 'yes',
						  'screenx'    => '0',
						  'screeny'    => '0'
						);
						foreach($p->detail as $d) {
							if($d->estado == 1){
								$this->table->add_row(
									$p->nro_comprobante,
									$d->debt->amount->concept->concepto.' '.
									$d->debt->amount->ciclo_lectivo,
									($p->fecha != "")?$p->fecha->format('d/m/Y'):"",
									'$'.$d->importe/*,
									anchor_popup('pagos/recibo/'.$p->id,img('static/img/icon/print.png'), $atts,'class="tipwe" title="Imprimir recibo"').' '.anchor('pagos/eliminar/'.$p->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular pago"')*/
								);
							}
						}
					}
				}
				$data['pagos'] = $this->table->generate(); // TABLA PAGOS


				$this->table->set_heading('# Comprobante','Concepto','Fecha de pago','Importe', 'Cobrador','Estado','Acciones');
				$count = 0;
				foreach($pagos as $p){
					$pdetails = Pdetail::find('all', array('conditions' => array('payment_id = ?',$p->id)));//volver2
					foreach ($pdetails as $key => $pd) {
						if(!$p->anulado && $pd->ptype_id == 17){
							$atts = array(
							  'width'      => '800',
							  'height'     => '600',
							  'scrollbars' => 'yes',
							  'status'     => 'yes',
							  'resizable'  => 'yes',
							  'screenx'    => '0',
							  'screeny'    => '0'
							);
							foreach($p->detail as $d) {
								if($d->estado == 0){
									$this->table->add_row(
										$p->nro_comprobante,
										$d->debt->amount->concept->concepto.' '.
										$d->debt->amount->ciclo_lectivo,
										($p->fecha != "")?$p->fecha->format('d/m/Y'):"",
										'$'.$d->importe,
										$p->user->apellido.', '.$p->user->nombre,
										($d->debt->pagado == 0)?"Pendiente":"Pagado",
										anchor_popup('pagos/recibo_para_tutor/'.$p->related_payments,img('static/img/icon/print.png'), $atts,'class="tipwe" title="Imprimir recibo"')
									);
								}
							}
						}					
					}
				}
				$data['boletas_pendientes'] = $this->table->generate(); // TABLA PENDIENTES BSJ		
			}
			else{
				$this->table->set_heading('# Comprobante','Concepto','Fecha de pago','Importe', 'Cobrador','Acciones');
				$count = 0;
				foreach($pagos as $p){	
					if(!$p->anulado){
						$atts = array(
						  'width'      => '800',
						  'height'     => '600',
						  'scrollbars' => 'yes',
						  'status'     => 'yes',
						  'resizable'  => 'yes',
						  'screenx'    => '0',
						  'screeny'    => '0'
						);
						foreach($p->detail as $d) {
							if($d->estado != 0){
								$this->table->add_row(
									$p->nro_comprobante,
									$d->debt->amount->concept->concepto.' '.
									$d->debt->amount->ciclo_lectivo,
									($p->fecha != "")?$p->fecha->format('d/m/Y'):"",
									'$'.$d->importe,
									$p->user->apellido.', '.$p->user->nombre,
									anchor_popup('pagos/recibo/'.$p->id,img('static/img/icon/print.png'), $atts,'class="tipwe" title="Imprimir recibo"').' '.anchor('pagos/eliminar/'.$p->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular pago"')
								);
							}
						}
					}
				}
				$data['pagos'] = $this->table->generate(); // TABLA PAGOS

				$this->table->set_heading('# Comprobante','Concepto','Fecha de pago','Importe', 'Cobrador','Estado','Acciones');
				$count = 0;
				foreach($pagos as $p){
					$pdetails = Pdetail::find('all', array('conditions' => array('payment_id = ?',$p->id)));//volver2
					foreach ($pdetails as $key => $pd) {
						if(!$p->anulado && $pd->ptype_id == 17){
							$atts = array(
							  'width'      => '800',
							  'height'     => '600',
							  'scrollbars' => 'yes',
							  'status'     => 'yes',
							  'resizable'  => 'yes',
							  'screenx'    => '0',
							  'screeny'    => '0'
							);
							foreach($p->detail as $d) {
								if($d->estado == 0){
									$this->table->add_row(
										$p->nro_comprobante,
										$d->debt->amount->concept->concepto.' '.
										$d->debt->amount->ciclo_lectivo,
										($p->fecha != "")?$p->fecha->format('d/m/Y'):"",
										'$'.$d->importe,
										$p->user->apellido.', '.$p->user->nombre,
										($d->debt->pagado == 0)?"Pendiente":"Pagado",
										anchor_popup('pagos/recibo_para_tutor/'.$p->related_payments,img('static/img/icon/print.png'), $atts,'class="tipwe" title="Imprimir recibo"').' '.anchor('pagos/eliminar/'.$p->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular pago"')
									);
								}
							}
						}					
					}
				}
				$data['boletas_pendientes'] = $this->table->generate(); // TABLA PENDIENTES BSJ					
			}
			
			$this->table->set_heading('Nombre','Telefono', 'Celular', 'Acciones');
			foreach($data['a']->family as $i){
				$this->table->add_row(
					$i->tutor->nombre .' '.$i->tutor->apellido,
					$i->tutor->telefono_fijo,
					$i->tutor->celular,
					anchor('tutores/editar/'.$i->tutor->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
					anchor('alumnos/eliminarelacion/'.$i->id,img('static/img/icon/trash.png'), 'class="tipwe" title="Quitar Relación"')
				);
			}
			$data['tutores'] = $this->table->generate(); // TABLA TUTORES
			
			$this->table->set_heading('Curso','División', 'Nivel','Año', 'Acciones');
			
			foreach($data['a']->inscription as $i){
				$this->table->add_row(
					$i->course->course,
					$i->division->division,
					$i->course->level->nivel,
					$i->ciclo_lectivo,
					($i->ciclo_lectivo>=date('Y'))?anchor('inscripciones/editar/'.$i->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Modificar"').' '.anchor('inscripciones/eliminar/'.$i->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular"'):''
				);
				
				if($i->ciclo_lectivo == date('Y'))
					$curso_actual = $i->course_id;
			}
						
			$data['inscripciones'] = $this->table->generate(); // TABLA INSCRIPCIONES
			
			$this->table->set_heading('Concepto','Curso', 'Descuento', 'Acciones');
			
			foreach($data['a']->scolarship as $b){
				$this->table->add_row(
					$b->amount->concept->concepto.' '.$b->amount->ciclo_lectivo,
					$b->amount->course->course,
					$b->porcien_descuento.'%',
					anchor('descuentos/editar/'.$b->student_id.'/'.$b->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Modificar"').' '.
					anchor('descuentos/eliminar/'.$b->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular"')
				);
			}
			$data['becas'] = $this->table->generate(); // TABLA BECAS
			
			$data['niveles'] = Level::all();
			$data['cursos'] = Course::all();
			$data['divisiones'] = Division::all(array('conditions' => 'id > 1'));
			
			$conditions = array(
				'conditions' => array(
					'student_id = ? AND pagado = 0 AND amount_id NOT IN (
																			SELECT amount_id
																			FROM scolarships
																			WHERE student_id = ? )',
					$id, $id
				)
			);
			
			$data['becas_a_asig']= Debt::count($conditions);
			
			// Trae el último nro de comprobante y le suma 1
			$nro = Payment::last(array('order'=>'nro_recibo ASC',
												'conditions'=>array('nro_comprobante NOT LIKE ? AND YEAR(fecha) > ?','0002-%','2017')));
			
			$data['nro_comprobante'] = '0001-00000000';
			if($nro){
				$nuevo = explode('-',$nro->nro_comprobante);
				$data['nro_comprobante'] = $nuevo[0].'-'.str_pad(($nuevo[1] + 1), 8, '0', STR_PAD_LEFT);
			}
			
			$conditions = array('conditions' => array('student_id = ?', $id));
			$pe = Inscription::last($conditions);
			if($pe){
				$data['eventual'] = array(
					'student_id' => $id,
					'inscription_id' => $pe->id,
					'course_id' => $pe->course_id
				);
			}
			else $data['eventual'] = FALSE;
			//Agregado por seba
			if($this->session->userdata('grupo') == 'alumno'){//esto es para el tutor
				$this->template->write_view('content', 'alumnos/tutor/ver',$data);
			}
			else{
				$this->template->write_view('content', 'alumnos/ver',$data);
			}			
			$this->template->render();
		}
		else{
			$this->session->set_flashdata('msg','<div class="notice">El alumno no existe.</div>');
			redirect('alumnos');
		}
	}
	
	public function agregar(){
		$data = array();
		if ( $_POST ){
			$this->load->helper('date');
			$this->load->library('Utils');
			$insert = $_POST;
			$insert['fecha_nacimiento'] = $this->utils->fecha_formato('%Y-%m-%d', $insert['fecha_nacimiento']);
			$insert['fecha_inscripcion'] = $this->utils->fecha_formato('%Y-%m-%d', $insert['fecha_inscripcion']);
			$alumno = new Student(
				elements( array(
					'city_id',
					'nombre',
					'apellido',
					'fecha_nacimiento',
					'sexo',
					'tipo_documento',
					'nro_documento',
					'domicilio',
					'tenencia',
					'nacionalidad',
					'grupo_sanguineo',
					'telefono',
					'celular',
					'obs_medicas',
					'observaciones',
					'lugar_nacimiento',
					'colegio_procedencia',
					'fecha_inscripcion',
				), $insert )
			);
			if( $alumno->is_valid()){
				$alumno->save();
				$this->session->set_flashdata( 'msg','<div class="success">El alumno se guardó correctamente.</div>' );
				redirect('alumnos/index/');
			}
			else{
				$data['errors'] = $alumno->errors;
			}
		}
		
		$data['paises'] = Country::all();
		$data['provincias'] = State::all();
		$data['ciudades'] = City::all();
		$data['titulo'] = "Agregar alumno";
		$data['action'] = "alumnos/agregar";
		
		$this->template->write_view('content', 'alumnos/agregar',$data);
		$this->template->render();
	}
	
	public function editar( $id ){	
		if(!$id){
			$this->session->set_flashdata( 'msg','<div class="notice">El alumno solicitado no existe.</div>' );
			redirect('alumnos');
		}
		elseif ( $_POST ){
			$this->load->helper('date');
			$this->load->library('Utils');
			$insert = $_POST;
			$insert['fecha_nacimiento'] = $this->utils->fecha_formato('%Y-%m-%d', $insert['fecha_nacimiento']);
			$insert['fecha_inscripcion'] = $this->utils->fecha_formato('%Y-%m-%d', $insert['fecha_inscripcion']);
			
			$alumno = Student::find($id);
			
			$alumno->update_attributes(elements( array(
					'city_id',
					'nombre',
					'apellido',
					'fecha_nacimiento',
					'sexo',
					'tipo_documento',
					'nro_documento',
					'domicilio',
					'tenencia',
					'nacionalidad',
					'grupo_sanguineo',
					'telefono',
					'celular',
					'obs_medicas',
					'observaciones',
					'lugar_nacimiento',
					'colegio_procedencia',
					'fecha_inscripcion',
				), $insert )
			);
			
			if($alumno->is_valid()){
				if($alumno->save()){
					$this->session->set_flashdata( 'msg','<div class="success">El alumno se guardó correctamente.</div>' );
					redirect('alumnos');
				}
				else{
					$this->session->set_flashdata( 'msg','<div class="error">Hubo un error al guardar los datos.</div>' );
					redirect($this->agent->referrer());
				}
			}
			else{
				$data['errors'] = $alumno->errors;
			}
		}
		else $data['a'] = Student::find($id);
		
		$data['paises'] = Country::all();
		$data['provincias'] = State::all();
		$data['ciudades'] = City::all();
		$data['titulo'] = "Editar alumno";
		$data['action'] = "alumnos/editar/".$id;
		
		$this->template->write_view('content', 'alumnos/agregar',$data);
		$this->template->render();
	}
	
	function eliminar($id){
		try{
			if($this->session->userdata('grupo') == 'admin'){
				$a = Student::find($id);
				
				$fec = date('Y-m-d');
				
				$sql = "SELECT * 
						FROM  `debts` 
						INNER JOIN amounts ON debts.amount_id = amounts.id
						WHERE  `student_id` = $id
						AND pagado = 0
						AND fecha <=  '$fec'";
						
				$tiene_deudas = Debt::find_by_sql($sql);
				
				if(sizeof($tiene_deudas) == 0){
					$a->update_attributes(elements( array('baja'), array('baja'=>1)));
					$a->save();
					//$a->delete();
					$this->session->set_flashdata('msg','<div class="success">El alumno fué eliminado correctamente.</div>');
				}
				else{
					$this->session->set_flashdata('msg','<div class="error">No puede realizar esta operación el alumno posee deuda.</div>');
					}
			}
			else{		
				$this->session->set_flashdata('msg','<div class="error">No tiene permisos para realizar esta acción.</div>');
			}
		}		
		catch( \Exception $e){
			$this->session->set_flashdata('msg','<div class="error">No puede realizar esta operación.</div>');
		}
		
		redirect('alumnos');
	}	

	function eliminarelacion($id){
		try
		{
			$familia = Family::find($id);
			$familia->delete();
			$this->session->set_flashdata('msg','<div class="success">La relación fué eliminada correctamente.</div>');
		}
		catch( \Exception $e)
		{
			$this->session->set_flashdata('msg','<div class="error">No se pudo realizar la acción.</div>');
		}
		redirect($this->agent->referrer());
	}
}
