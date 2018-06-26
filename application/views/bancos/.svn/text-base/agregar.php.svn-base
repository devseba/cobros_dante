<div class="yui-gb">
	<div class="pad">
		<h2><?=$titulo?></h2>
	</div>
	<div class="yui-u first">
		<div class="pad forms">
			<?=form_open($action)?>
			<?=form_label('Nombre del banco','nombre')?>
			<?php echo isset($errors) ? $errors->on('nombre') : ''; ?>
			<?=form_input('nombre', isset($a->nombre) ? $a->nombre : set_value('nombre'),'id="nombre"')?>
		</div>
	</div>
</div>
<div class="pad">
	<?=form_submit('enviar','Guardar')?>
	<?
		$url = site_url('paises');
		$js = "window.location='".$url."'";
		$atri = array(
				'content'=>'Cancelar',
				'class'=>"button red",
				'name'=>"cancelar",
				'id'=>"cancelar",
				'value'=>"Cancelar",
				'onClick'=>$js
				);
		echo form_button($atri);
		//form_button('cancelar','Cancelar','class="button red"'); ?>
	<?=form_reset('limpiar','Limpiar formulario')?>
	<?=form_close()?>
</div>
