<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ptypes extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('id')) redirect('auth/login');
	}

	public function index($offset = 0)
	{
		$this->load->library('pagination');
		$config['base_url'] = site_url('ptypes/index');
		$config['total_rows'] = Ptype::count();
		$config['per_page'] = '20'; 
		$config['num_links'] = '1'; 
		$config['first_link'] = '&larr; primero';
		$config['last_link'] = 'último &rarr;';
		$this->pagination->initialize($config);
		
		$ptypes = Ptype::find('all', array('limit' => $config['per_page'], 'offset' => $offset));
		
		$this->table->set_heading('Orden','Tipo de pago','Acciones');
		foreach($ptypes as $pa)
		{
			$this->table->add_row(
				$pa->id,
				$pa->tipo,
				anchor('ptypes/editar/'.$pa->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('ptypes/eliminar/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		
		$data['ptypes'] = $this->table->generate();
		$data['pagination'] = $this->pagination->create_links();
		
		$this->template->write_view('content', 'ptypes/index',$data);
		$this->template->render();
	}
	
	public function filters()
	{
		$string = '%'.$this->input->post('tipo').'%';
		$ptypes = array();
		
		$ptypes = Ptype::find('all', array('conditions' => array('tipo LIKE ?', $string)));
		
		$this->table->set_heading('Orden','País','Acciones');
		foreach($ptypes as $pa)
		{
			$this->table->add_row(
				$pa->id,
				$pa->tipo,
				anchor('ptypes/editar/'.$pa->id,img('static/img/icon/pencil.png'), 'class="tipwe" title="Editar"').' '.
				anchor('ptypes/eliminar/'.$pa->id,img('static/img/icon/trash.png'), 'class="tipwe eliminar" title="Eliminar"')
			);
		}
		echo $this->table->generate();
		
		$config['base_url'] = site_url('ptypes/index');
		$config['total_rows'] = Ptype::count();
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
			$ptype = new Ptype( 
				elements( array('tipo'	), $_POST )
			);
			if( $ptype->is_valid( ) )
			{
				$ptype->save();
				$this->session->set_flashdata( 'msg','<div class="success">El tipo de pago se guardó correctamente.</div>' );
				redirect('ptypes');
			}
			else
			{
				$data['errors'] = $ptype->errors;
			}
		}
		
		$data['titulo'] = "Agregar tipo de pago";
		$data['action'] = "ptypes/agregar";
		
		$this->template->write_view('content', 'ptypes/agregar',$data);
		$this->template->render();
	}
	
	public function editar( $id )
	{	
		if(!$id)
		{
			$this->session->set_flashdata( 'msg','<div class="error">El tipo de pago solicitado no existe.</div>' );
			redirect('ptypes');
		}
		elseif ( $_POST )
		{
			$this->load->helper('date');
			$this->load->library('Utils');
					
			$ptype = Ptype::find($id);
			
			$ptype->update_attributes(elements( array('tipo' ), $_POST ));
			
			if( $ptype->is_valid( ) )
			{
				if($ptype->save())
				{
					$this->session->set_flashdata( 'msg','<div class="success">El tipo de pago se guardó correctamente.</div>' );
					redirect('ptypes');
				}
				else
				{
					$this->session->set_flashdata( 'msg','<div class="error">Hubo un error al guardar los datos.</div>' );
					redirect('ptypes/editar/'.$id);
				}
			}
			else
			{
				$data['errors'] = $ptype->errors;
			}
		}
		else $data['a'] = Ptype::find($id);
		
		$data['titulo'] = "Editar tipo de pago";
		$data['action'] = "ptypes/editar/".$id;
		
		$this->template->write_view('content', 'ptypes/agregar',$data);
		$this->template->render();
	}
	
	function eliminar($id)
	{
		$a = Ptype::find($id);
		$a->delete();
		$this->session->set_flashdata('msg','<div class="success">El tipo de pago fué eliminado correctamente.</div>');
		redirect('ptypes');
	}
	
	function agregar_detalle()
	{
		$this->session->unset_userdata('pago');
		$this->session->set_userdata('pago', $_POST);
		$data['total_pagar'] = $_POST['importe'];
		$data['ptypes'] = Ptype::all();
		$data['bancos'] = Bank::all();
		$data['tarjetas'] = Creditcard::all();
		$data['titulo'] = 'Agregar detalles de pago';
		$this->template->write_view('content', 'ptypes/agregar_detalle',$data);
		$this->template->render();
	}
}
