<div class="pad">
	<h2>Listado de descuentos <span><?=anchor('descuentos/reporte',img('static/img/icon/print.png'),'id=print')?></span></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'estudiante', 'id' => 'estudiante', 'class' => 'tipns search', 'title' => 'Ingrese el nombre del alumno','value'=>isset($filtros[0]) ? str_replace("%", "", $filtros[0]): ""))?>
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'tutor', 'id' => 'tutor', 'class' => 'tipns search', 'title' => 'Ingrese el nombre del tutor','value'=>isset($filtros[0]) ? str_replace("%", "", $filtros[1]): ""))?>
			<div class="selects">
			<?=img('static/img/icon/filter.png')?>
			<?$ps = array();
				$ps[0] = 'Todos';
				foreach($conceptos as $p) $ps[$p->id] = $p->concepto;
				echo form_dropdown('concepto_id',$ps,isset($filtros[2]) ? $filtros[2] : set_value('concepto_id'),'id="concepto_id" class="tipns" title="Filtrar por Concepto"')?>
			<?=anchor('importes',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>
		</form>
	</div>
	<div id="results">
		<?=$descuentos?>
		<div class="pagination">
			<?=$pagination?>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$('#filtros select').change(function(){
			$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('descuentos/filters'); ?>",
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
			url: "<?php echo site_url('descuentos/filters'); ?>",
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
			url: "<?php echo site_url('descuentos/filters'); ?>",
			data: $('#filtros form').serialize(),
			success: function(data){
				$('#results').html(data);
			}
		});
	});
	
	$('#print').click(function(e){
		e.preventDefault();
		$('#f').attr('action','descuentos/reporte');
		$('#f').submit();
	});
});
</script>
