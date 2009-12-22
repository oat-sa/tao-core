class com.eXULiS.lib.calculator{
	/*************************************
	** Private attributes
	*/
		private var max_char:Number = 512;
		private var p_binary_op:Array = new Array();
		private var p_unary_op:Array = new Array();
		private var p_object:Array = new Array();
		private var p_level:Array = new Array();
		private var v_indices:Array = new Array();
		private var v_value:Array = new Array();
		private var v_name:Array = new Array();
		private var delimiters:Array = new Array("+","-","*","/","(",")"," ","^");
		private var do_unary:Number = 0;
		private var level:Number;
		private var base_level:Number;
		private var accum_ind:Number;
		private var var_ind:Number;
		private var variable_index:Array = new Array(); // This is the index of the variable for a plot
        private var accum_pos;

	public function calculator(){
		// empty constructor
	}

	/*************************************
	** Remove space characters from a string
	*/
	public function remove_space(xpr){
		var expression = new String(xpr);
		var newstr = "";
		for(var i=0;i<expression.length;i++){
			if(expression.charAt(i) != " "){
				newstr = newstr+expression.charAt(i);
			}
		}
		return newstr;
	}

	public function prepare(xpr){
		trace("Before Calc Preparation: " + xpr);
		var expression = new String(xpr);
		var newstr = "";
		var vCpt=0;
		while(vCpt<(expression.length)){
			if(((expression.charAt(vCpt) == "e") || (expression.charAt(vCpt) == "E")) && (expression.charAt(vCpt + 1) != "^")){
				if((isNaN(Number(expression.charAt(vCpt + 1))) == false) || (expression.charAt(vCpt + 1) == "-")){
					var tmpIndex:Number = vCpt + 1;
					while((tmpIndex < (expression.length - 1)) && ((expression.charAt(tmpIndex) == "-") || (expression.charAt(tmpIndex) == ".") || (isNaN(Number(expression.charAt(tmpIndex))) == false))){
						tmpIndex++ ;
					}
					if(tmpIndex == (expression.length - 1)){
						tmpIndex++ ;
					}
					newstr += "*10^(" + expression.substring(vCpt + 1,tmpIndex) + ")";
					vCpt = tmpIndex - 1;
				}
			}
			else{
				newstr += expression.charAt(vCpt);
			}
			vCpt++;
		}
//		newstr += expression.charAt(expression.length - 1);
		trace("After Calc Preparation: " + newstr);
		return newstr;
	}
	/*************************************
	** Perform a few initialization tasks
	*/
	public function preparse(xpr){
		// renew arrays
		p_binary_op = new Array;
		p_unary_op = new Array;
		p_object = new Array;
		p_level = new Array;
		v_indices = new Array;
		v_value = new Array;
		v_name = new Array;
		// other variables
		var theta = 1;
		var count = 0;
		var expression = new String(xpr);
		var p = expression.split("(");
		count = p.length-1;
		p = expression.split(")");
		count -= p.length-1;
		// Here are useful preassigned constants

		v_value[0] = -1;
		v_name[0] = "-1";
		v_value[1] = Math.PI;
		v_name[1] = "pi";
		v_value[2] = Math.E;
		v_name[2] = "e";
		accum_ind = 0;
		var_ind = 3;
		level = 0;
		base_level = 0;
		return count;
	}

	/*************************************
	** Parse a mathematical expression
	** Warning:  while this does use recursion (no way around it
	** for this problem), it does not follow the usual precepts
	** of parsing mathematical expressions.  Instead of building
	** complicated hash tables and tree structures to represent
	** the expression, it builds a single 4-d array (actually
	** four 1-d arrays) that can be evaluated by pushing and
	** popping from a stack.  This makes the evaluation process
	** quite fast - an important consideration in trying to
	** plot functions using this tool...
	** The idea here is to use "levels" to describe to an evaluation
	** function the priority of the operation.  Since multiplication/
	** division takes precedence over addition, it raises the level
	** by one.  Exponentiation takes precedence over multiplication,
	** so it raises the level by two.  Parentheses take precedence
	** over everything, so they raise the level by three.  They
	** also raise the "base level" - the level below which the
	** level indicator cannot go.
	*/
	public function parse(xpr){
		var delim_pos = new Array;
		var delim_type = new Array;
		var i=0;
		var istart=0;
		var val;
		var j;
		var exponentiating=0;
		var multiplying=0;
		var var_found = 0;
		var expression = new String(xpr);
		/*
		** First find the separators between objects.
		*/
		for(j=0;j<delimiters.length;j++){
			while(expression.indexOf(delimiters[j],istart) >= 0){
				delim_pos[i] = expression.indexOf(delimiters[j],istart);
				istart = delim_pos[i]+1;
				i++;
			}
			istart = 0;
		}
		delim_pos.sort(order);
		//trace(delim_pos);
		/*
		** Now parse it into four arrays
		*/
		i = 0;  	// Count objects
		j = 0;		// Count delimiters
		istart = 0;
		// First deal with a leading minus sign
		if(expression.charAt(0) == '-'){
			p_object[accum_ind] = 0;
			p_level[accum_ind] = ++level;
			if(!p_unary_op[accum_ind]) p_unary_op[accum_ind] = 0;
			p_binary_op[accum_ind] = 2;
			accum_ind++;
			istart=1;
			//multiplying = 1;
			i = 1;
		}
		// Now parse the rest
		var end = delim_pos.length;
		delim_pos[end] = expression.length;
		end++;
		/**
		** This is the beginning of the big loop over the whole
		** expression
		*/
		for(j=istart;j<end;j++){
			if(expression.charAt(istart) == '('){
				level += 3;
				base_level += 3;
				// Beware - recursion here.
				j += parse(expression.substr(istart+1));
				j++;	// Pass the right parenthesis we just finished.
				istart = delim_pos[j];
			}
			var obj = expression.substring(istart,delim_pos[j]);
			// Deal with special functions.  This section does
			// the same as above, but sticks a unary operation in as
			// well.
			p_unary_op[accum_ind] = assign_unary_op(obj);
			if(p_unary_op[accum_ind] > 0){
//				var_found = 1;
				p_binary_op[accum_ind] = 699;
				p_object[accum_ind] = 0;
				p_level[accum_ind] = level+3;
				accum_ind++;
				istart = delim_pos[j];
				level += 3;
				base_level += 3;
				// Beware - recursion here.
				j += parse(expression.substr(istart+1));
				j++;	// Pass the right parenthesis we just finished.
				istart = delim_pos[j];
				// Set up to finish the steps with the next object.
				obj = expression.substring(istart,delim_pos[j]);
				p_unary_op[accum_pos] = 0;
				do_unary = 0;
			}
			if((expression.charAt(delim_pos[j]) == '*') || (expression.charAt(delim_pos[j]) == '/')){
				if(expression.charAt(delim_pos[j]) == '*'){p_binary_op[accum_ind] = 2;}
				else{p_binary_op[accum_ind] = 3;}
				/*if(exponentiating > 0){
					level--;
					exponentiating = 0;
				}
				else{
					if(multiplying == 0) {
						//multiplying = 1;
						level++;
					}
				}*/
				level = base_level+1;
			}
			if(expression.charAt(delim_pos[j]) == '^'){
				//level++;
				//level += 2;
				level = base_level+2;
				exponentiating = 1;
				multiplying = 0;
				p_binary_op[accum_ind] = 4;
			}
			if((expression.charAt(delim_pos[j]) == '+') || (expression.charAt(delim_pos[j]) == '-')){
				//level -= 1;
				level = base_level;
				multiplying = 0;
				exponentiating = 0;
				if(expression.charAt(delim_pos[j]) == '+'){p_binary_op[accum_ind] = 0;}
				else{p_binary_op[accum_ind] = 1;}
			}
			if(level < base_level) {level = base_level;}
			if(do_unary == 0){
				// If there was no special function, look for variables
				var_found = 0;
				for(i=0;i<v_name.length;i++){
					if(v_name[i] == obj){
						p_object[accum_ind] = i;
						p_level[accum_ind] = level;
						var_found = 1;
						accum_ind++;
						break;
					}
				}
				if(var_found == 0){
					if(istart < delim_pos[j]){
						v_name[var_ind] = obj;
						v_value[var_ind] = obj;
						p_object[accum_ind] = var_ind;
					}
					else{  // This means we just exited parentheses
						p_object[accum_ind] = 1;
						if(expression.charAt(delim_pos[j]) == '^'){
							level++;
						}
					}
					p_level[accum_ind] = level;
					var_ind++;
					accum_ind++;
				}
			}
			if(expression.charAt(delim_pos[j]) == ')'){
				p_level[accum_ind-1] = base_level;
				level -= 3;
				base_level -= 3;
				//if(expression.charAt(delim_pos[j+1]) ne '^'){
					//p_level[accum_ind-1] = level;
				// }
				p_binary_op[accum_ind-1] = 899;
				return j+1;
			}
			istart = delim_pos[j]+1;
		}
		p_binary_op[accum_ind-1] = -1;
		p_level[accum_ind-1] = level;
		/*trace("Variables");
		trace(v_name);
		trace(v_value);
		trace("Objects");
		trace(p_object);
		trace(p_level);
		trace(p_unary_op);
		trace(p_binary_op);
		trace("---End of object ---");*/
		return delim_pos[end];
	}

	/*************************************
	** Evaluator
	*/
	public function evaluate (){
		var a_stack = new Array;
		var a_op = new Array;
		var a_level = new Array;
		var a_unary = new Array;
		var i=0;
		var ip1;
		var sp1;
		var stack_pos=0;
		var cur_level;
		if(p_object.length < 1){return 0;}
		a_stack[0] = v_value[p_object[0]];
		a_level[0] = p_level[0];
		a_op[0] = p_binary_op[0];
		a_unary[0] = p_unary_op[0];
		while(p_binary_op[i] >= 0){
			ip1 = i+1;
			if(a_op[stack_pos] == 899){
				// Preserve the next operation, and make sure the level
				// carries through.
				a_op[stack_pos] = p_binary_op[ip1];
				a_level[stack_pos] = p_level[ip1];
				// This is the end of a grouping
				// Collapse the current level and do the unary operation
				cur_level = a_level[stack_pos];
				while(stack_pos > 0 and a_level[stack_pos-1] >= cur_level){
					//If a unary operator applies, save it until the stack
					// is collapsed.
					if(a_level[stack_pos-1] == cur_level and a_op[stack_pos-1] == 699) break;
					sp1 = stack_pos;
					stack_pos--;
					if(a_op[stack_pos] == 699){
						a_stack[stack_pos] = a_stack[sp1];
						a_stack[stack_pos] = unary(a_stack[stack_pos],a_unary[stack_pos]);
					}
					else {
						a_stack[stack_pos] = binary(a_stack[stack_pos],a_stack[sp1],a_op[stack_pos]);
					}
					a_level[stack_pos] = a_level[sp1];
					a_op[stack_pos] = a_op[sp1];
				}
				//a_stack[stack_pos] = unary(a_stack[stack_pos],a_unary[stack_pos]);
				a_unary[stack_pos] = 0;
			}
			// If the level has dropped, then collapse the stack.
			else if(a_level[stack_pos] >= p_level[ip1] and a_op[stack_pos] < 100){
				a_stack[stack_pos] = binary(a_stack[stack_pos],v_value[p_object[ip1]],a_op[stack_pos]);
				a_op[stack_pos] = p_binary_op[ip1];
				a_level[stack_pos] = p_level[ip1];
				// Now collapse the accumulator backwards on this level.
				// Don't smash unary operators.
				cur_level = a_level[stack_pos];
				while(stack_pos>0 and a_level[stack_pos-1]>=cur_level and a_op[stack_pos-1]<100){
					sp1 = stack_pos;
					stack_pos--;
					a_stack[stack_pos] = binary(a_stack[stack_pos],a_stack[sp1],a_op[stack_pos]);
					a_level[stack_pos] = a_level[sp1];
					//a_unary[stack_pos] = a_unary[sp1];
					a_op[stack_pos] = a_op[sp1];
				}
			}
			else{
				// This just puts a new layer on the stack.  We work on
				// it later.  This happens when the level increases, or if
				// there is a unary operation to be dealt with later.
				stack_pos++;
				a_stack[stack_pos] = v_value[p_object[ip1]];
				a_level[stack_pos] = p_level[ip1];
				a_unary[stack_pos] = p_unary_op[ip1];
				a_op[stack_pos] = p_binary_op[ip1];
			}
			i++;
		}
		// Collapse everything that is left
		while(stack_pos > 0){
			sp1 = stack_pos;
			stack_pos--;
			a_stack[sp1] = unary(a_stack[sp1],a_unary[sp1]);
			a_stack[stack_pos] = binary(a_stack[stack_pos],a_stack[sp1],a_op[stack_pos]);
			a_level[stack_pos] = a_level[sp1];
			//a_unary[stack_pos] = a_unary[sp1];
			a_op[stack_pos] = a_op[sp1];
		}
		a_stack[stack_pos] = unary(a_stack[stack_pos],a_unary[stack_pos]);
		return a_stack[0];
	}

	/*************************************
	**  Determine the function that is to be applied to this group
	*/
	private function assign_unary_op(arg){
		switch(arg) {
			case "sin":
				return 1;
			case "cos":
				return 2;
			case "tan":
				return 3;
			case "sec":
				return 4;
			case "cosec":
				return 5;
			case "cotan":
				return 6;
			case "exp":
				return 20;
			case "log":
				return 21;
			case "ln":
				return 21;
			case "sqrt":
				return 30;
			case "abs":
				return 31;
			case "step":
				return 32;
			case "heaviside":
				return 32;
			case "asin":
				return 40;
			case "acos":
				return 41;
			case "atan":
				return 42;
			case "floor":
				return 50;
			case "ceil":
				return 51;
			case "round":
				return 52;
			default:
				return 0;
		}
	}

	/*************************************
	** Set a variable or parameter
	*/
	private function set_variable(name,value){
		var i;
		for(i=0;i<v_value.length;i++){
			if(name == v_name[i]){
				v_value[i] = value;
			}
		}
	}

	/*************************************
	** Perform binary operations
	*/
	private function binary(a,b,op){
		switch(op){
			case 0:
				return Number(a)+Number(b);
			case 1:
				return Number(a)-Number(b);
			case 2:
				return Number(a)*Number(b);
			case 3:
				return Number(a)/Number(b);
			case 4:
				return Math.pow(Number(a),Number(b));
			case 699:
				return Number(b);
			case 899:
				return 0;
			case -1:
				return 0;
		}
	}

	/*************************************
	** Evaluate special functions
	*/
	private function unary(a,op){
		switch(op){
			case 0:
				return a;
			case 1:
				return Math.sin(Number(a));
			case 2:
				return Math.cos(Number(a));
			case 3:
				return Math.tan(Number(a));
			case 4:   // What do we care about dividing by zero?
				return 1/Math.cos(Number(a));
			case 5:
				return 1/Math.sin(Number(a));
			case 6:
				return Math.cos(Number(a))/Math.sin(Number(a));
			case 20:
				return Math.exp(Number(a));
			case 21:
				return Math.log(Number(a));
			case 30:
				return Math.sqrt(Number(a));
			case 31:
				return Math.abs(Number(a));
			case 32:
				if(Number(a) <= 0) return Number(0);
				else return Number(1);
			case 40:
				return Math.asin(Number(a));
			case 41:
				return Math.acos(Number(a));
			case 42:
				return Math.atan(Number(a));
			case 50:
				return Math.floor(Number(a));
			case 51:
				return Math.ceil(Number(a));
			case 52:
				return Math.round(Number(a));
			default:
				return a;
		}
	}

	/*************************************
	** Order numbers.  The problem is that Flash treats most
	** variables as strings, and sorts lexicographically.
	** By doing an arithmetic calculation, we force Flash to
	** treat the numbers numerically.  The numbers will never
	** be equal, so we save `if' statements by avoiding the
	** test for equality.
	*/
	function order(a,b){
		if(b-a >= 0){return -1;}
		else{return 1;}
	}

	/*************************************
	** Set a variable's value
	*/
	private function set_variable_by_name(name,value){
		for(var i=0;i<v_name.length;i++){
			if(v_name[i] == name){
				v_value[i] = value;
				break;
			}
		}
	}
	private function set_variable_by_index(index,value){
		if(index >= 0 and index < v_name.length){
			v_value[index] = value;
		}
	}

	/*************************************
	** Get a variable's value
	*/
	private function get_variable_index(name){
		for(var i=0;i<v_name.length;i++){
			if(v_name[i] == name){
				return i;
			}
		}
		return -1;
	}
//	set(_parent.refers_to+"."+_parent.function_name,function (z){_root.p.code.set_variable_by_index(variable_index,z); return _root.p.code.evaluate();});
/*	if(_parent.purpose eq "plot"){
		set(_parent.refers_to+"."+_parent.function_name,function (z){set_variable_by_index(variable_index[0],z); return evaluate();});
	}
	else if(_parent.purpose eq "3dplot"){
		set(_parent.refers_to+"."+_parent.function_name,function (x,y){set_variable_by_index(variable_index[0],x); set_variable_by_index(variable_index[1],y); return evaluate();});
	}
	else if(_parent.purpose eq "diffeq"){
		set(_parent.refers_to+"."+_parent.function_name,function (x,y,t){set_variable_by_index(variable_index[0],x); set_variable_by_index(variable_index[1],y); set_variable_by_index(variable_index[2],t); return evaluate();});
	} */
}
