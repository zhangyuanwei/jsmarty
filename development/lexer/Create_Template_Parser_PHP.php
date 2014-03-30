<?php
require_once(dirname(__FILE__)."/../dev_settings.php");
ini_set('max_execution_time',300);
ini_set('xdebug.max_nesting_level',300);

// Create Parser
passthru("$smarty_dev_php_cli_bin ./ParserGenerator/cli.php smarty_internal_templateparser.y");

$contents = file_get_contents('smarty_internal_templateparser.php');
$contents = '<?php
/**
* Smarty Internal Plugin Templateparser
*
* This is the template parser.
* It is generated from the internal.templateparser.y file
* @package Smarty
* @subpackage Compiler
* @author Uwe Tews
*/
'.substr($contents,6);
file_put_contents('smarty_internal_templateparser.php', $contents."?>");
