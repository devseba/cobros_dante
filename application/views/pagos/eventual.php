<div class="yui-gb">
	<div class="pad">
		<h2><?=$titulo?></h2>
	</div>
	<div class="yui-u first">
		<div class="pad forms">
			<?=form_open(); ?>
			<?=form_label('Concepto','concepto')?>
			<?=form_input('concepto', isset($a->concepto) ? $a->concepto : set_value('concepto'),'id="concepto"')?>
			<?php echo isset($errors) ? $errors->on('concepto') : ''; ?>
			<?=form_label('Importe','importe')?>
			<?=form_input('importe', isset($a->importe) ? $a->importe : set_value('importe'),'id="importe"')?>
			<?=form_label('Número de comprobante','nro_comprobante')?>
			<?=form_input('nro_comprobante', isset($nro_comprobante) ? $nro_comprobante : set_value('nro_comprobante'),'id="nro_comprobante"')?>
			<?=form_label('Cuotas','cuotas')?>
			<?
				$ps = array("1","2","3","4");
			?>
			<?=form_dropdown('cuotas_num',$ps,isset($filtros['usuario']) ? $filtros['usuario'] : set_value('user_id'),'id="cuotas_num" class="tipns" title=""')?>
			<?=form_label('Pagado?','pagado')?>
			<?=form_checkbox('pagado',1)?>
		</div>
	</div>
</div>
<div class="pad">
	<?=form_submit('enviar','Guardar')?>
	<?
		$url = site_url('alumnos/ver/');
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
		?>
	<?=form_reset('limpiar','Limpiar formulario')?>
	<?=form_close()?>
</div>
