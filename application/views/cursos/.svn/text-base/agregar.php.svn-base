<div class="yui-gb">
	<div class="pad">
		<h2><?=$titulo?></h2>
	</div>
	<div class="yui-u first">
		<div class="pad forms">
			<?=form_open($action)?>
			<?=form_label('Nivel','level_id')?>
			<?php echo isset($errors) ? $errors->on('level_id') : ''; ?>
			<? 	$ps = array(0=>'Seleccione un Nivel');
				foreach($niveles as $p) $ps[$p->id] = $p->nivel; ?>
			<?=form_dropdown('level_id', $ps, isset($a->level_id) ? $a->level_id : set_value('level_id'),'id="level_id"')?>
			<?=form_label('Curso','course')?>
			<?php echo isset($errors) ? $errors->on('course_and_division_id_and_level_id') : ''; ?>
			<?php echo isset($errors) ? $errors->on('course') : ''; ?>
			<?=form_input('course', isset($a->course) ? $a->course : set_value('course'),'id="course"')?>
		</div>
	</div>
</div>
<div class="pad">
	<?=form_submit('enviar','Guardar')?>
	<?
		$url = site_url('cursos');
		$js = "window.location='".$url."'";
		$atri = array(
				'content'=>'Cancelar',
				'class'=>"button red",
				'name'=>"cancelar",
				'id'=>"cancelar",
				'value'=>"Cancelar",
				'onClick'=>$js
				);
		echo form_button($atri); ?>
	<?=form_reset('limpiar','Limpiar formulario')?>
	<?=form_close()?>
</div>
