<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cajas extends CI_Controller {

	function __construct(){
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
	}

	public function index($offset = 0){
		$condiciones = 'anulado = ? AND fecha BETWEEN ? AND DATE_ADD(?, INTERVAL 86399 SECOND)';
		$valores[] = 0;
		//$valores[] = 1; ptype_id
		$valores[] = date("Y-m-d");
		$valores[] = date("Y-m-d");
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$pagos = Pdetail::find('all', array('joins'=>array('payment'),'conditions' => $conditions));
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Importe','Usuario');
		
		$total_diario=0;
		 
		foreach($pagos as $pago)
		{
			$total_diario+=$pago->importe;
			
			$this->table->add_row(
				$pago->payment->fecha->format('d/m/Y'),
				$pago->payment->nro_comprobante,
				$pago->payment->student->apellido.' '.$pago->payment->student->nombre,
				'$'.$pago->importe,
				$pago->payment->user->apellido.' '.$pago->payment->user->nombre
			);
		}
		
		$data['pagos'] = $this->table->generate();
		$data['total_diario'] = '<h3 align="right"><strong><em>El total cobrado en el periodo es: $'.$total_diario.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</em></strong></h3>';
		$data['usuarios'] = User::all();
				
		$this->template->write_view('content', 'cajas/index',$data);
		$this->template->render();
	}
	
	public function reporte($offset = 0){
		$this->load->helpers('date');
		$usuario = $this->input->post('user_id');
		$fecha_desde = $this->input->post('fecha_desde');
		$fecha_hasta = $this->input->post('fecha_hasta');
		
		$condiciones = 'anulado = ? AND ptype_id = ?';
		$valores[] = $this->input->post('anulado');
		$valores[] = 1;
		
		if($usuario > 0){
			$condiciones .= " AND user_id = ?";
			$valores[] = $usuario;
		}
		
		if($fecha_desde != ''){
			$fecha_desde = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
					
			if($fecha_hasta != ''){
				$fecha_hasta = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND fecha BETWEEN ? AND DATE_ADD(?, INTERVAL 86399 SECOND)";
			$valores[] = $fecha_desde;
			$valores[] = $fecha_hasta;			
		
		}
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$pagos = Pdetail::all(array('joins'=>array('payment'),'conditions' => $conditions) );
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Importe','Usuario');
		
		$total_diario=0;
		foreach($pagos as $pago)
		{
			$total_diario+=$pago->importe;
			$this->table->add_row(
				$pago->payment->fecha->format('d/m/Y'),
				$pago->payment->nro_comprobante,
				$pago->payment->student->apellido.' '.$pago->payment->student->nombre,
				'$'.$pago->importe,
				$pago->payment->user->apellido.' '.$pago->payment->user->nombre
			);
		}
		
		$data['titulo'] = "Reporte de cajas";
		$data['reporte'] = $this->table->generate();
		$data['total'] = $total_diario;
		
		$this->session->set_flashdata('next',$this->agent->referrer());
		$this->template->set_template('reporte');
		$this->template->write_view('content', 'pagos/reporte',$data);
		$this->template->render();		
	}
	
	public function filters($offset = 0){
		$this->load->helpers('date');
		$usuario = $this->input->post('user_id');
		$fecha_desde = $this->input->post('fecha_desde');
		$fecha_hasta = $this->input->post('fecha_hasta');
		
		$condiciones = 'anulado = ?';
		$valores[] = $this->input->post('anulado');
		//$valores[] = 1;ptype_id
		
		if($usuario > 0){
			$condiciones .= " AND user_id = ?";
			$valores[] = $usuario;
		}
		
		if($fecha_desde != ''){
			$fecha_desde = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
					
			if($fecha_hasta != ''){
				$fecha_hasta = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND fecha BETWEEN ? AND DATE_ADD(?, INTERVAL 86399 SECOND)";
			$valores[] = $fecha_desde;
			$valores[] = $fecha_hasta;			
		
		}
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$pagos = Pdetail::all(array('joins'=>array('payment'),'conditions' => $conditions) );
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Importe','Usuario');
		
		$total_diario=0;
		foreach($pagos as $pago)
		{
			$total_diario+=$pago->importe;
			$this->table->add_row(
				$pago->payment->fecha->format('d/m/Y'),
				$pago->payment->nro_comprobante,
				$pago->payment->student->apellido.' '.$pago->payment->student->nombre,
				'$'.$pago->importe,
				$pago->payment->user->apellido.' '.$pago->payment->user->nombre
			);
		}
		
		$this->session->set_flashdata('next',base_url('cajas'));
		echo $this->table->generate();
		echo '<h3 align="right"><strong><em>El total cobrado en el periodo es: $'.$total_diario.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</em></strong></h3>';
	}
	
	function export(){
		$this->load->helper('excel');
		$this->load->helpers('date');
		$usuario = $this->input->post('user_id');
		$fecha_desde = $this->input->post('fecha_desde');
		$fecha_hasta = $this->input->post('fecha_hasta');
		
		$condiciones = 'anulado = ?';
		$valores[] = $this->input->post('anulado');
		//$valores[] = 1;
		
		if($usuario > 0){
			$condiciones .= " AND user_id = ?";
			$valores[] = $usuario;
		}
		
		if($fecha_desde != ''){
			$fecha_desde = mdate('%Y-%m-%d' ,normal_to_unix($fecha_desde));
					
			if($fecha_hasta != ''){
				$fecha_hasta = mdate('%Y-%m-%d' ,normal_to_unix($fecha_hasta));
			}
			else
				$fecha_hasta = date('Y-m-d');
			
			$condiciones .= " AND fecha BETWEEN ? AND DATE_ADD(?, INTERVAL 86399 SECOND)";
			$valores[] = $fecha_desde;
			$valores[] = $fecha_hasta;			
		
		}
		
		$conditions = array_merge(array($condiciones), $valores);
		
		$pagos = Pdetail::all(array('joins'=>array('payment'),'conditions' => $conditions) );
		
		$this->table->set_heading('Fecha','Nro Comprobante', 'Estudiante', 'Importe','Usuario');
		
		$total_diario=0;
	
		$res = array();
		$i = 0;
		foreach($pagos as $pago){
			$total_diario+=$pago->importe;
			$res[$i]['Fecha'] = $pago->payment->fecha->format('d/m/Y');
			$res[$i]['Comprobante'] = $pago->payment->nro_comprobante;
			$res[$i]['Estudiante'] = $pago->payment->student->apellido.', '.$pago->payment->student->nombre;
			$res[$i]['Usuario'] = $pago->payment->user->apellido.' '.$pago->payment->user->nombre;
			$res[$i++]['Importe'] = $pago->importe;
		}
		
		if($fecha_desde!==$fecha_hasta){
			$fecha = $fecha_desde." - ".$fecha_hasta;
		}
		else{
			$fecha = "";
			}
		
		to_excel($res, "\t Cobros Realizados en efectivo \t".$fecha);
		$hasta = $i+6-1;
		//echo "\n\t\t\tTotal:\t=SUMA(E5:E$hasta)"; 
		echo "\n\t\t\tTotal:\t".$total_diario;
	}
	
	/***********************************************************************************/
	public function resumen($offset = 0)
	{
		$desde = date('Y-m-d');
		$hasta = date('Y-m-d');
		
		$sql = "SELECT ptypes.id, tipo, IF(importe IS NULL, 0, importe) importe 
				FROM ptypes
				LEFT JOIN (	SELECT ptype_id, SUM( pdetails.importe ) as importe
							FROM `pdetails`
							JOIN payments on payments.id = pdetails.payment_id
							WHERE  anulado = 0 
							AND fecha BETWEEN '$desde' AND DATE_ADD('$hasta', INTERVAL 86399 SECOND)
							GROUP BY ptype_id) temp ON temp.ptype_id = ptypes.id ";
		
		$pagos = Ptype::find_by_sql($sql);
		
		$this->table->set_heading(' ', 'Forma de Pago', 'Importe $');
		
		$total_diario=0;
		 
		foreach($pagos as $pago)
		{
			$total_diario+=$pago->importe;
			
			$this->table->add_row(
				$pago->id,
				$pago->tipo,
				$pago->importe
			);
		}
		$this->session->set_flashdata('next',base_url('cajas/resumen'));
		$data['pagos'] = $this->table->generate();
		$data['total_diario'] = '<h3 align="right"><strong><em>El total cobrado en el periodo es: $'.$total_diario.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</em></strong></h3>';
		$data['usuarios'] = User::all();
			
		$this->template->write_view('content', 'cajas/resumen',$data);
		$this->template->render();
	}
	
	public function resumen_filters($offset = 0)
	{
		$this->load->helpers('date');
		$usuario = $this->input->post('user_id');
		$desde = ($this->input->post('fecha_desde'))?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_desde'))):date('Y-m-d');
		$hasta = ($this->input->post('fecha_hasta'))?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_hasta'))):date('Y-m-d');		
		$anulado = ($this->input->post('anulado'))?$this->input->post('anulado'):0;
		
		$sql = "SELECT ptypes.id, tipo, IF(importe IS NULL, 0, importe) importe 
				FROM ptypes
				LEFT JOIN (	SELECT ptype_id, SUM( pdetails.importe ) as importe
							FROM `pdetails`
							JOIN payments on payments.id = pdetails.payment_id
							WHERE  anulado = $anulado 
							AND fecha BETWEEN '$desde' AND DATE_ADD('$hasta', INTERVAL 86399 SECOND)";
		if($usuario > 0){
			$sql .= " AND user_id = $usuario ";
		}
		
		$sql .= " GROUP BY ptype_id) temp ON temp.ptype_id = ptypes.id ";
		
		$pagos = Ptype::find_by_sql($sql);
		
		$this->table->set_heading(' ', 'Forma de Pago', 'Importe $');
		
		$total_diario=0;
		 
		foreach($pagos as $pago)
		{
			$total_diario+=$pago->importe;
			
			$this->table->add_row(
				$pago->id,
				$pago->tipo,
				$pago->importe
			);
		}
		
		$this->session->set_flashdata('next',base_url('cajas/resumen'));
		echo $this->table->generate();
		echo '<h3 align="right"><strong><em>El total cobrado en el periodo es: $'.$total_diario.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</em></strong></h3>';
	}
	
	public function reporte_resumen()
	{
		$this->load->helpers('date');
		$usuario = $this->input->post('user_id');
		$desde = ($this->input->post('fecha_desde'))?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_desde'))):date('Y-m-d');
		$hasta = ($this->input->post('fecha_hasta'))?mdate('%Y-%m-%d' ,normal_to_unix($this->input->post('fecha_hasta'))):date('Y-m-d');		
		$anulado = ($this->input->post('anulado'))?$this->input->post('anulado'):0;
		
		$sql = "SELECT ptypes.id, tipo, IF(importe IS NULL, 0, importe) importe 
				FROM ptypes
				LEFT JOIN (	SELECT ptype_id, SUM( pdetails.importe ) as importe
							FROM `pdetails`
							JOIN payments on payments.id = pdetails.payment_id
							WHERE  anulado = $anulado 
							AND fecha BETWEEN '$desde' AND DATE_ADD('$hasta', INTERVAL 86399 SECOND)";
		if($usuario > 0){
			$sql .= " AND user_id = $usuario ";
		}
		
		$sql .= " GROUP BY ptype_id) temp ON temp.ptype_id = ptypes.id ";
		
		$pagos = Ptype::find_by_sql($sql);
		
		$this->table->set_heading(' ', 'Forma de Pago', 'Importe $');
		
		$total_diario=0;
		 
		foreach($pagos as $pago)
		{
			$total_diario+=$pago->importe;
			
			$this->table->add_row(
				$pago->id,
				$pago->tipo,
				$pago->importe
			);
		}
		
		$data['titulo'] = "Reporte resumen de caja ";
		if($desde !== $hasta)
			$data['fecha'] = mysql_to_human($desde).'-'.mysql_to_human($hasta);
		else
			$data['fecha'] = mysql_to_human($desde);
			
		if($usuario >0){
			$u = User::find($usuario);
			$data['usuario'] = $u->apellido.', '.$u->nombre;
			}
			
		$data['reporte'] = $this->table->generate();
		$data['total'] = $total_diario;
		
		$this->template->set_template('reporte');
		$this->template->write_view('content', 'pagos/reporte',$data);
		$this->template->render();
	}
	
}
