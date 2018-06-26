<div class="pad">
	<h2>Backup</h2>
	<br/>
	<p>El sistema debe realizar las tareas necesarias de backup.</p>
	<p>Si desea dejarlo pendiente para la próxima vez que inicie sesión <?=anchor('alumnos','haga click aquí')?>, de lo contrario espere unos segundos y comenzará el proceso de backup.</p>
	<p><strong>IMPORTANTE:</strong> No cierre esta ventana si decide correr los procesos de backup.</p>
	<div id="cuenta"></div>
	<script type="text/javascript" src="<?=site_url('static/js/jquery.countdown.js')?>"></script>
	<script type="text/javascript">
		$('#cuenta').countDown({
			startNumber: 15,
			callBack: function(me) {
				$(me).html('Comenzando proceso de backup, espere unos segundos...').css('color','red');
				window.location.replace("<?=site_url('backup/enviar')?>");
			}
		});
	</script>
</div>