<script type="text/javascript">
 $(document).ready(function() { 
	$('#filtros select').change(function(){
		$('#results').html('<img src="/sgi/static/img/ui-anim_basic_16x16.gif" /> Buscando...');
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('usuarios/filters'); ?>",
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
			url: "<?php echo site_url('usuarios/filter'); ?>",
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
			url: "<?php echo site_url('usuarios/filter'); ?>",
			data: $('#filtros form').serialize(),
			success: function(data){
				$('#results').html(data);
			}
		});
	})
});
</script>
<div class="pad">
	<h2>Usuarios <?=anchor('usuarios/agregar', img('static/img/icon/sq_plus.png').' Agregar usuario')?></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'string', 'class' => 'tipns search', 'title' => 'Nombre, apellido ó nombre de usuario'))?>
			<div class="selects">
			<?=anchor('#',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>
		</form>
	</div>
	<div id="results">
		<?=$alumnos?>
		<div class="pagination">
			<?=$pagination?>
		</div>
	</div>
</div>
