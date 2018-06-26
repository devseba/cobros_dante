<div class="yui-gb">
	<div class="pad">
		<h2><?=$titulo?></h2>
	</div>
	<div class="yui-u first">
		<div class="pad forms">
			<?
				if(isset($id)){
					echo form_open('descuentos/editar/'.$alumno.'/'.$id);
				}
				else
					echo form_open('descuentos/agregar/'.$alumno); ?>
			<?=form_label('Concepto','concept_id')?>
			<?php echo isset($errors) ? $errors->on('concept_id') : ''; ?>
			<?php $ds = array(); foreach($deudas as $d) $ds[$d->amount_id] = $d->amount->concept->concepto.' '.$d->amount->ciclo_lectivo.' - '.$d->amount->course->course; ?>
			<? if(isset($id)):?>
				<?=form_dropdown('amount_id', $ds, isset($a->amount_id) ? $a->amount_id : set_value('amount_id'),'id="amount_id"')?>
			<? else:?>
				<?=form_multiselect('amount_id[]', $ds, isset($a->amount_id) ? $a->amount_id : set_value('amount_id'),'id="amount_id"')?>
			<? endif ?>
			<?=form_label('Descuento en %','porcien_descuento')?>
			<?php echo isset($errors) ? $errors->on('porcien_descuento') : ''; ?>
			<?=form_input('porcien_descuento', isset($a->porcien_descuento) ? $a->porcien_descuento : set_value('porcien_descuento'),'id="porcien_descuento"')?>			
		</div>
	</div>
</div>
<div class="pad">
	<?=form_submit('enviar','Guardar')?>
	<?
		$url = site_url('alumnos/ver/'.$alumno);
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
