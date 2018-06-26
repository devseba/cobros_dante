<div class="pad">
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'ciudad', 'class' => 'tipns', 'title' => 'Nombre de la ciudad', 'autocomplete' => 'off', 'value'=>isset($filtros[0]) ? str_replace("%", "", $filtros[0]): ""))?>
			<?=img('static/img/icon/filter.png')?>
			<?
				$pro = array();
				$pro[0] = 'Todos';
				foreach($provincias as $p) $pro[$p->id] = $p->provincia;
				echo form_dropdown('state_id',$pro,isset($filtros[1]) ? $filtros[1] : set_value('state_id'),'id="state" class="tipns" title="Filtrar por provincia"')?>
			<?=img('static/img/icon/filter.png')?>
			<?$ps = array();
				$ps[0] = 'Todos';
				foreach($paises as $p) $ps[$p->id] = $p->pais;
				echo form_dropdown('country_id',$ps,isset($filtros[2]) ? $filtros[2] : set_value('country_id'),'id="country" class="tipns" title="Filtrar por país"')?>
			<?=anchor('ciudades',img('static/img/icon/delete.png'),'id="clean"')?>
		</form>
	</div>
	<div id="results">
		<?=$ciudades?>
		<div class="pagination">
			<?=$pagination?>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function(){
		$('#filtros select').change(function(){
			$('#results').html('<img src="/sgi/static/img/ui-anim_basic_16x16.gif" /> Buscando...');
			$.ajax({
				type: "POST",
				url: "<?php echo site_url('ciudades/filters'); ?>",
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
				url: "<?php echo site_url('ciudades/filters'); ?>",
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
				url: "<?php echo site_url('ciudades/filters'); ?>",
				data: $('#filtros form').serialize(),
				success: function(data){
					$('#results').html(data);
				}
			});
		})
	});
</script>
