<!DOCTYPE html>
<html>
	<head>
		<title>Recibo</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<?=link_tag('static/css/yui.css')?>
		<style type="text/css">
			/*strong { font-weight:900;}
			pre { line-height:8px; padding:0; margin:0;}
			#table { width:100%; margin-bottom:-15px;}
			#table #th { font-weight:bold;}
			#table #th, #table #td {padding: 5px 0; border-bottom: 1px dashed #000;}
			#bd { padding: 40px;}
			#original, #copia {
				height:250px;
				font-size: 11px;
			}
			#original
			{
				margin-top: 80px;
			}
			#copia {
				margin-top: 265px;
			}*/

			#div_detalle > table{width:90%; margin-bottom:-15px;}

			#div_detalle > table th, #div_detalle > table td {padding: 5px 0; border-bottom: 1px dashed #000;}

			#div_detalle > table td {text-align: left;}

			#div_detalle > table th { font-weight:bold;}
		</style>
		<script type="text/javascript">
			function load()
			{
				window.print()
				<?php if($this->session->flashdata('next')):?>
					setTimeout("location.href = '<?=$this->session->flashdata('next')?>';",1500);
				<?php endif;?>
			}
		</script>
	</head>
	<body onLoad="javascript:load();">
		<div id="" class="">
			<div id="">
				<div id="">
					<?=$content?>
				</div>
			</div>
		</div>
	</body>
</html>
