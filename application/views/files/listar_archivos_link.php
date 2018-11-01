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
		$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('files/filters'); ?>",
			data: $('#filtros form').serialize(),
			success: function(data){
				//console.log(data);
				$('#results').html(data);
			},
			error: function(error){
				console.log(error);
			}
		});
	});

	$('#anular_filtro').click(function(){
		$('#results').html('<?=img("static/img/ui-anim_basic_16x16.gif")?> Buscando...');
		$.ajax({
			type: "POST",
			url: "<?php echo site_url('files/anular_por_filtro'); ?>",
			data: $('#filtros form').serialize(),
			success: function(data){
				//console.log(data);
				$('#results').html(data);
			},
			error: function(error){
				console.log(error);
			}
		});		
	});
});
</script>
<div class="pad">
	<h2>Listado de bancos </h2>
	<a id="anular_filtro" align"left" href="#"><?=img('static/img/icon/trash.png').' Anular segun el filtro'?></a>
	<div id="filtros">
		<form action="" method="post" id="f">
			<?=img('static/img/icon/zoom.png')?>
			<?=form_input(array('name' => 'nombre', 'class' => 'tipns search', 'title' => 'Ingrese el nombre del archivo'))?>
			<?=form_input(array('name' => 'id_deuda', 'class' => 'tipns search', 'title' => 'Ingrese el id de deuda a anular'))?>			
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
