<?php 

	class File extends ActiveRecord\Model
	{			
		static $has_many = array(
			array('dfile'),
			array('debt')
		);

		static $belongs_to = array(
		);
	}