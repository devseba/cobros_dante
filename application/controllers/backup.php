<?php

	class Backup extends CI_Controller
	{
		function __construct()
		{
			parent::__construct();
			if(!$this->session->userdata('id')) redirect('auth/login');
		}
		
		function descargar()
		{
			$this->load->dbutil();
			
			$archivo = 'backup'.date("Y-m-d");
			
			$prefs = array(
				'format'   => 'zip',
				'filename' => $archivo.'.sql'
			);

			$backup =& $this->dbutil->backup($prefs);

			$this->load->helper('file');
			write_file('./static/backups/'.$archivo.'.zip', $backup);

			$this->load->helper('download');
			force_download($archivo.'.zip', $backup);
		}
		
		function aviso()
		{
			$this->template->write_view('content','backup/aviso');
			$this->template->render();
		}
		
		function configurar()
		{
			$backup = $this->db->get('config')->row();
			
			if($_POST)
			{
				$update = array(
					'frecuencia' => $this->input->post('frecuencia'),
					'email' => $this->input->post('email'),
					'smtp' => $this->input->post('smtp'),
					'pass' => $this->input->post('pass'),
					'destinatarios' => $this->input->post('destinatarios')
				);
				
				$this->db->update('config',$update);
				
				$this->session->set_flashdata('msg','<div class="success">La configuración se guardó con éxito.</div>');
				redirect('alumnos');
			}
			
			$data = array(
				'frecuencia' => $backup->frecuencia,
				'email' => $backup->email,
				'smtp' => $backup->smtp,
				'pass' => $backup->pass,
				'destinatarios' => $backup->destinatarios
			);
			
			$this->template->write_view('content','backup/configurar',$data);
			$this->template->render();
		}
		
		function enviar()
		{
			$b = $this->db->get('config')->row();
			
			$this->load->dbutil();
			
			$archivo = 'backup'.date("Y-m-d");
			
			$prefs = array(
				'format'      => 'zip',
				'filename'    => $archivo.'.sql'
			);

			$backup =& $this->dbutil->backup($prefs);
			
			$this->load->helper('file');
			write_file('./static/backups/'.$archivo.'.zip', $backup);
			
			$conf['protocol'] = 'smtp';
			$conf['smtp_host'] = $b->smtp;
			$conf['smtp_user'] = $b->email;
			$conf['smtp_pass'] = $b->pass;
			
			$this->load->library('email',$conf);
			$this->email->to($b->destinatarios);
			$this->email->from($b->email);
			$this->email->subject('Backup '.date('d/m/Y'));
			$this->email->attach('./static/backups/'.$archivo.'.zip');
			
			if($this->email->send())
			{
				$this->db->update('config', array('ultimo' => date('Y-m-d')));
				$this->session->set_flashdata('msg','<div class="success">El backup se realizó correctamente.</div>');
				redirect('alumnos');
			}
			else
			{
				$this->session->set_flashdata('msg','<div class="notice">El backup no pudo realizarse, intente realizarlo manualmente o contacte a soporte técnico.</div>');
			}
		}
	}
