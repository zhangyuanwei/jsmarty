/**
 * Smarty Internal Plugin Templatelexer
 *
 * This is the lexer to break the template source into tokens
 * @package Smarty
 * @subpackage Compiler
 * @author Uwe Tews
 * @modifier zhangyuanwei
 */

function preg_quote(str) {
    return String(str)
        .replace(/[.\\+*?\[\^\]$(){}=!<>|:\-]/g, "\\$&");
}

/**
 * Smarty Internal Plugin Templatelexer
 */

function Smarty_Internal_Templatelexer(data, compiler) {
    //      this.data = preg_replace("/(\r\n|\r|\n)/", "\n", data);
    this.data = data;
    this.dataLength = data.length;
    this.counter = 0;
    this.token = null;
    this.value = null;
    this.node = null;
    this.line = 1;
    this.taglineno = 1;
    this.state = 1;
    this.state_name = {
        "1": 'TEXT',
        "2": 'SMARTY',
        "3": 'LITERAL',
        "4": 'DOUBLEQUOTEDSTRING',
        "5": 'CHILDBODY'
    };
    this.heredoc_id_stack = [];
    //this.yyTraceFILE;
    //this.yyTracePrompt;

    this.smarty_token_names = { // Text for parser error messages
        'IDENTITY': '===',
        'NONEIDENTITY': '!==',
        'EQUALS': '==',
        'NOTEQUALS': '!=',
        'GREATEREQUAL': '(>=,ge)',
        'LESSEQUAL': '(<=,le)',
        'GREATERTHAN': '(>,gt)',
        'LESSTHAN': '(<,lt)',
        'MOD': '(%,mod)',
        'NOT': '(!,not)',
        'LAND': '(&&,and)',
        'LOR': '(||,or)',
        'LXOR': 'xor',
        'OPENP': '(',
        'CLOSEP': ')',
        'OPENB': '[',
        'CLOSEB': ']',
        'PTR': '->',
        'APTR': '=>',
        'EQUAL': '=',
        'NUMBER': 'number',
        'UNIMATH': '+" , "-',
        'MATH': '*" , "/" , "%',
        'INCDEC': '++" , "--',
        'SPACE': ' ',
        'DOLLAR': '$',
        'SEMICOLON': ';',
        'COLON': ':',
        'DOUBLECOLON': '::',
        'AT': '@',
        'HATCH': '#',
        'QUOTE': '"',
        'BACKTICK': '`',
        'VERT': '|',
        'DOT': '.',
        'COMMA': '","',
        'ANDSYM': '"&"',
        'QMARK': '"?"',
        'ID': 'identifier',
        'TEXT': 'text',
        'FAKEPHPSTARTTAG': 'Fake PHP start tag',
        'PHPSTARTTAG': 'PHP start tag',
        'PHPENDTAG': 'PHP end tag',
        'LITERALSTART': 'Literal start',
        'LITERALEND': 'Literal end',
        'LDELSLASH': 'closing tag',
        'COMMENT': 'comment',
        'AS': 'as',
        'TO': 'to'
    };

    this.smarty = compiler.smarty;
    this.compiler = compiler;
    this.ldel = preg_quote(this.smarty.left_delimiter);
    this.ldel_length = this.smarty.left_delimiter.length;
    this.rdel = preg_quote(this.smarty.right_delimiter);
    this.rdel_length = this.smarty.right_delimiter.length;
    this.smarty_token_names['LDEL'] = this.smarty.left_delimiter;
    this.smarty_token_names['RDEL'] = this.smarty.right_delimiter;
    //this.mbstring_overload = ini_get('mbstring.func_overload') & 2;

    this._init && this._init();
}


