<div class="pad">
	<h2>Provincias <?=anchor('provincias/agregar', img('static/img/icon/sq_plus.png').' Agregar provincia')?></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'provincia', 'class' => 'tipns search', 'title' => 'Nombre de la provincia', 'autocomplete' => 'off'))?>
			<div class="selects">
			<?=img('static/img/icon/filter.png')?>
			<?$ps = array();
				$ps[0] = 'Todos';
				foreach($paises as $p) $ps[$p->id] = $p->pais;
				echo form_dropdown('country_id',$ps,'','id="country" class="tipns" title="Filtrar por país"')?>
			<?=anchor('provincias',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>
		</form>
	</div>
	<div id="results">
		<?=$provincias?>
		<div class="pagination">
			<?=$pagination?>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#filtros select').change(function(){
			$('#results').html('<img src="/sgi/static/img/ui-anim_basic_16x16.gif" /> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('provincias/filters'); ?>",
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
				url: "<?php echo site_url('provincias/filters'); ?>",
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
				url: "<?php echo site_url('provincias/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
	});
</script>
