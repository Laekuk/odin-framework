<?php
#11OCT2014;AaronPeloquin;Odin-Framework

#kick on all errors.
ini_set('display_errors', 'On');
error_reporting(E_ALL);

#this file will initiate the odin-framework for you and create the $odin variable.
$base	= dirname(__FILE__);
require_once($base."/odin.php");
$odin	= new odin($base);
