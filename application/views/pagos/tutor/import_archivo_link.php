<div class="pad">
	<h2><?=$titulo?></h2>
    <?=form_open_multipart(base_url().'pagos/import_archivo_link')?>
    <?=form_upload('file')?>
    <?=form_submit('submit', 'Upload')?>
    <?=form_close()?>

    <div class="results">
    	<!-- HEADER -->
    	<!--
    	<?/*if(isset($header)){?>
			<table>
				<tr>
					<?php foreach($header as $key => $row) : ?>
				        <th><center><?=$key?></center></th>						
					<?php endforeach; ?>
				<tr>		                 
		        <tr>
		            <?php foreach ($header as $key => $row) : ?>
		            	<td><center><?=$row?></center></td>
		            <?php endforeach; ?>
		        </tr>
			</table>
		<?}*/?>
		-->
		<!-- BODY -->    
    	<?if(isset($datos)){?>
    		<br><h2>Pagos Importados</h2><br>
			<table>
				<tr>
					<th style="padding: 0px 15px 0px 15px;text-align: center;"># Deuda</th>
	        		<th style="padding: 0px 15px 0px 15px;text-align: center;"># Concepto</th>
	        		<th style="padding: 0px 15px 0px 15px;text-align: center;"># Usuario</th>
	        		<th style="padding: 0px 15px 0px 15px;text-align: center;">Importe Pagado</th>
	        		<th style="padding: 0px 15px 0px 15px;text-align: center;">Fecha Pago</th>
	        		<th style="padding: 0px 15px 0px 15px;text-align: center;">Concepto</th>
	        		<th style="padding: 0px 15px 0px 15px;text-align: center;">Alumno</th>
					<?/*foreach($datos[0] as $key => $row) : ?>
				        <th style="padding: 0px 15px 0px 15px">
				        	<center>
				        		<?//=$key?>
				        	</center>
				        </th>
					<?endforeach; */?>
				<tr>
			    <?php foreach($datos as $row) : ?>
			        <tr align="center">
			        	<td><?=$row["id_deuda"]?></td>
			        	<td><?=$row["id_concepto"]?></td>
			        	<td><?=$row["id_usuario"]?></td>
			        	<?$importe = $row["importe_pagado"];?>
			        	<?$importe = substr($importe, 0, 10).".".substr($importe, 9, 2);?>
			        	<td><?="$ ".number_format(floatval($importe),2,",",".")?></td>			        	
			        	<td><?=substr($row["fecha_pago"], 6,2)."/".
			        			substr($row["fecha_pago"], 4,2)."/".
			        			substr($row["fecha_pago"], 0,4);?></td>
			        </tr>
			    <?php endforeach; ?>
			</table>
		<?}?>
    	<!-- FOOTER -->
    	<!--
    	<?/*if(isset($footer)){?>
			<table>
				<tr>
					<?php foreach($footer as $key => $row) : ?>
				        <th><center><?=$key?></center></th>						
					<?php endforeach; ?>
				<tr>		                 
		        <tr>
		            <?php foreach ($footer as $key => $row) : ?>
		            	<td><center><?=$row?></center></td>
		            <?php endforeach; ?>
		        </tr>
			</table>
		<?}*/?>
		-->		
    </div>
</div>
