<div class="pad">
	<h2>Importes <?=anchor('importes/agregar', img('static/img/icon/sq_plus.png').' Agregar importe')?></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_desde',isset($filtros['fecha_desde'])?mdate('%d/%m/%Y' ,mysql_to_unix($filtros['fecha_desde'])):date('d/m/Y', mktime(0,0,0,1,1,date('Y'))),'id="fecha_desde" class="date tipns" title="Fecha Inicial"')?>
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_hasta',isset($filtros['fecha_desde'])?mdate('%d/%m/%Y' ,mysql_to_unix($filtros['fecha_hasta'])):date('d/m/Y', mktime(0,0,0,12,31,date('Y'))),'id="fecha_hasta" class="date tipns" title="Fecha Final"')?>
			<div class="selects">
			<?=img('static/img/icon/filter.png')?>
			<?$ps = array();
				$ps[0] = 'Todos';
				foreach($conceptos as $p) $ps[$p->id] = $p->concepto;
				echo form_dropdown('concepto_id',$ps,isset($filtros['concepto']) ? $filtros['concepto'] : set_value('concepto_id'),'id="concepto_id" class="tipns" title="Filtrar por Concepto"')?>
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
			<?=anchor('importes',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>			
		</form>
	</div>
	<div id="results">
		<?=$importes?>
		<div class="pagination">
			<?=$pagination?>
		</div>
	</div>
</div>
<script type="text/javascript" src="<?php echo base_url().'static/js/chained.js'; ?>"></script>
<script type="text/javascript">
	jQuery(document).ready(function(){
		$('#filtros select').change(function(){
		$('#results').html('<img src="/sgi/static/img/ui-anim_basic_16x16.gif" /> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('importes/filters'); ?>",
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
			$('#results').html('<img src="/sgi/static/img/ui-anim_basic_16x16.gif" /> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('importes/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
		
		$('#filtros input').keyup(function(){
			$('#results').html('<img src="/sgi/static/img/ui-anim_basic_16x16.gif" /> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('importes/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
		
		$('#filtros input').change(function(){
			$('#results').html('<img src="/sgi/static/img/ui-anim_basic_16x16.gif" /> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('importes/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
	});
	
	jQuery('#course').chainedTo('#level_id');
</script>
