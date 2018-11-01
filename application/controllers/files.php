<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Files extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
	}

	public function index($offset = 0)
	{
	}

	public function listar_archivos_link($offset = 0){
		$this->load->library('pagination');
		$config['base_url'] = site_url('files/listar_archivos_link');
		
		$config['per_page'] = '20'; 
		$config['num_links'] = '1'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->pagination->initialize($config);
		$conditions = array('medio = ? AND name LIKE ?',2,'PDKT%');
		$files = File::find('all', array('limit' => $config['per_page'], 'offset' => $offset, 'conditions' => $conditions));

		$config['total_rows'] = count($files);
		//var_dump($files);
		//	exit();
		
		$this->table->set_heading('Orden','Archivo','Acciones');
		foreach($files as $pa)
		{
			$this->table->add_row(
				$pa->id,
				$pa->name,
				($pa->anulado == 0)?anchor('files/anular_archivo_link/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"'):"Anulado"
			);
		}
		
		$data['bancos'] = $this->table->generate();
		$data['pagination'] = $this->pagination->create_links();
		
		$this->template->write_view('content', 'files/listar_archivos_link',$data);
		$this->template->render();
	}

	public function filters($offset = 0)
	{
		$this->load->library('pagination');
		$condiciones = "name LIKE ?";
		$valores[] = '%'.$this->input->post('nombre').'%';
		$debt_id = $this->input->post('id_deuda');
		if($debt_id != ''){
			$condiciones .= " AND debts.registro_link LIKE ?";
			$valores[] = $debt_id.'%';
		}

		$condiciones .= " AND debts.pagado <> ?";
		$valores[] = 1;

		$condiciones .= " AND dfiles.anulado = ?";
		$valores[] = 0;				
		
		$joins = "JOIN dfiles ON files.id = dfiles.file_id
				  JOIN debts ON dfiles.debt_id = debts.id
				  JOIN students ON debts.student_id = students.id
				  JOIN amounts ON debts.amount_id = amounts.id
				  JOIN concepts ON amounts.concept_id = concepts.id";
		$select = "files.*,dfiles.id dfile_id, students.apellido, students.nombre,concepts.concepto, amounts.importe";
		$conditions = array_merge(array($condiciones), $valores);
		$query = array('select' => $select,'joins' => $joins, 'conditions' => $conditions);
		$files = File::all($query);
		$i = 1;
		print_r($conditions);
		$this->table->set_heading('Orden','#Id','Archivo','Alumno','Concepto','Importe','Acciones');
		foreach($files as $pa)
		{
			$this->table->add_row(
				$i,
				$pa->dfile_id,
				$pa->name,
				$pa->apellido.' '.$pa->nombre,
				$pa->concepto,
				$pa->importe,
				anchor('files/anular_archivo_link/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
			$i++;
		}
		echo $this->table->generate();
		$config['base_url'] = site_url('files/listar_archivos_link');
		
		$config['per_page'] = '20'; 
		$config['num_links'] = '1'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->pagination->initialize($config);
		$conditions = array('medio = ? AND name LIKE ?',2,'PDKT%');
		$files = File::find('all', array('limit' => $config['per_page'], 'offset' => $offset, 'conditions' => $conditions));

		$config['total_rows'] = count($files);
		$this->load->library('pagination', $config);
		echo '<div class="pagination">';
		echo $this->pagination->create_links();
		echo '</div>';
	}

	public function anular_por_filtro(){
		$condiciones = "name LIKE ?";
		$valores[] = '%'.$this->input->post('nombre').'%';
		$debt_id = $this->input->post('id_deuda');
		$condiciones .= " AND debts.registro_link LIKE ?";
		$valores[] = $debt_id.'%';

		$condiciones .= " AND debts.pagado <> ?";
		$valores[] = 1;

		$condiciones .= " AND dfiles.anulado = ?";
		$valores[] = 0;		
		
		$joins = "JOIN dfiles ON files.id = dfiles.file_id
				  JOIN debts ON dfiles.debt_id = debts.id";
		$select = "files.*, dfiles.id dfile_id, debts.id debt_id";
		$conditions = array_merge(array($condiciones), $valores);
		$query = array('select' => $select,'joins' => $joins, 'conditions' => $conditions);
		
		$files = File::all($query);
		$ban = true;
		$i = Dfile::connection();
		try{
			$i->transaction();
			foreach($files as $pa)
			{
				//$dfile = Dfile::find('all', array('conditions' => array("dfiles.id = ?",$pa->dfile_id)));
				//$dfile->update_attributes( array('anulado' => 1));
				$datos = array('anulado' => 1,'fecha_anulado' => date('Y-m-d H:i:s'));
				$this->db->update("dfiles",$datos,"id = ".$pa->dfile_id);
				$datos2 = array("estado_pago_link" => 3);
				$this->db->update("debts", $datos, "id = ".$pa->debt_id);
				//$dfile->save();
			}
		
			//$this->session->set_flashdata('msg','<div class="success">El archivo se anulo correctamente.</div>');
			$i->commit();			
		}	
		catch (\Exception $e){
			$ban = false;
			print_r($e);
			$i->rollback();
			//$this->session->set_flashdata('msg','<div class="error">Hubo un error al anular los archivos.</div>');
		}
		if(!$ban){			
			echo "Errorrrrrrrrrrr";
		}
		else{
			echo "Bieeeeeen";			
		}			
	}

	public function anular_archivo_link($file_id){
		$conditions = array('id = ?',$file_id);
		$df = Dfile::all(array('conditions'=>$conditions));
		$datos = array("anulado" => 1);
		$this->db->update("files",$datos,"id = ".$file_id);
		$this->db->update("dfiles",$datos,"file_id = ".$file_id);
		foreach ($df as $key => $value) {
			$datos = array("estado_pago_link" => 0,
							"registro_link" => NULL,
							"pago_link" => 0,
							"file_id" => 0);
			$this->db->update("debts",$datos,"id = ".$value->debt_id." AND pagado <> 1");
			echo "=";
		}

		$this->listar_archivos_link();	
	}

	public function actualizar_deudas_anuladas(){
		$df = Dfile::all(array('conditions'=>array('anulado = ?',1)));
		echo "Inicio <br> ||";
		foreach ($df as $key => $value) {
			$datos = array("estado_pago_link" => 0,
							"registro_link" => NULL,
							"pago_link" => 0,
							"file_id" => 0);
			$this->db->update("debts",$datos,"id = ".$value->debt_id." AND pagado <> 1");
			echo "=";
		}
		echo " >> Fin";
	}

	public function llenar_dfiles(){
		$conditions = array("debts.pago_link = ?",1);
		$debts = Debt::all(array("conditions" => $conditions));
		foreach ($debts as $key => $d) {
			print_r($d->file_id);
			$datos = array(
				'debt_id' => $d->id,
				'file_id' => $d->file_id
			);
			$this->db->insert("dfiles",$datos);
		}
	}	
}
