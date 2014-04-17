"use strict";
(function(global){
///*- include_code
%%
//*/

function ParseyyStackEntry (){
    this.stateno = 0; /* The state-number */
    this.major = 0;
    /* The major token value.  This is the code
     ** number for the token at this stack level */
    this.minor = 0;
    /* The user-supplied minor token value.  This
     ** is the value of the token  */
}

var _constructor =
//*- constructor
%%
/*/

    function() {}
    //*/
    ;

//*- class define
%%
/*/

function ParserClass()
//*/
{
    this.yyidx = null;
    this.yyerrcnt = 0;
    this.yystack = [];

    this._retvalue = null;
    return _constructor.apply(this, arguments);
}

(function(self, proto, className) {

    function isset(obj, key) {
        return obj.hasOwnProperty(String(key));
    }

    function count(array) {
        return array.length;
    }

    function in_array(value, array, type) {
        for (var i = 0, length = array.length; i < length; i++) {
            var item = array[i];
            if (type ? item === value : item == value) {
                return true;
            }
        }
        return false;
    }

    //*- inclde_class
%%
    //*/

    //*- token defines
%%
    //*/

    //*- action tables
%%
    //*/

    //*- defines
%%
    //*/

    self.yyFallback = [
        //*- of fallback tokens table
%%
        //*/
    ];

    self.yyTokenName = [
        //*- symbolic names
%%
        //*/
    ];

    self.yyRuleName = [
        //*- rule describes
%%
        //*/
    ];

    proto.tokenName = function(tokenType) {
        if (tokenType === 0) {
            return 'End of Input';
        }
        if (tokenType > 0 && tokenType < count(self.yyTokenName)) {
            return self.yyTokenName[tokenType];
        } else {
            return "Unknown";
        }
    };

    self.yy_destructor = function(yymajor, yypminor) {
        switch (yymajor) {
            //*- symbol popped action
%%
            //*/
            default: break; /* If no destructor action specified: do nothing */
        }
    };

    proto.yy_pop_parser_stack = function() {
        if (!count(this.yystack)) {
            return;
        }
        var yytos = this.yystack.pop();
        var yymajor = yytos.major;
        self.yy_destructor(yymajor, yytos.minor);
        this.yyidx--;

        return yymajor;
    };


    proto.__destruct = function() {
        while (this.yystack.length) {
            this.yy_pop_parser_stack();
        }
    };



    proto.yy_get_expected_tokens = function(token) {
        var state = this.yystack[this.yyidx].stateno;
        var expected = self.yyExpectedTokens[state];
        if (in_array(token, self.yyExpectedTokens[state], true)) {
            return expected;
        }
        var stack = this.yystack.slice(0);
        var yyidx = this.yyidx;
        next: do {
            var yyact = this.yy_find_shift_action(token);
            if (yyact >= self.YYNSTATE && yyact < self.YYNSTATE + self.YYNRULE) {
                // reduce action
                var done = 0;
                do {
                    if (done++ == 100) {
                        this.yyidx = yyidx;
                        this.yystack = stack;
                        // too much recursion prevents proper detection
                        // so give up
                        //return array_unique(expected);
                        return expected;
                    }
                    var yyruleno = yyact - self.YYNSTATE;
                    this.yyidx -= self.yyRuleInfo[yyruleno][1];
                    var nextstate = this.yy_find_reduce_action(
                        this.yystack[this.yyidx].stateno,
                        self.yyRuleInfo[yyruleno][0]);
                    if (isset(self.yyExpectedTokens[nextstate])) {
                        expected = array_merge(expected, self.yyExpectedTokens[nextstate]);
                        if (in_array(token,
                            self.yyExpectedTokens[nextstate], true)) {
                            this.yyidx = yyidx;
                            this.yystack = stack;

                            //return array_unique(expected);
                            return expected;
                        }
                    }
                    if (nextstate < self.YYNSTATE) {
                        // we need to shift a non-terminal
                        this.yyidx++;
                        var x = new ParseyyStackEntry();
                        x.stateno = nextstate;
                        x.major = self.yyRuleInfo[yyruleno][0];
                        this.yystack[this.yyidx] = x;
                        continue next;
                    } else if (nextstate == self.YYNSTATE + self.YYNRULE + 1) {
                        this.yyidx = yyidx;
                        this.yystack = stack;
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        //return array_unique(expected);
                        return expected;
                    } else if (nextstate === self.YY_NO_ACTION) {
                        this.yyidx = yyidx;
                        this.yystack = stack;
                        // input accepted, but not shifted (I guess)
                        return expected;
                    } else {
                        yyact = nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        this.yyidx = yyidx;
        this.yystack = stack;

        //return array_unique(expected);
        return expected;
    };

    proto.yy_is_expected_token = function(token) {
        if (token === 0) {
            return true; // 0 is not part of this
        }
        var state = this.yystack[this.yyidx].stateno;
        if (in_array(token, self.yyExpectedTokens[state], true)) {
            return true;
        }
        var stack = this.yystack.slice(0);
        var yyidx = this.yyidx;
        next: do {
            var yyact = this.yy_find_shift_action(token);
            if (yyact >= self.YYNSTATE && yyact < self.YYNSTATE + self.YYNRULE) {
                // reduce action
                var done = 0;
                do {
                    if (done++ == 100) {
                        this.yyidx = yyidx;
                        this.yystack = stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return true;
                    }
                    var yyruleno = yyact - self.YYNSTATE;
                    this.yyidx -= self.yyRuleInfo[yyruleno][1];
                    var nextstate = this.yy_find_reduce_action(
                        this.yystack[this.yyidx].stateno,
                        self.yyRuleInfo[yyruleno][0]);
                    if (isset(self.yyExpectedTokens, nextstate) &&
                        in_array(token, self.yyExpectedTokens[nextstate], true)) {
                        this.yyidx = yyidx;
                        this.yystack = stack;

                        return true;
                    }
                    if (nextstate < self.YYNSTATE) {
                        // we need to shift a non-terminal
                        this.yyidx++;
                        var x = new ParseyyStackEntry();
                        x.stateno = nextstate;
                        x.major = self.yyRuleInfo[yyruleno][0];
                        this.yystack[this.yyidx] = x;
                        continue next;
                    } else if (nextstate == self.YYNSTATE + self.YYNRULE + 1) {
                        this.yyidx = yyidx;
                        this.yystack = stack;
                        if (!token) {
                            // end of input: this is valid
                            return true;
                        }
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return false;
                    } else if (nextstate === self.YY_NO_ACTION) {
                        this.yyidx = yyidx;
                        this.yystack = stack;
                        // input accepted, but not shifted (I guess)
                        return true;
                    } else {
                        yyact = nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        this.yyidx = yyidx;
        this.yystack = stack;

        return true;
    };

    proto.yy_find_shift_action = function(iLookAhead) {
        var stateno = this.yystack[this.yyidx].stateno;

        /* if (this.yyidx < 0) return self.YY_NO_ACTION;  */
        if (!isset(self.yy_shift_ofst, stateno)) {
            // no shift actions
            return self.yy_default[stateno];
        }
        var i = self.yy_shift_ofst[stateno];
        if (i === self.YY_SHIFT_USE_DFLT) {
            return self.yy_default[stateno];
        }
        if (iLookAhead == self.YYNOCODE) {
            return self.YY_NO_ACTION;
        }
        i += iLookAhead;
        if (i < 0 || i >= self.YY_SZ_ACTTAB ||
            self.yy_lookahead[i] != iLookAhead) {
            if (count(self.yyFallback) && iLookAhead < count(self.yyFallback) && (iFallback = self.yyFallback[iLookAhead]) != 0) {
                return this.yy_find_shift_action(iFallback);
            }

            return self.yy_default[stateno];
        } else {
            return self.yy_action[i];
        }
    };

    proto.yy_find_reduce_action = function(stateno, iLookAhead) {
        /* stateno = this.yystack[this.yyidx].stateno; */
        if (!isset(self.yy_reduce_ofst, stateno)) {
            return self.yy_default[stateno];
        }
        var i = self.yy_reduce_ofst[stateno];
        if (i == self.YY_REDUCE_USE_DFLT) {
            return self.yy_default[stateno];
        }
        if (iLookAhead == self.YYNOCODE) {
            return self.YY_NO_ACTION;
        }
        i += iLookAhead;
        if (i < 0 || i >= self.YY_SZ_ACTTAB ||
            self.yy_lookahead[i] != iLookAhead) {
            return self.yy_default[stateno];
        } else {
            return self.yy_action[i];
        }
    };

    proto.yy_shift = function(yyNewState, yyMajor, yypMinor) {
        this.yyidx++;
        if (this.yyidx >= self.YYSTACKDEPTH) {
            this.yyidx--;
            while (this.yyidx >= 0) {
                this.yy_pop_parser_stack();
            }
            //*- stack overflow action
%%
            //*/
            return;
        }

        var yytos = new ParseyyStackEntry();
        yytos.stateno = yyNewState;
        yytos.major = yyMajor;
        yytos.minor = yypMinor;
        this.yystack.push(yytos);
    };

    self.yyRuleInfo = [
        //*- rule info
%%
        //*/
    ];

    self.yyReduceMap = {
        //*- reduice map
%%
        //*/
    };

    //*- reduice actions
%%
    //*/

    proto.yy_reduce = function(yyruleno) {
        var yymsp = this.yystack[this.yyidx];
        var yy_lefthand_side = null;
        this._retvalue = null;

        if (isset(self.yyReduceMap, yyruleno)) {
            // call the action
            this._retvalue = null;
            this['yy_r' + self.yyReduceMap[yyruleno]]();
            yy_lefthand_side = this._retvalue;
        }

        var yygoto = self.yyRuleInfo[yyruleno][0];
        var yysize = self.yyRuleInfo[yyruleno][1];
        this.yyidx -= yysize;

        for (var i = yysize; i; i--) {
            // pop all of the right-hand side parameters
            this.yystack.pop();
        }

        var yyact = this.yy_find_reduce_action(this.yystack[this.yyidx].stateno, yygoto);
        if (yyact < self.YYNSTATE) {
            if (yysize) {
                this.yyidx++;
                var x = new ParseyyStackEntry();
                x.stateno = yyact;
                x.major = yygoto;
                x.minor = yy_lefthand_side;
                this.yystack[this.yyidx] = x;
            } else {
                this.yy_shift(yyact, yygoto, yy_lefthand_side);
            }
        } else if (yyact == self.YYNSTATE + self.YYNRULE + 1) {
            this.yy_accept();
        }
    };

    proto.yy_parse_failed = function() {
        while (this.yyidx >= 0) {
            this.yy_pop_parser_stack();
        }
        //*- parse fails action
%%
        //*/
    };

    proto.yy_syntax_error = function(yymajor, TOKEN) {
        //*- syntax error action
%%
        //*/
    };

    proto.yy_accept = function() {
        var stack;
        while (this.yyidx >= 0) {
            stack = this.yy_pop_parser_stack();
        }
        //*- parser accepts action
%%
        //*/
    };

    proto.doParse = function(yymajor, yytokenvalue) {
        var yyerrorhit = 0; /* True if yymajor has invoked an error */

        if (this.yyidx === null || this.yyidx < 0) {
            this.yyidx = 0;
            this.yyerrcnt = -1;
            var x = new ParseyyStackEntry();
            x.stateno = 0;
            x.major = 0;
            this.yystack = [x];
        }
        var yyendofinput = (yymajor == 0);

        do {
            var yyact = this.yy_find_shift_action(yymajor);
            if (yymajor < self.YYERRORSYMBOL && !this.yy_is_expected_token(yymajor)) {
                // force a syntax error
                yyact = self.YY_ERROR_ACTION;
            }
            if (yyact < self.YYNSTATE) {
                this.yy_shift(yyact, yymajor, yytokenvalue);
                this.yyerrcnt--;
                if (yyendofinput && this.yyidx >= 0) {
                    yymajor = 0;
                } else {
                    yymajor = self.YYNOCODE;
                }
            } else if (yyact < self.YYNSTATE + self.YYNRULE) {
                this.yy_reduce(yyact - self.YYNSTATE);
            } else if (yyact == self.YY_ERROR_ACTION) {
                if (self.YYERRORSYMBOL) {
                    if (this.yyerrcnt < 0) {
                        this.yy_syntax_error(yymajor, yytokenvalue);
                    }
                    var yymx = this.yystack[this.yyidx].major;
                    if (yymx == self.YYERRORSYMBOL || yyerrorhit) {
                        this.yy_destructor(yymajor, yytokenvalue);
                        yymajor = self.YYNOCODE;
                    } else {
                        while (this.yyidx >= 0 &&
                            yymx != self.YYERRORSYMBOL &&
                            (yyact = this.yy_find_shift_action(self.YYERRORSYMBOL)) >= self.YYNSTATE
                        ) {
                            this.yy_pop_parser_stack();
                        }
                        if (this.yyidx < 0 || yymajor == 0) {
                            this.yy_destructor(yymajor, yytokenvalue);
                            this.yy_parse_failed();
                            yymajor = self.YYNOCODE;
                        } else if (yymx != self.YYERRORSYMBOL) {
                            this.yy_shift(yyact, self.YYERRORSYMBOL, 0);
                        }
                    }
                    this.yyerrcnt = 3;
                    yyerrorhit = 1;
                } else {
                    if (this.yyerrcnt <= 0) {
                        this.yy_syntax_error(yymajor, yytokenvalue);
                    }
                    this.yyerrcnt = 3;
                    this.yy_destructor(yymajor, yytokenvalue);
                    if (yyendofinput) {
                        this.yy_parse_failed();
                    }
                    yymajor = self.YYNOCODE;
                }
            } else {
                this.yy_accept();
                yymajor = self.YYNOCODE;
            }
        } while (yymajor != self.YYNOCODE && this.yyidx >= 0);
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
    }else{
        global[className] = self;
    }
})
//*- bind arguments
%%
//*/
;
})(this);
