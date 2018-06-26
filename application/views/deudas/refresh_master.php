<div class="pad">
	<h2>Listado de deudas <span><?=anchor('deudas/generar_archivo_refresh',img('static/img/icon/doc_export.png'),'id=export')?></span></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_desde',isset($filtros['fecha_desde'])?mdate('%d/%m/%Y' ,mysql_to_unix($filtros['fecha_desde'])):date('d/m/Y', mktime(0,0,0,1,1,date('Y'))),'id="fecha_desde" class="date tipns" title="Fecha Inicial"')?>
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_hasta',isset($filtros['fecha_hasta'])?mdate('%d/%m/%Y' ,mysql_to_unix($filtros['fecha_hasta'])):date('d/m/Y'),'id="fecha_hasta" class="date tipns" title="Fecha Final"')?>
			<?=img('static/img/icon/zoom.png')?>
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
				url: "<?php echo site_url('deudas/filters_refresh'); ?>",
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
				url: "<?php echo site_url('deudas/filters_refresh'); ?>",
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
				url: "<?php echo site_url('deudas/filters_refresh'); ?>",
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
				url: "<?php echo site_url('deudas/filters_refresh'); ?>",
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
			$('#f').attr('action','generar_archivo_refresh');
			$('#f').submit();
		})
	});

	jQuery('#course').chainedTo('#level_id');
</script>
