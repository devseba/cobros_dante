<style>
	@font-face {
	    font-family: "ocrb-regular";
	    src: url(/template/fonts/OcrB-Regular.ttf) format("truetype");
	}

	@media all {
	   div.saltopagina{
	      display: none;
	   }

		.code{
			position:relative;
			top:-33px;
			font: 105% monospace;
			width: 160mm;
			background-color: white;
			height: 5mm;
			letter-spacing: 0.2em;
		}

		.codigo-barra{
			width: 50%;
		}  
	}
	   
	@media print{

		div.saltopagina{ 
		  display:block; 
		  page-break-before:always;
		}

		.code{
			position:relative;
			top:-33px;
			font: 105% monospace;
			width: 140mm;
			background-color: white;
			height: 5mm;
			letter-spacing: 0.1em;
		}

		.codigo-barra{
		}	   
	}
</style>
<?php
function digito_verificador($codigo){
	$lenght = strlen($codigo);
	$par = 0;
	$impar = 0;
	//Hago la suma de los impares y pares por separado
	for($i=0;$i<$lenght;$i++){
		if(($i%2)==0){//posicion impar en el codigo de barras
			$impar = ($impar + $codigo[$i]);
		}
		else{//posicion par en el codigo de barras
			$par = ($par + $codigo[$i]);
		}
	}
	//echo $impar;
	//echo "<br>";
	//echo $par;
	//echo "<br>";
	//Multiplico por 3 el impar y se lo suma al par
	//Despues le aplico el modulo 10
	//Por ultimo resto 10 menos el resultado anterior
	$resultado = ($par + ($impar * 3)) % 10;
	//echo $resultado;
	if($resultado == 0){
		return 0;
	}
	else{
		return 10 - $resultado;
	}

	return $resultado;
}


include('src/BarcodeGenerator.php');
include('src/BarcodeGeneratorPNG.php');
include('src/BarcodeGeneratorHTML.php');
include('src/BarcodeGeneratorSVG.php');

$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
$generatorHTML = new Picqer\Barcode\BarcodeGeneratorHTML();
$generatorSVG = new Picqer\Barcode\BarcodeGeneratorSVG(); 
$widthFactor = 2;
$height = 23;
//echo "<h1 align='center'>EJEMPLOS CODIGOS DE BARRA</h1>";
/*echo "<br><br>";
echo "<br><br>";
echo "<br><br>";
echo "<br><br>";
echo "<br><br>";
echo "<br><br>";
echo "<br><br>";
echo "<br><br>";
echo "<br><br>";
echo "<br><br>";
echo "<br><br>";*/
/* CUOTA AGOSTO 2016 */
$campo = array();
$campo[] = "7009"; //campo 1 Codigo identificacion 4 dig
//$campo[] = "46071344";//campo 2 Documento alumno 8 dig
$campo[] = "00003185";//campo 3 Numero de recibo 8 dig
//$campo[] = "0000054331";//campo 4 codigo de debts 10 dig
//$campo[] = "082016";//campo 5 Cuota 6 dig
$campo[] = "0100000";//campo 6 importe 1er vto 7 dig
$campo[] = "151216";//campo 7 Fecha 1er vto 6 dig
$campo[] = "0110000";//campo 8 Importe 2do vto
$campo[] = "201216";//campo 9 Fecha 2do vto
$campo[] = "0120000";//campo 10 Importe 3er vto
$campo[] = "211216";//campo 11 Fecha 3er vto
//$campo[] = rand(0,9);//campo 12 Digito verificador

$temp = "";
foreach ($campo as $key => $value) {
	$temp .= $value;
}

$temp .= digito_verificador($temp);

echo "<div style='margin-top:0px' align='center'>";
//echo $generatorHTML->getBarcode($temp, $generatorHTML::TYPE_INTERLEAVED_2_5,$widthFactor,$height);
echo $generatorHTML->getBarcode($temp, $generator::TYPE_CODE_128,$widthFactor,$height);
echo "<br>";
echo "<div class='code'>".$temp."</div>";
echo "</div>";
echo "<br>";
/*******************************************************************************************/

/* MATRICULA 2016 */
$campo = array();
$campo[] = "7009"; //campo 1 Codigo identificacion 4 dig
//$campo[] = "46071344";//campo 2 Documento alumno 8 dig
$campo[] = "00003121";//campo 3 Numero de recibo 8 dig
//$campo[] = "0000054331";//campo 4 codigo de debts 10 dig
//$campo[] = "082016";//campo 5 Cuota 6 dig
$campo[] = "0166000";//campo 6 importe 1er vto 7 dig
$campo[] = "150316";//campo 7 Fecha 1er vto 6 dig
$campo[] = "0182600";//campo 8 Importe 2do vto
$campo[] = "200316";//campo 9 Fecha 2do vto
$campo[] = "0199200";//campo 10 Importe 3er vto
$campo[] = "210316";//campo 11 Fecha 3er vto
//$campo[] = rand(0,9);//campo 12 Digito verificador

$temp = "";
foreach ($campo as $key => $value) {
	$temp .= $value;
}

