<?	private function set_barcode($code){
		$widthFactor = 2;
		//$height = 23;		
		$height = 80;
		//$this->barcode->load("Barcode/src/BarcodeGenerator");
		$this->barcode->load("Barcode/src/BarcodeGeneratorPNG");
		$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
		$codigo_barra = '<img style="height: '.$height.'px;
										width: 75%;
										float:left" 
							src="data:image/png;base64,' . base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128,$widthFactor,$height)) . '">';
		return $codigo_barra;
		//return $generator->getBarcode($code, $generator::TYPE_CODE_128,$widthFactor,$height);
	}?>