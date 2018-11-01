<?php 

	class Quota extends ActiveRecord\Model
	{
		static $belongs_to = array(
			array('debt')
		);	
	}
