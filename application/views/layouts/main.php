<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Gestión de cobros</title>
	<?=link_tag('static/css/yui.css')?>
	<?=link_tag('static/css/estilo.css')?>
	<?=link_tag('static/css/dropdown.css')?>
	<!--[if lte IE 6]>
	<?=link_tag('static/css/dropdown_ie.css')?>
	<![endif]-->
	<?=link_tag('static/css/tipsy.css')?>
	<?=link_tag('static/css/calendar.css')?>
	<?=link_tag('static/css/sgi-ui/jquery-ui-1.8.9.custom.css')?>
	<script src="<?=site_url('static/js/jquery-1.7.1.min.js')?>" type="text/javascript"></script>
	<script src="<?=site_url('static/js/jquery-ui-1.8.9.custom.min.js')?>" type="text/javascript"></script>
	<script src="<?=site_url('static/js/jquery.ui.datepicker-es.js')?>" type="text/javascript"></script>
	<script src="<?=site_url('static/js/jquery.tipsy.js')?>" type="text/javascript"></script>
	<script src="<?=site_url('static/js/jquery-ui-timepicker-addon.js')?>" type="text/javascript"></script>
	<script src="<?=site_url('static/js/FusionCharts.js')?>" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$.datepicker.setDefaults($.datepicker.regional['es']);
			
			
			$('.date').live('click', function() {
				$(this).datepicker({showOn:'focus', showAnim: 'fadeIn'}).focus();
			});
			
			$('.eliminar').click(function(event)
			{
				return confirm('¿Está seguro de eliminar este registro?');
			});
			
			$('.error, .success, .notice').click(function ()
			{
				$(this).hide("blind", { direction: "vertical" }, 1000);
			});
			
			$('#filtros input.search').focusin(function()
			{
				$('.selects').fadeOut(50);
					$(this).animate({
						width: "300px"
					}, 500 );
			});
			
			$('#filtros input.search').focusout(function()
			{
					$(this).animate({
						width: "70px"
					}, 500 );
					setTimeout(function() {
						$('.selects').fadeIn(400);
					}, 500);
			});
			
			$('.tipwe').tipsy({gravity: $.fn.tipsy.autoWE, fade:true});
			$('.tipns').tipsy({gravity: $.fn.tipsy.autoNS, fade:true});
		});
	</script>
</head>

