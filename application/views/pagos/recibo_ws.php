<!-- ========================================================================================== -->

<div style = "position:fixed; top:0px;">

		<?php		

			$fechaLarga = $pago->fecha->format('d/m/Y'); //anio-mes-dia
			$nom_alu = $student->apellido.','.$student->nombre;
			$division = $inscripto->course->course.' '.$inscripto->division->division;
			$num_recibo = $pago->nro_comprobante;
			$matricula = $student->id;
			$domicilio = $student->domicilio
		?>
				<div id="div_cabecera" >

					<div style="float:left; width: 50%; margin-top:10px;">						
						<img align="left" src="<?php echo base_url('/static/img/logo.png'); ?>" height="100" width="100" style="margin-left: 10px; margin-top: 0px;margin-right: 5px">						
						<div style="width:90%;font-size:18px;">COLEGIO </div>
						<div style="font-family: Verdana;font-style: oblique;font-size: 18px;"> <p style="width:90%;">DANTE ALIGHIERI</p> </div>
						<div style="width:100%;font-size:10px;"><strong>Av. José Ignacio de la Roza Oeste 1160</strong></div>
						<div style="width:10	0%;font-size:10px;"><strong>Tel.: +54 0264 422-0082 - C.P. 5400 - San Juan</strong></div>
						<div style="margin-top: 8px; width:90%;font-size:14px;font-style: oblique;">IVA EXENTO</div>
					</div>

					<div style="float:right; width: 50%; margin-top:10px">
						<div id="titulo">RECIBO C</div>
						<br>
						<div> 				
							<table width = "95%">
								<tr>
									<td align = "right"  style="font-family:Verdana;font-size:12px"><strong><?php echo "FECHA: ".$fechaLarga?></strong></td>
									<td align = "right"  style="font-size:10px"><strong>&nbsp;</strong></td>
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
									<td align = "left"  style="font-size:10px"><strong>Ing. Brutos:</strong></td>
								</tr>
								<tr>
									<td align = "left"  style="font-size:10px"><strong>Inicio de Act.:</strong></td>
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
						<table width = "100%">
							<?=$tabla?>
						</div>			
					</div>
				</div>

				<div id="div_cae" style="margin-top: 2px;">
					<div style="float:left;">
						<p style="font-size:12px"><i>COMPROBANTE AUTORIZADO - AFIP</i></p> 
						<div> 
							<!--<img alt="testing" src="<?//php echo base_url('/static/barcode/barcode.php');?>?text=<?php echo $codigoBarras; ?>&size=30&codetype=Code128&sizefactor=0.83&print=true" />-->
						<?php //echo $codigoBarras ?> </div>
					</div>
					<div style="float:right; margin-right:5%; margin-top:2%; font-size:12px">
						<table width = "90%">
						<tr>
							<td width = "80%" ><?php echo "CAE: ".(isset($recibo->cae))?$recibo->cae:'1234123412341234'; ?></td>
						</tr>
						<tr><td style="font-size:8px">&nbsp;</td></tr>
						<tr>
							<td width = "80%" ><?php echo "Venc. CAE: ".(isset($recibo->fcae))?$recibo->fcae:'15/11/2018'; ?></td>
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

