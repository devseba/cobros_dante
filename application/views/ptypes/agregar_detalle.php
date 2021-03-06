<?=form_open('pagos/agregar')?>
<div id="filter">
	<div class="yui-gb">
			<div class="pad">
				<h2>
					<?=$titulo?> ($<?=$total_pagar?>)
					<span>
						<?php echo img(array('src' => 'static/img/icon/sq_plus.png','class' => 'plus')) ?>
						<?php echo img(array('src' => 'static/img/icon/sq_minus.png','class' => 'minus'))  ?>
					</span>
				</h2>
				<?=form_hidden('total',$total_pagar)?>
			</div>
	<div id="duplicate">
			<div class="yui-u first">
				<div class="pad forms">
					<?=form_label('Tipo de pago','ptype_id')?>
					<?php echo isset($errors) ? $errors->on('ptype_id') : ''; ?>
					<? 	$pt = array();
						foreach($ptypes as $p) $pt[$p->id] = $p->tipo; ?>
					<?=form_dropdown('ptype_id[]', $pt, isset($t->ptype_id) ? $t->ptype_id : set_value('ptype_id'),'id="ptype_id"')?>
					<?=form_label('Importe','importe')?>
					<?php echo isset($errors) ? $errors->on('importe') : ''; ?>
					<?=form_input('subimporte[]', isset($t->importe) ? $t->importe : set_value('importe'),'id="importe" class="control"')?>
					<?=form_label('Fecha de vencimiento','vencimiento')?>
					<?php echo isset($errors) ? $errors->on('vencimiento') : ''; ?>
					<?=form_input('vencimiento[]', isset($t->vencimiento) ? $t->vencimiento->format('d/m/Y') : set_value('vencimiento'),' class="date"')?>
				</div>
			</div>
			<div class="yui-u">
				<div class="pad forms">
					<?=form_label('Banco','bank_id')?>
					<?php echo isset($errors) ? $errors->on('bank_id') : ''; ?>
					<? 	$bco = array( null => 'Ninguno');
						foreach($bancos as $b) $bco[$b->id] = $b->nombre; ?>
					<?=form_dropdown('bank_id[]', $bco, isset($t->bank_id) ? $t->bank_id : set_value('bank_id'),'id="bank_id"')?>
					<?=form_label('Nro de comprobante (nº cheque ó tarjeta)','nro_comprobante')?>
					<?php echo isset($errors) ? $errors->on('nro_comprobante') : ''; ?>
					<?=form_input('comprobante[]', '','id="nro_comprobante"')?>
				</div>
			</div>
			<div class="yui-u">
				<div class="pad forms">
					<?=form_label('Tarjeta','creditcard_id')?>
					<?php echo isset($errors) ? $errors->on('creditcard_id') : ''; ?>
					<? 	$trj = array( null => 'Ninguna');
						foreach($tarjetas as $tj) $trj[$tj->id] = $tj->nombre; ?>
					<?=form_dropdown('creditcard_id[]', $trj, isset($t->creditcard_id) ? $t->creditcard_id : set_value('creditcard_id'),'id="creditcard_id"')?>
					
					<?=form_label('Cantidad de cuotas','cuotas')?>
					<?php echo isset($errors) ? $errors->on('cuotas') : ''; ?>
					<?=form_input('cuotas[]', isset($t->cuotas) ? $t->cuotas : set_value('cuotas'),'id="cuotas"')?>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="pad forms">
<?=form_label('Observaciones','observaciones')?>
	<?php echo isset($errors) ? $errors->on('observaciones') : ''; 
	$data = array(
              'name'        => 'observaciones',
              'id'          => 'observaciones',
              'class'       => 'textareaplana',
              'value'       => isset($t->observaciones) ? $t->observaciones : set_value('observaciones')
            );

	echo form_textarea($data);?>
