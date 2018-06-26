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
			<?=form_dropdown('level_id', $ps, isset($a->course->level_id) ? $a->course->level_id : set_value('level_id'),'id="level_id"')?>
			<?=form_label('Division','division_id')?>
			<?php echo isset($errors) ? $errors->on('division_id') : ''; ?>
			<? 	$ps = array(0=>'Seleccione una Division');
				foreach($divisiones as $p) $ps[$p->id] = $p->division; ?>
			<?=form_dropdown('division_id', $ps, isset($a->division_id) ? $a->division_id : set_value('division_id'),'id="division_id"')?>
		</div>
	</div>
	<div class="yui-u first">
		<div class="pad forms">
			<?=form_label('Curso','course_id')?>
			<?php echo isset($errors) ? $errors->on('course_id') : ''; ?>
			<? 	$c = array(0=>'Seleccione un Curso');
				foreach($cursos as $cs) $c[$cs->id] = $cs->course.','.$cs->level_id; ?>
			<?=form_dropdown('course_id', $c, isset($a->course_id) ? $a->course_id : set_value('course_id'),'id="course_id"')?>
			<?=form_label('Ciclo lectivo','ciclo_lectivo')?>
			<?
				$cl = array('Seleccione', date('Y') => date('Y'), (date('Y')+1) => (date('Y')+1), (date('Y')+2) => (date('Y')+2), (date('Y')+3) => (date('Y')+3));
			?>
			<?=form_dropdown('ciclo_lectivo', $cl,isset($a->ciclo_lectivo) ? $a->ciclo_lectivo : set_value('ciclo_lectivo'),'id="ciclo_lectivo"')?>
			<?=form_hidden('student_id', $a->student_id);?>
			<?=form_hidden('ir_a', $ir_a);?>
		</div>
	</div>
</div>
<div class="pad">
	<?=form_submit('enviar','Guardar')?>
	<?
		$url = $ir_a; //site_url('cursos');
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
<script type="text/javascript">
	jQuery(document).ready(function(){
		$('#course').chainedTo('#level_id');
	})
</script>