$temp .= digito_verificador($temp);

echo "<div style='margin-top:0px' align='center'>";
//echo $generatorHTML->getBarcode($temp, $generatorHTML::TYPE_INTERLEAVED_2_5,$widthFactor,$height);
echo $generatorHTML->getBarcode($temp, $generator::TYPE_CODE_128,$widthFactor,$height);
echo "<br>";
echo "<div class='code'>".$temp."</div>";
echo "</div>";
echo "<br>";
/*******************************************************************************************/

/* LIBRETA SECUNDARIA 2016 */
$campo = array();
$campo[] = "7009"; //campo 1 Codigo identificacion 4 dig
//$campo[] = "46071344";//campo 2 Documento alumno 8 dig
$campo[] = "00003122";//campo 3 Numero de recibo 8 dig
//$campo[] = "0000054331";//campo 4 codigo de debts 10 dig
//$campo[] = "082016";//campo 5 Cuota 6 dig
$campo[] = "0113500";//campo 6 importe 1er vto 7 dig
$campo[] = "150316";//campo 7 Fecha 1er vto 6 dig
$campo[] = "0124850";//campo 8 Importe 2do vto
$campo[] = "200316";//campo 9 Fecha 2do vto
$campo[] = "0136200";//campo 10 Importe 3er vto
$campo[] = "210316";//campo 11 Fecha 3er vto
//$campo[] = rand(0,9);//campo 12 Digito verificador

$temp = "";
foreach ($campo as $key => $value) {
	$temp .= $value;
}

$temp .= digito_verificador($temp);

echo "<div style='margin-top:0px' align='center'>";
//echo $generatorHTML->getBarcode($temp, $generatorHTML::TYPE_INTERLEAVED_2_5,$widthFactor,$height);
echo $generatorHTML->getBarcode($temp, $generator::TYPE_CODE_128,$widthFactor,$height);
echo "<br>";
echo "<div class='code'>".$temp."</div>";
echo "</div>";
echo "<br>";
/*******************************************************************************************/
//echo "<div class='saltopagina'></div>";

/*******************************************************************************************/

/* MATERIAL DIDACTICO 2016 */
$campo = array();
$campo[] = "7009"; //campo 1 Codigo identificacion 4 dig
//$campo[] = "46071344";//campo 2 Documento alumno 8 dig
$campo[] = "00003123";//campo 3 Numero de recibo 8 dig
//$campo[] = "0000054331";//campo 4 codigo de debts 10 dig
//$campo[] = "082016";//campo 5 Cuota 6 dig
$campo[] = "0140000";//campo 6 importe 1er vto 7 dig
$campo[] = "150916";//campo 7 Fecha 1er vto 6 dig
$campo[] = "0154000";//campo 8 Importe 2do vto
$campo[] = "200916";//campo 9 Fecha 2do vto
$campo[] = "0168000";//campo 10 Importe 3er vto
$campo[] = "210916";//campo 11 Fecha 3er vto
//$campo[] = rand(0,9);//campo 12 Digito verificador

$temp = "";
foreach ($campo as $key => $value) {
	$temp .= $value;
}

$temp .= digito_verificador($temp);

echo "<div style='margin-top:0px' align='center'>";
//echo $generatorHTML->getBarcode($temp, $generatorHTML::TYPE_INTERLEAVED_2_5,$widthFactor,$height);
echo $generatorHTML->getBarcode($temp, $generator::TYPE_CODE_128,$widthFactor,$height);
echo "<br>";
echo "<div class='code'>".$temp."</div>";
echo "</div>";
echo "<br>";
/*******************************************************************************************/

/* LIBRO ITALIANO 2015 */
$campo = array();
$campo[] = "7009"; //campo 1 Codigo identificacion 4 dig
//$campo[] = "46071344";//campo 2 Documento alumno 8 dig
$campo[] = "00003223";//campo 3 Numero de recibo 8 dig
//$campo[] = "0000054331";//campo 4 codigo de debts 10 dig
//$campo[] = "082016";//campo 5 Cuota 6 dig
$campo[] = "0050000";//campo 6 importe 1er vto 7 dig
$campo[] = "151216";//campo 7 Fecha 1er vto 6 dig
$campo[] = "0055000";//campo 8 Importe 2do vto
$campo[] = "201216";//campo 9 Fecha 2do vto
$campo[] = "0060000";//campo 10 Importe 3er vto
$campo[] = "211216";//campo 11 Fecha 3er vto
//$campo[] = rand(0,9);//campo 12 Digito verificador

$temp = "";
foreach ($campo as $key => $value) {
	$temp .= $value;
}

$temp .= digito_verificador($temp);

echo "<div style='margin-top:0px' align='center'>";
//echo $generatorHTML->getBarcode($temp, $generatorHTML::TYPE_INTERLEAVED_2_5,$widthFactor,$height);
echo $generatorHTML->getBarcode($temp, $generator::TYPE_CODE_128,$widthFactor,$height);
echo "<br>";
echo "<div class='code'>".$temp."</div>";
echo "</div>";
echo "<br>";
/*******************************************************************************************/