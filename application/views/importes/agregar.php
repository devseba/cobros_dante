<div class="yui-gb">
	<div class="pad">
		<h2><?=$titulo?></h2>
	</div>
<? $attributes = array('id' => 'f');?>
<?=form_open($action, $attributes)?>
	<div class="yui-u first">
		<div class="pad forms">
			<?=form_label('Fecha de Vto','fecha')?>
			<?php echo isset($errors) ? $errors->on('fecha') : ''; ?>
			<?=form_input('fecha', (isset($a->fecha) && $a->fecha) ? $a->fecha->format('d/m/Y') : set_value('fecha'),'id="fecha" class="date" tabindex="1"')?>
			<?=form_label('Curso','course_id')?>
			<?php echo isset($errors) ? $errors->on('course_id') : ''; ?>
			<? 	$ps = array();
				foreach($cursos as $c) $ps[$c->id] = $c->course.' '.$c->level->nivel; ?>
			<?=(isset($a))?form_dropdown('course_id', $ps, isset($a->course_id) ? $a->course_id : set_value('course_id'),'id="course_id"'):form_multiselect('course_id[]', $ps, isset($a->course_id) ? $a->course_id : set_value('course_id'),'id="course_id" tabindex="3"')?>
			<?php echo isset($errors)? $errors->on('concept_id_and_course_id_and_ciclo_lectivo') : ''; ?>
			<?=form_label('Tipo de pago','pago')?>
			<?php echo isset($errors) ? $errors->on('pago') : ''; ?>
			<div class="radios">
				<?=form_radio(array(
					'name' => 'pago',
					'tabindex' => '5',
					'id' => 'pago_eventual',
					'value' => 1,
					'checked' => (isset($a->pago_eventual)&& $a->pago_eventual) ? 'checked' : set_radio('pago',1)
				))?>
				<?=form_label('Pago eventual','pago_eventual')?>
				<?=form_radio(array(
					'name' => 'pago',
					'tabindex' => '5',
					'id' => 'pago_parcial',
					'value' => 0,
					'checked' => (isset($a->pago_parcial) && $a->pago_parcial) ? 'checked' : set_radio('pago',1)
				))?>
				<?=form_label('Pago parcial','pago_parcial')?>
			</div>
		</div>
	</div>
	<div class="yui-u">
		<div class="pad forms">
			<?=form_label('Importe','importe')?>
			<?php echo isset($errors) ? $errors->on('importe') : ''; ?>
			<?=form_input('importe', isset($a->importe)?$a->importe : set_value('importe'),'id="importe" tabindex="2"')?><?=form_label('Concepto','concept_id')?>
			<?php echo isset($errors) ? $errors->on('concept_id') : ''; ?>
			<? 	$ps = array();
				foreach($conceptos as $c) $ps[$c->id] = $c->concepto; ?>
			<?=(isset($a))?form_dropdown('concept_id', $ps, isset($a->concept_id)?$a->concept_id : set_value('concept_id'),'id="concept_id" tabindex="4"'):form_multiselect('concept_id[]', $ps, isset($a->concept_id) ? $a->concept_id : set_value('concept_id'),'id="concept_id" tabindex="4"')?>
			<?=form_label('Ciclo lectivo','ciclo_lectivo')?>
			<?php echo isset($errors) ? $errors->on('ciclo_lectivo') : ''; ?>
			<?=form_input('ciclo_lectivo', isset($a->ciclo_lectivo)?$a->ciclo_lectivo:date('Y'),'id="ciclo_lectivo" tabindex="6"')?>
		</div>
	</div>
</div>
<div style="margin-left:20px; height:30px;"><?=form_label('','mensaje', array('id'=>'mensaje'))?></div>
<div class="pad">
	<? $arr = array('name'=> 'enviar',
					'content'=>'Guardar',
					'value'=>'Guardar',
					'id'=>'enviar',
					'tabindex'=>'7');
	?>
	<?=form_submit($arr);?>
	<?
		$url = site_url('importes');
		$js = "window.location='".$url."'";
		$atri = array(
				'content'=>'Cancelar',
				'class'=>"button red",
				'name'=>"cancelar",
				'id'=>"cancelar",
				'value'=>"Cancelar",
				'tabindex'=>"8",
				'onClick'=>$js
				);
		echo form_button($atri); ?>
	<?=form_reset('limpiar','Limpiar formulario', 'tabindex="9"')?>
</div>
<?=form_close()?>

<script type="text/javascript">
	$(document).ready(function(){
		$('#enviar').click(function(e){
			e.preventDefault();
			var ciclo = $('#ciclo_lectivo').val();
			var fecha = $('#fecha').val();
			var imp = $('#importe').val();
			var curso = $('#course_id').val();
			var concepto = $('#concept_id').val(); 
			
			if((fecha.length !=0)){
				if((imp.length != 0)){
				if((ciclo.length != 0)){
					if((curso != null)){
						if((concepto != null)){
							$('#f').submit();
						}
						else{
							$('#mensaje').css('color','red');
							$('#mensaje').text('Debe Seleccionar un concepto.');
							}
					}
					else{
							$('#mensaje').css('color','red');
							$('#mensaje').text('Debe Seleccionar un curso');
						}
					}
				else{
					$('#mensaje').css('color','red');
					$('#mensaje').text('Debe ingresar el ciclo lectivo al que pertenece el importe');
					}
				}
				else{
					$('#mensaje').css('color','red');
					$('#mensaje').text('Debe ingresar un importe.');
					}
			}
			else{
				$('#mensaje').css('color','red');
				$('#mensaje').text('Debe ingresar la fecha.');
				}
		});
	});
</script>
