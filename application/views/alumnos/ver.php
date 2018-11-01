<div class="yui-gd">
	<div class="yui-u first">
		<div class="pad">
			<h2>Datos personales <?=anchor('alumnos/editar/'.$a->id, img('static/img/icon/pencil.png').' Modificar')?></h2>
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
	</div>
	<div class="yui-u">
		<div class="pad">
		<h2>Información relacionada</h2>
		<div class="accordion">
			<h3 class="slide">Inscripciones</h3>
			<div class="hide">
			<?=$inscripciones?>
				<div class="forms">
					<?=form_open('inscripciones/agregar')?>
						<table>
							<tr>
								<td>
									<?=form_label('Nivel','level_id')?>
									<?
										$n = array('Seleccione');
										foreach($niveles as $nv) $n[$nv->id] = $nv->nivel
									?>
									<?=form_dropdown('level_id', $n, '', 'id="level"')?>
								</td>
								<td>
									<?=form_label('Curso','course_id')?>
									<?
										$c = array('' => 'Seleccione');
										foreach($cursos as $cs) $c[$cs->id] = $cs->course.','.$cs->level_id;
									?>
									<?=form_dropdown('course_id', $c, '', 'id="course"')?>
								</td>
							</tr>
							<tr>
								<td>
									<?=form_label('División','division_id')?>
									<?
										$d = array('Seleccione');
										foreach($divisiones as $dv) $d[$dv->id] = $dv->division
									?>
									<?=form_dropdown('division_id', $d)?>
								</td>
								<td>
									<?=form_label('Ciclo lectivo','ciclo_lectivo')?>
									<?
										$cl = array('Seleccione', (date('Y')-1) => (date('Y')-1), date('Y') => date('Y'), (date('Y')+1) => (date('Y')+1), (date('Y')+2) => (date('Y')+2), (date('Y')+3) => (date('Y')+3));
									?>
									<?=form_dropdown('ciclo_lectivo', $cl)?>
								</td>
							</tr>
						</table>
						<input type="hidden" value="<?=$a->id?>" name="student_id" id="student_id" />
						<br/>
						<?=form_submit(array('value' => 'Agregar inscripción', 'class' => 'button'))?>
					<?=form_close()?>
				</div>
			</div>
			<h3 class="slide">Deudas</h3>
			<div class="hide" id="cuotas">
			<? $hidden = array('student_id' => $a->id, 'importe' => 0);?>
			<?=form_open('ptypes/agregar_detalle', array('id' => 'pagar'),$hidden)?>
			<?=$deudas?>
			<div class="forms">
				<?=form_label('Alterar nro comprobante','nro_comprobante')?>
				<?//=form_input(array('name' => 'nro_comprobante', 'value' => $nro_comprobante, 'id' => 'nro_comprobante'))?>
				<?
					$comprobantes = array();
					$comprobantes[$nro_comprobante] = $nro_comprobante;
					$comprobantes[$nro_comprobante2] = $nro_comprobante2;
				?>
				<?=form_dropdown('nro_comprobante', $comprobantes, '', 'id="nro_comprobante"')?>				
			</div>
			<p><?=form_submit('ptypes/agregar_detalle', 'Seleccione las cuotas a pagar','class="button" id="total" disabled')?></p>
			<?=form_close()?>
			<div id="results"></div>
			</div>
			<!-- BOLETAS GENERADAS -->
			<h3 class="slide">Boletas pendientes generadas</h3>
			<div class="hide" id="pagos">
			<?=$boletas_pendientes?>			
			</div>
			<h3 class="slide">Pagos realizados (Últimos 10)</h3>
			<div class="hide" id="pagos">
			<?=$pagos?>
			<p>
				<?=anchor('historial/'.$a->id, 'Ver historial completo de pagos', 'class="button"')?>
				<? 
					if($eventual){
						echo anchor('pagos/pago_eventual/'.$eventual['student_id'].'/'.$eventual['course_id'].'/'.$eventual['inscription_id'], 'Pago eventual');
					}
				?>
			</p>
			</div>
			<h3 class="slide">Tutores</h3>
			<div class="hide">
			<?=$tutores;?>
			<div class="forms">
				<?=form_open('tutores/asignar')?>
				    <?=form_label('Escriba DNI del tutor','tutor')?>
					<?=form_input(array('name'=>'tutor','id'=>'tutor'))?>
					<input type="hidden" id="tutor_id" name="tutor_id" />
					<input type="hidden" id="student_id" name="student_id" value="<?=$a->id?>" />
					<p>
						<?=form_submit('asignar','Asignar tutor')?>
						<?=anchor('tutores/agregar', 'Crear nuevo tutor')?>
					</p>
				<?=form_close()?>
				
				<script type="text/javascript" src="<?=site_url('static/js/autocomplete.js')?>"></script>
				<script type="text/javascript">
					$("#tutor").autocomplete({
						source: "<?php echo site_url('tutores/buscar'); ?>", 
						method: "POST",
						select: function(event, ui) {
							$('#tutor_id').val(ui.item.id);
							$('#tutor').val(ui.item.value);
						}
					});
				</script>
			</div>
			</div>
			<h3 class="slide">Becas</h3>
			<div class="hide">
				<?=$becas?>
				<p><? if($becas_a_asig >0) echo anchor('descuentos/agregar/'.$a->id, 'Agregar descuento', 'class="button"')?></p>
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
