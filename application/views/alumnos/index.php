<div class="pad">
	<h2>Alumnos <?=anchor('alumnos/export',img('static/img/icon/doc_export.png'),array('id'=>'export'));?><?=anchor('alumnos/reporte',img('static/img/icon/print.png'),array('id'=>'print'));?><?=anchor('alumnos/agregar', img('static/img/icon/sq_plus.png'))?></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'string', 'class' => 'tipns search', 'title' => 'Nombre, apellido o DNI','value'=>isset($filtros[0]) ? str_replace("%", "", $filtros[0]): ""))?>
			<div class="selects">
				<?=img('static/img/icon/filter.png')?>
				<?
					$nv = array(0 => 'Todos');
					foreach($niveles as $n) $nv[$n->id] = $n->nivel
				?>
				<?=form_dropdown('level_id', $nv,isset($filtros[1]) ? $filtros[1] : set_value('level_id'),'id="level" class="tipns" title="Filtrar por niveles"')?>
				<?=img('static/img/icon/filter.png')?>
				<?
					$cs = array(0 => 'Todos');
					foreach($cursos as $c) $cs[$c->id] = $c->course.','.$c->level_id;
				?>
				<?=form_dropdown('course_id', $cs,isset($filtros[2]) ? $filtros[2] : set_value('course_id'),'id="course" class="tipns" title="Filtrar por curso"')?>
				<?
					$ds = array(0 => 'Todas');
					foreach($divisiones as $d) $ds[$d->id] = $d->division;
				?>
				<?=img('static/img/icon/filter.png')?>			
				<?=form_dropdown('division_id', $ds, isset($filtros[3]) ? $filtros[3] : set_value('division_id'),'id="division" class="tipns" title="Filtrar por división"')?>
				<?
					foreach($ciclo_lectivos as $cls) $cl[$cls->ciclo_lectivo] = $cls->ciclo_lectivo;
					
					for($i = 0; $i<=3; $i++){
						$cl[(date('Y')+$i)] =(date('Y')+$i);
					}
					
					$cl = array_unique($cl, SORT_NUMERIC);
				?>
				<?=form_dropdown('ciclo_lectivo', $cl, isset($filtros[4]) ? $filtros[4] : date('Y'), 'id="ciclo_lectivo" class="tipns" title="Filtrar por Ciclo"')?>
				<?=anchor('alumnos/index',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>
		</form>
	</div>
	<?=form_open("inscripciones/cambiar_division", array('id' => 'masivo'))?>
	<div id="results">
		<?=$alumnos?>
		<div class="pagination">
			<?=$pagination?>
		</div>			
	</div>
	<?php $ds[0] = "Seleccionar"; ?>
	<?=form_dropdown('division_mover', $ds, set_value('division_mover'),'id="division_mover" class="tipns" title="Filtrar por división"')?>
	<button class="button" type="submit" id="serialize" name="mover">Mover a división</button>
	<button class="button" type="submit" id="egresar" name="egresar">Egresar</button>
	<?=form_close()?>
</div>
<script type="text/javascript" src="<?php echo base_url().'static/js/chained.js'; ?>"></script>
<script type="text/javascript">
 $(document).ready(function() { 
	$('#course').chainedTo('#level');
	
	$('#select-all').live('click', function ()
	{
		if($('#select-all').attr('checked')) {
			$('input:checkbox').attr('checked','checked');	
		}
		else {
			$('input:checkbox').removeAttr('checked');
		}
	});
	
	$('#filtros select').change(function(){
		$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
		
		if($('#course').val() == '')
		{
			$('#division').attr('disabled',true);
		}
		else
		{
			$('#division').attr('disabled',false);
		}
		
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('alumnos/filter'); ?>",
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
			url: "<?php echo site_url('alumnos/filter'); ?>",
			data: $('#filtros form').serialize(),
			success: function(data){
				$('#results').html(data);
			}
		});
	})
	
	$('#print').click(function(e){
			e.preventDefault();
			$('#f').attr('action','alumnos/reporte');
			$('#f').submit();
		})
		
	$('#export').click(function(e){
			e.preventDefault();
			$('#f').attr('action','alumnos/export');
			$('#f').submit();
		})
});
</script>