<body>
	<div id="doc3" class="yui-t5">
		<div><?=$this->session->flashdata('msg');?></div>
		<div id="hd">
			<div class="menu">
				<ul>
				<?if($this->session->userdata('grupo') != 'alumno'){?>
				<li><?=anchor('alumnos', img('static/img/icon/user.png').' Alumnos')?>
				 
				<!--[if lte IE 6]>
				<a href="../menu/index.html">DEMOS
				<table><tr><td>
				<![endif]-->
				 
					<ul>
						<li><?=anchor('tutores', img('static/img/icon/users.png').' Tutores &rarr;')?>
				 
						<!--[if lte IE 6]>
						<a class="sub" href="../menu/hover_click.html" title="Hover/click with no active/focus borders">&lt; HOVER/CLICK
						<table><tr><td>
						<![endif]-->
					 
							<ul>
								<li><?=anchor('tutores', img('static/img/icon/list_num.png').' Listar')?></li>
								<li><?=anchor('tutores/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
							</ul>
					 
						<!--[if lte IE 6]>
						</td></tr></table>
						</a>
						<![endif]-->
					 
						</li>
						<li><?=anchor('alumnos', img('static/img/icon/list_num.png').' Listar alumnos')?></li>
						<li><?=anchor('alumnos/agregar', img('static/img/icon/sq_plus.png').' Agregar alumno')?></li>
					</ul>
				 
				<!--[if lte IE 6]>
				</td></tr></table>
				</a>
				<![endif]-->
				 
				</li>
				<li><?=anchor('pagos', img('static/img/icon/chart_bar.png').' Reportes')?>
				 
				<!--[if lte IE 6]>
				<a href="<?=site_url('alumnos')?>"> Cobranzas
				<table><tr><td>
				<![endif]-->
				 
					<ul>
						<li><?=anchor('pagos', img('static/img/icon/list_num.png').' Pagos')?>
							<ul>
								<li><?=anchor('pagos', img('static/img/icon/list_num.png').' Pagos')?></li>
								<li><?=anchor('cajas', img('static/img/icon/list_num.png').' Cajas')?></li>
								<li><?=anchor('cajas/resumen', img('static/img/icon/list_num.png').' Resumen Pagos')?></li><li><?=anchor('pagos/cheques', img('static/img/icon/list_num.png').' Cheques')?></li>
							</ul>
						</li>
						<li><?=anchor('deudas', img('static/img/icon/list_num.png').' Deudas')?></li>
						<li><?=anchor('descuentos', img('static/img/icon/list_num.png').' Descuentos')?></li>
					</ul>
				 
				<!--[if lte IE 6]>
				</td></tr></table>
				</a>
				<![endif]-->
				 
				</li>
				<li><?=anchor('pagos', img('static/img/icon/chart_bar.png').' Archivos')?>
					<ul>

						<li><?=anchor('pagos', img('static/img/icon/chart_bar.png').' Importar')?>
						 
							<!--[if lte IE 6]>
							<a href="<?=site_url('alumnos')?>"> Cobranzas
							<table><tr><td>
							<![endif]-->
						 
							<ul>
								<li><?=anchor('pagos/import_archivo_bsj', img('static/img/icon/list_num.png').' Archivo BSJ')?>							
								</li>
								<li><?=anchor('pagos/import_archivo_link', img('static/img/icon/list_num.png').' Archivo Extract (link)')?>							
								</li>								
							</ul>					
						 
							<!--[if lte IE 6]>
							</td></tr></table>
							</a>
							<![endif]-->					 
						</li>
						<li><?=anchor('pagos', img('static/img/icon/chart_bar.png').' Exportar')?>
						 
							<!--[if lte IE 6]>
							<a href="<?=site_url('alumnos')?>"> Cobranzas
							<table><tr><td>
							<![endif]-->
						 
							<ul>
								<li><?=anchor('deudas/refresh_master', img('static/img/icon/list_num.png').'REFRESH')?></li>
							</ul>					
						 
							<!--[if lte IE 6]>
							</td></tr></table>
							</a>
							<![endif]-->					 
						</li>
						<li>
							<?=anchor('pagos/ver_archivos_bsj', img('static/img/icon/list_num.png').' Ver Archivos BSJ')?>
						</li>
						<li>
							<?=anchor('files/listar_archivos_link', img('static/img/icon/list_num.png').' Ver Archivos Link')?>
						</li>						
					</ul>
				</li>						
				
				<?php if($this->session->userdata('grupo') == 'admin'):?>
				<li><?=anchor('paises/agregar', img('static/img/icon/wrench_plus.png').' Configuración')?>
				 
				<!--[if lte IE 6]>
				<a href="../opacity/index.html">OPACITY
				<table><tr><td>
				<![endif]-->
				 
					<ul>
						<li><?=anchor('paises', img('static/img/icon/globe_2.png').' Ubicacion &rarr;')?>
							<ul>
								<li><?=anchor('paises', img('static/img/icon/globe_2.png').' Países &rarr;')?>
									<ul>
										<li><?=anchor('paises', img('static/img/icon/list_num.png').' Listar')?></li>								</li>
										<li><?=anchor('paises/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
								<li><?=anchor('provincias', img('static/img/icon/globe_2.png').' Provincias &rarr;')?>
									<ul>
										<li><?=anchor('provincias', img('static/img/icon/list_num.png').' Listar')?></li>
										<li><?=anchor('provincias/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
								<li><?=anchor('ciudades', img('static/img/icon/globe_2.png').' Ciudades &rarr;')?>
									<ul>
										<li><?=anchor('ciudades', img('static/img/icon/list_num.png').' Listar')?></li>
										<li><?=anchor('ciudades/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
							</ul>
						</li>
						<li><?=anchor('niveles', img('static/img/icon/folder_open.png').' Parametros &rarr;')?>
							<ul>
								<li><?=anchor('niveles', img('static/img/icon/folder_open.png').' Niveles &rarr;')?>
									<ul>
										<li><?=anchor('niveles', img('static/img/icon/list_num.png').' Listar')?></li>
										<li><?=anchor('niveles/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
								<li><?=anchor('cursos', img('static/img/icon/folder_open.png').' Cursos &rarr;')?>
									<ul>
										<li><?=anchor('cursos', img('static/img/icon/list_num.png').' Listar')?></li>
										<li><?=anchor('cursos/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
								<li><?=anchor('divisiones', img('static/img/icon/folder_open.png').' Divisiones &rarr;')?>
									<ul>
										<li><?=anchor('divisiones', img('static/img/icon/list_num.png').' Listar')?></li>
										<li><?=anchor('divisiones/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
							</ul>
						</li>
						<li><?=anchor('conceptos', img('static/img/icon/folder_open.png').' Conceptos &rarr;')?>
							<ul>
								<li><?=anchor('conceptos', img('static/img/icon/list_num.png').' Listar')?></li>
								<li><?=anchor('conceptos/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
								<li><?=anchor('importes', img('static/img/icon/folder_open.png').' Importes &rarr;')?>
									<ul>
										<li><?=anchor('importes', img('static/img/icon/list_num.png').' Listar')?></li>
										<li><?=anchor('importes/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
							</ul>
						</li>
						<li><?=anchor('ptypes', img('static/img/icon/folder_open.png').' Tipos de pago &rarr;')?>
							<ul>
								<li><?=anchor('ptypes', img('static/img/icon/list_num.png').' Listar')?></li>
								<li><?=anchor('ptypes/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
								<li><?=anchor('bancos',img('static/img/icon/cur_dollar.png').' Bancos &rarr;')?>
									<ul>
										<li><?=anchor('bancos', img('static/img/icon/list_num.png').' Listar')?></li>
										<li><?=anchor('bancos/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
								<li><?=anchor('tarjetas',img('static/img/icon/contact_card.png').' Tarjetas &rarr;')?>
									<ul>
										<li><?=anchor('tarjetas', img('static/img/icon/list_num.png').' Listar')?></li>
										<li><?=anchor('tarjetas/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
									</ul>
								</li>
							</ul>
						</li>
						<li><?=anchor('usuarios', img('static/img/icon/users.png').' Usuarios &rarr;')?>
							<ul>
								<li><?=anchor('usuarios', img('static/img/icon/list_num.png').' Listar')?></li>
								<li><?=anchor('usuarios/agregar', img('static/img/icon/sq_plus.png').' Agregar')?></li>
							</ul>
						</li>
						<li><?=anchor('backup/configurar', img('static/img/icon/db.png').' Backups &rarr;')?>
							<ul>
								<li><?=anchor('backup/descargar', img('static/img/icon/save.png').' Descargar ahora')?></li>
								<li><?=anchor('backup/configurar', img('static/img/icon/wrench.png').' Configurar')?></li>
							</ul>
						</li>
					</ul>
				 
				<!--[if lte IE 6]>
				</td></tr></table>
				</a>
				<![endif]-->
				 
				</li>
				<?php endif	?>
				<?}	?>
				</ul>
				<ul class="user">
					<li>
						<?//=anchor('#', img('static/img/icon/on-off.png').' '.$this->session->userdata('usuario'))?>
						<a href="#"><?=img('static/img/icon/on-off.png').' '.$this->session->userdata('usuario')?></a>
						<ul>
							<?if($this->session->userdata('grupo') != "alumno"){?>
							    <li><?=anchor('usuarios/editar/'.$this->session->userdata('id'), img('static/img/icon/contact_card.png').' Modificar datos')?></li>
							<?}?>
							<li><?=anchor('auth/logout', img('static/img/icon/padlock_closed.png').' Cerrar sesión')?></li>
						</ul>
					</li>
				</ul>
				
			</div>
		</div>
		<div id="bd">
			<div id="yui-main">
				<?=$content?>
			</div>
		</div>
	</div>
</body>
</html>
