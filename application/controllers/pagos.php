<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pagos extends CI_Controller {

	function __construct(){
		parent::__construct();
		//$this->output->enable_profiler(TRUE);
		if(!$this->session->userdata('id')) redirect('auth/login');
	}

	public function index($offset = 0){
		$this->load->helpers('date');
		
		if(!$offset)
			$this->session->unset_userdata('filtros_pagos');
			
		$datos = $this->session->all_userdata();
				
		$string = isset($datos['filtros_pagos']['estudiante'])?$datos['filtros_pagos']['estudiante']:'%%'; 
		$usuario = isset($datos['filtros_pagos']['usuario'])?$datos['filtros_pagos']['usuario']:0;
		$ptype_id = isset($datos['filtros_pagos']['ptype_id'])?$datos['filtros_pagos']['ptype_id']:0;
		$fecha_desde = isset($datos['filtros_pagos']['fecha_desde'])?$datos['filtros_pagos']['fecha_desde']:'';
		$fecha_hasta = isset($datos['filtros_pagos']['fecha_hasta'])?$datos['filtros_pagos']['fecha_hasta']:date('Y-m-d');
		
		$condiciones = 'anulado = ?';
		$valores['anulado'] = isset($datos['filtros_pagos']['anulado'])?$datos['filtros_pagos']['anulado']:0;
		
		if($string != '%%'){
			$condiciones .= ' AND (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ? ';
			$condiciones .= " OR payments.nro_comprobante LIKE ? )";
			$valores['estudiante'] = $string;
			$valores[] = $string;
			$valores[] = $string;
		}
		
		if($usuario > 0){
			$condiciones .= " AND user_id = ?";
			$valores['usuario'] = $usuario;
		}
		
		if($fecha_desde != ''){
			$fecha_desde = $fecha_desde;
					
			if($fecha_hasta != ''){
				$fecha_hasta = $fecha_hasta;
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND fecha BETWEEN ? AND ?";
			$valores['fecha_desde'] = $fecha_desde;
			$valores['fecha_hasta'] = $fecha_hasta;			
		
		}
		
		$joins = ' LEFT JOIN students ON students.id = payments.student_id ';
		
		if($ptype_id > 0){
			$joins .= ' LEFT JOIN pdetails ON payments.id = pdetails.payment_id ';
			$condiciones .= " AND ptype_id = ?";
			$valores['ptype_id'] = $ptype_id;
		}
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$config['base_url'] = site_url('pagos/index');
		$config['total_rows'] = Payment::count(array('joins'=>$joins,'conditions' => $conditions));
		$config['per_page'] = '20'; 
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$pagos = array();
		$pagos = Payment::all(array('joins'=>$joins,
									'conditions' => $conditions, 
									'limit' => $config['per_page'], 
									'offset' => $offset
									) 
								);
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Importe','Usuario','Estado','Observaciones', 'Acciones');
		
		$atts = array(
					  'width'      => '800',
					  'height'     => '600',
					  'scrollbars' => 'yes',
					  'status'     => 'yes',
					  'resizable'  => 'yes',
					  'screenx'    => '0',
					  'screeny'    => '0'
		);
		
		foreach($pagos as $pago){
			$pagado = 1;
			foreach ($pago->debt as $key => $value) {
				if($value->pagado == 0){
					$pagado = 0;
				}				
			}			
			$this->table->add_row(
				$pago->fecha->format('d/m/Y'),
				$pago->nro_comprobante,
				$pago->student->apellido.' '.$pago->student->nombre,
				'$'.$pago->importe,
				$pago->user->apellido.' '.$pago->user->nombre,
				($pagado == 0)?"Pendiente":"Pagado",
				substr($pago->observaciones,0,13).'...',
				anchor_popup('pagos/ver/'.$pago->id, img('static/img/icon/info.png'),array(
					  'width'      => '850',
					  'height'     => '500',
					  'scrollbars' => 'yes',
					  'status'     => 'yes',
					  'resizable'  => 'yes',
					  'screenx'    => '0',
					  'screeny'    => '0'
		), 'class="tipwe" title="Ver Detalle"').' '.
				anchor_popup('pagos/recibo/'.$pago->id,img('static/img/icon/print.png'), $atts,'class="tipwe" title="Imprimir comprobante de pago"').' '.
				anchor('pagos/eliminar/'.$pago->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar pago"')
			);
		}
		
		$data['pagos'] = $this->table->generate();
		$data['usuarios'] = User::all();
		$data['tipos_pagos']=Ptype::all();
		$data['filtros'] = $valores;
		$data['pagination'] = $this->pagination->create_links();
		
		$this->session->set_flashdata('next',current_url());
		$this->template->write_view('content', 'pagos/index',$data);
		$this->template->render();
	}
	
	public function filters($offset = 0){
		$this->load->helpers('date');
		$string = '%'.str_replace(' ', '%', $this->input->post('estudiante')).'%';
		$usuario = $this->input->post('user_id');
		$ptype_id = $this->input->post('ptype_id');
		$fecha_desde = $this->input->post('fecha_desde');
		$fecha_hasta = $this->input->post('fecha_hasta');
		
		$condiciones = 'anulado = ?';
		$valores['anulado'] = $this->input->post('anulado');
		
		if($string != '%%'){
			$condiciones .= ' AND (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ? ';
			$condiciones .= " OR payments.nro_comprobante LIKE ? )";
			$valores['estudiante'] = $string;
			$valores[] = $string;
			$valores[] = $string;
		}
		
		if($usuario > 0){
			$condiciones .= " AND user_id = ?";
			$valores['usuario'] = $usuario;
		}
		
		if($fecha_desde != ''){
			$fecha_desde = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
					
			if($fecha_hasta != ''){
				$fecha_hasta = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND fecha BETWEEN ? AND ?";
			$valores['fecha_desde'] = $fecha_desde;
			$valores['fecha_hasta'] = $fecha_hasta;			
		
		}
		
		$joins = ' LEFT JOIN students ON students.id = payments.student_id ';
		
		if($ptype_id > 0){
			$joins .= ' LEFT JOIN pdetails ON payments.id = pdetails.payment_id ';
			$condiciones .= " AND ptype_id = ?";
			$valores['ptype_id'] = $ptype_id;
		}
		
		$this->session->set_userdata('filtros_pagos', $valores);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$config['base_url'] = site_url('pagos/index');
		$config['total_rows'] = Payment::count(array('joins'=>$joins,'conditions' => $conditions));
		$config['per_page'] = '20'; 
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$pagos = array();
		$pagos = Payment::all(array('joins'=>$joins,
									'conditions' => $conditions, 
									'limit' => $config['per_page'], 
									'offset' => $offset
									)
								);
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Importe','Usuario','Estado','Observaciones', 'Acciones');
		
		$atts = array(
					  'width'      => '800',
					  'height'     => '600',
					  'scrollbars' => 'yes',
					  'status'     => 'yes',
					  'resizable'  => 'yes',
					  'screenx'    => '0',
					  'screeny'    => '0'
		);		

		foreach($pagos as $pago){
			$pagado = 1;
			foreach ($pago->debt as $key => $value) {
				if($value->pagado == 0){
					$pagado = 0;
				}				
			}
			$this->table->add_row(
				$pago->fecha->format('d/m/Y'),
				$pago->nro_comprobante,
				$pago->student->apellido.' '.$pago->student->nombre,
				'$'.$pago->importe,
				$pago->user->apellido.' '.$pago->user->nombre,
				($pagado == 0)?"Pendiente":"Pagado",
				substr($pago->observaciones,0,13).'...',
				anchor_popup('pagos/ver/'.$pago->id, img('static/img/icon/info.png'),array(
					  'width'      => '850',
					  'height'     => '500',
					  'scrollbars' => 'yes',
					  'status'     => 'yes',
					  'resizable'  => 'yes',
					  'screenx'    => '0',
					  'screeny'    => '0'
		), 'class="tipwe" title="Ver Detalle"').' '.
				anchor_popup('pagos/recibo/'.$pago->id,img('static/img/icon/print.png'), $atts,'class="tipwe" title="Imprimir comprobante de pago"').' '.
				anchor('pagos/eliminar/'.$pago->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar pago"')
			);
		}

		$this->session->set_flashdata('next',base_url('pagos'));				
		echo $this->table->generate();
		echo '<div class="pagination">';
		echo $this->pagination->create_links();
		echo '</div>';
	}
	
	function export(){
		$this->load->helper('excel');
		$a = $this->_obtener_datos($_POST);
		$res = array();
		$i = 0;
		foreach($a as $al){
			$res[$i]['Fecha'] = ($al->fecha)?$al->fecha->format('d/m/Y'):'';
			$res[$i]['Comprobante'] = $al->nro_comprobante;
			$res[$i]['Estudiante'] = $al->student->apellido.', '.$al->student->nombre;
			$res[$i]['Importe'] = $al->importe;
			$res[$i]['Usuario'] = $al->user->apellido.', '.$al->user->nombre;
			$res[$i++]['Observaciones'] = $al->observaciones;
		}
		
		to_excel($res, "Cobros Realizados \t".$_POST['fecha_desde']."-".$_POST['fecha_hasta']); 
	}
	
	function _obtener_datos($datos){
		$this->load->helpers('date');
		
		$string = isset($datos['estudiante'])?'%'.str_replace(' ', '%', $datos['estudiante']).'%':'%%'; 
		$usuario = isset($datos['user_id'])?$datos['user_id']:0;
		$ptype_id = isset($datos['ptype_id'])?$datos['ptype_id']:0;
		$fecha_desde = isset($datos['fecha_desde'])?$datos['fecha_desde']:'';
		$fecha_hasta = isset($datos['fecha_hasta'])?$datos['fecha_hasta']:date('Y-m-d');
		
		$condiciones = 'anulado = ?';
		$valores['anulado'] = isset($datos['anulado'])?$datos['anulado']:0;
		
		if($string != '%%'){
			$condiciones .= ' AND (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ? ';
			$condiciones .= " OR payments.nro_comprobante LIKE ? )";
			$valores['estudiante'] = $string;
			$valores[] = $string;
			$valores[] = $string;
		}
		
		if($usuario > 0){
			$condiciones .= " AND user_id = ?";
			$valores['usuario'] = $usuario;
		}
		
		if($fecha_desde != ''){
			$fecha_desde = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
					
			if($fecha_hasta != ''){
				$fecha_hasta = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND fecha BETWEEN ? AND ?";
			$valores['fecha_desde'] = $fecha_desde;
			$valores['fecha_hasta'] = $fecha_hasta;			
		
		}
		
		$joins = ' LEFT JOIN students ON students.id = payments.student_id ';
		
		if($ptype_id > 0){
			$joins .= ' LEFT JOIN pdetails ON payments.id = pdetails.payment_id ';
			$condiciones .= " AND ptype_id = ?";
			$valores['ptype_id'] = $ptype_id;
		}
		
		$conditions = array_merge(array($condiciones), $valores);
			
		return $pagos = Payment::all(array('joins'=>$joins,
									'conditions' => $conditions
									) 
								);
	}
	
	public function reporte(){
		$this->load->helpers('date');
		$string = '%'.str_replace(' ', '%', $this->input->post('estudiante')).'%';
		$usuario = $this->input->post('user_id');
		$fecha_desde = $this->input->post('fecha_desde');
		$fecha_hasta = $this->input->post('fecha_hasta');
		
		$condiciones = 'anulado = ?';
		$valores['anulado'] = $this->input->post('anulado');
		$data['anulado'] = $valores['anulado'];
		
		if($string != '%%'){
			$condiciones .= ' AND (CONCAT_WS(" ",students.apellido, students.nombre) LIKE ? OR CONCAT_WS(" ",students.nombre, students.apellido) LIKE ? ';
			$condiciones .= " OR payments.nro_comprobante LIKE ? )";
			$valores['estudiante'] = $string;
			$valores[] = $string;
			$valores[] = $string;
		}
		
		if($usuario > 0){
			$condiciones .= " AND user_id = ?";
			$valores['usuario'] = $usuario;
			$u = User::find($usuario);
			$data['usuario'] = $u->apellido." ".$u->nombre ;
		}
		
		if($fecha_desde != ''){
			$fecha_desde = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
					
			if($fecha_hasta != ''){
				$fecha_hasta = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND fecha BETWEEN ? AND ?";
			$valores['fecha_desde'] = $fecha_desde;
			$valores['fecha_hasta'] = $fecha_hasta;			
		
		}
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$pagos = array();
		$pagos = Payment::all(array('joins'=>array('student'),
									'conditions' => $conditions
									)
								);
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Importe','Usuario','Observaciones');
		
		foreach($pagos as $pago){
			$this->table->add_row(
				$pago->fecha->format('d/m/Y'),
				$pago->nro_comprobante,
				$pago->student->apellido.' '.$pago->student->nombre,
				'$'.$pago->importe,
				$pago->user->apellido.' '.$pago->user->nombre,
				$pago->observaciones
				);
		}
		
		$data['titulo'] = "Reporte de pagos";
		if($fecha_desde !== $fecha_hasta)
			$data['fecha'] = mysql_to_human($fecha_desde).'-'.mysql_to_human($fecha_hasta);
		else
			$data['fecha'] = mysql_to_human($fecha_desde);
			
		$data['reporte'] = $this->table->generate();
		
		$this->session->set_flashdata('next',$this->agent->referrer());
		$this->template->set_template('reporte');
		$this->template->write_view('content', 'pagos/reporte',$data);
		$this->template->render();
	}
	
	function historial($student_id, $offset = 0){
		$this->load->library('pagination');
		$config['base_url'] = site_url('historial/'.$student_id.'/');
		$config['total_rows'] = Payment::count(array('conditions' => array('student_id = ?', $student_id)));
		$config['per_page'] = '20';  
		$config['num_links'] = '1'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->pagination->initialize($config);
		
		$h = Payment::find('all', array('conditions' => array('student_id = ?', $student_id), 'limit' => $config['per_page'], 'offset' => $offset));

			if($this->session->userdata('grupo') == 'alumno'){//esto es para el tutor
				$this->table->set_heading('# Comprobante','Concepto','Fecha de pago','Importe', 'Cobrador','Estado'/*,'Acciones'*/);
				$count = 0;
				foreach($h as $p){	
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
							$this->table->add_row(
								$p->nro_comprobante,
								$d->debt->amount->concept->concepto.' '.
								$d->debt->amount->ciclo_lectivo,
								($p->fecha != "")?$p->fecha->format('d/m/Y'):"",
								'$'.$d->importe,
								$p->user->apellido.', '.$p->user->nombre,
								($d->debt->pagado == 0)?"Pendiente":"Pagado"/*,
								anchor_popup('pagos/recibo/'.$p->id,img('static/img/icon/print.png'), $atts,'class="tipwe" title="Imprimir recibo"').' '.anchor('pagos/eliminar/'.$p->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular pago"')*/
							);
						}
					}
				}				
			}
			else{
				$this->table->set_heading('# Pago','Concepto','Fecha de pago','Importe','Anulado', 'Cobrador','Acciones');
				foreach($h as $p){
					foreach($p->detail as $d){
						$this->table->add_row(
							$p->id,
							$d->debt->amount->concept->concepto.' '.$d->debt->amount->ciclo_lectivo,
							($p->fecha != "")?$p->fecha->format('d/m/Y H:i a'):"",
							'$'.$d->importe,
							$p->anulado ? 'Si ('.$p->fecha_anulado->format('d/m/Y').')' : 'No',
							$p->user->apellido.', '.$p->user->nombre,
							anchor('alumnos/recibos/',img('static/img/icon/print.png'), 'class="tipwe" title="Imprimir recibo"').' '.anchor('alumnos/recibos/',img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Anular pago"')
						);
					}
				}
			}		
		

		$data['a'] = Student::find_by_id($student_id);
		$data['pagos'] = $this->table->generate();
		$data['pagination'] = $this->pagination->create_links();
		
		$this->template->write_view('content', 'pagos/historial',$data);
		$this->template->render();
	}
	
	function agregar(){
		$this->load->helpers('date');
		
		$datos = $this->session->userdata('pago');
		$insert = array_merge($datos, $_POST);
		//print_r($insert);
		$insert['moneda']=isset($insert['moneda'])?$insert['moneda']:1; //Pesos
		$nro_c = Payment::count(array('nro_comprobante'=>$insert['nro_comprobante']));
		if($nro_c != 0){
			$ultimo = Payment::last(array('order'=>'id ASC',
									'conditions'=>array('nro_comprobante LIKE ?','0001-%')))->nro_comprobante;
			$nuevo = explode('-',$ultimo);
			$insert['nro_comprobante'] = $nuevo[0].'-'.str_pad(($nuevo[1] + 1), 8, '0', STR_PAD_LEFT);
			$nuevo[1] = str_pad(($nuevo[1] + 1), 8, '0', STR_PAD_LEFT);
		}
		else{
			$nuevo = explode('-',$insert['nro_comprobante']);
			$insert['nro_comprobante'] = $nuevo[0].'-'.str_pad(($nuevo[1]), 8, '0', STR_PAD_LEFT);			
		}
				
		$i = Payment::connection();
		try{
			$i->transaction();
			$insert['user_id'] = $this->session->userdata('id');
			$insert['fecha'] = date('Y-m-d H:i:s');
			$insert['nro_recibo'] = $nuevo[1];
			$insert['pto_venta'] = $nuevo[0];
			Payment::create(
				elements( array(
					'student_id',
					'user_id',
					'nro_comprobante',
					'fecha',
					'moneda',
					'importe',
					'observaciones',
					'nro_recibo',
					'pto_venta'
				), $insert )
			);
			$pagoid = $i->insert_id();

			$conditions = array('conditions' => 'id = "'.$pagoid.'"');
			$pago_db = Payment::find($conditions);
			$pago_db->update_attributes(array('related_payments' => $pagoid,));
			$pago_db->save();
		
			foreach($insert['parcial'] as $k => $v){
				$detalle = array('debt_id' => $k, 'payment_id' => $pagoid , 'importe' => $v, 'estado' => 1);
				$d = new Detail($detalle);
				
				if($d->is_valid()){
					$d->save();
				}
				
				$deu = Debt::find($k);
				if($deu->amount->concept_id!=2){
					if($insert['saldo'][$k]==$v){
						$deu->pagado = 1;
						$deu->importe = $deu->amount->importe;
						$deu->save();
					}
				}				
			}
		
			
			foreach($insert['ptype_id'] as $k=>$v){
				$det = array(
								'payment_id'=>$pagoid,
								'ptype_id'=>$v,
								'creditcard_id'=>$insert['creditcard_id'][$k],
								'bank_id'=>$insert['bank_id'][$k],
								'importe'=>$insert['subimporte'][$k],
								'vencimiento'=>mdate('%Y-%m-%d' ,normal_to_unix($insert['vencimiento'][$k])),
								'nro_comprobante'=>$insert['comprobante'][$k],
								'cuotas'=>$insert['cuotas'][$k],
								);
								
				$pdetail = new Pdetail($det);
				if( $pdetail->is_valid( ) ){
					$suma = Pdetail::find(
						array(
							'select' => 'sum(importe) as importe',
							'conditions' => array('payment_id = ?',$pagoid)
						)
					);
					
					$pago = Payment::find($pagoid);
					
					if(($suma->importe + $det['importe']) <= $pago->importe){
						$pdetail->save();
					}
				}
			}
		
			$this->session->set_flashdata('msg','<div class="success">El pago se realizó correctamente.</div>');
			$i->commit();
			
			$this->session->unset_userdata('pago');
						
			$this->session->set_userdata('next',$this->agent->referrer());
			$this->session->set_flashdata('next',base_url().'alumnos/ver/'.$insert['student_id']);
			redirect('pagos/recibo/'.$pago->id);
		}
		catch (\Exception $e){
			//print_r($e);
			$i->rollback();
			$this->session->set_flashdata('msg','<div class="error">Hubo un error al realizar el pago.</div>');
			$this->session->unset_userdata('pago');
			redirect('alumnos/ver/'.$insert['student_id']);
		}
	}
	
	function pago_eventual($student,$course,$inscription){
		if($_POST){
			$i = Payment::connection();
			try{
				$i->transaction();
				
				if( ! Concept::exists(array('concepto'  => $this->input->post('concepto')))){
					$c = Concept::create(array('concepto' => $this->input->post('concepto')));
				}
				else{
					$c = Concept::find_by_concepto($this->input->post('concepto'));
				}
				$c = $c->id;
				
				$a = Amount::create(array(
					'concept_id' => $c,
					'importe' => $this->input->post('importe'),
					'course_id' => $course,
					'ciclo_lectivo' => date('Y'),
					'fecha' => date('Y-m-d'),
					'pago_eventual' => 1
				));
				$a = $a->id;
				
				$d = Debt::create(array(
					'student_id' => $student,
					'amount_id' => $a,
					'inscription_id' => $inscription,
					'pagado' => $this->input->post('pagado'),
				));
				$d = $d->id;
				
				if($this->input->post('pagado')){
					$p = Payment::create(array(
						'student_id' => $student,
						'user_id' => $this->session->userdata('id'),
						'importe' => $this->input->post('importe'),
						'fecha' => date('Y-m-d H:i:s'),
						'nro_comprobante' => $this->input->post('nro_comprobante'),
						'observaciones' => "Pago eventual"
					));
					$p = $p->id;
					
					$dt = Detail::create(array(
						'debt_id' => $d,
						'payment_id' => $p,
						'estado' => 1,
						'importe' => $this->input->post('importe')
					));
					
					$pd = Pdetail::create(array(
						'ptype_id' => 1,
						'payment_id' => $p,
						'importe' => $this->input->post('importe'),
						'nro_comprobante' => $this->input->post('nro_comprobante')
					));
				}
				$i->commit();
				$this->session->set_flashdata('msg','<div class="success">El pago se realizó correctamente.</div>');
			}
			catch (\Exception $e){
				$i->rollback();
				$this->session->set_flashdata('msg','<div class="error">Hubo un error al realizar el pago.</div>');
			}
			redirect('alumnos/ver/'.$student);
		}
		
		$nro = Payment::find('last');
			
		$data['nro_comprobante'] = '0001-00000000';
		if($nro){
			$nuevo = explode('-',$nro->nro_comprobante);
			$data['nro_comprobante'] = $nuevo[0].'-'.str_pad(($nuevo[1] + 1), 8, '0', STR_PAD_LEFT);
		}
		
		$data['titulo'] = "Pago eventual";
		$this->template->write_view('content','pagos/eventual', $data);
		$this->template->render();
	}
	
	function recibo($pagoid){
		
		$data['pago'] = Payment::find($pagoid);
		$cd = array('conditions' => array('payment_id = ?', $pagoid));
		$data['detalles'] = Detail::all($cd);
		$data['student'] = Student::find($data['pago']->student_id);
		$ci = array('conditions' => array('student_id = ?', $data['student']->id));
		$data['inscripto'] = Inscription::last($ci);
		
		$this->load->library('table');
		
		$this->table->set_heading('CONCEPTO','VENCIMIENTO','IMPORTE','DESCUENTO','PAGADO');
		$t = 0;
		foreach($data['detalles'] as $d){
			$desc = Scolarship::first(array('conditions' => array('amount_id = ? AND student_id = ?', $d->debt->amount_id,$d->debt->student_id)));
			
			$this->table->add_row(
				$d->debt->amount->concept->concepto.' '.$d->debt->amount->ciclo_lectivo,
				$d->debt->amount->fecha->format('d/m/Y'),
				'$'.$d->debt->amount->importe,
				(isset($desc))?$desc->porcien_descuento.' %':'',
				'$'.$d->importe
			);
			$t += $d->importe;
		}
		
		$cell = array('data' => '<strong>TOTAL</strong>', 'colspan' => 4);
		$this->table->add_row($cell, '$'.$t);
		$data['tabla'] = $this->table->generate();
		
		$this->template->set_template('recibo');
		$this->template->write_view('content', 'pagos/recibo',$data);
		$this->template->render();
	}
		
	function ver($pagoid){
		$data['pago'] = Payment::find($pagoid);
		$cd = array('conditions' => array('payment_id = ?', $pagoid));
		$data['detalles'] = Detail::all($cd);
		$data['student'] = Student::find($data['pago']->student_id);
		$data['pdetails']=Pdetail::all($cd);
		$fecha = date_parse($data['pago']->fecha);
		$ci = array('conditions' => array('student_id = ? AND ciclo_lectivo = ?', $data['student']->id, $fecha['year']));
		$data['inscripto'] = Inscription::find($ci);
		if(!$data['inscripto'])
			$data['inscripto']= Inscription::last(array('conditions' => array('student_id = ?', $data['student']->id)));
		
				
		$this->load->library('table');
		
		$this->table->set_heading('Concepto','Vencimiento','Importe','Descuento','Pagado');
		foreach($data['detalles'] as $d){
			$desc = Scolarship::first(array('conditions' => array('amount_id = ? AND student_id = ?', $d->debt->amount_id,$d->debt->student_id)));
			
			$this->table->add_row(
				$d->debt->amount->concept->concepto.' '.$d->debt->amount->ciclo_lectivo,
				$d->debt->amount->fecha->format('d/m/Y'),
				'$'.$d->debt->amount->importe,
				(isset($desc))?$desc->porcien_descuento.' %':'',
				'$'.$d->importe
			);
		}
		
		$data['tabla'] = $this->table->generate();
		
		//////**************Formas Pago***********************/
		$this->table->set_heading('Descripción','Comprobante','Banco/Tarjeta','Vto/Cuotas','Importe');
		foreach($data['pdetails'] as $d){
			$bt = '';
			if($d->bank_id > 0){
				$bt = Bank::find($d->bank_id)->nombre;
			}
			elseif($d->creditcard_id > 0){
				$bt = Creditcard::find($d->creditcard_id)->nombre;
				}
				
			$this->table->add_row(
				$d->ptype->tipo,
				(strlen($d->nro_comprobante)>0)?$d->nro_comprobante:'-',
				$bt,
				($d->vencimiento != NULL)?$d->vencimiento:($d->cuotas > 0)?$d->cuotas:'',
				'$'.$d->importe
			);
		}
		
		$tt = $this->table->generate().'<br />Observaciones: '.$data['pago']->observaciones;
				
		$this->template->set_template('detalle');
		$this->template->write_view('content', 'pagos/ver',$data);
		$this->template->write('footer', $tt,$overwrite = FALSE);
		$this->template->render();
	}
	
	function eliminar($id){
		try{
			$a = Payment::find($id);
			$a->user_id = $this->session->userdata('id');
			$a->anulado = 1;
			$a->fecha_anulado = date('Y-m-d');
			foreach($a->debt as $d){
				$d->pagado = 0;
				$d->save();
			}
			$a->save();
			$this->session->set_flashdata('msg','<div class="success">El pago fué anulado.</div>');
		}
		catch( \Exception $e){
			$this->session->set_flashdata('msg','<div class="error">El pago no fué anulado.</div>');
			}
			
		if ($this->agent->is_referral()){
			$str = $this->agent->referrer();
			$desde = strlen(base_url());
			redirect(substr($str,$desde));
		}
		redirect('pagos');
	}
	
	function cheques($offset = 0){
		$this->load->helpers('date');
		
		$datos = $this->session->all_userdata();
		
		if(!$offset)
			$this->session->unset_userdata('filtros_cheques');
			
		$nrocomprobante = isset($datos['filtros_cheques']['nrocomprobante'])?$datos['filtros_cheques']['nrocomprobante']:'%%'; 
		$fecha_desde = isset($datos['filtros_cheques']['fecha_desde'])?$datos['filtros_cheques']['fecha_desde']:'';
		$fecha_hasta = isset($datos['filtros_cheques']['fecha_hasta'])?$datos['filtros_cheques']['fecha_hasta']:date('Y-m-d');
		
		$condiciones = 'anulado = ?';
		$valores['anulado'] = 0;
		
		if($nrocomprobante != '%%'){
			$condiciones .= " AND pdetails.nro_comprobante LIKE ? ";
			$valores["nrocomprobante"] = '%'.$nrocomprobante.'%';
		}
		
		if($fecha_desde != ''){
			$fecha_desde = $fecha_desde;
					
			if($fecha_hasta != ''){
				$fecha_hasta = $fecha_hasta;
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND (vencimiento BETWEEN ? AND ? OR vencimiento IS NULL)";
			$valores['fecha_desde'] = $fecha_desde;
			$valores['fecha_hasta'] = $fecha_hasta;			
		}
		
		$joins = ' LEFT JOIN payments ON payments.id = pdetails.payment_id ';
		
		$condiciones .= " AND ptype_id IN (?)";
		$valores['ptype_id'] = '2 , 3';
		
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$config['base_url'] = site_url('pagos/cheques');
		$config['total_rows'] = Pdetail::count(array('joins'=>$joins,'conditions' => $conditions));
		$config['per_page'] = '20'; 
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$pagos = array();
		$pagos = Pdetail::all(array('joins'=>$joins,
									'conditions' => $conditions, 
									'limit' => $config['per_page'], 
									'offset' => $offset
									) 
								);
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Banco','Cheque','Vencimiento','Importe','Usuario');
		
		foreach($pagos as $pago){
			$this->table->add_row(
				$pago->payment->fecha->format('d/m/Y'),
				$pago->payment->nro_comprobante,
				$pago->payment->student->apellido.' '.$pago->payment->student->nombre,
				($pago->bank_id > 0)?Bank::find($pago->bank_id)->nombre:'',	
				$pago->nro_comprobante,
				($pago->vencimiento!= null)?$pago->vencimiento->format('d/m/Y'):'',			
				'$'.$pago->importe,				
				$pago->payment->user->apellido.' '.$pago->payment->user->nombre
			);
		}
		
		$data['cheques'] = $this->table->generate();
		$data['filtros'] = $valores;
		$data['pagination'] = $this->pagination->create_links();
		
		$this->session->set_flashdata('next',current_url());
		$this->template->write_view('content', 'pagos/cheques',$data);
		$this->template->render();
	}
	
	public function filter($offset=0){
		
		$this->load->helpers('date');
				
		$nrocomprobante = trim($this->input->post('nrocomprobante'));
		$fecha_desde = $this->input->post('fecha_desde');
		$fecha_hasta = $this->input->post('fecha_hasta');
		
		$condiciones = 'anulado = ?';
		$valores['anulado'] = 0;
		
		if($nrocomprobante != '%'){
			$condiciones .= " AND pdetails.nro_comprobante LIKE ? ";
			$valores["nrocomprobante"] = '%'.$nrocomprobante.'%';
			$valor["nrocomprobante"] = $nrocomprobante;
		}
		
		if($fecha_desde != ''){
			$fecha_desde = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
			if($fecha_hasta != ''){
				$fecha_hasta = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND (vencimiento BETWEEN ? AND ? OR vencimiento IS NULL)";
			$valores['fecha_desde'] = $fecha_desde;
			$valor['fecha_desde'] = $fecha_desde;
			$valores['fecha_hasta'] = $fecha_hasta;				
			$valor['fecha_hasta'] = $fecha_hasta;				
		}
		
		$joins = ' LEFT JOIN payments ON payments.id = pdetails.payment_id ';
		
		$condiciones .= " AND ptype_id IN ( ?)";
		$valores['ptype_id'] = '2 , 3';
		$valor['ptype_id'] = '2 , 3';
			
		$this->session->set_userdata('filtros_cheques', $valor);
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$config['base_url'] = site_url('pagos/cheques');
		$config['total_rows'] = Pdetail::count(array('joins'=>$joins,'conditions' => $conditions));
		$config['per_page'] = '20'; 
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		
		$pagos = array();
		$pagos = Pdetail::all(array('joins'=>$joins,
									'conditions' => $conditions, 
									'limit' => $config['per_page'], 
									'offset' => $offset
									)
								);
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Banco','Cheque','Vencimiento','Importe','Usuario');
		
		foreach($pagos as $pago){
			$this->table->add_row(
				$pago->payment->fecha->format('d/m/Y'),
				$pago->payment->nro_comprobante,
				$pago->payment->student->apellido.' '.$pago->payment->student->nombre,
				($pago->bank_id > 0)?Bank::find($pago->bank_id)->nombre:'',	
				$pago->nro_comprobante,
				($pago->vencimiento!= null)?$pago->vencimiento->format('d/m/Y'):'',			
				'$'.$pago->importe,				
				$pago->payment->user->apellido.' '.$pago->payment->user->nombre
			);
		}

		$this->session->set_flashdata('next',base_url('pagos'));				
		echo $this->table->generate();
		echo '<div class="pagination">';
		echo $this->pagination->create_links();
		echo '</div>';
	}
	
	function exportcheques(){
		$this->load->helper('excel');
		$a = $this->_obtener_datosCheques($_POST);
		$res = array();
		$i = 0;
		foreach($a as $pago){
			$res[$i]['Fecha'] = $pago->payment->fecha->format('d/m/Y');
			$res[$i]['Comprobante'] = $pago->payment->nro_comprobante;
			$res[$i]['Estudiante'] = $pago->payment->student->apellido.' '.$pago->payment->student->nombre;
			$res[$i]['Banco'] = ($pago->bank_id > 0)?Bank::find($pago->bank_id)->nombre:'-';
			$res[$i]['Cheque'] = $pago->nro_comprobante;
			$res[$i]['Fecha Vto'] = ($pago->vencimiento!= null)?$pago->vencimiento->format('d/m/Y'):'-';
			$res[$i]['Importe'] = '$'.$pago->importe;
			$res[$i++]['Usuario'] = $pago->payment->user->apellido.' '.$pago->payment->user->nombre;
		}
		
		to_excel($res, "Cheques Recibidos \t".$_POST['fecha_desde']."-".$_POST['fecha_hasta']); 
	}
	
	function reportecheques(){
		
		$pagos = $this->_obtener_datosCheques($_POST);
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Banco','Cheque','Vencimiento','Importe','Usuario');
		
		foreach($pagos as $pago){
			$this->table->add_row(
				$pago->payment->fecha->format('d/m/Y'),
				$pago->payment->nro_comprobante,
				$pago->payment->student->apellido.' '.$pago->payment->student->nombre,
				($pago->bank_id > 0)?Bank::find($pago->bank_id)->nombre:'',	
				$pago->nro_comprobante,
				($pago->vencimiento!= null)?$pago->vencimiento->format('d/m/Y'):'',			
				'$'.$pago->importe,				
				$pago->payment->user->apellido.' '.$pago->payment->user->nombre
			);
		}
		
		$data['titulo'] = "Reporte de Cheques: ".$_POST['fecha_desde']."-".$_POST['fecha_hasta'];
					
		$data['reporte'] = $this->table->generate();
		
		$this->session->set_flashdata('next',$this->agent->referrer());
		$this->template->set_template('reporte');
		$this->template->write_view('content', 'pagos/reporte',$data);
		$this->template->render();
	}
	
	function _obtener_datosCheques($arr){
		$this->load->helpers('date');
				
		$nrocomprobante = trim($arr['nrocomprobante']);
		$fecha_desde = $arr['fecha_desde'];
		$fecha_hasta = $arr['fecha_hasta'];
		
		$condiciones = 'anulado = ?';
		$valores['anulado'] = 0;
		
		if($nrocomprobante != '%'){
			$condiciones .= " AND pdetails.nro_comprobante LIKE ? ";
			$valores["nrocomprobante"] = '%'.$nrocomprobante.'%';
		}
		
		if($fecha_desde != ''){
			$fecha_desde = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
			if($fecha_hasta != ''){
				$fecha_hasta = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND (vencimiento BETWEEN ? AND ? OR vencimiento IS NULL)";
			$valores['fecha_desde'] = $fecha_desde;
			$valores['fecha_hasta'] = $fecha_hasta;				
		}
		
		$joins = ' LEFT JOIN payments ON payments.id = pdetails.payment_id ';
		
		$condiciones .= " AND ptype_id IN ( ?)";
		$valores['ptype_id'] = '2 , 3';
					
		$conditions = array_merge(array($condiciones), $valores);
		
		$pagos = array();
		
		return Pdetail::all(array('joins'=>$joins,
									'conditions' => $conditions
									)
								);
	}

	/***************************************************************************/
	/////////////////TUTOR PARA PAGAR POR BANCO SAN JUAN/////////////////////////
	function agregar_pago_tutor(){
		$this->load->helpers('date');
		$insert = $_POST;
		//print_r($_POST);
		//print_r($this->session->userdata('pago'));
		$insert['moneda']=isset($insert['moneda'])?$insert['moneda']:1; //Pesos
		$insert['ptype_id'] = isset($insert['ptype'])?$insert['ptype']:array(17); //Pesos
		$string_pagosid = "";
		$user = User::find(
			array(
				'select' => 'id',
				'conditions' => array('usuario = ?',"banco_sj")
			)
		);//volver
		try{
			$i = Payment::connection();
			$i->transaction();
			foreach($insert['parcial'] as $k => $v){
				if($v){
					$ultimo = Payment::last(array('order'=>'id ASC',
												'conditions'=>array('nro_comprobante LIKE ?','0002-%')));
					if($ultimo){
						$ultimo = $ultimo->nro_comprobante;
					}
					else{
						$ultimo = "0002-00000000";
					}

					$nuevo = explode('-',$ultimo);
					$insert['nro_comprobante'] = $nuevo[0].'-'.str_pad(($nuevo[1] + 1), 8, '0', STR_PAD_LEFT);

					
					$insert['user_id'] = $user->id;
					$insert['fecha'] = date('Y-m-d H:i:s');
					$insert['importe'] = $v;
					$insert['related_payments'] = "";
					$insert['nro_recibo'] = str_pad(($nuevo[1] + 1), 8, '0', STR_PAD_LEFT);
					$insert['pto_venta'] = $nuevo[0];

					Payment::create(
						elements( array(
							'student_id',
							'user_id',
							'nro_comprobante',
							'fecha',
							'moneda',
							'importe',
							'observaciones',
							'related_payments',
							'nro_recibo',
							'pto_venta'
						), $insert )
					);
					$pagoid = $i->insert_id();
					//Armo la cadena con IDs de pagos
					$string_pagosid .= $pagoid."-";
					$insert['pago'][$k] = $pagoid;		
					$detalle = array('debt_id' => $k, 'payment_id' => $pagoid , 'importe' => $v, 'estado' => 0);
					$d = new Detail($detalle);

					//var_dump($d);		
					//echo "<br>---------------------------------------------------------------------------------------------------------------------------------------------<br>";
					if($d->is_valid()){
						$d->save();
					}
					
					//Esto lo debe hacer despues cuando importamos el archivo del banco
					/*
					$deu = Debt::find($k);
					if($deu->amount->concept_id!=2){
						if($insert['saldo'][$k]==$v){
							$deu->pagado = 1;
							$deu->importe = $deu->amount->importe;
							$deu->save();
						}
					}*/

					//Forma de pagos
					foreach($insert['ptype_id'] as $j=>$v){
						$det = array(
								'payment_id'=>$insert['pago'][$k],
								'ptype_id'=>$v,
								'bank_id'=>2,
								'importe'=>$insert['importe']
								);
										
						$pdetail = new Pdetail($det);
						if( $pdetail->is_valid( ) ){
							$suma = Pdetail::find(
								array(
									'select' => 'sum(importe) as importe',
									'conditions' => array('payment_id = ?',$insert['pago'][$k])
								)
							);
							
							$pago = Payment::find($insert['pago'][$k]);
							if(($suma->importe + $det['importe']) <= $pago->importe){
								$pdetail->save();
							}
						}
					}					
				}//fin if si viene importe en la deuda
			}//fin for de cada pago

			//saco el ultimo caracter al string de id pagos
			$string_pagosid = trim($string_pagosid, '-');
			//Creo un arreglo para recorrer los ids e ir actualizando uno por uno
			$array_pagos = explode("-", $string_pagosid);
			foreach ($array_pagos as $key => $value) {
				$conditions = array('conditions' => 'id = '.$value);
				$pago_db = Payment::find($conditions);				
				$pago_db->update_attributes(array('related_payments' => $string_pagosid));
				$pago_db->save();
			}
		
			$this->session->set_flashdata('msg','<div class="success">El pago se realizó correctamente.</div>');
			$i->commit();
			
			$this->session->unset_userdata('pago');
						
			$this->session->set_userdata('next',$this->agent->referrer());
			$this->session->set_flashdata('next',base_url().'alumnos/ver/'.$insert['student_id']);
			redirect('pagos/recibo_para_tutor/'.$string_pagosid);
		}
		catch (\Exception $e){
			//print_r($e);
			$i->rollback();
			$this->session->set_flashdata('msg','<div class="error">Hubo un error al realizar el pago.'.print_r($e).'</div>');
			$this->session->unset_userdata('pago');
			redirect('alumnos/ver/'.$insert['student_id']);
		}
	}

	function recibo_para_tutor($str_pagosid){
		$pagosid = explode('-', $str_pagosid);
		foreach ($pagosid as $key => $pagoid) {
			$data['pago'] = Payment::find($pagoid);
			$cd = array('conditions' => array('payment_id = ?', $pagoid));
			$data['detalles'] = Detail::all($cd);
			$data['student'] = Student::all($data['pago']->student_id);
			$ci = array('conditions' => array('student_id = ?', $data['student']->id));
			$data['inscripto'] = Inscription::last($ci);
			$cc = array('conditions' => array('id = ?', $data['inscripto']->course_id));
			$data['curso'] = Course::first($cc);
			$cdiv = array('conditions' => array('id = ?', $data['inscripto']->division_id));
			$data["division"] = Division::first($cdiv)->division;
			$cn =  array('conditions' => array('id = ?', $data['curso']->level_id));
			$data["nivel"] = Level::first($cn)->nivel;

			$t = 0;
			foreach($data['detalles'] as $d){
				$desc = Scolarship::first(array('conditions' => array('amount_id = ? AND student_id = ?', $d->debt->amount_id,$d->debt->student_id)));

				/*$d->debt->amount->concept->concepto.' '.$d->debt->amount->ciclo_lectivo,
				$d->debt->amount->fecha->format('d/m/Y'),
				'$'.$d->debt->amount->importe,
				(isset($desc))?$desc->porcien_descuento.' %':'',
				'$'.$d->importe*/

				//Arreglo para armar el barcode
				$campo = array();
				$campo[] = "7009"; //campo 1 Codigo identificacion 4 dig
				//$campo[] = "8052";
				//Obtengo el numero de recibo que va a ir en la boleta
				$array_nro_recibo = explode("-", $data['pago']->nro_comprobante);			
				$campo[] = $array_nro_recibo[1];//campo 3 Numero de recibo 8 dig
				//Guardo el importe en una variable para despues calcularle los importes de los demas vencimientos
				$importe = $d->importe;

				//Obtengo fecha de hoy y la ultima fecha del mes para ver si paso el 3er vencimiento
				$hoy = getdate();
				$hoy_string = $hoy['mday'].'-'.$hoy['mon'].'-'.$hoy['year'];
				$hoy = date('Ymd',strtotime($hoy_string));
				$ultimo_dia = $this->utils->calcular_ultimo_dia_mes($d->debt->amount->fecha);
				$ultima_fecha_mes = date("Ym".$ultimo_dia,strtotime($d->debt->amount->fecha));

				//arreglos para el recibo
				$array_fechas = array();
				$array_importes = array();
				if($hoy <= $ultima_fecha_mes){
					$campo[] = $this->utils->importe_barcode($importe,5,2);//campo 6 importe 1er vto 7 dig					
					$campo[] = date("15my",strtotime($d->debt->amount->fecha));//campo 7 Fecha 1er vto 6 dig
					$array_importes['importe1'] = number_format($importe,"2",",",".");
					$array_fechas['fecha1'] = date("15/m/Y",strtotime($d->debt->amount->fecha));
					//$campo[] = date("1506y",strtotime($d->debt->amount->fecha));//campo 7 Fecha 1er vto 6 dig					
					$campo[] = $this->utils->importe_barcode($importe * 1.10,5,2);//campo 8 Importe 2do vto
					$campo[] = date("25my",strtotime($d->debt->amount->fecha));;//campo 9 Fecha 2do vto
					//$campo[] = date("2006y",strtotime($d->debt->amount->fecha));;//campo 9 Fecha 2do vto
					$array_importes['importe2'] = number_format(($importe * 1.10),"2",",",".");
					$array_fechas['fecha2'] = date("25/m/Y",strtotime($d->debt->amount->fecha));

					$campo[] = $this->utils->importe_barcode($importe * 1.15,5,2);//campo 10 Importe 3er vto					
					$campo[] = date($ultimo_dia."my",strtotime($d->debt->amount->fecha));//campo 11 Fecha 3er vto
					$array_importes['importe3'] = number_format(($importe * 1.15),"2",",",".");
					$array_fechas['fecha3'] = date($ultimo_dia."/m/Y",strtotime($d->debt->amount->fecha));
				}
				else{
					if($d->payment->fecha_reimpresion == ''){
						//insertar fecha de reimpresion
						$d->payment->fecha_reimpresion = date('Y-m-d');
						$d->payment->save();
					}
					else{
						$fecha_reimpresion = date('Ymd',strtotime($d->payment->fecha_reimpresion));
						$m = date("m",$fecha_reimpresion);
						$day = date("d",$fecha_reimpresion);
						$y = date("Y",$fecha_reimpresion);
						$nuevo_venc = date('Y-m-d',mktime(0, 0, 0,$m ,$day + 7 ,$y ));
						if($hoy > $nuevo_venc){
							$d->payment->fecha_reimpresion = date('Y-m-d');
							$d->payment->save();							
						}
					}
					$nueva_fecha = strtotime($d->payment->fecha_reimpresion);
					$m = date("m",$nueva_fecha);
					$day = date("d",$nueva_fecha);
					$y = date("Y",$nueva_fecha);
					$nuevo_venc1 = date('Y-m-d',mktime(0, 0, 0,$m ,$day + 3 ,$y ));
					$nuevo_venc2 = date('Y-m-d',mktime(0, 0, 0,$m ,$day + 5,$y ));
					$nuevo_venc3 = date('Y-m-d',mktime(0, 0, 0,$m ,$day + 7,$y ));
					$importe = $importe * 1.2;
					$campo[] = $this->utils->importe_barcode($importe,5,2);//campo 6 importe 1er vto 7 dig
					$campo[] = date("dmy",strtotime($nuevo_venc1));//campo 7 Fecha 1er vto 6 dig
					//$campo[] = date("1506y",strtotime($d->debt->amount->fecha));//campo 7 Fecha 1er vto 6 dig
					$array_importes['importe1'] = number_format($importe,"2",",",".");
					$array_fechas['fecha1'] = date("d/m/y",strtotime($nuevo_venc1));

					$campo[] = $this->utils->importe_barcode($importe,5,2);//campo 8 Importe 2do vto
					$campo[] = date("dmy",strtotime($nuevo_venc2));//campo 9 Fecha 2do vto
					//$campo[] = date("2006y",strtotime($d->debt->amount->fecha));;//campo 9 Fecha 2do vto
					$array_importes['importe2'] = number_format($importe,"2",",",".");
					$array_fechas['fecha2'] = date("d/m/y",strtotime($nuevo_venc2));

					$campo[] = $this->utils->importe_barcode($importe,5,2);//campo 10 Importe 3er vto
					$campo[] = date("dmy",strtotime($nuevo_venc3));//campo 11 Fecha 3er vto

					$array_importes['importe3'] = number_format($importe,"2",",",".");
					$array_fechas['fecha3'] = date("d/m/y",strtotime($nuevo_venc3));
				}

				//$campo[] = date("2106y",strtotime($d->debt->amount->fecha));;//campo 11 Fecha 3er vto
				//exit();			
				$codigo_link = "";
				if($d->debt->registro_link != ""){
					$codigo_link = substr($d->debt->registro_link, 8, 15);
				}
				$data['codigo_link'] = $codigo_link;
				$data['barcode'][] = $this->generar_barcode($campo);
				$concepto = $d->debt->amount->concept->concepto.' '.$d->debt->amount->ciclo_lectivo;
				$data['detalle'][] = array("concepto" => $concepto,
											"nro_recibo" => $array_nro_recibo[1],
											"importe" => $array_importes,
											"fecha" => $array_fechas);
				$t += $d->importe;
			}
		}
		
		$this->template->set_template('recibo');
		$this->template->write_view('content', 'pagos/tutor/recibo',$data);
		$this->template->render();
	}

	function ver_recibo_tutor($str_pagosid){}

	public function generar_barcode($campo)
	{
		//I'm just using rand() function for data example
		//45636348

		$temp = "";
		foreach ($campo as $key => $value) {
			$temp .= $value;
		}

		//Genero el digito verificador
		$temp .= $this->utils->digito_verificador($temp);
		//Guardo el codigo en una variable
		$campo["codigo"] = $temp;
		//Genero el codigo de barra
		$campo["codigoBarra"] = $this->set_barcode($temp);
		//Retorno codigo y codigo de barra
		return $campo;
	}

	private function set_barcode($code){
		$widthFactor = 2;
		//$height = 23;		
		$height = 80;
		$this->barcode->load("Barcode/src/BarcodeGenerator");
		$this->barcode->load("Barcode/src/BarcodeGeneratorPNG");
		$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
		$codigo_barra = '<img style="height: '.$height.'px;
										width: 75%;
										float:left" 
							src="data:image/png;base64,' . base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128,$widthFactor,$height)) . '">';
		return $codigo_barra;
		//return $generator->getBarcode($code, $generator::TYPE_CODE_128,$widthFactor,$height);
	}

	/**
	 * Importa el archivo que viene del banco san juan para luego actualizar la cuenta corriente
	 * de los alumnos
	 */

	function import_archivo_bsj(){
		if($this->input->post()){
			//Bandera para saber si debo actualizar la db files
			$update_file = 0;
			$nombre_fichero = "files/cobros_bsj/" . basename($_FILES["file"]["name"]);
			//Si existe el archivo no hace falta subirlo solo lo voy a leer
			if (file_exists($nombre_fichero)) {
				$file["error"] = 0;
				$file["file"] = $nombre_fichero;
				$update_file = 1;
			}
			else{
				$path = "files/cobros_bsj/";
				$file = $this->upload_file($path);
				//Verifico si ha habido error en la subida del archivo
				if(!$file["error"] == 0){
					$this->session->set_flashdata('msg','<div class="error">Error al subir archivo.</div>');
				}
				else{
					//Guardo en la bd un registro del archivo subido
					$file_db = array('name' => $file["upload_data"]["file_name"],
									'path' => $file["upload_data"]["file_path"],
									'type' => $file["upload_data"]["file_type"],
									"fecha_subida" => date("Y-m-d"),
									"medio" => 1);
					$f = new File($file_db);
					if(!$f->save()){
						$this->session->set_flashdata('msg','<div class="error">Error al guardar datos del archivo.</div>');
					}
				}
			}

			//Si no hubo error procedo a leer el archivo
			if($file["error"] == 0){
				//Leo el excel y armo un arreglo con los datos
				$data = $this->leer_archivo_bsj($file["file"]);
				//Si no hubo error al leer archivo 				
				if(isset($data["datos"])){
					//Si es 1 actualizo la db files. Lo dejo para analizarlo despues
					/*if($update_file == 1){
						$conditions = array('conditions' => 'path LIKE "'.$file["file"].'"');
						$file_db = File::find($conditions);
						$file_db->update_attributes( array(
								'importe' => $value["importe"],
								'fecha_update' => $value["fecha_pago"]));
						$payment->save();						
					}*/
					//Actualizo la cuenta corriente del alummno
					$result = $this->actualizar_cuenta_corriente($data["datos"]);
					
					if($result["estado"] === FALSE){
						$this->session->set_flashdata('msg','<div class="error">Error al guardar datos</div>');
					}
					else{
						$this->session->set_flashdata('msg','<div class="success">Importacion realizada satisfactoriamente</div>');
					}
				}
				else{
					$this->session->set_flashdata('msg','<div class="error">Error al leer archivo.</div>');
				}
			}		
		}
		$data['titulo'] = "Importar archivo del Banco San Juan";
		//$this->template->set_template('reporte');
		$this->template->write_view('content', 'pagos/tutor/import_archivo_bsj',$data);
		$this->template->render();			
	}

	public function upload_file($path){
		//Config the parameters to upload the file to the server.
		//Configuramos los parametros para subir el archivo al servidor.               
		$config['upload_path'] = realpath(APPPATH.'../'.$path);         
		$config['allowed_types'] = '*';
		$config['max_size']     = '0';       

		//Load the Upload CI library
		//Cargamos la libreria CI para Subir
		$this->load->library('upload', $config);		

		if ( ! $this->upload->do_upload('file') ){             
			//Displaying Errors.
			//Mostramos los errores.
			//print_r($this->upload->display_errors());
			$data["error"] = 1;
			$data["msg"] = "Error al subir archivo.";
			return $data;
			//$this->session->set_flashdata('msg','<div class="success">Error al subir archivo.</div>');
		}
		else{
			//Uploads the excel file and read it with the PHPExcel Library.
			//Subimos el archivo de excel y devolvemos la ruta del archivo.
			$data = array('upload_data' => $this->upload->data());
			$data["error"] = 0;
			$data["file"] = $config['upload_path'].'/'.$data['upload_data']['file_name'];
			return $data;
		}
	}

	/**
	 * Recibe el nombre del archivo y devuelve un arreglo de objetos que representa 
	 * cada linea del archivo
	 */
	function leer_archivo_bsj($archivo){
		$lineas = file($archivo);
		$datos = Array();
		$cant = count($lineas);
		$i = 0;
		foreach ($lineas as $linea_num => $linea){
			if($linea_num == 0){//HEADER
				$datos["header"]["tipo_registro"] = substr($linea, 0, 6);
				$datos["header"]["codigo_bcra"] = substr($linea, 6,3);
				$datos["header"]["fecha_negocios"] = substr($linea, 9,8);
				$datos["header"]["fecha_aplanado"] = substr($linea, 17,8);
				$datos["header"]["nro_lote"] = substr($linea, 25,5);
			}
			elseif($linea_num == ($cant - 1)){
				$datos["footer"]["tipo_archivo"] = substr($linea, 0, 7);
				$datos["footer"]["cantidad_registro"] = substr($linea, 7,8);
				$datos["footer"]["importe"] = substr($linea, 15,13);
				$datos["footer"]["cantidad_trx"] = substr($linea, 28,8);
			}
			else{
				$datos["datos"][$i]["datos_mas_relleno"] = substr($linea, 0, 8);// 0 -> Datos+Relleno(3)
				$datos["datos"][$i]["codigo_bcra"] = substr($linea, 8,4);// 1 -> Código Banco SAM
				$datos["datos"][$i]["r"] = substr($linea, 12,1);// 2 -> R
				$datos["datos"][$i]["codigo_terminal_sam"] = substr($linea, 13,5);// 3 -> Código de Terminal SAM
				$datos["datos"][$i]["relleno1"] = substr($linea, 18,10);// 4 -> Relleno
				$datos["datos"][$i]["codigo_sucursal_sam"] = substr($linea, 28,4);// 5 -> Código de Sucursal SAM
				$datos["datos"][$i]["n_secuencia_on"] = substr($linea, 32,8);// 6 -> Sin Uso actualmente
				$datos["datos"][$i]["transaccion"] = substr($linea, 40,8);// 7 -> Nro. de Transacción SAM
				$datos["datos"][$i]["codigo_operacion"] = substr($linea, 48,2);// 8 -> A3 efectivo
															 //      A2 cheque v.impuestos
															 //		 A5 cheque común

				$datos["datos"][$i]["relleno2"] = substr($linea, 50,2);// 9 -> Relleno
				$datos["datos"][$i]["relleno3"] = substr($linea, 52,2);// 10 -> Relleno
				$datos["datos"][$i]["codigo_ente"] = substr($linea, 54,4);// 11 -> Código de Ente
				$datos["datos"][$i]["codigo_servicio_ident"] = substr($linea, 58,19);// 12 -> Identificación de Ticket
				$datos["datos"][$i]["importe"] = substr($linea, 77,11);// 13 -> Importe de Transacción
				$datos["datos"][$i]["relleno4"] = substr($linea, 88,11);// 14 -> Relleno
				$datos["datos"][$i]["relleno5"] = substr($linea, 99,11);// 15 -> Relleno
				$datos["datos"][$i]["moneda"] = substr($linea, 110,1);// 16 -> Moneda 0 = Pesos, 1 = Doláres
				$datos["datos"][$i]["codigo_cajero"] = substr($linea, 111,4);// 17 -> Código de Cajero 
				$datos["datos"][$i]["relleno6"] = substr($linea, 115,3);// 18 -> Relleno
				$datos["datos"][$i]["relleno7"] = substr($linea, 118,1);// 19 -> Relleno
				$datos["datos"][$i]["codigo_seguridad"] = substr($linea, 120,3);// 20 -> Código de Seguridad SAM
				$datos["datos"][$i]["relleno_primer_venc"] = substr($linea, 123,6);// 21 -> Relleno o fecha de 1er.vto
				$datos["datos"][$i]["relleno8"] = substr($linea, 129,6);// 22 -> Relleno
				$datos["datos"][$i]["banco_cheque"] = substr($linea, 135,3);// 23 -> Bco. del Cheque
				$datos["datos"][$i]["sucursal"] = substr($linea, 138,3);// 24 -> Suc. del Cheque
				$datos["datos"][$i]["cod_postal"] = substr($linea, 141,4);// 25 -> CodPostal 0000
				$datos["datos"][$i]["nro_cheque"] = substr($linea, 145,8);// 26 -> Nro. de Cheque
				$datos["datos"][$i]["nro_cuenta"] = substr($linea, 153,8);// 27 -> Nro Cuenta
				$datos["datos"][$i]["plazo"] = substr($linea, 161,3);// 28 -> Plazo Clearing del cheque
				$datos["datos"][$i]["codigo_barra"] = substr($linea, 164,60);// 29 -> Código de Barra
				$datos["datos"][$i]["fecha_pago"] = substr($linea, 224,6);// 30 -> Fecha de Pago de la Transacción
				$datos["datos"][$i]["modo_pago"] = substr($linea, 230,1);// 31 -> Modo de Pago 
				$datos["datos"][$i]["relleno9"] = substr($linea, 231,7);// 32 -> Relleno 
				$datos["datos"][$i]["relleno10"] = substr($linea, 238,9);// 33 -> Relleno 
				$datos["datos"][$i]["forma_pago"] = substr($linea, 247,2);// 34 -> Forma Pago
				$datos["datos"][$i]["relleno11"] = substr($linea, 249,4);// 35 -> Relleno 0000
				$datos["datos"][$i]["relleno12"] = substr($linea, 253,3);// 36 -> Relleno 
				$datos["datos"][$i]["autorizacion_relleno"] = substr($linea, 256,15);// 37 -> Autorización (*) o Relleno
				$datos["datos"][$i]["relleno13"] = substr($linea, 271,8);// 38 -> Relleno

				$nro_comprobante = substr($datos["datos"][$i]["codigo_barra"],4,8);
				$payment = Payment::find(array('order'=>'id ASC',
										'conditions'=>array('nro_comprobante LIKE ?','0002-'.$nro_comprobante)));
				$datos["datos"][$i]["payment"] = $payment;
				foreach ($payment->detail as $key => $value) {
					$datos["datos"][$i]["detail"][$value->payment_id] = $value;
				}
				$i++;
			}
			/*$clave = trim($datos[0]);
			$producto = trim($datos[1]);
			$precio = trim($datos[2]);*/
		}
		return $datos;
	}

	/**
	 * Procesa los datos del archivo del banco san juan para actualizar la cuenta corriente
	 * del alumno
	 * @param $datos array()
	 * @return array("estado", "pagos")
	 */

	public function actualizar_cuenta_corriente($datos){
		$columnas = array();
		$data = array();
		//Actualizar payment
		$errors = array();
		$this->db->trans_begin();//inicio la transaccion
		//Recorro cada fila donde cada fila es un pago de un recibo
		foreach ($datos as $key => $value) {
			$importe = $value["importe"];
			$importe = substr($importe, 0, 9).".".substr($importe, 9, 2);
			$conditions = array('conditions' => 'nro_comprobante LIKE "0002-'.substr($value["codigo_barra"],4,8).'"');
			$payment = Payment::find($conditions);
			$payment->update_attributes( array(
					'importe' => $importe,
					'fecha' => $value["fecha_pago"]));
			$payment->save();

			$conditions = array('conditions' => 'payment_id = '.$payment->id);
			$detail = Detail::find($conditions);
			$detail->update_attributes( array('importe' => $importe, 'estado' => 1));
			$detail->save();

			$debt = Debt::find($detail->debt_id);
			$saldo = $this->utils->calcular_saldo($debt);
			//echo $saldo."<br>";
			$p = 0; //pagado
			if($saldo == 0){
				$p = 1;				
			}
			$debt->update_attributes(array('pagado' => $p));
			$debt->save();

			if(!$detail->is_valid() || !$debt->is_valid() || !$payment->is_valid()){
				$this->db->trans_rollback();
			}
			else if($this->db->trans_status() === FALSE){//verifico si hubo algun error
				$this->db->trans_rollback();
				$errors[] = $payment; 
			}
			else{
				$this->db->trans_commit();
			}
		}

		if(count($errors) > 0){
			return array("estado"=>false,
					"pagos"=>$errors);
		}
		else{
			return array("estado" => true);
		}
	}

	public function ver_archivos_bsj(){
		$todos = File::all(array("order"=>"id ASC"));
		$this->load->library('pagination');
		if(sizeof($todos)>0){
			$config['base_url'] = site_url('pagos/tutor/ver_archivos_bsj');
			$config['total_rows'] = sizeof($todos); 
			$config['per_page'] = '20'; 
			$config['num_links'] = '1'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			$this->pagination->initialize($config);

			$files = File::all(array("order"=>"id ASC"));

			$this->table->set_heading('#Id','Nombre', 'Fecha','Ruta','');

			foreach ($files as $key => $row) {				
				$this->table->add_row(
					$row->id,
					$row->name,
					$row->fecha_subida,
					$row->path,
					"<a href='".base_url("pagos/var_archivo_bsj_detalle/".$row->id)."'>Ver detalles</a>");
			}
			$data['files'] = "<br/><div align='right'>Total de archivos ".sizeof($todos)."</div><br/>".$this->table->generate();
			$data['pagination'] = $this->pagination->create_links();			
		}
		else{
			$data['alumnos'] = "No hay resultados para mostrar";
			$data['pagination'] = '';			
		}

		$data["titulo"] = "Ver archivos subidos del Banco San Juan";
		$this->session->set_flashdata('next',site_url('pagos'));
		$this->template->write_view('content', 'pagos/tutor/ver_archivos_bsj',$data);
		$this->template->render();
	}

	public function var_archivo_bsj_detalle($id)
	{
		$file = File::find($id);
		$archivo = $file->path . $file->name;
		$data = $this->leer_archivo_bsj($archivo);
		$data['titulo'] = "Detalle del archivo del Banco San Juan";
		//$this->template->set_template('reporte');
		$this->template->write_view('content', 'pagos/tutor/import_archivo_bsj',$data);
		$this->template->render();		
	}

	/***************************************************************************/
	/////////////////TUTOR PARA PAGAR POR BANCO SAN JUAN/////////////////////////
	public function exportar_refresh($periodo=0)
    {
    	$periodo = isset($_POST["periodo"])?$_POST["periodo"]:$periodo;
		$mes_hex = dechex((int)$periodo);
		$dia = date("d");
		$archivo ='PATK1'.strtoupper($mes_hex).$dia;
        //$txt= fopen($archivo, 'w+') or die ('Problemas al crear el archivo');
        /*$condiciones = "MONTH(fecha) = ?";
        $valores["periodo"] = $periodo;
        $conditions = array($condiciones,$periodo);
        $joins = ' JOIN amounts ON amounts.id = debts.amount_id ';
        $deudas = Debt::all(array("conditions" => $conditions, "join" => $joins));*/
		$sql = 'SELECT debts.importe, 
						debts.id debt_id, 
						concepts.concepto, 
						students.*, 
						amounts.fecha, 
						amounts.ciclo_lectivo 
				FROM debts
				JOIN students ON students.id = debts.student_id AND students.baja = 0
				JOIN amounts ON amounts.id = debts.amount_id
				JOIN concepts ON concepts.id = amounts.concept_id
				JOIN courses ON courses.id = amounts.course_id
				LEFT JOIN families ON families.student_id = students.id
				LEFT JOIN tutors ON tutors.id = families.tutor_id
				WHERE MONTH(amounts.fecha) = '.$periodo.' 
					AND YEAR(amounts.fecha) = 2018
					AND debts.pagado = 0';
        $deudas = $this->db->query($sql);
        //print_r($conditions);
        //var_dump($this->db->last_query());
        $todos = $deudas->num_rows();
		if($deudas->num_rows() > 0){
			$config['base_url'] = site_url('pagos/exportar_refresh');
			$config['total_rows'] = sizeof($todos); 
			$config['per_page'] = '20'; 
			$config['num_links'] = '1'; 
			$config['first_link'] = '&larr; primero';
			$config['last_link'] = 'último &rarr;';
			//$this->pagination->initialize($config);

			$this->load->library('pagination', $config);			

			$this->table->set_heading('#Id','Alumno', 'Fecha','Concepto','Importe','');

			foreach ($deudas->result() as $key => $row) {				
				$this->table->add_row(
					$row->debt_id,
					$row->apellido.' '.$row->nombre,
					$row->fecha,
					$row->concepto.' '.$row->ciclo_lectivo,
					$row->importe
					//"<a href='".base_url("pagos/var_archivo_bsj_detalle/".$row->id)."'>Ver detalles</a>"
				);
			}
			$data['files'] = "<br/><div align='right'>Total de registros ".sizeof($todos)."</div><br/>".$this->table->generate();
			$data['pagination'] = $this->pagination->create_links();			
		}
		else{
			$data['deudas'] = "No hay resultados para mostrar";
			$data['pagination'] = '';			
		}
        $data["title"] = "Ver Deudas del Periodo seleccionado";
		$this->template->write_view('content', 'deudas/lista_deudas',$data);
		$this->template->render();    
	}

	/***************************************************************************/
	/*********************************PAGOS LINK********************************/
	public function ver_codigo_link($deuda_id){
		$deuda = Debt::find($deuda_id);
		echo substr($deuda->registro_link, 8,15);
	}

	/**
	 * Importa el archivo extract que viene de link pagos para luego actualizar la cuenta corriente
	 * de los alumnos
	 */
	function import_archivo_link(){
		if($this->input->post()){
			//Bandera para saber si debo actualizar la db files
			$update_file = 0;
			$file_id = 0;
			$nombre_fichero = "files/link/" . basename($_FILES["file"]["name"]);
			//Si existe el archivo no hace falta subirlo solo lo voy a leer
			if (file_exists($nombre_fichero)) {
				$file["error"] = 1;
				$file["msg"] = "Archivo duplicado. No se puede subir un archivo existente.";
				$file["file"] = $nombre_fichero;
				$update_file = 1;
			}
			else{
				$path = "files/link/";
				$file = $this->upload_file($path);
				//Verifico si ha habido error en la subida del archivo
				if(!$file["error"] == 0){
					$this->session->set_flashdata('msg','<div class="error">Error al subir archivo.</div>');
				}
				else{
					//Guardo en la bd un registro del archivo subido
					$file_db = array('name' => $file["upload_data"]["file_name"],
									'path' => $file["upload_data"]["file_path"],
									'type' => $file["upload_data"]["file_type"],
									"fecha_subida" => date("Y-m-d"),
									"medio" => 2);
					$f = new File($file_db);
					if(!$f->save()){
						$this->session->set_flashdata('msg','<div class="error">Error al guardar datos del archivo.</div>');
					}
					else{
						$file_id = $f->id;
					}
				}
			}

			//Si no hubo error procedo a leer el archivo
			if($file["error"] == 0){
				//Leo el excel y armo un arreglo con los datos
				$data = $this->leer_archivo_link($file["file"]);
				//Si no hubo error al leer archivo 				
				if(isset($data["datos"])){
					//Si es 1 actualizo la db files. Lo dejo para analizarlo despues
					/*if($update_file == 1){
						$conditions = array('conditions' => 'path LIKE "'.$file["file"].'"');
						$file_db = File::find($conditions);
						$file_db->update_attributes( array(
								'importe' => $value["importe"],
								'fecha_update' => $value["fecha_pago"]));
						$payment->save();						
					}*/
					//Actualizo la cuenta corriente del alummno
					$result = $this->actualizar_cuenta_corriente_link($data["datos"],$file_id);
					
					if($result["estado"] === FALSE){
						$this->session->set_flashdata('msg','<div class="error">'.$result['msg'].'</div>');
					}
					else{
						$this->session->set_flashdata('msg','<div class="success">Importacion realizada satisfactoriamente</div>');
					}
				}
				else{
					$this->session->set_flashdata('msg','<div class="error">No hay pagos en el archivo para guardar.</div>');
					redirect('pagos/import_archivo_link');
				}
			}
			else{
				$this->session->set_flashdata('msg','<div class="error">'.$file['msg'].'</div>');
				redirect('pagos/import_archivo_link');				
			}
			$data['titulo'] = "Importar archivo de Pago Link";
			//$this->template->set_template('reporte');
			$this->template->write_view('content', 'pagos/tutor/import_archivo_link',$data);
			$this->template->render();				
		}
		else{
			$data['titulo'] = "Importar archivo de Pago Link";
			//$this->template->set_template('reporte');
			$this->template->write_view('content', 'pagos/tutor/import_archivo_link',$data);
			$this->template->render();	
		}		
	}


	/**
	 * Recibe el nombre del archivo y devuelve un arreglo de objetos que representa 
	 * cada linea del archivo
	 */
	function leer_archivo_link($archivo){
		$lineas = file($archivo);
		$datos = Array();
		$cant = count($lineas);
		$i = 0;
		foreach ($lineas as $linea_num => $linea){
			if($linea_num == 0){//HEADER
				$datos["header"]["tipo_registro"] = substr($linea, 0, 1);
				$datos["header"]["codigo_ente"] = substr($linea, 1,3);
				$datos["header"]["fecha_proceso"] = substr($linea, 4,8);
				$datos["header"]["filer"] = substr($linea, 12,86);
			}
			elseif($linea_num == ($cant - 1)){//FOOTER
				$datos["footer"]["tipo_registro"] = substr($linea, 0, 1);
				$datos["footer"]["cantidad_registro"] = substr($linea, 1,6);
				$datos["footer"]["importe"] = substr($linea, 7,16);
				$datos["footer"]["filer"] = substr($linea, 23,75);
			}
			else{
				$datos["datos"][$i]["tipo_registro"] = substr($linea, 0, 1);// 0 -> tipo de registro
				$datos["datos"][$i]["id_deuda"] = substr($linea, 1,5);// 1 -> Identificador de deuda
				$datos["datos"][$i]["id_concepto"] = substr($linea, 6,3);// 2 -> Identificador de concepto
				$datos["datos"][$i]["id_usuario"] = substr($linea, 9,15);// 3 -> Identificador de usuario
				$datos["datos"][$i]["importe_pagado"] = substr($linea, 28,12);// 4 -> Importe pagado
				$datos["datos"][$i]["fecha_pago"] = substr($linea, 40,8);// 5 -> Fecha de pago
				$datos["datos"][$i]["discrecional"] = substr($linea, 48,50);// 6 -> Discrecional
				$i++;
			}
			/*$clave = trim($datos[0]);
			$producto = trim($datos[1]);
			$precio = trim($datos[2]);*/
		}
		return $datos;
	}

	/**
	 * Procesa los datos del archivo extract de pagos link para actualizar la cuenta corriente
	 * del alumno
	 * @param $datos array()
	 * @return array("estado", "pagos")
	 */
	public function actualizar_cuenta_corriente_link($datos,$file_id){
		$columnas = array();
		$data = array();
		//Actualizar payment
		$errors = array();
		$string_pagosid = "";
		$ban = true;
		$pagoid = 0;
		$debtid = 0;
		$error = "";

		try{
			$this->db->trans_begin();//inicio la transaccion
			$user = User::find(
				array(
					'select' => 'id',
					'conditions' => array('usuario = ?',"pago_link")
				)
			);
			//Recorro cada fila donde cada fila es un pago de un recibo
			foreach ($datos as $key => $value) {
				$importe = $value["importe_pagado"];
				$importe = substr($importe, 0, 10).".".substr($importe, 10, 2);
				$nro = Payment::last(array('order'=>'id ASC',
													'conditions'=>array('nro_comprobante LIKE ? AND YEAR(fecha) > ?','0003-%','2017')));
				$insert['nro_comprobante'] = '0003-00000001';
				$nuevo = explode('-',$insert['nro_comprobante']);
				if($nro){
					$insert['nro_comprobante'] = $nro->pto_venta.'-'.str_pad(($nro->nro_recibo + 1), 8, '0', STR_PAD_LEFT);
					$nuevo = explode('-',$insert['nro_comprobante']);
				}
				$insert['moneda']=isset($insert['moneda'])?$insert['moneda']:1; //Pesos
				$insert['ptype_id'] = isset($insert['ptype'])?$insert['ptype']:array(18); //Pesos
				$insert['user_id'] = $user->id;
				$insert['fecha'] = $value["fecha_pago"];
				$insert['importe'] = $importe;
				$insert['related_payments'] = "";
				$insert['nro_recibo'] = $nuevo[1];
				$insert['pto_venta'] = $nuevo[0];
				$condiciones = "debts.registro_link LIKE ?";
				$valores["codigo_link"] = $value["id_deuda"].$value["id_concepto"].$value["id_usuario"].'%';
				$conditions = array_merge(array($condiciones), $valores);
				$debt = Debt::find(array('conditions' => $conditions));
				$debtid = $debt->id;
				$debt->update_attributes(array('estado_pago_link' => 2, 
					'pagado' => 1, 
					'importe' => $insert['importe']));
				if($debt->is_valid()){
					$debt->save();					
				}
				else{
					$ban = false;
					$error .= " Error al actualizar el estado de la Deuda.";
				}

				$insert['student_id'] = $debt->student_id;
				$insert['observaciones'] = "Pagos link";

				$payment = array(
						'student_id'=>$insert['student_id'],
						'user_id'=>$insert['user_id'],
						'nro_comprobante'=>$insert['nro_comprobante'],
						'fecha'=>$insert['fecha'],
						'moneda'=>$insert['moneda'],
						'importe'=>$insert['importe'],
						'observaciones'=>$insert['observaciones'],
						'related_payments'=>$insert['related_payments'],
						'nro_recibo'=>$insert['nro_recibo'],
						'pto_venta'=>$insert['pto_venta']);
				$pay = new Payment($payment);
				if($pay->is_valid()){
					$pay->save();
					$pay = Payment::find(array('nro_comprobante'=>$insert['nro_comprobante']));
					$pagoid = $pay->id;
				}
				else{
					$ban = false;
					$error .= " Error al guardar Pago.";
				}
				//Armo la cadena con IDs de pagos
				$string_pagosid .= $pagoid."-";
				$insert['pago'][$debt->id] = $pagoid;		
				$detalle = array('debt_id' => $debt->id, 
					'payment_id' => $pagoid , 
					'importe' => $importe, 
					'estado' => 1,
					'file_extract_id' => $file_id);
				$d = new Detail($detalle);

				if($d->is_valid()){
					$d->save();
				}
				else{
					$ban = false;
					$error .= " Error al guardar Detail.";
				}

				//Forma de pagos
				foreach($insert['ptype_id'] as $j=>$v){
					$det = array(
							'payment_id'=>$pagoid,
							'ptype_id'=>$v,
							'importe'=>$insert['importe']
							);
									
					$pdetail = new Pdetail($det);
					if( $pdetail->is_valid( ) ){
						$pdetail->save();
					}
					else{
						$ban = false;
						$error .= " Error al guardar Pdetail.";
					}
				}			
			}
			//saco el ultimo caracter al string de id pagos
			$string_pagosid = trim($string_pagosid, '-');
			//Creo un arreglo para recorrer los ids e ir actualizando uno por uno
			$array_pagos = explode("-", $string_pagosid);
			foreach ($array_pagos as $key => $value) {
				$conditions = array('conditions' => 'id = '.$value);
				$pago_db = Payment::find($conditions);				
				$pago_db->update_attributes(array('related_payments' => $string_pagosid));
				$pago_db->save();
			}			

			if($ban === false){
				$this->db->trans_rollback();
				$result['msg'] = "Error al guardar en alguna entidad";
				$result['estado'] = false;
			}
			else{
				$this->db->trans_commit();
				$result['msg'] = "Se guardo correctamente";
				$result['estado'] = false;
			}
		}
		catch (\Exception $e){
			//print_r($e);
			$this->db->trans_rollback();
			$result['msg'] = $e->getMessage()." Id pago: ".$pagoid." Id deuda: ".$debtid.$error;
			$result['estado'] = false;
		}
		return $result;
	}	
}
