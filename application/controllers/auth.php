<?php 

	class Auth extends CI_Controller {
	
		function login()
		{
			$this->load->view('auth/login');
		}
		
		function recuperar()
		{
			if($_POST)
			{
				$recuperar = User::recuperar($_POST['email']);
				if($recuperar)
				{
					$b = $this->db->get('config')->row();
					$config['protocol'] = 'smtp';
					$config['smtp_host'] = $b->smtp;
					$config['smtp_user'] = $b->email;
					$config['smtp_pass'] = $b->pass;
					$config['charset'] = 'utf-8';
					$config['wordwrap'] = TRUE;
					$config['mailtype'] = 'html';
					
					$this->load->library('email');
					$this->email->initialize($config);
					$this->email->from('dante@itexa.com.ar', 'Sistema de gestión');
					$this->email->to($_POST['email']);
					$this->email->subject('Instrucciones para recuperar tu clave');
					$msg = '<p>Si has pedido recuperar tu clave en el sistema haz '.anchor('auth/reset/'.$recuperar,'click aquí');
					$msg .= '</p><p>Si Ud. no solicitó el cambio de clave ignore este mensaje y elimínelo.</p>';
					$this->email->message($msg);

					if($this->email->send()) $this->session->set_flashdata('msg','<div class="success">Se envió un correo a su dirección con instrucciones para recuperar la clave.</div>');
					else $this->session->set_flashdata('msg','<div class="notice">Hubo un problema al enviar el correo, intenta nuevamente.</div>');
					
					redirect('auth/login');
				}
				else
				{
					$this->session->set_flashdata('msg','<div class="notice">La dirección de correo no existe en el sistema.</div>');
					redirect('auth/recuperar');
				}
			}
			else $this->load->view('auth/recuperar');
		}
		
		function reset($hash)
		{
			$u = User::find_by_hash($hash);
			if($u)
			{
				$pass = uniqid();
				$u->password = $pass;
				$u->hash = NULL;
				$u->save();
				$data['msg'] = '<p>Su clave ha sido reestablecida a:</p>';
				$data['msg'] .= '<p><strong class="resaltado">'.$pass.'</strong></p>';
				$data['msg'] .= '<p>Anotela en un lugar seguro e inicie sesión nuevamente para cambiarla.</p>';
			}
			else
			{
				$data['msg'] = 'Ha sido imposible recuperar su contraseña, intente nuevamente o pongase en contacto con el administrador del sistema.';
			}
			
			$this->load->view('auth/reset',$data);
		}
		
		function validar($documento = 0)
		{
			if($documento == 0){
				$auth = User::validar($_POST['usuario'],$_POST['password']);
				if($auth)
				{
					$data = array(
						'usuario' => $auth->usuario,
						'grupo' => $auth->grupo,
						'id' => $auth->id
					);
					
					$this->session->set_userdata($data);

					//Si es alumno
					if($auth->grupo == 'alumno'){
						$alumno_id = Student::find(array(
							'select'	 => 'id',
							'conditions' => array('nro_documento = ?',$auth->usuario)
							));
						redirect('alumnos/ver/'.$alumno_id->id);
					}
					else{
						$sql = 'SELECT
									DATEDIFF(NOW(), ultimo) AS diff,
									frecuencia
								FROM config';
						
						$b = $this->db->query($sql)->row();
						
						if($b->diff >= $b->frecuencia)
						{
							redirect('backup/aviso/');
						}
						else redirect('alumnos');
					}
				}
				else
				{
					$this->session->set_flashdata('msg','<div class="notice">El nombre de usuario o contraseña son incorrectos.</div>');
					redirect('auth/login');
				}
			}
			else{//viene de ael				
				
				if(!isset($_SERVER["HTTP_REFERER"])){
					redirect('auth/login');
				}
				else{
					//echo "<center>El sistema de pago momentaneamente no esta disponible, por favor intente mas tarde.</center>";
					$http_referer = $_SERVER["HTTP_REFERER"];
					$http_referer = substr($http_referer, -18);
					if($http_referer == "lista_hijos_cobros"){
						$alumno = Student::find(array(
							'select'	 => 'id',
							'conditions' => array('nro_documento = ?',$documento)
							));
						if(!$alumno){
							$this->session->set_flashdata('msg','<div class="notice">El alumno no esta registrado en el sistema de cobranza. Dirijase a la Instituci&oacute;n para solucionar el problema</div>');
							redirect('auth/login');							
						}
						$usuario = User::find(array(
							'select'	 => 'usuario, grupo',
							'conditions' => array('usuario = ?',$documento)
							));
						if(!$usuario){
							$this->session->set_flashdata('msg','<div class="notice">El alumno no esta dado de alta como usuario. Dirijase a la Instituci&oacute;n para solucionar el problema</div>');
							redirect('auth/login');							
						}

						$data = array(
							'usuario' => $usuario->usuario,
							'grupo' => $usuario->grupo,
							'id' => $alumno->id
						);
						
						$this->session->set_userdata($data);

						redirect('alumnos/ver/'.$alumno->id);					
					}
					else{
						redirect('auth/login');					
					}
				}
			}
		}

		function crear_usuarios_alumnos(){
			$alumnos = Student::all();
			foreach ($alumnos as $key => $row) {
				$usuario = User::find(array(
						'select'	 => 'usuario, grupo',
						'conditions' => array('usuario = ?',$row->nro_documento)
					));
				if(!$usuario){//si no existe el usuario lo inserto

					$datos = array(
							"apellido" => $row->apellido,
							"nombre" => $row->nombre,
							"grupo" => "alumno",
							"usuario" => $row->nro_documento,
							"password" => $row->nro_documento,
							"email" => (isset($row->email)?$row->email:"NO_".$row->nro_documento),
							"direccion" => $row->domicilio,
							"telefono" => (isset($row->telefono)?$row->telefono:""),
							"celular" => $row->celular,
							"nro_documento" => $row->nro_documento
						);
					$nuevo_usuario = new User($datos);

					if($nuevo_usuario->is_valid()){
						$nuevo_usuario->save();
					}
					else{
						echo "<b>Nombre: ".$row->apellido." ".$row->nombre." Dni: ".$row->nro_documento."</b>";
						echo "<br>";
						foreach ($nuevo_usuario->errors as $key => $value) {
							print_r($value);
							echo "<br>";
						}
						echo "<br>";
						echo "-----------------------------------------------------------------------------<br>";
						//echo "<br>no valido ".$row->nro_documento;
					}
				}
			}
		}
		
		function logout()
		{
			$this->session->sess_destroy();
			redirect('auth/login');
		}
	}
