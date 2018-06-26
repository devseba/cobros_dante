<div class="pad">
	<h2>Listado de deudas <span><?=anchor('deudas/export',img('static/img/icon/doc_export.png'),'id=export')?><?=anchor('deudas/reporte',img('static/img/icon/print.png'),'id=print')?></span></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_desde',isset($filtros['fecha_desde'])?mdate('%d/%m/%Y' ,mysql_to_unix($filtros['fecha_desde'])):date('d/m/Y', mktime(0,0,0,1,1,date('Y'))),'id="fecha_desde" class="date tipns" title="Fecha Inicial"')?>
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_hasta',isset($filtros['fecha_hasta'])?mdate('%d/%m/%Y' ,mysql_to_unix($filtros['fecha_hasta'])):date('d/m/Y'),'id="fecha_hasta" class="date tipns" title="Fecha Final"')?>
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'estudiante', 'class' => 'tipns search', 'title' => 'Nombre del estudiante', 'autocomplete' => 'off','value'=>isset($filtros['estudiante']) ? str_replace("%", " ", $filtros['estudiante']): ""))?>
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'tutor', 'class' => 'tipns search', 'title' => 'Nombre del tutor', 'autocomplete' => 'off','value'=>isset($filtros['tutor']) ? str_replace("%", " ", $filtros['tutor']): ""))?>
			<div class="selects">
			<?=img('static/img/icon/filter.png')?>
			<?$ps = array();
				$ps[0] = 'Todos';
				foreach($conceptos as $p) $ps[$p->id] = $p->concepto;
				echo form_dropdown('concepto_id',$ps,isset($filtros['concepto']) ? $filtros['concepto'] : set_value('concepto_id'),'id="concepto_id" class="tipns" title="Filtrar por Concepto"')?>
			<?=img('static/img/icon/filter.png')?>
			<?$ls = array();
				$ls[0] = 'Todos';
				$st = '';
				foreach($niveles as $p){ 
					$st .= $p->id.' ';
					$ls[$p->id] = $p->nivel; }
				echo form_dropdown('level_id',$ls,isset($filtros['nivel']) ? $filtros['nivel'] : set_value('level_id'),'id="level_id" class="tipns" title="Filtrar por Nivel"')?>
			<?=img('static/img/icon/filter.png')?>
				<?
					$cs = array(0 => 'Todos'.', '.$st);
					foreach($cursos as $c) $cs[$c->id] = $c->course.','.$c->level_id;
				?>
				<?=form_dropdown('course_id', $cs,isset($filtros['curso']) ? $filtros['curso'] : set_value('course_id'),'id="course" class="tipns" title="Filtrar por curso"')?>
			<?=anchor('deudas',img('static/img/icon/delete.png'))?>
			<?//=anchor('deudas',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>
		</form>
	</div>
	<div id="results">
		<?=$deudas?>
		<div>
		<?=isset($total_deuda)?$total_deuda:'';?>
		</div>
		<br/>
		<div class="pagination">
			<?=isset($pagination)?$pagination:''?>
		</div>
	</div>
</div>
<script type="text/javascript" src="<?php echo base_url().'static/js/chained.js'; ?>"></script>
<script type="text/javascript">
	$(document).ready(function(){
		$('#filtros select').change(function(){
			$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('deudas/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
		
		$('#clean').click(function(e){
			e.preventDefault();
			$(':input','#f')
			.not(':button, :submit, :reset, :hidden')
			.val('')
			.removeAttr('checked')
			.removeAttr('selected');
			$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('deudas/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
		
		$('#filtros input').keyup(function(){
			$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('deudas/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
		
		$('#filtros input').change(function(){
			$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('deudas/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		});
		
		$('#print').click(function(e){
			e.preventDefault();
			$('#f').attr('action','deudas/reporte');
			$('#f').submit();
		})
		
		$('#export').click(function(e){
			e.preventDefault();
			$('#f').attr('action','deudas/export');
			$('#f').submit();
		})
	});

	jQuery('#course').chainedTo('#level_id');
</script>
