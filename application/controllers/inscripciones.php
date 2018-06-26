<?php
	class Inscripciones extends CI_Controller{
		function __construct(){
			parent::__construct();
			if(!$this->session->userdata('id')) redirect('auth/login');
		}
		
		function agregar(){
			if($_POST['level_id']==1){
				
				$insert = array(
							'student_id' => $_POST['student_id'],
							'course_id' => 1,
							'division_id' => 1,
							'ciclo_lectivo' => date('Y')
						);
				
				$insc = new Inscription($insert);
				$insc->save();
				$est = Student::find($_POST['student_id']);
				$est->update_attributes(elements( array('egresado' ), array('egresado'=>1)));
				$est->save();
				$this->session->set_flashdata('msg','<div class="success">El alumno ha sido egresado.</div>');			
			}
			else{
				$i = Inscription::connection();
				try{
						$i->transaction();
						$insert = array(
							'student_id' => $_POST['student_id'],
							'course_id' => $_POST['course_id'],
							'division_id' => $_POST['division_id'],
							'ciclo_lectivo' => $_POST['ciclo_lectivo']
						);
						
						Inscription::create($insert);
						$ins = $i->insert_id();
						
						$string = "SELECT *
									FROM amounts
									WHERE ciclo_lectivo = ". $insert['ciclo_lectivo']." AND course_id = ".$insert['course_id']." AND ((fecha >= '". date('Y-m-d')."' AND concept_id >2 AND concept_id <= 12) OR (concept_id <= 2 OR concept_id > 12))";
						
						$amount = Amount::find_by_sql($string);	
							
							foreach($amount as $a){
								if (! $a->pago_eventual)
								{
									$d = Debt::find(array('conditions'=>array('student_id = ? AND amount_id = ?', $insert['student_id'], $a->id )));
									if($d){
										$d->update_attributes(array('inscription_id'=>$ins));
										$d->save();
										}
									else{
										$deuda = array(
												'student_id' => $insert['student_id'],
												'amount_id' => $a->id,
												'inscription_id' => $ins
											);
										Debt::create($deuda);
									}
								}
							}
					$this->session->set_flashdata('msg','<div class="success">La inscripción se realizó correctamente.</div>');
					$i->commit();
				}
				catch (\Exception $e){
					$i->rollback();
					$this->session->set_flashdata('msg','<div class="error">Hubo un error al realizar la inscripción.</div>');
				}
			}
			redirect($this->agent->referrer());
		}
		
		public function editar( $id ){	
		if(!$id){
			$this->session->set_flashdata( 'msg','<div class="error">La inscripción no existe.</div>' );
			redirect($_SERVER['HTTP_REFERER']);
		}
		elseif ( $_POST ){
			try{
				$insc = Inscription::find($id);
				
				$insc->update_attributes(elements(array('student_id','division_id','course_id', 'ciclo_lectivo'),$_POST));
				
				if( $insc->is_valid( ) ){
					$insc->save();
					$this->session->set_flashdata( 'msg','<div class="success">La Inscripción ha sido modificada.</div>' );
					redirect($_POST['ir_a']);
				}
				else{
					$data['errors'] = $insc->errors;
					$data['ir_a'] = isset($_POST['ir_a'])?$_POST['ir_a']:$_SERVER['HTTP_REFERER'];
				}
			}
			catch( \Exception $e){
				$this->session->set_flashdata( 'msg','<div class="error">La Inscripción no pudo ser modificada.</div>' );
				redirect($_POST['ir_a']);
			}
		}
		else $data['a'] = Inscription::find($id);
		
		$data['ir_a'] = isset($data['ir_a'])?$data['ir_a']:$_SERVER['HTTP_REFERER'];
		$data['titulo'] = "Editar Inscripción";
		$data['action'] = "inscripciones/editar/".$id;
		$data['niveles']= Level::all();
		$data['divisiones']= Division::all(array('conditions'=>array('divisions.id != 1')));
		$data['cursos']=Course::all(array('joins'=>array('level'),'conditions'=>array('courses.id != 1')));
		
		$this->template->write_view('content', 'inscripciones/agregar',$data);
		$this->template->render();
		}
		
		function eliminar($id){
			if ($this->session->userdata('grupo') == 'admin'){
				try{
					
					$detalle = Detail::all(array('joins'=>'JOIN debts ON debts.id = details.debt_id', 'conditions'=>array('debts.inscription_id = ?', $id)));
					
					/*print_r($imp);
					die;
					$consulta = " SELECT * 
								  FROM  `details` 
								  WHERE debt_id
											IN ( SELECT id
												 FROM debts
												 WHERE inscription_id = $id
												 )";
					$detalle = Detail::find_by_sql($consulta);*/
										
					if(sizeof($detalle)>0){
						$this->session->set_flashdata('msg','<div class="error">No se pudo eliminar la inscripción ya que tiene movimientos.</div>');
					}
					else{
						$deudas = Debt::all(array('conditions'=>array('debts.inscription_id = ?', $id)));
						
						foreach($deudas as $d){
							$d->delete();
						}
						
						$a = Inscription::find($id);
						$est = Student::find($a->student_id);
						$est->update_attributes(elements( array('egresado' ), array('egresado'=>0)));
						$est->save();
						$a->delete();
						$this->session->set_flashdata('msg','<div class="success">La inscripción fué eliminada correctamente.</div>');
					}
				}
				catch (\Exception $e){
					$this->session->set_flashdata('msg','<div class="error">No se pudo eliminar la inscripción ya que tiene movimientos.</div>');
				}
				redirect($this->agent->referrer());	
			}
			else{		
				$this->session->set_flashdata('msg','<div class="error">No tiene permisos para realizar esta acción.</div>');
				redirect($this->agent->referrer());
			}
		}
		
		function cambiar_division()
		{
			if(isset($_POST['mover'])){
				$this->db->where_in('id',$this->input->post('inscriptions'));
				$this->db->update('inscriptions',array('division_id' => $this->input->post('division_mover')));
				$this->session->set_flashdata('msg','<div class="success">El cambio de división se realizó con éxito</div>');
				redirect($this->agent->referrer());
			}
			
			if(isset($_POST['egresar'])){
				foreach($this->input->post('inscriptions') as $ins){
					$datos = Inscription::find($ins);
					$insert = array(
							'student_id' => $datos->student_id,
							'course_id' => 1,
							'division_id' => 1,
							'ciclo_lectivo' => date('Y')
						);
				
					$insc = new Inscription($insert);
					$insc->save();
					
					$est = Student::find($datos->student_id);
					$est->update_attributes(elements( array('egresado' ), array('egresado'=>1)));
					$est->save();
					}

				$this->session->set_flashdata('msg','<div class="success">El/los alumnos han sido egresados.</div>');
				redirect($this->agent->referrer());
				}
		}
	}
