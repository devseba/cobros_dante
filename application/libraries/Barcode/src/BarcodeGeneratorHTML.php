<?php

namespace Picqer\Barcode;

class BarcodeGeneratorHTML extends BarcodeGenerator
{

    /**
     * Return an HTML representation of barcode.
     *
     * @param string $code code to print
     * @param string $type type of barcode
     * @param int $widthFactor Width of a single bar element in pixels.
     * @param int $totalHeight Height of a single bar element in pixels.
     * @param int|string $color Foreground color for bar elements (background is transparent).
     * @return string HTML code.
     * @public
     */
    public function getBarcode($code, $type, $widthFactor = 2, $totalHeight = 30, $color = 'black')//Modificado por SEBA
    {
        $barcodeData = $this->getBarcodeData($code, $type);
        //$width = ($barcodeData['maxWidth'] * $widthFactor);//Este valor es el que tenia antes
        $width = "100%"; // este valor lo coloque yo para que quede alineado a la izq

        $html = '<div class="codigo-barra" style="font-size:0;position:relative;left: 0px;width:' . $width . ';height:' . ($totalHeight) . 'mm;">' . "\n";

        $positionHorizontal = 0;
        foreach ($barcodeData['bars'] as $bar) {
            $barWidth = round(($bar['width'] * $widthFactor), 3);
            $barHeight = round(($bar['height'] * $totalHeight / $barcodeData['maxHeight']), 3);

            if ($bar['drawBar']) {
                $positionVertical = round(($bar['positionVertical'] * $totalHeight / $barcodeData['maxHeight']), 3);
                // draw a vertical bar
                $html .= '<div style="background-color:' . $color . ';width:' . $barWidth . 'px;height:' . $barHeight . 'mm;position:absolute;left:' . $positionHorizontal . 'px;top:' . $positionVertical . 'px;">&nbsp;</div>' . "\n";
            }

            $positionHorizontal += $barWidth;
        }

        $html .= '</div>' . "\n";

        return $html;
    }
}