parser_generator_file:= ParserGenerator.php $(wildcard ParserGenerator/*.php)
lexer_generator_file:= LexerGenerator.php LexerGenerator/Parser.php $(wildcard LexerGenerator/*.php)
js_output:= smarty_internal_templatelexer.js smarty_internal_templateparser.js
php_output:= smarty_internal_templatelexer.php smarty_internal_templateparser.php
output:= $(js_output) $(php_output)

all:js php

js:$(js_output)

php:$(php_output)

smarty_internal_templatelexer.js:smarty_internal_templatelexer.jlex $(lexer_generator_file)
	php Create_Template_Lexer_JS.php

smarty_internal_templateparser.js:smarty_internal_templateparser.jy Lempar.js $(parser_generator_file)
	php Create_Template_Parser_JS.php > /dev/null

smarty_internal_templatelexer.php:smarty_internal_templatelexer.plex $(lexer_generator_file)
	php Create_Template_Lexer_PHP.php

smarty_internal_templateparser.php:smarty_internal_templateparser.y Lempar.php $(parser_generator_file)
	php Create_Template_Parser_PHP.php > /dev/null

LexerGenerator/Parser.php:LexerGenerator/Parser.y Lempar.php $(parser_generator_file)
	-php ParserGenerator/cli.php LexerGenerator/Parser.y

%.diff:%
	svn diff $^ > $@

test:js
	node smarty_internal_templatelexer.js
	node smarty_internal_templateparser.js

clean:
	rm -f $(output)
	rm -f LexerGenerator/Parser.php
	find . -name "*.diff" -o -name "*.out" | xargs rm -rf

.PHONY: all js php test clean
