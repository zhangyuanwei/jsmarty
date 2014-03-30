<?php
require_once(dirname(__FILE__)."/../dev_settings.php");
ini_set('max_execution_time',300);
ini_set('xdebug.max_nesting_level',300);

// Create Lexer
require_once './LexerGenerator.php';
$lex = new PHP_LexerGenerator('smarty_internal_templatelexer.jlex','smarty_internal_templatelexer.js');
$contents = file_get_contents('smarty_internal_templatelexer.js');
$contents = str_replace(array('SMARTYldel','SMARTYrdel'),array('"+this.ldel+"','"+this.rdel+"'),$contents);
file_put_contents('smarty_internal_templatelexer.js', $contents);
