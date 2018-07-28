<style>
	.borde-codigo-link{
		border-style: solid;
		padding: 20px 0px 20px 20px;
		margin-left: 10px;
		color: red;
		font-weight: bold;
		border-color: red;
	}
</style>
<div class="yui-gd">
	<div class="yui-u first">
		<div class="pad">
			<h2>Datos personales</h2>
			<dl>
				<dt>Nombre</dt>
				<dd><?=$a->nombre.' '.$a->apellido?></dd>
				<dt>Fecha de nacimiento</dt>
				<dd><?=$a->fecha_nacimiento->format('d/m/Y')?></dd>
				<dt>Lugar de nacimiento</dt>
				<dd><?=$a->lugar_nacimiento?></dd>
				<dt>Sexo</dt>
				<dd><?=$a->sexo?></dd>
				<dt>Grupo sanguíneo</dt>
				<dd><?=$a->grupo_sanguineo?></dd>
				<dt>Documento</dt>
				<dd><?=$a->tipo_documento.' '.$a->nro_documento?></dd>
				<dt>Teléfono</dt>
				<dd><?=$a->telefono?></dd>
				<dt>Celular</dt>
				<dd><?=$a->celular?></dd>
				<dt>Domicilio</dt>
				<dd><?=$a->domicilio?></dd>
				<dt>Ciudad</dt>
				<dd><?=$a->city->nombre?></dd>
				<dt>Provincia</dt>
				<dd><?=$a->city->state->provincia?></dd>
				<dt>País</dt>
				<dd><?=$a->city->state->country->pais?></dd>
				<dt>Nacionalidad</dt>
				<dd><?=$a->nacionalidad?></dd>
			</dl>
		</div>
		<br><br>
		<div class="borde-codigo-link"><!-- codigo link -->
			<h1><b>CODIGO DE LINK PAGOS:</b><?=$codigo_link?></h1>
		</div>
	</div>
	<div class="yui-u">
		<div class="pad">
		<h2>Información relacionada</h2>
		<div class="accordion">
			<h3 class="slide">Deudas</h3>
			<div class="hide" id="cuotas">
			<? $hidden = array('student_id' => $a->id, 'importe' => 0);?>
			<?=form_open('pagos/agregar_pago_tutor', array('id' => 'pagar'),$hidden)?>
			<?=$deudas?>
			<p>
				<?
				//$url = $_SERVER['HTTP_REFERER']; 
				$url = site_url('pagos');
				$js = "window.location='".$url."'";
				/*$atri = array(
						'disabled'=>'disabled',
						'content'=>'Seleccione las cuotas a pagar',
						'class'=>"button",
						'name'=>"total",
						'id'=>"total",
						'onClick'=>$js
						);*/
				//echo form_button($atri);
				?>				
			</p>
			<p><?=form_submit('pagos/agregar', 'Seleccione las cuotas a pagar','class="button" id="total" onClick ="'.$js.'" disabled')?></p>
			<?=form_close()?>
			<div id="results"></div>
			</div>
			<!-- BOLETAS GENERADAS -->
			<h3 class="slide">Boletas pendientes generadas</h3>
			<div class="hide" id="pagos">
			<?=$boletas_pendientes?>			
			</div>
			<!-- PAGOS REALIZADOS -->
			<h3 class="slide">Pagos realizados (Últimos 10)</h3>
			<div class="hide" id="pagos">
			<?=$pagos?>
			<p>
				<?=anchor('historial/'.$a->id, 'Ver historial completo de pagos', 'class="button"')?>
			</p>			
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="<?php echo base_url().'static/js/chained.js'; ?>"></script>
<script type="text/javascript">
	jQuery(document).ready(function()
	{
		$('#course').chainedTo('#level');
		
		$('.accordion .slide').click(function()
		{
			$(this).next().toggle('blind','','fast');
			return false;
		}).next().hide();
		
		$('input.small').keyup(function()
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

		$('input.small').change(function()
		{
			// value is present
			_alertColor = 'red';
			var tval=$.trim($(this).val());
			if (tval=='') return true;
			reg=/^0*/;
			tval=tval.replace(reg,'')

			if (tval!='') 
			val=parseFloat(tval);
			else
			val=0;
			var min=parseFloat($(this).attr('min'));
			var max=parseFloat($(this).attr('max'));
			var msg="";

			if(min!='' && max !='')
			{
				msg='El valor del campo debería ser entre '+min + ' y ' + max + '.' ;
			}
			else{
				if(min!='') {msg='El valor debe ser mayor que '+min +'.';}
				else{
					if(max!='') {msg='El valor no puede ser mayor al saldo $'+ max +'.';}
				}
			}
			if(min!='')
			{
				if (min>val)
				{
					alert(msg);
					$(this).val('');
					$(this).css('border-color',_alertColor);
				}
				else $(this).css('border-color','green');
			}

			if (max!='')
			{
				if (val>max)
				{
					alert(msg);
					$(this).val('');
					$(this).css('border-color',_alertColor);
				}
				else $(this).css('border-color','green');
			}
			
			calculateSum();
		});
		
		$(".small").each(function()
		{
			$(this).keyup(function()
			{
                calculateSum();
            });
        });
		
		$('.check').click(function()
		{
			if($(this).attr('checked') == false)
			{
				$(this).prev().val('').attr('readonly',false);
				calculateSum();
			}
			else
			{
				$(this).prev().val($(this).val()).attr('readonly',true);
				calculateSum();
			}
		});
	});
	
	function calculateSum()
	{
         var sum = 0;
        //iterate through each textboxes and add the values
        $(".small").each(function()
		{ 
            var val = this.value;
			val = val.replace(/,/gi, ".");
            //add only if the value is number
            if(!isNaN(val) && val.length!=0)
			{
                sum += parseFloat(val);
            }
 
        });
        //.toFixed() method will roundoff the final sum to 2 decimal places
		if(sum > 0)
		{
			$("#total").val('Total a pagar $ '+sum.toFixed(2)).attr('disabled',false);
			$("input[name=importe]").val(sum.toFixed(2));
		}
		else
		{
			$('#total').val('Seleccione las cuotas a pagar').attr('disabled',true);
			$("input[name=importe]").val(0);
		}        
    }
</script>
