<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tarjetas extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
	}

	public function index($offset = 0)
	{
		$this->load->library('pagination');
		$config['base_url'] = site_url('tarjetas/index');
		$config['total_rows'] = Creditcard::count();
		$config['per_page'] = '20'; 
		$config['num_links'] = '1'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->pagination->initialize($config);
		
		$tarjetas = Creditcard::find('all', array('limit' => $config['per_page'], 'offset' => $offset));
		
		$this->table->set_heading('Orden','Tarjeta','Acciones');
		foreach($tarjetas as $pa)
		{
			$this->table->add_row(
				$pa->id,
				$pa->nombre,
				anchor('tarjetas/editar/'.$pa->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('tarjetas/eliminar/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		
		$data['tarjetas'] = $this->table->generate();
		$data['pagination'] = $this->pagination->create_links();
		
		$this->template->write_view('content', 'tarjetas/index',$data);
		$this->template->render();
	}
	
	public function filters()
	{
		$string = '%'.$this->input->post('nombre').'%';
		$tarjetas = array();
		
		$tarjetas = Creditcard::find('all', array('conditions' => array('nombre LIKE ?', $string)));
		
		$this->table->set_heading('Orden','Tarjeta','Acciones');
		foreach($tarjetas as $pa)
		{
			$this->table->add_row(
				$pa->id,
				$pa->nombre,
				anchor('tarjetas/editar/'.$pa->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('tarjetas/eliminar/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		echo $this->table->generate();
		
		$config['base_url'] = site_url('tarjetas/index');
		$config['total_rows'] = Creditcard::count();
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
			$tarjeta = new Creditcard( 
				elements( array('nombre'), $_POST )
			);
			if( $tarjeta->is_valid( ) )
			{
				$tarjeta->save();
				$this->session->set_flashdata( 'msg','<div class="success">La tarjeta se guardó correctamente.</div>' );
				redirect('tarjetas');
			}
			else
			{
				$data['errors'] = $tarjeta->errors;
			}
		}
		
		$data['titulo'] = "Agregar tarjeta";
		$data['action'] = "tarjetas/agregar";
		
		$this->template->write_view('content', 'tarjetas/agregar',$data);
		$this->template->render();
	}
	
	public function editar( $id )
	{	
		if(!$id)
		{
			$this->session->set_flashdata( 'msg','<div class="error">La tarjeta solicitado no existe.</div>' );
			redirect('tarjetas');
		}
		elseif ( $_POST )
		{
			$this->load->helper('date');
			$this->load->library('Utils');
					
			$tarjeta = Creditcard::find($id);
			
			$tarjeta->update_attributes(elements( array('nombre' ), $_POST ));
			
			if( $tarjeta->is_valid( ) )
			{
				if($tarjeta->save())
				{
					$this->session->set_flashdata( 'msg','<div class="success">La tarjeta se guardó correctamente.</div>' );
					redirect('tarjetas');
				}
				else
				{
					$this->session->set_flashdata( 'msg','<div class="error">Hubo un error al guardar los datos.</div>' );
					redirect('tarjetas/editar/'.$id);
				}
			}
			else
			{
				$data['errors'] = $tarjeta->errors;
			}
		}
		else $data['a'] = Creditcard::find($id);
		
		$data['titulo'] = "Editar tarjeta";
		$data['action'] = "tarjetas/editar/".$id;
		
		$this->template->write_view('content', 'tarjetas/agregar',$data);
		$this->template->render();
	}
	
	function eliminar($id)
	{
		$a = Creditcard::find($id);
		$a->delete();
		$this->session->set_flashdata('msg','<div class="success">La tarjeta fué eliminado correctamente.</div>');
		redirect('tarjetas');
	}
}
