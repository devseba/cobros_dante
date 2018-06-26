<script type="text/javascript">
 $(document).ready(function() { 
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
			url: "<?php echo site_url('tutores/filters'); ?>",
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
			url: "<?php echo site_url('tutores/filters'); ?>",
			data: $('#filtros form').serialize(),
			success: function(data){
				$('#results').html(data);
			}
		});
	})
});
</script>
<div class="pad">
	<h2>Tutores <?=anchor('tutores/agregar', img('static/img/icon/sq_plus.png').' Agregar tutor')?></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'string', 'class' => 'tipns search', 'title' => 'Nombre del tutor','value'=>isset($filtros['string']) ? str_replace("%", "", $filtros['string']): ""))?>
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('id' => 'estudiante','name' => 'estudiante', 'class' => 'tipns search', 'title' => 'Nombre del hijo','value'=>isset($filtros['estudiante']) ? str_replace("%", "", $filtros['estudiante']): ""))?>
			<div class="selects">
			<?if($this->session->userdata('admin')):?>
				<?=img('static/img/icon/user.png')?>
				<?=form_dropdown('user_id',array('Varón','Mujer'),'','id="users" class="tipns" title="Filtrar por usuario"')?>
				<?=img('static/img/icon/globe_2.png')?>
				<?=form_dropdown('branch_id',array('hola'=>'hola'),'','id="branches" class="tipns" title="Filtrar por sucursal"')?>
			<?php endif;?>
			<?=anchor('#',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>
		</form>
	</div>
	<div id="results">
		<?=$tutores?>
		<div class="pagination">
			<?=$pagination?>
		</div>
	</div>
</div>
