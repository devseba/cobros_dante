<div class="pad">
	<h2><?=$titulo?></h2>
    <?=form_open_multipart(base_url().'pagos/import_archivo_bsj')?>
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
					<th style="padding: 0px 15px 0px 15px;text-align: center;">Comprobante</th>
	        		<th style="padding: 0px 15px 0px 15px;text-align: center;">Fecha de pago</th>
	        		<th style="padding: 0px 15px 0px 15px;text-align: center;">Importe</th>
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
			        	<td><?=substr($row["codigo_barra"],4,8)?></td>
			        	<td><?=substr($row["fecha_pago"], 4,2)."/".
			        			substr($row["fecha_pago"], 2,2)."/".
			        			substr($row["fecha_pago"], 0,2);?></td>
			        	<?$importe = $row["importe"];?>
			        	<?$importe = substr($importe, 0, 9).".".substr($importe, 9, 2);?>
			        	<td><?=number_format(floatval($importe),2,",",".")?></td>
			        	<td><?=$row["payment"]->student->apellido.", ".$row["payment"]->student->nombre?></td>
			            <?/* foreach ($row as $col) : ?>
			            	<td><center><?=$col?></center></td>
			            <?php endforeach; */?>
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
