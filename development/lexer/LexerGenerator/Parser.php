<?php
class PHP_LexerGenerator_ParseryyToken implements ArrayAccess
{
    public $string = '';
    public $metadata = array();

    public function __construct($s, $m = array())
    {
        if ($s instanceof PHP_LexerGenerator_ParseryyToken) {
            $this->string = $s->string;
            $this->metadata = $s->metadata;
        } else {
            $this->string = (string) $s;
            if ($m instanceof PHP_LexerGenerator_ParseryyToken) {
                $this->metadata = $m->metadata;
            } elseif (is_array($m)) {
                $this->metadata = $m;
            }
        }
    }

    public function __toString()
    {
        return $this->_string;
    }

    public function offsetExists($offset)
    {
        return isset($this->metadata[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->metadata[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            if (isset($value[0])) {
                $x = ($value instanceof PHP_LexerGenerator_ParseryyToken) ?
                    $value->metadata : $value;
                $this->metadata = array_merge($this->metadata, $x);

                return;
            }
            $offset = count($this->metadata);
        }
        if ($value === null) {
            return;
        }
        if ($value instanceof PHP_LexerGenerator_ParseryyToken) {
            if ($value->metadata) {
                $this->metadata[$offset] = $value->metadata;
            }
        } elseif ($value) {
            $this->metadata[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->metadata[$offset]);
    }
}

class PHP_LexerGenerator_ParseryyStackEntry
{
    public $stateno;       /* The state-number */
    public $major;         /* The major token value.  This is the code
                     ** number for the token at this stack level */
    public $minor; /* The user-supplied minor token value.  This
                     ** is the value of the token  */
};

#line 3 "LexerGenerator/Parser.y"

/* ?><?php {//*/
/**
 * PHP_LexerGenerator, a php 5 lexer generator.
 *
 * This lexer generator translates a file in a format similar to
 * re2c ({@link http://re2c.org}) and translates it into a PHP 5-based lexer
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006, Gregory Beaver <cellog@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in
 *       the documentation and/or other materials provided with the distribution.
 *     * Neither the name of the PHP_LexerGenerator nor the names of its
 *       contributors may be used to endorse or promote products derived
 *       from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   php
 * @package    PHP_LexerGenerator
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id: Parser.php 246683 2007-11-22 04:43:52Z instance $
 * @since      File available since Release 0.1.0
 */
/**
 * For regular expression validation
 */
require_once './LexerGenerator/Regex/Lexer.php';
require_once './LexerGenerator/Regex/Parser.php';
require_once './LexerGenerator/Exception.php';
/**
 * Token parser for plex files.
 *
 * This parser converts tokens pulled from {@link PHP_LexerGenerator_Lexer}
 * into abstract patterns and rules, then creates the output file
 * @package    PHP_LexerGenerator
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2006 Gregory Beaver
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    @package_version@
 * @since      Class available since Release 0.1.0
 */
#line 143 "LexerGenerator/Parser.php"

#line 2 "LexerGenerator/Parser.y"
class PHP_LexerGenerator_Parser#line 146 "LexerGenerator/Parser.php"
{
#line 78 "LexerGenerator/Parser.y"

    private $patterns;
    private $out;
    private $lex;
    private $input;
    private $counter;
    private $token;
    private $value;
    private $line;
    private $matchlongest;
    private $_regexLexer;
    private $_regexParser;
    private $_patternIndex = 0;
    private $_outRuleIndex = 1;
    private $caseinsensitive;
    private $patternFlags;
    private $unicode;
    private $format;

    public $transTable = array(
        1 => self::PHPCODE,
        2 => self::COMMENTSTART,
        3 => self::COMMENTEND,
        4 => self::QUOTE,
        5 => self::SINGLEQUOTE,
        6 => self::PATTERN,
        7 => self::CODE,
        8 => self::SUBPATTERN,
        9 => self::PI,
    );

    public function __construct($outfile, $lex)
    {
        $this->out = fopen($outfile, 'wb');
        if (!$this->out) {
            throw new Exception('unable to open lexer output file "' . $outfile . '"');
        }
        $this->lex = $lex;
        $this->_regexLexer = new PHP_LexerGenerator_Regex_Lexer('');
        $this->_regexParser = new PHP_LexerGenerator_Regex_Parser($this->_regexLexer);
    }

    public function doLongestMatchJS($rules, $statename, $ruleindex)
    {
        //TODO
    }

    public function doLongestMatch($rules, $statename, $ruleindex)
    {
        fwrite($this->out, '
        if (' . $this->counter . ' >= strlen(' . $this->input . ')) {
            return false; // end of input
        }
        do {
            $rules = array(');
        foreach ($rules as $rule) {
            fwrite($this->out, '
                \'/\G' . $rule['pattern'] . '/' . $this->patternFlags . ' \',');
        }
        fwrite($this->out, '
            );
            $match = false;
            foreach ($rules as $index => $rule) {
                if (preg_match($rule, substr(' . $this->input . ', ' .
                 $this->counter . '), $yymatches)) {
                    if ($match) {
                        if (strlen($yymatches[0]) > strlen($match[0][0])) {
                            $match = array($yymatches, $index); // matches, token
                        }
                    } else {
                        $match = array($yymatches, $index);
                    }
                }
            }
            if (!$match) {
                throw new Exception(\'Unexpected input at line \' . ' . $this->line . ' .
                    \': \' . ' . $this->input . '[' . $this->counter . ']);
            }
            ' . $this->token . ' = $match[1];
            ' . $this->value . ' = $match[0][0];
            $yysubmatches = $match[0];
            array_shift($yysubmatches);
            if (!$yysubmatches) {
                $yysubmatches = array();
            }
            $r = $this->{\'yy_r' . $ruleindex . '_\' . ' . $this->token . '}($yysubmatches);
            if ($r === null) {
                ' . $this->counter . ' += strlen(' . $this->value . ');
                ' . $this->line . ' += substr_count(' . $this->value . ', "\n");
                // accept this token
                return true;
            } elseif ($r === true) {
                // we have changed state
                // process this token in the new state
                return $this->yylex();
            } elseif ($r === false) {
                ' . $this->counter . ' += strlen(' . $this->value . ');
                ' . $this->line . ' += substr_count(' . $this->value . ', "\n");
                if (' . $this->counter . ' >= strlen(' . $this->input . ')) {
                    return false; // end of input
                }
                // skip this token
                continue;
            } else {');
            fwrite($this->out, '
                $yy_yymore_patterns = array_slice($rules, $this->token, true);
                // yymore is needed
                do {
                    if (!isset($yy_yymore_patterns[' . $this->token . '])) {
                        throw new Exception(\'cannot do yymore for the last token\');
                    }
                    $match = false;
                    foreach ($yy_yymore_patterns[' . $this->token . '] as $index => $rule) {
                        if (preg_match(\'/\' . $rule . \'/' . $this->patternFlags . '\',
                                ' . $this->input . ', $yymatches, null, ' . $this->counter . ')) {
                            $yymatches = array_filter($yymatches, \'strlen\'); // remove empty sub-patterns
                            if ($match) {
                                if (strlen($yymatches[0]) > strlen($match[0][0])) {
                                    $match = array($yymatches, $index); // matches, token
                                }
                            } else {
                                $match = array($yymatches, $index);
                            }
                        }
                    }
                    if (!$match) {
                        throw new Exception(\'Unexpected input at line \' . ' . $this->line . ' .
                            \': \' . ' . $this->input . '[' . $this->counter . ']);
                    }
                    ' . $this->token . ' = $match[1];
                    ' . $this->value . ' = $match[0][0];
                    $yysubmatches = $match[0];
                    array_shift($yysubmatches);
                    if (!$yysubmatches) {
                        $yysubmatches = array();
                    }
                    ' . $this->line . ' = substr_count(' . $this->value . ', "\n");
                    $r = $this->{\'yy_r' . $ruleindex . '_\' . ' . $this->token . '}();
                } while ($r !== null || !$r);
                if ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } else {
                    // accept
                    ' . $this->counter . ' += strlen(' . $this->value . ');
                    ' . $this->line . ' += substr_count(' . $this->value . ', "\n");

                    return true;
                }
            }
        } while (true);
');
    }

    public function doFirstMatchJS($rules, $statename, $ruleindex)
    {

        $patterns = array();
        //$pattern = '/';
        $pattern = '';
        $ruleMap = array();
        $tokenindex = array();
        $actualindex = 1;
        $i = 0;
        foreach ($rules as $rule) {
            $ruleMap[$i++] = $actualindex;
            $tokenindex[$actualindex] = $rule['subpatterns'];
            $actualindex += $rule['subpatterns'] + 1;
            //$patterns[] = '\G(' . $rule['pattern'] . ')';
            $patterns[] = '(' . $rule['pattern'] . ')';
        }
        // Re-index tokencount from zero.
        $tokencount = array_values($tokenindex);
        //$tokenindex = var_export($tokenindex, true);
        $tokenindex = json_encode($tokenindex);
        //$tokenindex = preg_split('/[\\{,\\}]/', $tokenindex);
        //$tokenindex = implode("\n", $tokenindex);
        //$tokenindex = explode("\n", $tokenindex);
        //$tokenindex = preg_split('/,/', $tokenindex);
        // indent for prettiness
        //$tokenindex = implode(",\n            ", $tokenindex);
        $pattern .= implode('|', $patterns);

        //for js
        $pattern = str_replace(array(
            "\\/",
            "\\\\"
        ), array(
            "/",
            "\\"
        ), $pattern);
        //$pattern .= '/' . $this->patternFlags;

        fwrite($this->out, '
        if (' . $this->counter . ' >= ' . $this->input . '.length) {
            return false; // end of input
        }
        var tokenMap = ' . $tokenindex . ';
        ');

        fwrite($this->out, '
        var yy_global_pattern = new RegExp(' . json_encode($pattern) . ', \'g' . $this->patternFlags . '\');
        ');

        fwrite($this->out, '
        do {
            yy_global_pattern.lastIndex = ' . $this->counter . ';
            var result = yy_global_pattern.exec(' . $this->input . ');
            if(result){
                var yymatches = result, yysubmatches = [];
                if (!yymatches[0]) {
                    throw new Error(\'Error: lexing failed because a rule matched\' +
                        \' an empty string.  Input "\' + ' . $this->input . '
                        .substr(' . $this->counter . ', 5) + \'..." state:' . $statename . '\');
                }
                for(var i = 1, count = yymatches.length; i < count; i++) { // skip global match
                    var item = yymatches[i];
                    if(item){
                        ' . $this->token . ' = i;    // token number
                        ' . $this->value . ' = item; // token value
                        break;
                    }
                }
                if (tokenMap[' . $this->token . ']) {
                    // extract sub-patterns for passing to lex function
                    yysubmatches = yymatches.slice(' . $this->token . ' + 1, tokenMap[' . $this->token . ']);  
                }
                var r = this[\'yy_r' . $ruleindex . '_\' + ' . $this->token . '](yysubmatches);
                if (r === undefined) {
                    ' . $this->counter . ' += ' . $this->value . '.length;
                    ' . $this->line . ' += ' . $this->value . '.split("\n").length  - 1;
                    // accept this token
                    return true;
                } else if (r === true) {
                    // we have changed state
                    // process this token in the new state
                    return this.yylex();
                } else if (r === false) {
                    ' . $this->counter . ' += ' . $this->value . '.length;
                    ' . $this->line . ' += ' . $this->value . '.split("\n").length  - 1;
                    if (' . $this->counter . ' >= ' . $this->input . '.length) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }');

       fwrite($this->out, '            } else {
                throw new Error(\'Unexpected input at line\' + ' . $this->line . ' +
                    \': \' + ' . $this->input . '[' . $this->counter . ']);
            }
            break;
        } while (true);
        ');
    }

    public function doFirstMatch($rules, $statename, $ruleindex)
    {
        $patterns = array();
        $pattern = '/';
        $ruleMap = array();
        $tokenindex = array();
        $actualindex = 1;
        $i = 0;
        foreach ($rules as $rule) {
            $ruleMap[$i++] = $actualindex;
            $tokenindex[$actualindex] = $rule['subpatterns'];
            $actualindex += $rule['subpatterns'] + 1;
            $patterns[] = '\G(' . $rule['pattern'] . ')';
        }
        // Re-index tokencount from zero.
        $tokencount = array_values($tokenindex);
        $tokenindex = var_export($tokenindex, true);
        $tokenindex = explode("\n", $tokenindex);
        // indent for prettiness
        $tokenindex = implode("\n            ", $tokenindex);
        $pattern .= implode('|', $patterns);
        $pattern .= '/' . $this->patternFlags;
        fwrite($this->out, '
        $tokenMap = ' . $tokenindex . ';
        if (' . $this->counter . ' >= ($this->mbstring_overload ? mb_strlen(' . $this->input . ',\'latin1\'): strlen(' . $this->input . '))) {
            return false; // end of input
        }
        ');
        fwrite($this->out, '$yy_global_pattern = "' .
            $pattern . 'iS";' . "\n");
        fwrite($this->out, '
        do {
            if ($this->mbstring_overload ? preg_match($yy_global_pattern, mb_substr(' . $this->input . ', ' .
             $this->counter .
                    ',2000000000,\'latin1\'), $yymatches) : preg_match($yy_global_pattern,' . $this->input . ', $yymatches, null, ' .
             $this->counter .
                    ')) {
                $yysubmatches = $yymatches;
                $yymatches = array_filter($yymatches, \'strlen\'); // remove empty sub-patterns
                if (!count($yymatches)) {
                    throw new Exception(\'Error: lexing failed because a rule matched\' .
                        \' an empty string.  Input "\' . substr(' . $this->input . ',
                        ' . $this->counter . ', 5) . \'... state ' . $statename . '\');
                }
                next($yymatches); // skip global match
                ' . $this->token . ' = key($yymatches); // token number
                if ($tokenMap[' . $this->token . ']) {
                    // extract sub-patterns for passing to lex function
                    $yysubmatches = array_slice($yysubmatches, ' . $this->token . ' + 1,
                        $tokenMap[' . $this->token . ']);
                } else {
                    $yysubmatches = array();
                }
                ' . $this->value . ' = current($yymatches); // token value
                $r = $this->{\'yy_r' . $ruleindex . '_\' . ' . $this->token . '}($yysubmatches);
                if ($r === null) {
                    ' . $this->counter . ' += ($this->mbstring_overload ? mb_strlen(' . $this->value . ',\'latin1\'): strlen(' . $this->value . '));
                    ' . $this->line . ' += substr_count(' . $this->value . ', "\n");
                    // accept this token
                    return true;
                } elseif ($r === true) {
                    // we have changed state
                    // process this token in the new state
                    return $this->yylex();
                } elseif ($r === false) {
                    ' . $this->counter . ' += ($this->mbstring_overload ? mb_strlen(' . $this->value . ',\'latin1\'): strlen(' . $this->value . '));
                    ' . $this->line . ' += substr_count(' . $this->value . ', "\n");
                    if (' . $this->counter . ' >= ($this->mbstring_overload ? mb_strlen(' . $this->input . ',\'latin1\'): strlen(' . $this->input . '))) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }');
//                } else {');
/**        fwrite($this->out, '                    $yy_yymore_patterns = array(' . "\n");
        $extra = 0;
        for ($i = 0; count($patterns); $i++) {
            unset($patterns[$i]);
            $extra += $tokencount[0];
            array_shift($tokencount);
            fwrite($this->out, '        ' . $ruleMap[$i] . ' => array(' . $extra . ', "' .
                implode('|', $patterns) . "\"),\n");
        }
        fwrite($this->out, '    );' . "\n");
        fwrite($this->out, '
                    // yymore is needed
                    do {
                        if (!strlen($yy_yymore_patterns[' . $this->token . '][1])) {
                            throw new Exception(\'cannot do yymore for the last token\');
                        }
                        $yysubmatches = array();
                        if (preg_match(\'/\' . $yy_yymore_patterns[' . $this->token . '][1] . \'/' . $this->patternFlags . '\',
                              ' . $this->input . ', $yymatches, null, ' . $this->counter .')) {
                            $yysubmatches = $yymatches;
                            $yymatches = array_filter($yymatches, \'strlen\'); // remove empty sub-patterns
                            next($yymatches); // skip global match
                            ' . $this->token . ' += key($yymatches) + $yy_yymore_patterns[' . $this->token . '][0]; // token number
                            ' . $this->value . ' = current($yymatches); // token value
                            ' . $this->line . ' = substr_count(' . $this->value . ', "\n");
                            if ($tokenMap[' . $this->token . ']) {
                                // extract sub-patterns for passing to lex function
                                $yysubmatches = array_slice($yysubmatches, ' . $this->token . ' + 1,
                                    $tokenMap[' . $this->token . ']);
                            } else {
                                $yysubmatches = array();
                            }
                        }
                        $r = $this->{\'yy_r' . $ruleindex . '_\' . ' . $this->token . '}($yysubmatches);
                    } while ($r !== null && !is_bool($r));
                    if ($r === true) {
                        // we have changed state
                        // process this token in the new state
                        return $this->yylex();
                    } elseif ($r === false) {
                        ' . $this->counter . ' += ($this->mbstring_overload ? mb_strlen(' . $this->value . ',\'latin1\'): strlen(' . $this->value . '));
                        ' . $this->line . ' += substr_count(' . $this->value . ', "\n");
                        if (' . $this->counter . ' >= ($this->mbstring_overload ? mb_strlen(' . $this->input . ',\'latin1\'): strlen(' . $this->input . '))) {
                            return false; // end of input
                        }
                        // skip this token
                        continue;
                    } else {
                        // accept
                        ' . $this->counter . ' += ($this->mbstring_overload ? mb_strlen(' . $this->value . ',\'latin1\'): strlen(' . $this->value . '));
                        ' . $this->line . ' += substr_count(' . $this->value . ', "\n");

                        return true;
                    }
                }
*/
       fwrite($this->out, '            } else {
                throw new Exception(\'Unexpected input at line\' . ' . $this->line . ' .
                    \': \' . ' . $this->input . '[' . $this->counter . ']);
            }
            break;
        } while (true);
');
    }

    public function makeCaseInsensitve($string)
    {
        return preg_replace('/[a-z]/ie', "'[\\0'.strtoupper('\\0').']'", strtolower($string));
    }

    public function outputCommonFunctions()
    {
        switch($this->format) {
        case "js":
        case "javascript":
            return $this->outputCommonFunctionsJS($rules, $statename);
            break;
        case "php":
        default:
            return $this->outputCommonFunctionsPHP($rules, $statename);
            break;
        }
    }

    public function outputCommonFunctionsJS()
    {
        fwrite($this->out, '
    proto._init = function()
    {
        this._yy_state = 1;
        this._yy_stack = [];
    };

    proto.yylex = function()
    {
        return this[\'yylex\' + this._yy_state]();
    };

    proto.yypushstate = function(state)
    {
        this._yy_stack.push(this._yy_state);
        this._yy_state = state;
    };

    proto.yypopstate = function()
    {
       this._yy_state = this._yy_stack.pop();
    };

    proto.yybegin = function($state)
    {
       this._yy_state = state;
    };
        ');
    }

    public function outputCommonFunctionsPHP()
    {
        fwrite($this->out, '
    private $_yy_state = 1;
    private $_yy_stack = array();

    public function yylex()
    {
        return $this->{\'yylex\' . $this->_yy_state}();
    }

    public function yypushstate($state)
    {
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState push %s\n", $this->yyTracePrompt, isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
        array_push($this->_yy_stack, $this->_yy_state);
        $this->_yy_state = $state;
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%snew State %s\n", $this->yyTracePrompt, isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
    }

    public function yypopstate()
    {
       if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState pop %s\n", $this->yyTracePrompt,  isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
       $this->_yy_state = array_pop($this->_yy_stack);
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%snew State %s\n", $this->yyTracePrompt, isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
    }

    public function yybegin($state)
    {
       $this->_yy_state = $state;
        if ($this->yyTraceFILE) {
             fprintf($this->yyTraceFILE, "%sState set %s\n", $this->yyTracePrompt, isset($this->state_name[$this->_yy_state]) ? $this->state_name[$this->_yy_state] : $this->_yy_state);
        }
    }
        ');
    }

    public function outputRules($rules, $statename)
    {
        switch($this->format) {
        case "js":
        case "javascript":
            return $this->outputRulesJS($rules, $statename);
            break;
        case "php":
        default:
            return $this->outputRulesPHP($rules, $statename);
            break;
        }
    }
    
    public function outputRulesJS($rules, $statename)
    {
        if (!$statename) {
            $statename = $this -> _outRuleIndex;
        }
        fwrite($this->out, '
    proto.yylex' . $this -> _outRuleIndex . ' = function()
    {');
        if ($this->matchlongest) {
            $ruleMap = array();
            foreach ($rules as $i => $rule) {
                $ruleMap[$i] = $i;
            }
            $this->doLongestMatchJS($rules, $statename, $this -> _outRuleIndex);
        } else {
            $ruleMap = array();
            $actualindex = 1;
            $i = 0;
            foreach ($rules as $rule) {
                $ruleMap[$i++] = $actualindex;
                $actualindex += $rule['subpatterns'] + 1;
            }
            $this->doFirstMatchJS($rules, $statename, $this -> _outRuleIndex);
        }
        fwrite($this->out, '
    }; // end function
        ');
        if (is_string($statename)) {
            fwrite($this->out, '
    self.' . $statename . ' = ' . $this -> _outRuleIndex . ';
        ');
        }
        foreach ($rules as $i => $rule) {
            fwrite($this->out, '    proto.yy_r' . $this -> _outRuleIndex . '_' . $ruleMap[$i] . ' = function($yy_subpatterns)
    {
' . $rule['code'] .
'    };
');
        }
        $this -> _outRuleIndex++; // for next set of rules
    }

    public function outputRulesPHP($rules, $statename)
    {
        if (!$statename) {
            $statename = $this -> _outRuleIndex;
        }
        fwrite($this->out, '
    public function yylex' . $this -> _outRuleIndex . '()
    {');
        if ($this->matchlongest) {
            $ruleMap = array();
            foreach ($rules as $i => $rule) {
                $ruleMap[$i] = $i;
            }
            $this->doLongestMatch($rules, $statename, $this -> _outRuleIndex);
        } else {
            $ruleMap = array();
            $actualindex = 1;
            $i = 0;
            foreach ($rules as $rule) {
                $ruleMap[$i++] = $actualindex;
                $actualindex += $rule['subpatterns'] + 1;
            }
            $this->doFirstMatch($rules, $statename, $this -> _outRuleIndex);
        }
        fwrite($this->out, '
    } // end function

');
        if (is_string($statename)) {
            fwrite($this->out, '
    const ' . $statename . ' = ' . $this -> _outRuleIndex . ';
');
        }
        foreach ($rules as $i => $rule) {
            fwrite($this->out, '    function yy_r' . $this -> _outRuleIndex . '_' . $ruleMap[$i] . '($yy_subpatterns)
    {
' . $rule['code'] .
'    }
');
        }
        $this -> _outRuleIndex++; // for next set of rules
    }

    public function error($msg)
    {
        echo 'Error on line ' . $this->lex->line . ': ' , $msg;
    }

    public function _validatePattern($pattern, $update = false)
    {
        $this->_regexLexer->reset($pattern, $this->lex->line);
        $this->_regexParser->reset($this->_patternIndex, $update);
        try {
            while ($this->_regexLexer->yylex()) {
                $this->_regexParser->doParse(
                    $this->_regexLexer->token, $this->_regexLexer->value);
            }
            $this->_regexParser->doParse(0, 0);
        } catch (PHP_LexerGenerator_Exception $e) {
            $this->error($e->getMessage());
            throw new PHP_LexerGenerator_Exception('Invalid pattern "' . $pattern . '"');
        }

        return $this->_regexParser->result;
    }
#line 761 "LexerGenerator/Parser.php"

    const PHPCODE                        =  1;
    const COMMENTSTART                   =  2;
    const COMMENTEND                     =  3;
    const PI                             =  4;
    const SUBPATTERN                     =  5;
    const CODE                           =  6;
    const PATTERN                        =  7;
    const QUOTE                          =  8;
    const SINGLEQUOTE                    =  9;
    const YY_NO_ACTION = 99;
    const YY_ACCEPT_ACTION = 98;
    const YY_ERROR_ACTION = 97;

    const YY_SZ_ACTTAB = 91;
static public $yy_action = array(
 /*     0 */    25,   50,   49,   31,   49,   54,   53,   54,   53,   35,
 /*    10 */    11,   49,   18,   22,   54,   53,   14,   59,   51,   28,
 /*    20 */    55,   57,   58,   59,   47,    1,   55,   57,   32,   15,
 /*    30 */    49,   29,   49,   54,   53,   54,   53,   30,   52,   49,
 /*    40 */    42,   46,   54,   53,   98,   56,    5,   13,   38,   18,
 /*    50 */    49,   43,   40,   54,   53,   12,   39,   18,    3,   37,
 /*    60 */    36,   17,    7,    8,    2,   10,   33,   18,    9,    2,
 /*    70 */    41,   44,    1,   24,   16,   34,   45,   27,   60,   48,
 /*    80 */     4,    1,    2,    1,   20,   19,   21,   26,   23,    6,
 /*    90 */     7,
    );
    static public $yy_lookahead = array(
 /*     0 */     3,    1,    5,    4,    5,    8,    9,    8,    9,    3,
 /*    10 */    19,    5,   21,    4,    8,    9,    7,    5,    6,   14,
 /*    20 */     8,    9,    3,    5,    6,   20,    8,    9,    3,    7,
 /*    30 */     5,    4,    5,    8,    9,    8,    9,    3,    1,    5,
 /*    40 */     5,    6,    8,    9,   11,   12,   13,   19,    5,   21,
 /*    50 */     5,    8,    9,    8,    9,   19,    5,   21,    5,    8,
 /*    60 */     9,    1,    2,    1,    2,   19,   14,   21,    1,    2,
 /*    70 */     5,    6,   20,   15,   16,   14,    2,   14,    1,    1,
 /*    80 */     5,   20,    2,   20,   18,   21,   18,   17,    4,   13,
 /*    90 */     2,
);
    const YY_SHIFT_USE_DFLT = -4;
    const YY_SHIFT_MAX = 35;
    static public $yy_shift_ofst = array(
 /*     0 */    60,   27,   -1,   45,   45,   62,   67,   84,   80,   80,
 /*    10 */    34,   25,   -3,    6,   51,   51,    9,   88,   12,   18,
 /*    20 */    43,   43,   65,   35,   19,    0,   22,   74,   74,   75,
 /*    30 */    78,   53,   77,   74,   74,   37,
);
    const YY_REDUCE_USE_DFLT = -10;
    const YY_REDUCE_MAX = 17;
    static public $yy_reduce_ofst = array(
 /*     0 */    33,   28,   -9,   46,   36,   52,   63,   58,   61,    5,
 /*    10 */    64,   64,   64,   64,   66,   68,   70,   76,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(1, 2, ),
        /* 1 */ array(4, 5, 8, 9, ),
        /* 2 */ array(4, 5, 8, 9, ),
        /* 3 */ array(5, 8, 9, ),
        /* 4 */ array(5, 8, 9, ),
        /* 5 */ array(1, 2, ),
        /* 6 */ array(1, 2, ),
        /* 7 */ array(4, ),
        /* 8 */ array(2, ),
        /* 9 */ array(2, ),
        /* 10 */ array(3, 5, 8, 9, ),
        /* 11 */ array(3, 5, 8, 9, ),
        /* 12 */ array(3, 5, 8, 9, ),
        /* 13 */ array(3, 5, 8, 9, ),
        /* 14 */ array(5, 8, 9, ),
        /* 15 */ array(5, 8, 9, ),
        /* 16 */ array(4, 7, ),
        /* 17 */ array(2, ),
        /* 18 */ array(5, 6, 8, 9, ),
        /* 19 */ array(5, 6, 8, 9, ),
        /* 20 */ array(5, 8, 9, ),
        /* 21 */ array(5, 8, 9, ),
        /* 22 */ array(5, 6, ),
        /* 23 */ array(5, 6, ),
        /* 24 */ array(3, ),
        /* 25 */ array(1, ),
        /* 26 */ array(7, ),
        /* 27 */ array(2, ),
        /* 28 */ array(2, ),
        /* 29 */ array(5, ),
        /* 30 */ array(1, ),
        /* 31 */ array(5, ),
        /* 32 */ array(1, ),
        /* 33 */ array(2, ),
        /* 34 */ array(2, ),
        /* 35 */ array(1, ),
        /* 36 */ array(),
        /* 37 */ array(),
        /* 38 */ array(),
        /* 39 */ array(),
        /* 40 */ array(),
        /* 41 */ array(),
        /* 42 */ array(),
        /* 43 */ array(),
        /* 44 */ array(),
        /* 45 */ array(),
        /* 46 */ array(),
        /* 47 */ array(),
        /* 48 */ array(),
        /* 49 */ array(),
        /* 50 */ array(),
        /* 51 */ array(),
        /* 52 */ array(),
        /* 53 */ array(),
        /* 54 */ array(),
        /* 55 */ array(),
        /* 56 */ array(),
        /* 57 */ array(),
        /* 58 */ array(),
        /* 59 */ array(),
        /* 60 */ array(),
);
    static public $yy_default = array(
 /*     0 */    97,   97,   97,   97,   97,   97,   97,   97,   97,   97,
 /*    10 */    97,   97,   97,   97,   97,   97,   97,   97,   97,   97,
 /*    20 */    72,   73,   97,   97,   97,   79,   67,   64,   65,   97,
 /*    30 */    75,   97,   74,   62,   63,   78,   92,   91,   96,   93,
 /*    40 */    95,   70,   68,   94,   71,   82,   69,   84,   77,   87,
 /*    50 */    81,   83,   80,   86,   85,   88,   61,   89,   66,   90,
 /*    60 */    76,
);
    const YYNOCODE = 23;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 61;
    const YYNRULE = 36;
    const YYERRORSYMBOL = 10;
    const YYERRSYMDT = 'yy0';
    const YYFALLBACK = 0;
    public static $yyFallback = array(
    );
    public function Trace($TraceFILE, $zTracePrompt)
    {
        if (!$TraceFILE) {
            $zTracePrompt = 0;
        } elseif (!$zTracePrompt) {
            $TraceFILE = 0;
        }
        $this->yyTraceFILE = $TraceFILE;
        $this->yyTracePrompt = $zTracePrompt;
    }

    public function PrintTrace()
    {
        $this->yyTraceFILE = fopen('php://output', 'w');
        $this->yyTracePrompt = '<br>';
    }

    public $yyTraceFILE;
    public $yyTracePrompt;
    public $yyidx;                    /* Index of top element in stack */
    public $yyerrcnt;                 /* Shifts left before out of the error */
    public $yystack = array();  /* The parser's stack */

    public $yyTokenName = array(
  '$',             'PHPCODE',       'COMMENTSTART',  'COMMENTEND',  
  'PI',            'SUBPATTERN',    'CODE',          'PATTERN',     
  'QUOTE',         'SINGLEQUOTE',   'error',         'start',       
  'lexfile',       'declare',       'rules',         'declarations',
  'processing_instructions',  'pattern_declarations',  'subpattern',    'rule',        
  'reset_rules',   'rule_subpattern',
    );

    public static $yyRuleName = array(
 /*   0 */ "start ::= lexfile",
 /*   1 */ "lexfile ::= declare rules",
 /*   2 */ "lexfile ::= declare PHPCODE rules",
 /*   3 */ "lexfile ::= PHPCODE declare rules",
 /*   4 */ "lexfile ::= PHPCODE declare PHPCODE rules",
 /*   5 */ "declare ::= COMMENTSTART declarations COMMENTEND",
 /*   6 */ "declarations ::= processing_instructions pattern_declarations",
 /*   7 */ "processing_instructions ::= PI SUBPATTERN",
 /*   8 */ "processing_instructions ::= PI CODE",
 /*   9 */ "processing_instructions ::= processing_instructions PI SUBPATTERN",
 /*  10 */ "processing_instructions ::= processing_instructions PI CODE",
 /*  11 */ "pattern_declarations ::= PATTERN subpattern",
 /*  12 */ "pattern_declarations ::= pattern_declarations PATTERN subpattern",
 /*  13 */ "rules ::= COMMENTSTART rule COMMENTEND",
 /*  14 */ "rules ::= COMMENTSTART PI SUBPATTERN rule COMMENTEND",
 /*  15 */ "rules ::= COMMENTSTART rule COMMENTEND PHPCODE",
 /*  16 */ "rules ::= COMMENTSTART PI SUBPATTERN rule COMMENTEND PHPCODE",
 /*  17 */ "rules ::= reset_rules rule COMMENTEND",
 /*  18 */ "rules ::= reset_rules PI SUBPATTERN rule COMMENTEND",
 /*  19 */ "rules ::= reset_rules rule COMMENTEND PHPCODE",
 /*  20 */ "rules ::= reset_rules PI SUBPATTERN rule COMMENTEND PHPCODE",
 /*  21 */ "reset_rules ::= rules COMMENTSTART",
 /*  22 */ "rule ::= rule_subpattern CODE",
 /*  23 */ "rule ::= rule rule_subpattern CODE",
 /*  24 */ "rule_subpattern ::= QUOTE",
 /*  25 */ "rule_subpattern ::= SINGLEQUOTE",
 /*  26 */ "rule_subpattern ::= SUBPATTERN",
 /*  27 */ "rule_subpattern ::= rule_subpattern QUOTE",
 /*  28 */ "rule_subpattern ::= rule_subpattern SINGLEQUOTE",
 /*  29 */ "rule_subpattern ::= rule_subpattern SUBPATTERN",
 /*  30 */ "subpattern ::= QUOTE",
 /*  31 */ "subpattern ::= SINGLEQUOTE",
 /*  32 */ "subpattern ::= SUBPATTERN",
 /*  33 */ "subpattern ::= subpattern QUOTE",
 /*  34 */ "subpattern ::= subpattern SINGLEQUOTE",
 /*  35 */ "subpattern ::= subpattern SUBPATTERN",
    );

    public function tokenName($tokenType)
    {
        if ($tokenType === 0) {
            return 'End of Input';
        }
        if ($tokenType > 0 && $tokenType < count($this->yyTokenName)) {
            return $this->yyTokenName[$tokenType];
        } else {
            return "Unknown";
        }
    }

    public static function yy_destructor($yymajor, $yypminor)
    {
        switch ($yymajor) {
            default:  break;   /* If no destructor action specified: do nothing */
        }
    }

    public function yy_pop_parser_stack()
    {
        if (!count($this->yystack)) {
            return;
        }
        $yytos = array_pop($this->yystack);
        if ($this->yyTraceFILE && $this->yyidx >= 0) {
            fwrite($this->yyTraceFILE,
                $this->yyTracePrompt . 'Popping ' . $this->yyTokenName[$yytos->major] .
                    "\n");
        }
        $yymajor = $yytos->major;
        self::yy_destructor($yymajor, $yytos->minor);
        $this->yyidx--;

        return $yymajor;
    }

    public function __destruct()
    {
        while ($this->yystack !== Array()) {
            $this->yy_pop_parser_stack();
        }
        if (is_resource($this->yyTraceFILE)) {
            fclose($this->yyTraceFILE);
        }
    }

    public function yy_get_expected_tokens($token)
    {
        $state = $this->yystack[$this->yyidx]->stateno;
        $expected = self::$yyExpectedTokens[$state];
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return $expected;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return array_unique($expected);
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate])) {
                $expected = array_merge($expected, self::$yyExpectedTokens[$nextstate]);
                            if (in_array($token,
                                  self::$yyExpectedTokens[$nextstate], true)) {
                            $this->yyidx = $yyidx;
                            $this->yystack = $stack;

                            return array_unique($expected);
                        }
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new PHP_LexerGenerator_ParseryyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return array_unique($expected);
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return $expected;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
    $this->yyidx = $yyidx;
    $this->yystack = $stack;

        return array_unique($expected);
    }

    public function yy_is_expected_token($token)
    {
        if ($token === 0) {
            return true; // 0 is not part of this
        }
        $state = $this->yystack[$this->yyidx]->stateno;
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return true;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return true;
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate]) &&
                          in_array($token, self::$yyExpectedTokens[$nextstate], true)) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;

                        return true;
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new PHP_LexerGenerator_ParseryyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        if (!$token) {
                            // end of input: this is valid
                            return true;
                        }
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return false;
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return true;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        $this->yyidx = $yyidx;
        $this->yystack = $stack;

        return true;
    }

   public function yy_find_shift_action($iLookAhead)
    {
        $stateno = $this->yystack[$this->yyidx]->stateno;

        /* if ($this->yyidx < 0) return self::YY_NO_ACTION;  */
        if (!isset(self::$yy_shift_ofst[$stateno])) {
            // no shift actions
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_shift_ofst[$stateno];
        if ($i === self::YY_SHIFT_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            if (count(self::$yyFallback) && $iLookAhead < count(self::$yyFallback)
                   && ($iFallback = self::$yyFallback[$iLookAhead]) != 0) {
                if ($this->yyTraceFILE) {
                    fwrite($this->yyTraceFILE, $this->yyTracePrompt . "FALLBACK " .
                        $this->yyTokenName[$iLookAhead] . " => " .
                        $this->yyTokenName[$iFallback] . "\n");
                }

                return $this->yy_find_shift_action($iFallback);
            }

            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    public function yy_find_reduce_action($stateno, $iLookAhead)
    {
        /* $stateno = $this->yystack[$this->yyidx]->stateno; */

        if (!isset(self::$yy_reduce_ofst[$stateno])) {
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_reduce_ofst[$stateno];
        if ($i == self::YY_REDUCE_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    public function yy_shift($yyNewState, $yyMajor, $yypMinor)
    {
        $this->yyidx++;
        if ($this->yyidx >= self::YYSTACKDEPTH) {
            $this->yyidx--;
            if ($this->yyTraceFILE) {
                fprintf($this->yyTraceFILE, "%sStack Overflow!\n", $this->yyTracePrompt);
            }
            while ($this->yyidx >= 0) {
                $this->yy_pop_parser_stack();
            }

            return;
        }
        $yytos = new PHP_LexerGenerator_ParseryyStackEntry;
        $yytos->stateno = $yyNewState;
        $yytos->major = $yyMajor;
        $yytos->minor = $yypMinor;
        array_push($this->yystack, $yytos);
        if ($this->yyTraceFILE && $this->yyidx > 0) {
            fprintf($this->yyTraceFILE, "%sShift %d\n", $this->yyTracePrompt,
                $yyNewState);
            fprintf($this->yyTraceFILE, "%sStack:", $this->yyTracePrompt);
            for ($i = 1; $i <= $this->yyidx; $i++) {
                fprintf($this->yyTraceFILE, " %s",
                    $this->yyTokenName[$this->yystack[$i]->major]);
            }
            fwrite($this->yyTraceFILE,"\n");
        }
    }

    public static $yyRuleInfo = array(
  array( 'lhs' => 11, 'rhs' => 1 ),
  array( 'lhs' => 12, 'rhs' => 2 ),
  array( 'lhs' => 12, 'rhs' => 3 ),
  array( 'lhs' => 12, 'rhs' => 3 ),
  array( 'lhs' => 12, 'rhs' => 4 ),
  array( 'lhs' => 13, 'rhs' => 3 ),
  array( 'lhs' => 15, 'rhs' => 2 ),
  array( 'lhs' => 16, 'rhs' => 2 ),
  array( 'lhs' => 16, 'rhs' => 2 ),
  array( 'lhs' => 16, 'rhs' => 3 ),
  array( 'lhs' => 16, 'rhs' => 3 ),
  array( 'lhs' => 17, 'rhs' => 2 ),
  array( 'lhs' => 17, 'rhs' => 3 ),
  array( 'lhs' => 14, 'rhs' => 3 ),
  array( 'lhs' => 14, 'rhs' => 5 ),
  array( 'lhs' => 14, 'rhs' => 4 ),
  array( 'lhs' => 14, 'rhs' => 6 ),
  array( 'lhs' => 14, 'rhs' => 3 ),
  array( 'lhs' => 14, 'rhs' => 5 ),
  array( 'lhs' => 14, 'rhs' => 4 ),
  array( 'lhs' => 14, 'rhs' => 6 ),
  array( 'lhs' => 20, 'rhs' => 2 ),
  array( 'lhs' => 19, 'rhs' => 2 ),
  array( 'lhs' => 19, 'rhs' => 3 ),
  array( 'lhs' => 21, 'rhs' => 1 ),
  array( 'lhs' => 21, 'rhs' => 1 ),
  array( 'lhs' => 21, 'rhs' => 1 ),
  array( 'lhs' => 21, 'rhs' => 2 ),
  array( 'lhs' => 21, 'rhs' => 2 ),
  array( 'lhs' => 21, 'rhs' => 2 ),
  array( 'lhs' => 18, 'rhs' => 1 ),
  array( 'lhs' => 18, 'rhs' => 1 ),
  array( 'lhs' => 18, 'rhs' => 1 ),
  array( 'lhs' => 18, 'rhs' => 2 ),
  array( 'lhs' => 18, 'rhs' => 2 ),
  array( 'lhs' => 18, 'rhs' => 2 ),
    );

    public static $yyReduceMap = array(
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 7,
        9 => 9,
        10 => 9,
        11 => 11,
        12 => 12,
        13 => 13,
        14 => 14,
        15 => 15,
        16 => 16,
        17 => 17,
        18 => 18,
        19 => 19,
        20 => 20,
        21 => 21,
        22 => 22,
        23 => 23,
        24 => 24,
        25 => 25,
        26 => 26,
        27 => 27,
        28 => 28,
        29 => 29,
        30 => 30,
        31 => 31,
        32 => 32,
        33 => 33,
        34 => 34,
        35 => 35,
    );
#line 693 "LexerGenerator/Parser.y"
    function yy_r1(){

    $this->outputCommonFunctions();

    foreach ($this->yystack[$this->yyidx + 0]->minor as $rule) {
        $this->outputRules($rule['rules'], $rule['statename']);
        if ($rule['code']) {
            fwrite($this->out, $rule['code']);
        }
    }
    }
#line 1338 "LexerGenerator/Parser.php"
#line 704 "LexerGenerator/Parser.y"
    function yy_r2(){

    $this->outputCommonFunctions();

    if (strlen($this->yystack[$this->yyidx + -1]->minor)) {
        fwrite($this->out, $this->yystack[$this->yyidx + -1]->minor);
    }
    foreach ($this->yystack[$this->yyidx + 0]->minor as $rule) {
        $this->outputRules($rule['rules'], $rule['statename']);
        if ($rule['code']) {
            fwrite($this->out, $rule['code']);
        }
    }
    }
#line 1354 "LexerGenerator/Parser.php"
#line 718 "LexerGenerator/Parser.y"
    function yy_r3(){
    if (strlen($this->yystack[$this->yyidx + -2]->minor)) {
        fwrite($this->out, $this->yystack[$this->yyidx + -2]->minor);
    }

    $this->outputCommonFunctions();

    foreach ($this->yystack[$this->yyidx + 0]->minor as $rule) {
        $this->outputRules($rule['rules'], $rule['statename']);
        if ($rule['code']) {
            fwrite($this->out, $rule['code']);
        }
    }
    }
#line 1370 "LexerGenerator/Parser.php"
#line 732 "LexerGenerator/Parser.y"
    function yy_r4(){
    if (strlen($this->yystack[$this->yyidx + -3]->minor)) {
        fwrite($this->out, $this->yystack[$this->yyidx + -3]->minor);
    }

    $this->outputCommonFunctions();

    if (strlen($this->yystack[$this->yyidx + -1]->minor)) {
        fwrite($this->out, $this->yystack[$this->yyidx + -1]->minor);
    }
    foreach ($this->yystack[$this->yyidx + 0]->minor as $rule) {
        $this->outputRules($rule['rules'], $rule['statename']);
        if ($rule['code']) {
            fwrite($this->out, $rule['code']);
        }
    }
    }
#line 1389 "LexerGenerator/Parser.php"
#line 750 "LexerGenerator/Parser.y"
    function yy_r5(){
    $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
    $this->patterns = $this->yystack[$this->yyidx + -1]->minor['patterns'];
    $this->_patternIndex = 1;
    }
#line 1396 "LexerGenerator/Parser.php"
#line 756 "LexerGenerator/Parser.y"
    function yy_r6(){
    $expected = array(
        'counter' => true,
        'input' => true,
        'token' => true,
        'value' => true,
        'line' => true,
    );
    foreach ($this->yystack[$this->yyidx + -1]->minor as $pi) {
        if (isset($expected[$pi['pi']])) {
            unset($expected[$pi['pi']]);
            continue;
        }
        if (count($expected)) {
            throw new Exception('Processing Instructions "' .
                implode(', ', array_keys($expected)) . '" must be defined');
        }
    }
    $expected = array(
        'caseinsensitive' => true,
        'counter' => true,
        'input' => true,
        'token' => true,
        'value' => true,
        'line' => true,
        'matchlongest' => true,
        'unicode' => true,
        'format' => true,
    );
    foreach ($this->yystack[$this->yyidx + -1]->minor as $pi) {
        if (isset($expected[$pi['pi']])) {
            $this->{$pi['pi']} = $pi['definition'];
            if ($pi['pi'] == 'matchlongest') {
                $this->matchlongest = true;
            }
            continue;
        }
        $this->error('Unknown processing instruction %' . $pi['pi'] .
            ', should be one of "' . implode(', ', array_keys($expected)) . '"');
    }
    $this->patternFlags = ($this->caseinsensitive ? 'i' : '')
        . ($this->unicode ? 'u' : '');
    $this->_retvalue = array('patterns' => $this->yystack[$this->yyidx + 0]->minor, 'pis' => $this->yystack[$this->yyidx + -1]->minor);
    $this->_patternIndex = 1;
    }
#line 1443 "LexerGenerator/Parser.php"
#line 802 "LexerGenerator/Parser.y"
    function yy_r7(){
    $this->_retvalue = array(array('pi' => $this->yystack[$this->yyidx + -1]->minor, 'definition' => $this->yystack[$this->yyidx + 0]->minor));
    }
#line 1448 "LexerGenerator/Parser.php"
#line 808 "LexerGenerator/Parser.y"
    function yy_r9(){
    $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
    $this->_retvalue[] = array('pi' => $this->yystack[$this->yyidx + -1]->minor, 'definition' => $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1454 "LexerGenerator/Parser.php"
#line 817 "LexerGenerator/Parser.y"
    function yy_r11(){
    $this->_retvalue = array($this->yystack[$this->yyidx + -1]->minor => $this->yystack[$this->yyidx + 0]->minor);
    // reset internal indicator of where we are in a pattern
    $this->_patternIndex = 0;
    }
#line 1461 "LexerGenerator/Parser.php"
#line 822 "LexerGenerator/Parser.y"
    function yy_r12(){
    $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
    if (isset($this->_retvalue[$this->yystack[$this->yyidx + -1]->minor])) {
        throw new Exception('Pattern "' . $this->yystack[$this->yyidx + -1]->minor . '" is already defined as "' .
            $this->_retvalue[$this->yystack[$this->yyidx + -1]->minor] . '", cannot redefine as "' . $this->yystack[$this->yyidx + 0]->minor->string . '"');
    }
    $this->_retvalue[$this->yystack[$this->yyidx + -1]->minor] = $this->yystack[$this->yyidx + 0]->minor;
    // reset internal indicator of where we are in a pattern declaration
    $this->_patternIndex = 0;
    }
#line 1473 "LexerGenerator/Parser.php"
#line 833 "LexerGenerator/Parser.y"
    function yy_r13(){
    $this->_retvalue = array(array('rules' => $this->yystack[$this->yyidx + -1]->minor, 'code' => '', 'statename' => ''));
    }
#line 1478 "LexerGenerator/Parser.php"
#line 836 "LexerGenerator/Parser.y"
    function yy_r14(){
    if ($this->yystack[$this->yyidx + -3]->minor != 'statename') {
        throw new Exception('Error: only %statename processing instruction ' .
            'is allowed in rule sections (found ' . $this->yystack[$this->yyidx + -3]->minor . ').');
    }
    $this->_retvalue = array(array('rules' => $this->yystack[$this->yyidx + -1]->minor, 'code' => '', 'statename' => $this->yystack[$this->yyidx + -2]->minor));
    }
#line 1487 "LexerGenerator/Parser.php"
#line 843 "LexerGenerator/Parser.y"
    function yy_r15(){
    $this->_retvalue = array(array('rules' => $this->yystack[$this->yyidx + -2]->minor, 'code' => $this->yystack[$this->yyidx + 0]->minor, 'statename' => ''));
    }
#line 1492 "LexerGenerator/Parser.php"
#line 846 "LexerGenerator/Parser.y"
    function yy_r16(){
    if ($this->yystack[$this->yyidx + -4]->minor != 'statename') {
        throw new Exception('Error: only %statename processing instruction ' .
            'is allowed in rule sections (found ' . $this->yystack[$this->yyidx + -4]->minor . ').');
    }
    $this->_retvalue = array(array('rules' => $this->yystack[$this->yyidx + -2]->minor, 'code' => $this->yystack[$this->yyidx + 0]->minor, 'statename' => $this->yystack[$this->yyidx + -3]->minor));
    $this->_patternIndex = 1;
    }
#line 1502 "LexerGenerator/Parser.php"
#line 854 "LexerGenerator/Parser.y"
    function yy_r17(){
    $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
    $this->_retvalue[] = array('rules' => $this->yystack[$this->yyidx + -1]->minor, 'code' => '', 'statename' => '');
    $this->_patternIndex = 1;
    }
#line 1509 "LexerGenerator/Parser.php"
#line 859 "LexerGenerator/Parser.y"
    function yy_r18(){
    if ($this->yystack[$this->yyidx + -3]->minor != 'statename') {
        throw new Exception('Error: only %statename processing instruction ' .
            'is allowed in rule sections (found ' . $this->yystack[$this->yyidx + -3]->minor . ').');
    }
    $this->_retvalue = $this->yystack[$this->yyidx + -4]->minor;
    $this->_retvalue[] = array('rules' => $this->yystack[$this->yyidx + -1]->minor, 'code' => '', 'statename' => $this->yystack[$this->yyidx + -2]->minor);
    }
#line 1519 "LexerGenerator/Parser.php"
#line 867 "LexerGenerator/Parser.y"
    function yy_r19(){
    $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor;
    $this->_retvalue[] = array('rules' => $this->yystack[$this->yyidx + -2]->minor, 'code' => $this->yystack[$this->yyidx + 0]->minor, 'statename' => '');
    }
#line 1525 "LexerGenerator/Parser.php"
#line 871 "LexerGenerator/Parser.y"
    function yy_r20(){
    if ($this->yystack[$this->yyidx + -4]->minor != 'statename') {
        throw new Exception('Error: only %statename processing instruction ' .
            'is allowed in rule sections (found ' . $this->yystack[$this->yyidx + -4]->minor . ').');
    }
    $this->_retvalue = $this->yystack[$this->yyidx + -5]->minor;
    $this->_retvalue[] = array('rules' => $this->yystack[$this->yyidx + -2]->minor, 'code' => $this->yystack[$this->yyidx + 0]->minor, 'statename' => $this->yystack[$this->yyidx + -3]->minor);
    }
#line 1535 "LexerGenerator/Parser.php"
#line 880 "LexerGenerator/Parser.y"
    function yy_r21(){
    $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor;
    $this->_patternIndex = 1;
    }
#line 1541 "LexerGenerator/Parser.php"
#line 885 "LexerGenerator/Parser.y"
    function yy_r22(){
    $name = $this->yystack[$this->yyidx + -1]->minor[1];
    $this->yystack[$this->yyidx + -1]->minor = $this->yystack[$this->yyidx + -1]->minor[0];
    $this->yystack[$this->yyidx + -1]->minor = $this->_validatePattern($this->yystack[$this->yyidx + -1]->minor);
    $this->_patternIndex += $this->yystack[$this->yyidx + -1]->minor['subpatterns'] + 1;
    if (@preg_match('/' . str_replace('/', '\\/', $this->yystack[$this->yyidx + -1]->minor['pattern']) . '/', '')) {
        $this->error('Rule "' . $name . '" can match the empty string, this will break lexing');
    }
    $this->_retvalue = array(array('pattern' => str_replace('/', '\\/', $this->yystack[$this->yyidx + -1]->minor->string), 'code' => $this->yystack[$this->yyidx + 0]->minor, 'subpatterns' => $this->yystack[$this->yyidx + -1]->minor['subpatterns']));
    }
#line 1553 "LexerGenerator/Parser.php"
#line 895 "LexerGenerator/Parser.y"
    function yy_r23(){
    $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;
    $name = $this->yystack[$this->yyidx + -1]->minor[1];
    $this->yystack[$this->yyidx + -1]->minor = $this->yystack[$this->yyidx + -1]->minor[0];
    $this->yystack[$this->yyidx + -1]->minor = $this->_validatePattern($this->yystack[$this->yyidx + -1]->minor);
    $this->_patternIndex += $this->yystack[$this->yyidx + -1]->minor['subpatterns'] + 1;
    if (@preg_match('/' . str_replace('/', '\\/', $this->yystack[$this->yyidx + -1]->minor['pattern']) . '/', '')) {
        $this->error('Rule "' . $name . '" can match the empty string, this will break lexing');
    }
    $this->_retvalue[] = array('pattern' => str_replace('/', '\\/', $this->yystack[$this->yyidx + -1]->minor->string), 'code' => $this->yystack[$this->yyidx + 0]->minor, 'subpatterns' => $this->yystack[$this->yyidx + -1]->minor['subpatterns']);
    }
#line 1566 "LexerGenerator/Parser.php"
#line 907 "LexerGenerator/Parser.y"
    function yy_r24(){
    $this->_retvalue = array(preg_quote($this->yystack[$this->yyidx + 0]->minor, '/'), $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1571 "LexerGenerator/Parser.php"
#line 910 "LexerGenerator/Parser.y"
    function yy_r25(){
    $this->_retvalue = array($this->makeCaseInsensitve(preg_quote($this->yystack[$this->yyidx + 0]->minor, '/')), $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1576 "LexerGenerator/Parser.php"
#line 913 "LexerGenerator/Parser.y"
    function yy_r26(){
    if (!isset($this->patterns[$this->yystack[$this->yyidx + 0]->minor])) {
        $this->error('Undefined pattern "' . $this->yystack[$this->yyidx + 0]->minor . '" used in rules');
        throw new Exception('Undefined pattern "' . $this->yystack[$this->yyidx + 0]->minor . '" used in rules');
    }
    $this->_retvalue = array($this->patterns[$this->yystack[$this->yyidx + 0]->minor], $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1585 "LexerGenerator/Parser.php"
#line 920 "LexerGenerator/Parser.y"
    function yy_r27(){
    $this->_retvalue = array($this->yystack[$this->yyidx + -1]->minor[0] . preg_quote($this->yystack[$this->yyidx + 0]->minor, '/'), $this->yystack[$this->yyidx + -1]->minor[1] . ' ' . $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1590 "LexerGenerator/Parser.php"
#line 923 "LexerGenerator/Parser.y"
    function yy_r28(){
    $this->_retvalue = array($this->yystack[$this->yyidx + -1]->minor[0] . $this->makeCaseInsensitve(preg_quote($this->yystack[$this->yyidx + 0]->minor, '/')), $this->yystack[$this->yyidx + -1]->minor[1] . ' ' . $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1595 "LexerGenerator/Parser.php"
#line 926 "LexerGenerator/Parser.y"
    function yy_r29(){
    if (!isset($this->patterns[$this->yystack[$this->yyidx + 0]->minor])) {
        $this->error('Undefined pattern "' . $this->yystack[$this->yyidx + 0]->minor . '" used in rules');
        throw new Exception('Undefined pattern "' . $this->yystack[$this->yyidx + 0]->minor . '" used in rules');
    }
    $this->_retvalue = array($this->yystack[$this->yyidx + -1]->minor[0] . $this->patterns[$this->yystack[$this->yyidx + 0]->minor], $this->yystack[$this->yyidx + -1]->minor[1] . ' ' . $this->yystack[$this->yyidx + 0]->minor);
    }
#line 1604 "LexerGenerator/Parser.php"
#line 934 "LexerGenerator/Parser.y"
    function yy_r30(){
    $this->_retvalue = preg_quote($this->yystack[$this->yyidx + 0]->minor, '/');
    }
#line 1609 "LexerGenerator/Parser.php"
#line 937 "LexerGenerator/Parser.y"
    function yy_r31(){
    $this->_retvalue = $this->makeCaseInsensitve(preg_quote($this->yystack[$this->yyidx + 0]->minor, '/'));
    }
#line 1614 "LexerGenerator/Parser.php"
#line 940 "LexerGenerator/Parser.y"
    function yy_r32(){
    // increment internal sub-pattern counter
    // adjust back-references in pattern based on previous pattern
    $test = $this->_validatePattern($this->yystack[$this->yyidx + 0]->minor, true);
    $this->_patternIndex += $test['subpatterns'];
    $this->_retvalue = $test['pattern'];
    }
#line 1623 "LexerGenerator/Parser.php"
#line 947 "LexerGenerator/Parser.y"
    function yy_r33(){
    $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor . preg_quote($this->yystack[$this->yyidx + 0]->minor, '/');
    }
#line 1628 "LexerGenerator/Parser.php"
#line 950 "LexerGenerator/Parser.y"
    function yy_r34(){
    $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor . $this->makeCaseInsensitve(preg_quote($this->yystack[$this->yyidx + 0]->minor, '/'));
    }
#line 1633 "LexerGenerator/Parser.php"
#line 953 "LexerGenerator/Parser.y"
    function yy_r35(){
    // increment internal sub-pattern counter
    // adjust back-references in pattern based on previous pattern
    $test = $this->_validatePattern($this->yystack[$this->yyidx + 0]->minor, true);
    $this->_patternIndex += $test['subpatterns'];
    $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor . $test['pattern'];
    }
#line 1642 "LexerGenerator/Parser.php"

    private $_retvalue;

    public function yy_reduce($yyruleno)
    {
        $yymsp = $this->yystack[$this->yyidx];
        if ($this->yyTraceFILE && $yyruleno >= 0
              && $yyruleno < count(self::$yyRuleName)) {
            fprintf($this->yyTraceFILE, "%sReduce (%d) [%s].\n",
                $this->yyTracePrompt, $yyruleno,
                self::$yyRuleName[$yyruleno]);
        }

        $this->_retvalue = $yy_lefthand_side = null;
        if (array_key_exists($yyruleno, self::$yyReduceMap)) {
            // call the action
            $this->_retvalue = null;
            $this->{'yy_r' . self::$yyReduceMap[$yyruleno]}();
            $yy_lefthand_side = $this->_retvalue;
        }
        $yygoto = self::$yyRuleInfo[$yyruleno]['lhs'];
        $yysize = self::$yyRuleInfo[$yyruleno]['rhs'];
        $this->yyidx -= $yysize;
        for ($i = $yysize; $i; $i--) {
            // pop all of the right-hand side parameters
            array_pop($this->yystack);
        }
        $yyact = $this->yy_find_reduce_action($this->yystack[$this->yyidx]->stateno, $yygoto);
        if ($yyact < self::YYNSTATE) {
            if (!$this->yyTraceFILE && $yysize) {
                $this->yyidx++;
                $x = new PHP_LexerGenerator_ParseryyStackEntry;
                $x->stateno = $yyact;
                $x->major = $yygoto;
                $x->minor = $yy_lefthand_side;
                $this->yystack[$this->yyidx] = $x;
            } else {
                $this->yy_shift($yyact, $yygoto, $yy_lefthand_side);
            }
        } elseif ($yyact == self::YYNSTATE + self::YYNRULE + 1) {
            $this->yy_accept();
        }
    }

    public function yy_parse_failed()
    {
        if ($this->yyTraceFILE) {
            fprintf($this->yyTraceFILE, "%sFail!\n", $this->yyTracePrompt);
        } while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
    }

    public function yy_syntax_error($yymajor, $TOKEN)
    {
#line 66 "LexerGenerator/Parser.y"

    echo "Syntax Error on line " . $this->lex->line . ": token '" .
        $this->lex->value . "' while parsing rule:";
    foreach ($this->yystack as $entry) {
        echo $this->tokenName($entry->major) . ' ';
    }
    foreach ($this->yy_get_expected_tokens($yymajor) as $token) {
        $expect[] = self::$yyTokenName[$token];
    }
    throw new Exception('Unexpected ' . $this->tokenName($yymajor) . '(' . $TOKEN
        . '), expected one of: ' . implode(',', $expect));
#line 1709 "LexerGenerator/Parser.php"
    }

    public function yy_accept()
    {
        if ($this->yyTraceFILE) {
            fprintf($this->yyTraceFILE, "%sAccept!\n", $this->yyTracePrompt);
        } while ($this->yyidx >= 0) {
            $stack = $this->yy_pop_parser_stack();
        }
    }

    public function doParse($yymajor, $yytokenvalue)
    {
        $yyerrorhit = 0;   /* True if yymajor has invoked an error */

        if ($this->yyidx === null || $this->yyidx < 0) {
            $this->yyidx = 0;
            $this->yyerrcnt = -1;
            $x = new PHP_LexerGenerator_ParseryyStackEntry;
            $x->stateno = 0;
            $x->major = 0;
            $this->yystack = array();
            array_push($this->yystack, $x);
        }
        $yyendofinput = ($yymajor==0);

        if ($this->yyTraceFILE) {
            fprintf($this->yyTraceFILE, "%sInput %s\n",
                $this->yyTracePrompt, $this->yyTokenName[$yymajor]);
        }

        do {
            $yyact = $this->yy_find_shift_action($yymajor);
            if ($yymajor < self::YYERRORSYMBOL &&
                  !$this->yy_is_expected_token($yymajor)) {
                // force a syntax error
                $yyact = self::YY_ERROR_ACTION;
            }
            if ($yyact < self::YYNSTATE) {
                $this->yy_shift($yyact, $yymajor, $yytokenvalue);
                $this->yyerrcnt--;
                if ($yyendofinput && $this->yyidx >= 0) {
                    $yymajor = 0;
                } else {
                    $yymajor = self::YYNOCODE;
                }
            } elseif ($yyact < self::YYNSTATE + self::YYNRULE) {
                $this->yy_reduce($yyact - self::YYNSTATE);
            } elseif ($yyact == self::YY_ERROR_ACTION) {
                if ($this->yyTraceFILE) {
                    fprintf($this->yyTraceFILE, "%sSyntax Error!\n",
                        $this->yyTracePrompt);
                }
                if (self::YYERRORSYMBOL) {
                    if ($this->yyerrcnt < 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $yymx = $this->yystack[$this->yyidx]->major;
                    if ($yymx == self::YYERRORSYMBOL || $yyerrorhit) {
                        if ($this->yyTraceFILE) {
                            fprintf($this->yyTraceFILE, "%sDiscard input token %s\n",
                                $this->yyTracePrompt, $this->yyTokenName[$yymajor]);
                        }
                        $this->yy_destructor($yymajor, $yytokenvalue);
                        $yymajor = self::YYNOCODE;
                    } else {
                        while ($this->yyidx >= 0 &&
                                 $yymx != self::YYERRORSYMBOL &&
        ($yyact = $this->yy_find_shift_action(self::YYERRORSYMBOL)) >= self::YYNSTATE
                              ){
                            $this->yy_pop_parser_stack();
                        }
                        if ($this->yyidx < 0 || $yymajor==0) {
                            $this->yy_destructor($yymajor, $yytokenvalue);
                            $this->yy_parse_failed();
                            $yymajor = self::YYNOCODE;
                        } elseif ($yymx != self::YYERRORSYMBOL) {
                            $u2 = 0;
                            $this->yy_shift($yyact, self::YYERRORSYMBOL, $u2);
                        }
                    }
                    $this->yyerrcnt = 3;
                    $yyerrorhit = 1;
                } else {
                    if ($this->yyerrcnt <= 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $this->yyerrcnt = 3;
                    $this->yy_destructor($yymajor, $yytokenvalue);
                    if ($yyendofinput) {
                        $this->yy_parse_failed();
                    }
                    $yymajor = self::YYNOCODE;
                }
            } else {
                $this->yy_accept();
                $yymajor = self::YYNOCODE;
            }
        } while ($yymajor != self::YYNOCODE && $this->yyidx >= 0);
    }
}
