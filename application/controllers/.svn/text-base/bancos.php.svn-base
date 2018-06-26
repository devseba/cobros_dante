<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bancos extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
	}

	public function index($offset = 0)
	{
		$this->load->library('pagination');
		$config['base_url'] = site_url('bancos/index');
		$config['total_rows'] = Bank::count();
		$config['per_page'] = '20'; 
		$config['num_links'] = '1'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->pagination->initialize($config);
		
		$bancos = Bank::find('all', array('limit' => $config['per_page'], 'offset' => $offset));
		
		$this->table->set_heading('Orden','Banco','Acciones');
		foreach($bancos as $pa)
		{
			$this->table->add_row(
				$pa->id,
				$pa->nombre,
				anchor('bancos/editar/'.$pa->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('bancos/eliminar/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		
		$data['bancos'] = $this->table->generate();
		$data['pagination'] = $this->pagination->create_links();
		
		$this->template->write_view('content', 'bancos/index',$data);
		$this->template->render();
	}
	
	public function filters()
	{
		$string = '%'.$this->input->post('nombre').'%';
		$bancos = array();
		
		$bancos = Bank::find('all', array('conditions' => array('nombre LIKE ?', $string)));
		
		$this->table->set_heading('Orden','Banco','Acciones');
		foreach($bancos as $pa)
		{
			$this->table->add_row(
				$pa->id,
				$pa->nombre,
				anchor('bancos/editar/'.$pa->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('bancos/eliminar/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		echo $this->table->generate();
		
		$config['base_url'] = site_url('bancos/index');
		$config['total_rows'] = Bank::count();
		$config['per_page'] = '20'; 
		$config['num_links'] = '2'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->load->library('pagination', $config);
		echo '<div class="pagination">';
		echo $this->pagination->create_links();
		echo '</div>';
	}
	
	public function agregar()
	{				
		$data = array();
		if ( $_POST )
		{
			$banco = new Bank( 
				elements( array('nombre'), $_POST )
			);
			if( $banco->is_valid( ) )
			{
				$banco->save();
				$this->session->set_flashdata( 'msg','<div class="success">El banco se guardó correctamente.</div>' );
				redirect('bancos');
			}
			else
			{
				$data['errors'] = $banco->errors;
			}
		}
		
		$data['titulo'] = "Agregar banco";
		$data['action'] = "bancos/agregar";
		
		$this->template->write_view('content', 'bancos/agregar',$data);
		$this->template->render();
	}
	
	public function editar( $id )
	{	
		if(!$id)
		{
			$this->session->set_flashdata( 'msg','<div class="error">El banco solicitado no existe.</div>' );
			redirect('bancos');
		}
		elseif ( $_POST )
		{
			$this->load->helper('date');
			$this->load->library('Utils');
					
			$banco = Bank::find($id);
			
			$banco->update_attributes(elements( array('nombre' ), $_POST ));
			
			if( $banco->is_valid( ) )
			{
				if($banco->save())
				{
					$this->session->set_flashdata( 'msg','<div class="success">El banco se guardó correctamente.</div>' );
					redirect('bancos');
				}
				else
				{
					$this->session->set_flashdata( 'msg','<div class="error">Hubo un error al guardar los datos.</div>' );
					redirect('bancos/editar/'.$id);
				}
			}
			else
			{
				$data['errors'] = $banco->errors;
			}
		}
		else $data['a'] = Bank::find($id);
		
		$data['titulo'] = "Editar banco";
		$data['action'] = "bancos/editar/".$id;
		
		$this->template->write_view('content', 'bancos/agregar',$data);
		$this->template->render();
	}
	
	function eliminar($id)
	{
		$a = Bank::find($id);
		$a->delete();
		$this->session->set_flashdata('msg','<div class="success">El banco fué eliminado correctamente.</div>');
		redirect('bancos');
	}
}