</div>
<div class="pad">
	<?=form_submit('enviar','Guardar')?>
	<?
		$url = $_SERVER['HTTP_REFERER']; //site_url('pagos');
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
<div id="template" style="display:none;">
	<div class="yui-gb"> 
		<div class="yui-u first">
			<div class="pad forms">
				<?=form_label('Tipo de pago','ptype_id')?>
				<?php echo isset($errors) ? $errors->on('ptype_id') : ''; ?>
				<? 	$pt = array();
					foreach($ptypes as $p) $pt[$p->id] = $p->tipo; ?>
				<?=form_dropdown('ptype_id[]', $pt, isset($t->ptype_id) ? $t->ptype_id : set_value('ptype_id'),'id="ptype_id"')?>
				<?=form_label('Importe','importe')?>
				<?php echo isset($errors) ? $errors->on('importe') : ''; ?>
				<?=form_input('subimporte[]', isset($t->importe) ? $t->importe : set_value('importe'),'id="importe" class="control"')?>
				<?=form_label('Fecha de vencimiento','vencimiento')?>
				<?php echo isset($errors) ? $errors->on('vencimiento') : ''; ?>
				<?=form_input('vencimiento[]', isset($t->vencimiento) ? $t->vencimiento->format('d/m/Y') : set_value('vencimiento'),' class="date"')?>
			</div>
		</div>
		<div class="yui-u">
			<div class="pad forms">
				<?=form_label('Banco','bank_id')?>
				<?php echo isset($errors) ? $errors->on('bank_id') : ''; ?>
				<?=form_dropdown('bank_id[]', $bco, isset($t->bank_id) ? $t->bank_id : set_value('bank_id'),'id="bank_id"')?>
				<?=form_label('Nro de comprobante (nº cheque ó tarjeta)','nro_comprobante')?>
				<?php echo isset($errors) ? $errors->on('nro_comprobante') : ''; ?>
				<?=form_input('comprobante[]', '','id="nro_comprobante"')?>
			</div>
		</div>
		<div class="yui-u">
			<div class="pad forms">
				<?=form_label('Tarjeta','creditcard_id')?>
				<?php echo isset($errors) ? $errors->on('creditcard_id') : ''; ?>
				<?=form_dropdown('creditcard_id[]', $trj, isset($t->creditcard_id) ? $t->creditcard_id : set_value('creditcard_id'),'id="creditcard_id"')?>
				<?=form_label('Cantidad de cuotas','cuotas')?>
				<?php echo isset($errors) ? $errors->on('cuotas') : ''; ?>
				<?=form_input('cuotas[]', isset($t->cuotas) ? $t->cuotas : set_value('cuotas'),'id="cuotas"')?>
			</div>
		</div>
</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('.plus').click(function(){
			var row = $('#template').clone().fadeIn('fast').appendTo('#filter');
			$('#filter #template').attr('id','duplicate');
			$('#filter div:hidden').fadeIn();
		});
		
		var total = Number($('input[name*=total]').val());
		
		function sumar()
		{
			var sum = 0;
			$('#filter input[name*=subimporte]').each(function ()
			{
				var val = $(this).val();
				val = val.replace(/,/gi, ".")
				
				sum += Number(val);
			});
			if(sum == total)
			{
				$('form').submit();
			}
			else
			{
				alert('La suma de los detalles debe ser igual al monto total a pagar: $'+ total);
				return false;
			}
		}
		
		$('.minus').click(function(){
			$('div[id*=duplicate]:last').fadeOut('fast',function(){ $(this).remove(); });
		});
		
		$('input:submit').click(function (e)
		{
			e.preventDefault();
			sumar();
		});
		
		$('#filter input.control').keyup(function()
		{
			var val = $(this).val();
			var originalValue = val;
			var valor = val.replace(/,/gi, ".");
			
			var msg="Solo puede ingresar números"; 
			
			if (val!='')
			{
				val = parseFloat(valor);
				
				if(val!=originalValue && val != valor){
					$(this).val(val);
					alert(msg);
				}
			}
			
		});
	});	
</script>
