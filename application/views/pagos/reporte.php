<div class="yui-g">
	<pre>
		<h1><?=$titulo?> <? if(isset($fecha)) echo $fecha?><br/>
		<?if(isset($curso)){ echo $curso; }?> <?if(isset($division)){ echo $division; }?> <?if(isset($nivel)){ echo $nivel; }?></h1>
		<? if(isset($usuario)){ ?><h1 align="left"><?=$usuario?></h1><? } ?>
		<? if(isset($anulado) && ($anulado == 1)){ ?><h1 align="left"><?='Anulados';?></h1><? } ?>
		<?=$reporte?>
	</pre>
	<pre>
		<?if(isset($total)){?>
			<strong style="display:block; margin-top: 50px;">Total: $<?=$total?></strong>
		<?}?>
	</pre>
</div>
