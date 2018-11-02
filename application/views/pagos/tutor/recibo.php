<style>
	@font-face {
	    font-family: "ocrb-regular";
	    src: url(/template/fonts/OcrB-Regular.ttf) format("truetype");
	}

	@media all {
		body{
			width: 960px;
		}

		h1{
			font-size: 16pt;
		}

	    div.saltopagina{
	       display: none;
	    }

		.code{
			position:relative;
			float:left;
			/*top: 0px;
			left: -110px;*/
			top:-10px;
			left: 80px;			
			text-align: center;
			font: 90% monospace;
			width: 130mm;
			background-color: white;
			height: 5mm;
			letter-spacing: 0.2em;
		}

		.codigo-barra{
			width: 50%;
		}

		.cabecera{
			position: relative;
			display: block;
			width: 100%;
			height: 150px;
			margin-bottom: 20px;		
		}

		.cab-left{
			float: left;
			display: inline-block;
			width: 20%;
			height: 150px;
		}

		.cab-center{
			float: left;
			display: inline-block;
			width: 35%;
			height: 150px;
			padding-left: 30px;
			line-height: 40px;
		}

		.cab-right{
			position: relative;
			display: inline-block;
			float: right;
			width: 35%;
			height: 150px;
			border-style: solid;
			border-width: 1px;
			padding: 5px 15px 5px 5px;
			line-height: 30px;
		}
		.cuerpo{
			position: relative;
			display: block;
		}

		.pos-cabecera{
			line-height: 30px;
			font-size: 12pt
		}

		.cab-barcode{
			position: relative;
			display: block;
			width: 100%;
			height: 100px;
			margin-bottom: 20px;		
		}

		.cab-barcode-left{
			float: left;
			display: inline-block;
			width: 30%;
			height: 100px;
			text-align: left;
		}

		.cab-barcode-center{
			float: left;
			display: inline-block;
			width: 30%;
			height: 100px;
			padding-left: 30px;
			line-height: 20px;
		}

		.cab-barcode-right{
			position: relative;
			display: inline-block;
			width: 30%;
			height: 100px
			padding: 5px 15px 5px 5px;
			text-align: left;
			line-height: 20px;
		}

		.linea { 
			border-bottom-style: dashed; 
			border-bottom-width: 2px; 
		}		
	}
	   
	@media print{

		div.saltopagina{ 
		  display:block; 
		  page-break-before:always;
		}

		.code{
			position:relative;
			top:-10px;
			left: 80px;
			text-align: center;
			font: 90% monospace;
			width: 130mm;
			background-color: white;
			height: 5mm;
			letter-spacing: 0.2em;
		}

		.codigo-barra{
			width: 50%;
		}

		.cabecera{
			position: relative;
			display: block;
			width: 100%;
			height: 150px;
			margin-bottom: 20px;		
		}

		.cab-left{
			float: left;
			display: inline-block;
			width: 20%;
			height: 150px;
		}

		.cab-center{
			float: left;
			display: inline-block;
			width: 35%;
			height: 150px;
			padding-left: 30px;
			line-height: 40px;
		}

		.cab-right{
			position: relative;
			display: inline-block;
			float: right;
			width: 35%;
			height: 150px;
			border-style: solid;
			border-width: 1px;
			padding: 5px 15px 5px 5px;
			line-height: 30px;
		}
		.cuerpo{
			position: relative;
			display: block;
		}

		.pos-cabecera{
			line-height: 30px;
			font-size: 12pt
		}

		.cab-barcode{
			position: relative;
			display: block;
			width: 100%;
			height: 100px;
			margin-bottom: 20px;		
		}

		.cab-barcode-left{
			float: left;
			display: inline-block;
			width: 30%;
			height: 100px;
			text-align: left;
		}

		.cab-barcode-center{
			float: left;
			display: inline-block;
			width: 30%;
			height: 100px;
			padding-left: 30px;
			line-height: 20px;
		}

		.cab-barcode-right{
			position: relative;
			display: inline-block;
			width: 30%;
			height: 100px
			padding: 5px 15px 5px 5px;
			text-align: left;
			line-height: 20px;
		}

		.linea { 
			border-bottom-style: dashed; 
			border-bottom-width: 2px; 
		}	   
	}
