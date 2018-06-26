<div class="pad">
	<h2>Listado de Cheques <span><?=anchor('pagos/exportcheques',img('static/img/icon/doc_export.png'),'id=export')?><?=anchor('pagos/reportecheques',img('static/img/icon/print.png'),'id=print')?></span></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_desde',isset($filtros['fecha_desde'])?mdate('%d/%m/%Y' ,mysql_to_unix($filtros['fecha_desde'])):date('d/m/Y', mktime(0,0,0,1,1,date('Y'))),'id="fecha_desde" class="date tipns" title="Fecha Inicial"')?>
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_hasta',isset($filtros['fecha_desde'])?mdate('%d/%m/%Y' ,mysql_to_unix($filtros['fecha_hasta'])):date('d/m/Y'),'id="fecha_hasta" class="date tipns" title="Fecha Final"')?>
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'nrocomprobante', 'class' => 'tipns search', 'title' => 'Nro de Cheque', 'autocomplete' => 'off', 'value'=>isset($filtros['nrocomprobante'])?str_replace("%", " ", $filtros['nrocomprobante']): " "))?>
			<div class="selects">
				<?=anchor('pagos',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>
		</form>
	</div>
	<div id="results">
		<?=$cheques?>
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
				url: "<?php echo site_url('pagos/filter'); ?>",
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
				url: "<?php echo site_url('pagos/filter'); ?>",
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
				url: "<?php echo site_url('pagos/filter'); ?>",
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
				url: "<?php echo site_url('pagos/filter'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
		
		$('#print').click(function(e){
			e.preventDefault();
			$('#f').attr('action','reportecheques');
			$('#f').submit();
		})
		
		$('#export').click(function(e){
			e.preventDefault();
			$('#f').attr('action','exportcheques');
			$('#f').submit();
		})
	});
</script>
