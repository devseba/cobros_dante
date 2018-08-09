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
				anchor('files/anular_archivo_link/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
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
		
		$joins = "JOIN dfiles ON files.id = dfiles.file_id
				  JOIN debts ON dfiles.debt_id = debts.id";
		$conditions = array_merge(array($condiciones), $valores);
		$query = array('joins' => $joins, 'conditions' => $conditions);
		print_r($query);
		$files = File::all($query);
		
		$this->table->set_heading('Orden','Banco','Acciones');
		foreach($files as $pa)
		{
			$this->table->add_row(
				$pa->id,
				$pa->name,
				anchor('files/anular_archivo_link/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
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