</style>
<body>
	<div class="cabecera">
		<div class="cab-left">
			<img src="<?= base_url('static/img/logofinaldantea.png') ?>" alt="" width="140px"/>	
		</div>
		<div class="cab-center">
			<p><h1>COLEGIO DANTE ALIGHIERI</h1></p>
			<p>Av. José Ignacio de la Roza 1160 (O)</p>
			<p>Tel: 4220082</p>
		</div>
		<div class="cab-right">
			<p>Alumno: <?= $student->apellido." ".$student->nombre?></p>
			<p>D.N.I.: <?=$student->nro_documento?></p>
			<p>Nivel: <?= $nivel?></p>
			<p>Curso: <?= $curso->course." ".$division?></p>
		</div>
	</div>
	<div class="pos-cabecera">
		<p>Área de notificaciones</p>
		<p style=""><b>Los recibos deben ser abonados antes del 3° vencimiento, caso contrario deberán dirigirse a tesorer&iacute;a del colegio.</b></p>
		<p><b>MEDIOS DE PAGO:</b><b>Bco San Juan, </b><b>Link Pagos:</b> Pague ingresando al Home Banking de su Banco, desde un cajero LINK, o desde Link Celular, las 24 horas todos los días del año. Para mayor información ingrese a www.linkpagos.com.ar</p>
		<?echo "<div><b>CODIGO DE LINK PAGOS:</b> ".$codigo_link."</div>";?>
		<p>Consultas al email sociedantealighieri@speedy.com.ar</p>
		<p>Defensa al consumidor San Juan Tel. 0800-3333366/0264-4306400</p>
		<p style="font-size: 8pt">
			<i>Impreso desde la web de AUTOGESTIÓN DE ALUMNOS</i>
		</p>
	</div>			

	<?
	$i = 0;
	//http://dantealighierisanjuan.edu.ar/cobros_prueba/pagos/recibo_para_tutor/30352-30353-30354-30355-30356-30357-30358-30359-30360-30361-30362-30363-30364-30365-30366
	foreach ($barcode as $key => $value) {
		if($i == 3){
			$i=0;?>
			<div class="cabecera" style="margin-top: 60px">
				<div class="cab-left">
					<img src="<?= base_url('static/img/logofinaldantea.png') ?>" alt="" width="140px"/>	
				</div>
				<div class="cab-center">
					<p><h1>COLEGIO DANTE ALIGHIERI</h1></p>
					<p>Av. José Ignacio de la Roza 1160 (O)</p>
					<p>Tel: 4220082</p>
				</div>
				<div class="cab-right">
					<p>Alumno: <?= $student->apellido." ".$student->nombre?></p>
					<p>D.N.I.: <?=$student->nro_documento?></p>
					<p>Nivel: <?= $nivel?></p>
					<p>Curso: <?= $curso->course." ".$division?></p>
				</div>
			</div>
			<div class="pos-cabecera" style="margin-top: 130px">
				<p>Área de notificaciones</p>
				<p style=""><b>Los recibos deben ser abonados antes del 3° vencimiento, caso contrario deberán dirigirse a tesorer&iacute;a del colegio.</b></p>
				<p><b>MEDIOS DE PAGO:</b> Bco San Juan.</p>
				<p>Consultas al email sociedantealighieri@speedy.com.ar</p>
				<p>Defensa al consumidor San Juan Tel. 0800-3333366/0264-4306400</p>
				<p style="font-size: 8pt">
					<i>Impreso desde la web de AUTOGESTIÓN DE ALUMNOS</i>
				</p>
			</div>
		<?}?>
		<div style='margin-top:30px;height: 210px' align='center' class='cuerpo'>
			<div class='cab-barcode'>
				<div class="cab-barcode-left">
					<p><H1>VOLANTE DE PAGO Nº:<?= $detalle[$key]["nro_recibo"]?></H1></p>
					<br><br>
					<p><?=$detalle[$key]["concepto"]?></p>
				</div>
				<div class="cab-barcode-center">
					<p><b>Vencimiento</b></p>
					<p>1º Vto. <?=$detalle[$key]["fecha"]['fecha1']?></p>
					<p>2º Vto. <?=$detalle[$key]["fecha"]['fecha2']?></p>
					<p>3º Vto. <?=$detalle[$key]["fecha"]['fecha3']?></p>
				</div>
				<div class="cab-barcode-right">
					<p><b>Importe</b></p>
					<p>$ <?=$detalle[$key]["importe"]['importe1']?></p>
					<p>$ <?=$detalle[$key]["importe"]['importe2']?></p>
					<p>$ <?=$detalle[$key]["importe"]['importe3']?></p>
				</div>
			</div><?
		//echo $generatorHTML->getBarcode($temp, $generatorHTML::TYPE_INTERLEAVED_2_5,$widthFactor,$height);
		//echo $generatorHTML->getBarcode($temp, $generator::TYPE_CODE_128,$widthFactor,$height);
		//echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode('$temp', $generator::TYPE_CODE_128)) . '">';
		echo "<div class='barcode'>";
		echo $value["codigoBarra"];
		echo "<div class='code'>".$value["codigo"]."</div>";
		echo "</div>";
		echo "</div>";
		
		if($key != (count($barcode) - 1)){
			echo "<br>";
			echo "<div class='linea'></div>";
			echo "<br>";
		}		
		$i++;
	}?>
	</div>
</body>