(function(self, proto) {

    proto._init = function()
    {
        this._yy_state = 1;
        this._yy_stack = [];
    };

    proto.yylex = function()
    {
        return this['yylex' + this._yy_state]();
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
        

    proto.yylex1 = function()
    {
        if (this.counter >= this.data.length) {
            return false; // end of input
        }
        var tokenMap = {"1":0,"2":1,"4":0,"5":0,"6":0,"7":1,"9":0,"10":0,"11":0,"12":0,"13":0,"14":0,"15":0,"16":0,"17":0,"18":0,"19":0};
        
        var yy_global_pattern = new RegExp("(\\{\\})|("+this.ldel+"\\*([\\S\\s]*?)\\*"+this.rdel+")|("+this.ldel+"\\s*strip\\s*"+this.rdel+")|("+this.ldel+"\\s*\/strip\\s*"+this.rdel+")|("+this.ldel+"\\s*literal\\s*"+this.rdel+")|("+this.ldel+"\\s*(if|elseif|else if|while)\\s+)|("+this.ldel+"\\s*for\\s+)|("+this.ldel+"\\s*foreach(?![^\\s]))|("+this.ldel+"\\s*setfilter\\s+)|("+this.ldel+"\\s*\/)|("+this.ldel+"\\s*)|(<\\?(?:php\\w+|=|[a-zA-Z]+)?)|(\\?>)|(\\s*"+this.rdel+")|(<%)|(%>)|([\\S\\s])", 'g');
        
        do {
            yy_global_pattern.lastIndex = this.counter;
            var result = yy_global_pattern.exec(this.data);
            if(result){
                var yymatches = result, yysubmatches = [];
                if (!yymatches[0]) {
                    throw new Error('Error: lexing failed because a rule matched' +
                        ' an empty string.  Input "' + this.data
                        .substr(this.counter, 5) + '..." state:TEXT');
                }
                for(var i = 1, count = yymatches.length; i < count; i++) { // skip global match
                    var item = yymatches[i];
                    if(item){
                        this.token = i;    // token number
                        this.value = item; // token value
                        break;
                    }
                }
                if (tokenMap[this.token]) {
                    // extract sub-patterns for passing to lex function
                    yysubmatches = yymatches.slice(this.token + 1, tokenMap[this.token]);  
                }
                var r = this['yy_r1_' + this.token](yysubmatches);
                if (r === undefined) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    // accept this token
                    return true;
                } else if (r === true) {
                    // we have changed state
                    // process this token in the new state
                    return this.yylex();
                } else if (r === false) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    if (this.counter >= this.data.length) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Error('Unexpected input at line' + this.line +
                    ': ' + this.data[this.counter]);
            }
            break;
        } while (true);
        
    }; // end function
        
    self.TEXT = 1;
            proto.yy_r1_1 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_TEXT;
    };
    proto.yy_r1_2 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_COMMENT;
    };
    proto.yy_r1_4 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false)  {
    this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_STRIPON;
  }
    };
    proto.yy_r1_5 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_STRIPOFF;
  }
    };
    proto.yy_r1_6 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_LITERALSTART;
    this.yypushstate(self.LITERAL);
   }
    };
    proto.yy_r1_7 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELIF;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r1_9 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELFOR;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r1_10 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELFOREACH;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r1_11 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELSETFILTER;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r1_12 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
    this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_LDELSLASH;
    this.yypushstate(self.SMARTY);
    this.taglineno = this.line;
  }
    };
    proto.yy_r1_13 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
    this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDEL;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r1_14 = function($yy_subpatterns)
    {

  if(this.value == '<?' || this.value == '<?=' || this.value == '<?php'){
    this.token = Smarty_Internal_Templateparser.TP_PHPSTARTTAG;
  } else if (this.value == '<?xml') {
    this.token = Smarty_Internal_Templateparser.TP_XMLTAG;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_FAKEPHPSTARTTAG;
    this.value = this.value.substr(0, 2);
  }
     };
    proto.yy_r1_15 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_PHPENDTAG;
    };
    proto.yy_r1_16 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_TEXT;
    };
    proto.yy_r1_17 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ASPSTARTTAG;
    };
    proto.yy_r1_18 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ASPENDTAG;
    };
    proto.yy_r1_19 = function($yy_subpatterns)
    {

  var nextReg = new RegExp(this.ldel + "|<\\?|\\?>|<%|%>", "g");
  nextReg.lastIndex = this.counter;

  var to = this.dataLength,
      result;
  if((result = nextReg.exec(this.data)) !== null){
    to = result.index;
  }
  this.value = this.data.substring(this.counter, to);
  this.token = Smarty_Internal_Templateparser.TP_TEXT;
    };


    proto.yylex2 = function()
    {
        if (this.counter >= this.data.length) {
            return false; // end of input
        }
        var tokenMap = {"1":0,"2":0,"3":1,"5":0,"6":0,"7":0,"8":0,"9":0,"10":0,"11":0,"12":0,"13":0,"14":0,"15":1,"17":1,"19":1,"21":0,"22":0,"23":0,"24":0,"25":0,"26":0,"27":0,"28":0,"29":0,"30":0,"31":0,"32":0,"33":0,"34":0,"35":0,"36":0,"37":0,"38":3,"42":0,"43":0,"44":0,"45":0,"46":0,"47":0,"48":0,"49":0,"50":1,"52":1,"54":0,"55":0,"56":0,"57":0,"58":0,"59":0,"60":0,"61":0,"62":0,"63":0,"64":0,"65":0,"66":0,"67":0,"68":0,"69":0,"70":1,"72":0,"73":0,"74":0,"75":0,"76":0};
        
        var yy_global_pattern = new RegExp("(\\\")|('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')|([$]smarty\\.block\\.(child|parent))|(\\$)|(\\s*"+this.rdel+")|(\\s+is\\s+in\\s+)|(\\s+as\\s+)|(\\s+to\\s+)|(\\s+step\\s+)|(\\s+instanceof\\s+)|(\\s*===\\s*)|(\\s*!==\\s*)|(\\s*==\\s*|\\s+eq\\s+)|(\\s*!=\\s*|\\s*<>\\s*|\\s+(ne|neq)\\s+)|(\\s*>=\\s*|\\s+(ge|gte)\\s+)|(\\s*<=\\s*|\\s+(le|lte)\\s+)|(\\s*>\\s*|\\s+gt\\s+)|(\\s*<\\s*|\\s+lt\\s+)|(\\s+mod\\s+)|(!\\s*|not\\s+)|(\\s*&&\\s*|\\s*and\\s+)|(\\s*\\|\\|\\s*|\\s*or\\s+)|(\\s*xor\\s+)|(\\s+is\\s+odd\\s+by\\s+)|(\\s+is\\s+not\\s+odd\\s+by\\s+)|(\\s+is\\s+odd)|(\\s+is\\s+not\\s+odd)|(\\s+is\\s+even\\s+by\\s+)|(\\s+is\\s+not\\s+even\\s+by\\s+)|(\\s+is\\s+even)|(\\s+is\\s+not\\s+even)|(\\s+is\\s+div\\s+by\\s+)|(\\s+is\\s+not\\s+div\\s+by\\s+)|(\\((int(eger)?|bool(ean)?|float|double|real|string|binary|array|object)\\)\\s*)|(\\s*\\(\\s*)|(\\s*\\))|(\\[\\s*)|(\\s*\\])|(\\s*->\\s*)|(\\s*=>\\s*)|(\\s*=\\s*)|(\\+\\+|--)|(\\s*(\\+|-)\\s*)|(\\s*(\\*|\/|%)\\s*)|(@)|(#)|(\\s+[0-9]*[a-zA-Z_][a-zA-Z0-9_\\-:]*\\s*=\\s*)|([0-9]*[a-zA-Z_]\\w*)|(\\d+)|(`)|(\\|)|(\\.)|(\\s*,\\s*)|(\\s*;)|(::)|(\\s*:\\s*)|(\\s*&\\s*)|(\\s*\\?\\s*)|(0[xX][0-9a-fA-F]+)|(\\s+)|("+this.ldel+"\\s*(if|elseif|else if|while)\\s+)|("+this.ldel+"\\s*for\\s+)|("+this.ldel+"\\s*foreach(?![^\\s]))|("+this.ldel+"\\s*\/)|("+this.ldel+"\\s*)|([\\S\\s])", 'g');
        
        do {
            yy_global_pattern.lastIndex = this.counter;
            var result = yy_global_pattern.exec(this.data);
            if(result){
                var yymatches = result, yysubmatches = [];
                if (!yymatches[0]) {
                    throw new Error('Error: lexing failed because a rule matched' +
                        ' an empty string.  Input "' + this.data
                        .substr(this.counter, 5) + '..." state:SMARTY');
                }
                for(var i = 1, count = yymatches.length; i < count; i++) { // skip global match
                    var item = yymatches[i];
                    if(item){
                        this.token = i;    // token number
                        this.value = item; // token value
                        break;
                    }
                }
                if (tokenMap[this.token]) {
                    // extract sub-patterns for passing to lex function
                    yysubmatches = yymatches.slice(this.token + 1, tokenMap[this.token]);  
                }
                var r = this['yy_r2_' + this.token](yysubmatches);
                if (r === undefined) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    // accept this token
                    return true;
                } else if (r === true) {
                    // we have changed state
                    // process this token in the new state
                    return this.yylex();
                } else if (r === false) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    if (this.counter >= this.data.length) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Error('Unexpected input at line' + this.line +
                    ': ' + this.data[this.counter]);
            }
            break;
        } while (true);
        
    }; // end function
        
    self.SMARTY = 2;
            proto.yy_r2_1 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_QUOTE;
  this.yypushstate(self.DOUBLEQUOTEDSTRING);
    };
    proto.yy_r2_2 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_SINGLEQUOTESTRING;
    };
    proto.yy_r2_3 = function($yy_subpatterns)
    {

     this.token = Smarty_Internal_Templateparser.TP_SMARTYBLOCKCHILDPARENT;
     this.taglineno = this.line;
    };
    proto.yy_r2_5 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_DOLLAR;
    };
    proto.yy_r2_6 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_RDEL;
  this.yypopstate();
    };
    proto.yy_r2_7 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISIN;
    };
    proto.yy_r2_8 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_AS;
    };
    proto.yy_r2_9 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_TO;
    };
    proto.yy_r2_10 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_STEP;
    };
    proto.yy_r2_11 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_INSTANCEOF;
    };
    proto.yy_r2_12 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_IDENTITY;
    };
    proto.yy_r2_13 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_NONEIDENTITY;
    };
    proto.yy_r2_14 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_EQUALS;
    };
    proto.yy_r2_15 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_NOTEQUALS;
    };
    proto.yy_r2_17 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_GREATEREQUAL;
    };
    proto.yy_r2_19 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_LESSEQUAL;
    };
    proto.yy_r2_21 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_GREATERTHAN;
    };
    proto.yy_r2_22 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_LESSTHAN;
    };
    proto.yy_r2_23 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_MOD;
    };
    proto.yy_r2_24 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_NOT;
    };
    proto.yy_r2_25 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_LAND;
    };
    proto.yy_r2_26 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_LOR;
    };
    proto.yy_r2_27 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_LXOR;
    };
    proto.yy_r2_28 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISODDBY;
    };
    proto.yy_r2_29 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISNOTODDBY;
    };
    proto.yy_r2_30 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISODD;
    };
    proto.yy_r2_31 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISNOTODD;
    };
    proto.yy_r2_32 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISEVENBY;
    };
    proto.yy_r2_33 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISNOTEVENBY;
    };
    proto.yy_r2_34 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISEVEN;
    };
    proto.yy_r2_35 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISNOTEVEN;
    };
    proto.yy_r2_36 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISDIVBY;
    };
    proto.yy_r2_37 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ISNOTDIVBY;
    };
    proto.yy_r2_38 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_TYPECAST;
    };
    proto.yy_r2_42 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_OPENP;
    };
    proto.yy_r2_43 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_CLOSEP;
    };
    proto.yy_r2_44 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_OPENB;
    };
    proto.yy_r2_45 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_CLOSEB;
    };
    proto.yy_r2_46 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_PTR;
    };
    proto.yy_r2_47 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_APTR;
    };
    proto.yy_r2_48 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_EQUAL;
    };
    proto.yy_r2_49 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_INCDEC;
    };
    proto.yy_r2_50 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_UNIMATH;
    };
    proto.yy_r2_52 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_MATH;
    };
    proto.yy_r2_54 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_AT;
    };
    proto.yy_r2_55 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_HATCH;
    };
    proto.yy_r2_56 = function($yy_subpatterns)
    {

  // resolve conflicts with shorttag and right_delimiter starting with '='
  var start = this.counter + this.value.length - 1;
  //if (substr(this.data, this.counter + strlen(this.value) - 1, this.rdel_length) == this.smarty.right_delimiter) {
  if (this.data.indexOf(this.smarty.right_delimiter, start) == start) {
    // TODO
     preg_match("/\s+/",this.value,$match);
     this.value = $match[0];
     this.token = Smarty_Internal_Templateparser.TP_SPACE;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_ATTR;
  }
    };
    proto.yy_r2_57 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ID;
    };
    proto.yy_r2_58 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_INTEGER;
    };
    proto.yy_r2_59 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_BACKTICK;
  this.yypopstate();
    };
    proto.yy_r2_60 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_VERT;
    };
    proto.yy_r2_61 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_DOT;
    };
    proto.yy_r2_62 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_COMMA;
    };
    proto.yy_r2_63 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_SEMICOLON;
    };
    proto.yy_r2_64 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_DOUBLECOLON;
    };
    proto.yy_r2_65 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_COLON;
    };
    proto.yy_r2_66 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ANDSYM;
    };
    proto.yy_r2_67 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_QMARK;
    };
    proto.yy_r2_68 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_HEX;
    };
    proto.yy_r2_69 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_SPACE;
    };
    proto.yy_r2_70 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELIF;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r2_72 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELFOR;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r2_73 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELFOREACH;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r2_74 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
    this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_LDELSLASH;
    this.yypushstate(self.SMARTY);
    this.taglineno = this.line;
  }
    };
    proto.yy_r2_75 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
    this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDEL;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r2_76 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_TEXT;
    };



    proto.yylex3 = function()
    {
        if (this.counter >= this.data.length) {
            return false; // end of input
        }
        var tokenMap = {"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0};
        
        var yy_global_pattern = new RegExp("("+this.ldel+"\\s*literal\\s*"+this.rdel+")|("+this.ldel+"\\s*\/literal\\s*"+this.rdel+")|(<\\?(?:php\\w+|=|[a-zA-Z]+)?)|(\\?>)|(<%)|(%>)|([\\S\\s])", 'g');
        
        do {
            yy_global_pattern.lastIndex = this.counter;
            var result = yy_global_pattern.exec(this.data);
            if(result){
                var yymatches = result, yysubmatches = [];
                if (!yymatches[0]) {
                    throw new Error('Error: lexing failed because a rule matched' +
                        ' an empty string.  Input "' + this.data
                        .substr(this.counter, 5) + '..." state:LITERAL');
                }
                for(var i = 1, count = yymatches.length; i < count; i++) { // skip global match
                    var item = yymatches[i];
                    if(item){
                        this.token = i;    // token number
                        this.value = item; // token value
                        break;
                    }
                }
                if (tokenMap[this.token]) {
                    // extract sub-patterns for passing to lex function
                    yysubmatches = yymatches.slice(this.token + 1, tokenMap[this.token]);  
                }
                var r = this['yy_r3_' + this.token](yysubmatches);
                if (r === undefined) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    // accept this token
                    return true;
                } else if (r === true) {
                    // we have changed state
                    // process this token in the new state
                    return this.yylex();
                } else if (r === false) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    if (this.counter >= this.data.length) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Error('Unexpected input at line' + this.line +
                    ': ' + this.data[this.counter]);
            }
            break;
        } while (true);
        
    }; // end function
        
    self.LITERAL = 3;
            proto.yy_r3_1 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_LITERALSTART;
    this.yypushstate(self.LITERAL);
  }
    };
    proto.yy_r3_2 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_LITERALEND;
    this.yypopstate();
  }
    };
    proto.yy_r3_3 = function($yy_subpatterns)
    {

   if(this.value == '<?' || this.value == '<?=' || this.value == '<?php'){
    this.token = Smarty_Internal_Templateparser.TP_PHPSTARTTAG;
   } else {
    this.token = Smarty_Internal_Templateparser.TP_FAKEPHPSTARTTAG;
    this.value = this.value.substr(0, 2);
   }
    };
    proto.yy_r3_4 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_PHPENDTAG;
    };
    proto.yy_r3_5 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ASPSTARTTAG;
    };
    proto.yy_r3_6 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_ASPENDTAG;
    };
    proto.yy_r3_7 = function($yy_subpatterns)
    {

  var nextReg = new RegExp(this.ldel + "/?literal" + this.rdel + "|<\\?|\\?>|<%|%>", "g");
  nextReg.lastIndex = this.counter;

  var to = this.dataLength,
      result;
  if((result = nextReg.exec(this.data)) !== null){
    to = result.index;
  } else {
    this.compiler.trigger_template_error("missing or misspelled literal closing tag");
  }
  this.value = this.data.substring(this.counter, to);
  this.token = Smarty_Internal_Templateparser.TP_LITERAL;
    };


    proto.yylex4 = function()
    {
        if (this.counter >= this.data.length) {
            return false; // end of input
        }
        var tokenMap = {"1":1,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0,"10":0,"11":3,"15":0};
        
        var yy_global_pattern = new RegExp("("+this.ldel+"\\s*(if|elseif|else if|while)\\s+)|("+this.ldel+"\\s*for\\s+)|("+this.ldel+"\\s*foreach(?![^\\s]))|("+this.ldel+"\\s*\/)|("+this.ldel+"\\s*)|(\\\")|(`\\$)|(\\$[0-9]*[a-zA-Z_]\\w*)|(\\$)|(([^\\\"\\\\]*?)((?:\\\\.[^\\\"\\\\]*?)*?)(?=("+this.ldel+"|\\$|`\\$|\\\")))|([\\S\\s])", 'g');
        
        do {
            yy_global_pattern.lastIndex = this.counter;
            var result = yy_global_pattern.exec(this.data);
            if(result){
                var yymatches = result, yysubmatches = [];
                if (!yymatches[0]) {
                    throw new Error('Error: lexing failed because a rule matched' +
                        ' an empty string.  Input "' + this.data
                        .substr(this.counter, 5) + '..." state:DOUBLEQUOTEDSTRING');
                }
                for(var i = 1, count = yymatches.length; i < count; i++) { // skip global match
                    var item = yymatches[i];
                    if(item){
                        this.token = i;    // token number
                        this.value = item; // token value
                        break;
                    }
                }
                if (tokenMap[this.token]) {
                    // extract sub-patterns for passing to lex function
                    yysubmatches = yymatches.slice(this.token + 1, tokenMap[this.token]);  
                }
                var r = this['yy_r4_' + this.token](yysubmatches);
                if (r === undefined) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    // accept this token
                    return true;
                } else if (r === true) {
                    // we have changed state
                    // process this token in the new state
                    return this.yylex();
                } else if (r === false) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    if (this.counter >= this.data.length) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Error('Unexpected input at line' + this.line +
                    ': ' + this.data[this.counter]);
            }
            break;
        } while (true);
        
    }; // end function
        
    self.DOUBLEQUOTEDSTRING = 4;
            proto.yy_r4_1 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELIF;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r4_3 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELFOR;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r4_4 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDELFOREACH;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r4_5 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
    this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_LDELSLASH;
    this.yypushstate(self.SMARTY);
    this.taglineno = this.line;
  }
    };
    proto.yy_r4_6 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
    this.token = Smarty_Internal_Templateparser.TP_TEXT;
  } else {
     this.token = Smarty_Internal_Templateparser.TP_LDEL;
     this.yypushstate(self.SMARTY);
     this.taglineno = this.line;
  }
    };
    proto.yy_r4_7 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_QUOTE;
  this.yypopstate();
    };
    proto.yy_r4_8 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_BACKTICK;
  this.value = substr(this.value,0,-1);
  this.yypushstate(self.SMARTY);
  this.taglineno = this.line;
    };
    proto.yy_r4_9 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_DOLLARID;
    };
    proto.yy_r4_10 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_TEXT;
    };
    proto.yy_r4_11 = function($yy_subpatterns)
    {

  this.token = Smarty_Internal_Templateparser.TP_TEXT;
    };
    proto.yy_r4_15 = function($yy_subpatterns)
    {

  if (this.mbstring_overload) {
    $to = mb_strlen(this.data,'latin1');
  } else {
    $to = strlen(this.data);
  }
  if (this.mbstring_overload) {
    this.value = mb_substr(this.data,this.counter,$to-this.counter,'latin1');
  } else {
    this.value = substr(this.data,this.counter,$to-this.counter);
  }
  this.token = Smarty_Internal_Templateparser.TP_TEXT;
    };


    proto.yylex5 = function()
    {
        if (this.counter >= this.data.length) {
            return false; // end of input
        }
        var tokenMap = {"1":0,"2":0,"3":0,"4":0};
        
        var yy_global_pattern = new RegExp("("+this.ldel+"\\s*strip\\s*"+this.rdel+")|("+this.ldel+"\\s*\/strip\\s*"+this.rdel+")|("+this.ldel+"\\s*block)|([\\S\\s])", 'g');
        
        do {
            yy_global_pattern.lastIndex = this.counter;
            var result = yy_global_pattern.exec(this.data);
            if(result){
                var yymatches = result, yysubmatches = [];
                if (!yymatches[0]) {
                    throw new Error('Error: lexing failed because a rule matched' +
                        ' an empty string.  Input "' + this.data
                        .substr(this.counter, 5) + '..." state:CHILDBODY');
                }
                for(var i = 1, count = yymatches.length; i < count; i++) { // skip global match
                    var item = yymatches[i];
                    if(item){
                        this.token = i;    // token number
                        this.value = item; // token value
                        break;
                    }
                }
                if (tokenMap[this.token]) {
                    // extract sub-patterns for passing to lex function
                    yysubmatches = yymatches.slice(this.token + 1, tokenMap[this.token]);  
                }
                var r = this['yy_r5_' + this.token](yysubmatches);
                if (r === undefined) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    // accept this token
                    return true;
                } else if (r === true) {
                    // we have changed state
                    // process this token in the new state
                    return this.yylex();
                } else if (r === false) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    if (this.counter >= this.data.length) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Error('Unexpected input at line' + this.line +
                    ': ' + this.data[this.counter]);
            }
            break;
        } while (true);
        
    }; // end function
        
    self.CHILDBODY = 5;
            proto.yy_r5_1 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     return false;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_STRIPON;
  }
    };
    proto.yy_r5_2 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     return false;
  } else {
    this.token = Smarty_Internal_Templateparser.TP_STRIPOFF;
  }
    };
    proto.yy_r5_3 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     return false;
  } else {
    this.yypopstate();
    return true;
  }
    };
    proto.yy_r5_4 = function($yy_subpatterns)
    {

  if (this.mbstring_overload) {
    $to = mb_strlen(this.data,'latin1');
  } else {
    $to = strlen(this.data);
  }
  preg_match("/"+this.ldel+"\s*((\/)?strip\s*"+this.rdel+"|block\s+)/",this.data,$match,PREG_OFFSET_CAPTURE,this.counter);
  if (isset($match[0][1])) {
    $to = $match[0][1];
  }
  if (this.mbstring_overload) {
    this.value = mb_substr(this.data,this.counter,$to-this.counter,'latin1');
  } else {
    this.value = substr(this.data,this.counter,$to-this.counter);
  }
  return false;
    };


    proto.yylex6 = function()
    {
        if (this.counter >= this.data.length) {
            return false; // end of input
        }
        var tokenMap = {"1":0,"2":0,"3":1,"5":0};
        
        var yy_global_pattern = new RegExp("("+this.ldel+"\\s*block)|("+this.ldel+"\\s*\/block)|("+this.ldel+"\\s*[$]smarty\\.block\\.(child|parent))|([\\S\\s])", 'g');
        
        do {
            yy_global_pattern.lastIndex = this.counter;
            var result = yy_global_pattern.exec(this.data);
            if(result){
                var yymatches = result, yysubmatches = [];
                if (!yymatches[0]) {
                    throw new Error('Error: lexing failed because a rule matched' +
                        ' an empty string.  Input "' + this.data
                        .substr(this.counter, 5) + '..." state:CHILDBLOCK');
                }
                for(var i = 1, count = yymatches.length; i < count; i++) { // skip global match
                    var item = yymatches[i];
                    if(item){
                        this.token = i;    // token number
                        this.value = item; // token value
                        break;
                    }
                }
                if (tokenMap[this.token]) {
                    // extract sub-patterns for passing to lex function
                    yysubmatches = yymatches.slice(this.token + 1, tokenMap[this.token]);  
                }
                var r = this['yy_r6_' + this.token](yysubmatches);
                if (r === undefined) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    // accept this token
                    return true;
                } else if (r === true) {
                    // we have changed state
                    // process this token in the new state
                    return this.yylex();
                } else if (r === false) {
                    this.counter += this.value.length;
                    this.line += this.value.split("\n").length  - 1;
                    if (this.counter >= this.data.length) {
                        return false; // end of input
                    }
                    // skip this token
                    continue;
                }            } else {
                throw new Error('Unexpected input at line' + this.line +
                    ': ' + this.data[this.counter]);
            }
            break;
        } while (true);
        
    }; // end function
        
    self.CHILDBLOCK = 6;
            proto.yy_r6_1 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_BLOCKSOURCE;
  } else {
    this.yypopstate();
    return true;
  }
    };
    proto.yy_r6_2 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_BLOCKSOURCE;
  } else {
    this.yypopstate();
    return true;
  }
    };
    proto.yy_r6_3 = function($yy_subpatterns)
    {

  if (this.smarty.auto_literal && isset(this.value[this.ldel_length]) ? strpos(" \n\t\r", this.value[this.ldel_length]) !== false : false) {
     this.token = Smarty_Internal_Templateparser.TP_BLOCKSOURCE;
  } else {
    this.yypopstate();
    return true;
  }
    };
    proto.yy_r6_5 = function($yy_subpatterns)
    {

  if (this.mbstring_overload) {
    $to = mb_strlen(this.data,'latin1');
  } else {
    $to = strlen(this.data);
  }
  preg_match("/"+this.ldel+"\s*((\/)?block(\s|"+this.rdel+")|[\$]smarty\.block\.(child|parent)\s*"+this.rdel+")/",this.data,$match,PREG_OFFSET_CAPTURE,this.counter);
  if (isset($match[0][1])) {
    $to = $match[0][1];
  }
  if (this.mbstring_overload) {
    this.value = mb_substr(this.data,this.counter,$to-this.counter,'latin1');
  } else {
    this.value = substr(this.data,this.counter,$to-this.counter);
  }
  this.token = Smarty_Internal_Templateparser.TP_BLOCKSOURCE;
    };

    
    if (typeof module === "object" && typeof module.exports === "object") {
        // For CommonJS and CommonJS-like environments where a proper window is present,
        // execute the factory and get jQuery
        // For environments that do not inherently posses a window with a document
        // (such as Node.js), expose a jQuery-making factory as module.exports
        // This accentuates the need for the creation of a real window
        // e.g. var jQuery = require("jquery")(window);
        // See ticket #14549 for more info
        module.exports = self;
    }
})(Smarty_Internal_Templatelexer, Smarty_Internal_Templatelexer.prototype);
// vim600: syn=javascript
