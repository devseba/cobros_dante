<div class="yui-gb">
	<div class="pad">
		<h2><?=$titulo?></h2>
	</div>
	<div class="yui-u first">
		<div class="pad forms">
			<?=form_open($action)?>
			<?=form_label('Tipo de pago','tipo')?>
			<?php echo isset($errors) ? $errors->on('tipo') : ''; ?>
			<?=form_input('tipo', isset($a->tipo) ? $a->tipo : set_value('tipo'),'id="tipo"')?>
		</div>
	</div>
</div>
<div class="pad">
	<?=form_submit('enviar','Guardar')?>
	<?
		$url = site_url('ptypes');
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
