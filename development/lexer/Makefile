parser_generator_file:= ParserGenerator.php $(wildcard ParserGenerator/*.php)
lexer_generator_file:= LexerGenerator.php LexerGenerator/Parser.php $(wildcard LexerGenerator/*.php)
js_output:= smarty_internal_templatelexer.js smarty_internal_templateparser.js
js_output_min:= $(addsuffix .min.js, $(basename $(js_output)))
php_output:= smarty_internal_templatelexer.php smarty_internal_templateparser.php
output:= $(js_output) $(php_output) $(js_output_min)

all:js
	cp $(js_output) ../../distribution/libs/sysplugins/

js:$(js_output)

js_min:$(js_output_min)

php:$(php_output)

smarty_internal_templatelexer.js:smarty_internal_templatelexer.jlex $(lexer_generator_file)
	php Create_Template_Lexer_JS.php

smarty_internal_templateparser.js:smarty_internal_templateparser.jy Lempar.js $(parser_generator_file)
	php Create_Template_Parser_JS.php

smarty_internal_templatelexer.php:smarty_internal_templatelexer.plex $(lexer_generator_file)
	php Create_Template_Lexer_PHP.php

smarty_internal_templateparser.php:smarty_internal_templateparser.y Lempar.php $(parser_generator_file)
	php Create_Template_Parser_PHP.php

LexerGenerator/Parser.php:LexerGenerator/Parser.y Lempar.php $(parser_generator_file)
	-php ParserGenerator/cli.php LexerGenerator/Parser.y > /dev/null

%.diff:%
	svn diff $^ > $@

%.min.js:%.js
	uglifyjs $^ -m -o $@

clean:
	rm -f $(output)
	rm -f LexerGenerator/Parser.php
	find . -name "*.diff" -o -name "*.out" | xargs rm -rf

.PHONY: all js js_min php clean
