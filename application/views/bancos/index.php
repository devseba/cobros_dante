<script type="text/javascript">
$(document).ready(function(){
	
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
			url: "<?php echo site_url('bancos/filters'); ?>",
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
			url: "<?php echo site_url('bancos/filters'); ?>",
			data: $('#filtros form').serialize(),
			success: function(data){
				$('#results').html(data);
			}
		});
	})
});
</script>
<div class="pad">
	<h2>Listado de bancos <?=anchor('bancos/agregar', img('static/img/icon/sq_plus.png').' Agregar banco')?></h2>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'nombre', 'class' => 'tipns search', 'title' => 'Ingrese el nombre del banco'))?>
			<div class="selects">
			<?=anchor('bancos',img('static/img/icon/delete.png'),'id="clean"')?>
			</div>
		</form>
	</div>
	<div id="results">
		<?=$bancos?>
		<div class="pagination">
			<?=$pagination?>
		</div>
	</div>
</div>
