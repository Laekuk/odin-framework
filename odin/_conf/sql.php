<?php
#11OCT2014;AaronPeloquin;Odin-Framework
$conf	= (object)array(
	"default_conn"	=> "local_mysql",
	"connections"	=> array(
		"generic_mysql"		=> array(
			"type"				=> "mysql",
			"params"			=> array("host"=>"108.61.62.67","dbname"=>"mead_db"),
			"user"				=> "user",
			"pass"				=> "password",
		),
		"oracle"			=> array(
			"protocol"			=> "oci",
			"params"			=> array("dbname"=>"yoursid"),
			"user"				=> "username",
			"pass"				=> "password",
		),
		"local_mysql"		=> array(
			"type"				=> "mysql",
			"params"			=> array("host"=>"127.0.0.1","dbname"=>"information_schema"),
			"user"				=> "",
			"pass"				=> "",
		),
		"local_mysql_root"	=> array(
			"type"				=> "mysql",
			"params"			=> array("host"=>"127.0.0.1","dbname"=>"test"),
			"user"				=> "",
			"pass"				=> "",
		),
	),
);