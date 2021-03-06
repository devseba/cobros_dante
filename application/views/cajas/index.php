<div class="pad">
	<h2>Listado de Caja <span><?=anchor('cajas/export',img('static/img/icon/doc_export.png'),'id=export')?><?=anchor('cajas/reporte',img('static/img/icon/print.png'),'id=print')?></span></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_desde', date('d/m/Y', mktime(0,0,0,date('m'),date('d'),date('Y'))),'id="fecha_desde" class="date tipns" title="Fecha Inicial"')?>
			<?=img('static/img/icon/calendar_1.png')?>
			<?=form_input('fecha_hasta',date('d/m/Y'),'id="fecha_hasta" class="date tipns" title="Fecha Final"')?>
			<div class="selects">
				<?=img('static/img/icon/filter.png')?>
				<?
					$estado = array(0 => 'Confirmados', 1 => 'Anulados');
					echo form_dropdown('anulado',$estado,'','id="anulado" class="tipns" title="Filtrar por estado"')
				?>
				<?=img('static/img/icon/filter.png')?>
				<?
					$ps = array();
					$ps[0] = 'Todos';
					foreach($usuarios as $p) $ps[$p->id] = $p->apellido.' '.$p->nombre;
					echo form_dropdown('user_id',$ps,'','id="user_id" class="tipns" title="Filtrar por Usuario"')
				?>
				<?=anchor('cajas',img('static/img/icon/delete.png'))?>
			</div>
		</form>
	</div>
	<div id="results">
		<?=$pagos?>
		<!--<div class="pagination">
			<?=isset($pagination)?$pagination:''; ?>
		</div>-->
		<?=$total_diario?>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#filtros select').change(function(){
			$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('cajas/filters'); ?>",
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
				url: "<?php echo site_url('cajas/filters'); ?>",
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
				url: "<?php echo site_url('cajas/filters'); ?>",
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
				url: "<?php echo site_url('cajas/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		});
		
		$('#print').click(function(e){
			e.preventDefault();
			$('#f').attr('action','cajas/reporte');
			$('#f').submit();
		})
		
		$('#export').click(function(e){
			e.preventDefault();
			$('#f').attr('action','cajas/export');
			$('#f').submit();
		})
	});
</script>
