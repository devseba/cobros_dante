<div class="pad">
	<h2>Configurar opciones de backup</h2>
	<div class="forms">
		<?=form_open()?>
		    <?=form_label('Frecuencia','frecuencia')?>
			<?php 
				$f = array(
					1 => 'Cada 1 día',
					7 => 'Cada 1 semana',
					14 => 'Cada 2 semanas',
					30 => 'Cada 1 mes',
				);
			?>
			<?=form_dropdown('frecuencia',$f,$frecuencia)?>
			<?=form_label('Servidor SMTP','smtp')?>
			<?=form_input('smtp',$smtp)?>
			<?=form_label('E-mail','email')?>
			<?=form_input('email',$email)?>
			<?=form_label('Contraseña','pass')?>
			<?=form_password('pass',$pass)?>
			<?=form_label('Destinatarios (separados por coma)','destinatarios')?>
			<?=form_textarea('destinatarios',$destinatarios)?>
			<br/>
			<?=form_submit('Enviar','Guardar configuración')?>
		<?=form_close()?>
	</div>
</div>