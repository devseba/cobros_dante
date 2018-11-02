<!-- ========================================================================================== -->

<div style = "position:fixed; top:0px;">
	<?php

		$fechaLarga = $recibo->fecha->format('d/m/Y'); //anio-mes-dia
		$nom_alu = $student->apellido.','.$student->nombre;
		$division = $inscripto->course->course.' '.$inscripto->division->division;
		$num_recibo = str_pad($recibo->punto_v,4,"0",STR_PAD_LEFT)."-".str_pad($recibo->numero,8,"0",STR_PAD_LEFT);
		$matricula = $student->id;
		$domicilio = $student->domicilio
	?>
	<div id="div_cabecera" >
		<div style="float:left; width: 50%; margin-top:10px;">						
			<img align="left" src="<?php echo base_url('/static/img/logo.png'); ?>" height="100" width="100" style="margin-left: 10px; margin-top: 0px;margin-right: 5px">						
			<div style="width:90%;font-size:10px; text-align: center">SOCIEDAD ITALIANA </div>
			<div style="font-family: Verdana;font-style: oblique;font-size: 18px; text-align: center"> <p style="width:90%;">DANTE ALIGHIERI</p> </div>
			<div style="width:100%;font-size:10px; text-align: center"><strong>Av. José Ignacio de la Roza Oeste 1160</strong></div>
			<div style="width:10	0%;font-size:10px; text-align: center"><strong>Tel.: +54 0264 422-0082 - C.P. 5400 - San Juan</strong></div>
			<div style="margin-top: 8px; width:90%;font-size:14px;font-style: oblique; text-align: center">IVA EXENTO</div>
		</div>

		<div style="float:right; width: 50%; margin-top:10px">
			<div id="titulo">RECIBO C</div>
			<br>
			<div> 				
				<table width = "95%">
					<tr>
						<td align = "right"  style="font-family:Verdana;font-size:12px"><strong><?php echo "FECHA: ".$fechaLarga?></strong></td>
						<td align = "right" style="font-size:10px"><strong>&nbsp;</strong></td>
					</tr>
				</table>
			</div>

			<br>
			<div style="float:left; margin-left: 20px" width = "95%">
								
				<table >
					<tr>
						<td align = "left"  style="font-size:10px"><strong>Cuit: 30-51896700-9</strong></td>
					</tr>
					<tr>
						<td align = "left"  style="font-size:10px"><strong>Ing. Brutos: EXENTO</strong></td>
					</tr>
					<tr>
						<td align = "left"  style="font-size:10px"><strong>Inicio de Act.: 01/03/1960</strong></td>
					</tr>
				</table>
			</div>

			<div style="float:right;margin-right: 25px; font-size:14px; margin-top:10px" >
				N&deg; <?php echo $num_recibo; //este num viene directo de la libreria ws?>
			</div>
		</div>					
	</div>

	<div id="original" style = "width:715px">
		<div>			
			
			<table id="div_alu" width = "90%" >
				<tr >
					<td  style="font-size:10px" ><strong>MATR&Iacute;CULA: </strong> <?php echo $matricula?></td>
					<td  style="font-size:10px"><strong>ALUMNO: </strong> <?php echo $nom_alu?></td>
					<td  style="font-size:10px"><span><strong>CURSO: </strong><?php echo $division?></span></td>
				</tr>
				<tr><td style="font-size:8px">&nbsp;</td></tr>
			</table>

			<br>
			<!-- DETALLE DEL RECIBO -->
			<div id="div_detalle" width = "90%">
				<?=$tabla?>
			</div>
		</div>
		<div style="text-align: left;margin-left: 15px"><?echo (isset($leyenda))?$leyenda:"";?></div>
	</div>

	<div id="div_cae" style="margin-top: 2px;">
		<div style="float:left;width:50%">
			<center><label style="font-size:12px;display: block;"><i>COMPROBANTE AUTORIZADO - AFIP</i></label></center>
			<label style="display: block;"><?echo $codigo_barra?></label><br><br><br>
			<center><label style="font-size:10px;display: block;"><?echo $codigo ?></label></center>
		</div>
		<div style="float:right; margin-right:5%; margin-top:2%; font-size:12px;width:40%">
			<table width = "90%">
			<tr>
				<td><?php echo (isset($recibo->cae))?"<strong>CAE:</strong> ".$recibo->cae:''; ?></td>
			</tr>
			<tr><td style="font-size:8px">&nbsp;</td></tr>
			<tr>
				<td><?php echo "<strong>Venc. CAE:</strong> ".$fcae; ?></td>
			</tr>
			</table>
		</div>
	</div>
	<br><br><br><br><br>
	<hr style="border-bottom-style: dashed;">
</div><br />
<style>
	#div_cabecera{
		 width:715px;
		 height: 120px;
		 border: 2px solid;
    	 border-radius: 10px;
		}

	#original{
		 width:715px;
		 height: 300;
		 border: 2px solid;
    	 border-radius: 10px;

		}

	#titulo{
		font-size:16px;
		font-style: italic;
		font-weight: bold;
		color: #fff; 
		background-color: #000;
		border-radius: 7px;
		width: 30%;
		height: 22px;
		padding-top: 2px;
		margin-left: 28%;
	}

	#div_alu{
		margin-top: 10px;
		margin-left: 5%;
	}

	#div_detalle{
		margin-top: -5px;
		margin-left: 5%;
		height: 210px;
		/*min-height: 210px;*/
		max-height: 210px;
	}

	#div_totales{
		margin-top: 0px;
		margin-left: 5%;
		font-weight: bold;		
	}
</style>

