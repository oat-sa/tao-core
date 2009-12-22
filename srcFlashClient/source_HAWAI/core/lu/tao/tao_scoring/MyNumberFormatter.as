import lu.tao.utils.tao_toolbox;

/**
 * MyNumberFormatter.class.php
 *
 * @author Philipp Wiesemann
 * @author Alexander Scharaf
 * @author Raynald Jadoul
 */

class lu.tao.tao_scoring.MyNumberFormatter{

	private var vPOINT:String;
	private var vCOMMA:String;
	private var vSPACE:String;

	private var normalize_data:Object;
	private var ignore_whitespace:Boolean;
	private var weak_separator_checks:Boolean;
	private var my_toolbox:tao_toolbox;

	public function MyNumberFormatter(){
		vPOINT = '.';
		vCOMMA = ',';
		vSPACE = ' ';
		ignore_whitespace = true;
		weak_separator_checks = true;

		normalize_data = new Object();
		normalize_data['en-US']={decimal:vPOINT,thousand:vCOMMA}; // not in Philipp's code
		normalize_data['en-AU']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['de-AT']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['tr-AT']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['sh-AT']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['nl-BE']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['en-CA']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['fr-CA']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['cs-CZ']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['de-DE']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['da-DK']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['fi-FI']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['sv-FI']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['fr-FR']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['en-GB']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['cy-GB']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['hu-HU']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['en-IE']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['it-IT']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['ja-JP']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['ko-KR']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['nl-NL']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['nb-NO']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['en-NO']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['pl-PL']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['pt-PT']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['hu-SK']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['sk-SK']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['sl-SI']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['eu-ES']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['ca-ES']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['es-ES']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['gl-ES']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['xa-ES']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['sv-SE']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['en-UX']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['es-UX']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['et-EE']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['ru-EE']={decimal:vCOMMA,thousand:vSPACE};
		normalize_data['mt-MT']={decimal:vPOINT,thousand:vCOMMA};
		normalize_data['es-CL']={decimal:vCOMMA,thousand:vPOINT};
		normalize_data['el-CY']={decimal:vCOMMA,thousand:vPOINT};
		my_toolbox = new tao_toolbox();
	}

	/**
	* @param bool enabled default is true.
	*/
	public function setIgnoreWhitespace(enabled_bool) {
		ignore_whitespace = (enabled_bool === true);
	}

	/**
	* @param bool enabled default is true.
	*/
	public function setWeakSeparatorChecks(enabled_bool) {
		weak_separator_checks = (enabled_bool === true);
	}

	/**
	* Filter all whitespace characters from given string.
	*/
	private function filterWhitespace(string_str) {
//		search = array(' ', '\t', '\n', '\r', '\0', '\x0B');
//		return(str_replace(search, array(''), string));
		return(my_toolbox.cleanString(string_str,true,true,true,true));
	}

	/**
	* @param string number_str input.
	* @param string locale_str supported locale.
	* @return string normalized input or error message starting with 'Error'.
	*/
	public function normalize(number_str, locale_str) {
		trace("CPLX IN  normalize with number_str = '" + number_str + "' and locale_str = '" + locale_str + "'");
		if (strlen(number_str) == 0) {
			return 'Error: empty input string for number.';
		}
		if (strlen(locale_str) == 0) {
			return 'Error: empty input string for locale.';
		}
		if (array_key_exists(locale_str, normalize_data) == false) {
			return 'Error: unknown locale.';
		}

		number_str = trim(number_str);
		if (ignore_whitespace == true) {
			number_str = filterWhitespace(number_str);
		}

		trace("CPLX     normalize with strpos(number_str, '-') = '" + strpos(number_str, '-') + "'");
		var is_neg:Boolean = (strpos(number_str, '-') === 0);
		trace("CPLX     normalize with is_neg = " + is_neg);
		if (is_neg == true) {
			number_str = substr(number_str, 1);
		}
		number_str = ltrim(number_str); // remove possible whitespace behind minus
		if (strlen(number_str) == 0) {
			return 'Error: empty string after removing minus.';
		}

		var separators:Object = normalize_data[locale_str];
		var decimal_sep:String = separators.decimal;
		var thousand_sep:String = separators.thousand;
		trace("CPLX     normalize with decimal_sep = '" + decimal_sep + "' and thousand_sep = '" + thousand_sep + "'");

		var integral_part:String = number_str;
		var fractional_part:String = '0';

		if (strpos(number_str, decimal_sep) !== false) {
			var number_str_chunks = explode(decimal_sep, number_str);

			if (count(number_str_chunks) != 2) {
				return 'Error: too many decimal separators.';
			}
			integral_part = number_str_chunks[0];
			fractional_part = number_str_chunks[1];
			trace("CPLX     normalize with integral_part = '" + integral_part + "' and fractional_part = '" + fractional_part + "'");

			if (ctype_digit(fractional_part) == false) {
				return 'Error: garbage in fractional part.';
			}

			fractional_part = rtrim(fractional_part, '0');
			if (strlen(fractional_part) == 0) {
	// removed to much from end
				fractional_part = '0';
			}
		}

		if (weak_separator_checks == true) {
			integral_part = str_replace(thousand_sep, '', integral_part); 
		} 
		else {
			var integral_part_chunks = explode(thousand_sep, integral_part);
//			foreach (integral_part_chunks as i => chunk) {
			for(var i:Number = 0;i<integral_part_chunks.length;i++){
				var chunk = integral_part_chunks[i];
				if ((strlen(chunk) != 3) && (i != '0')) {
					return 'Error: numbers not correctly separated in integral part.';
				}
			}
			integral_part = implode(integral_part_chunks);
		}

		if (ctype_digit(integral_part) == false) {
			return 'Error: garbage in integral part.';
		}

		integral_part = ltrim(integral_part, '0');
		if (strlen(integral_part) == 0) {
	// removed to much from beginning
			integral_part = '0';
		}

		trace("CPLX OUT normalize");
		return(((is_neg) ? '-' : '') + integral_part + '.' + fractional_part);
	}

	private function array_key_exists(vArg,vObject):Boolean{
		var vResult_bool:Boolean = false;
		if(vObject[vArg] != undefined){
			vResult_bool = true;
		}
		return(vResult_bool);
	}

	private function strlen(vArg):Number{
		var vWork_str:String = new String(vArg);
		return(vWork_str.length);
	}

	private function trim(vArg, vMask):String{
		return(my_toolbox.trimStringMask(vArg,((vMask == undefined) ? " ":vMask),"BOTH"));
	}

	private function ltrim(vArg, vMask):String{
		return(my_toolbox.trimStringMask(vArg,((vMask == undefined) ? " ":vMask),"LEFT"));
	}

	private function rtrim(vArg, vMask):String{
		return(my_toolbox.trimStringMask(vArg,((vMask == undefined) ? " ":vMask),"RIGHT"));
	}

	private function strpos(hay_str:String, needle_str:String){
		var vWork_num:Number = hay_str.indexOf(needle_str);
		var vResult = (vWork_num == -1) ? false : vWork_num;
		return(vResult);
	}

	private function str_replace(target_str:String, replacer_str:String, source_str:String){
		return(my_toolbox.replaceString(source_str,target_str,replacer_str));
	}

	private function substr(string_str:String, pos_num:Number):String{
		return(string_str.substr(pos_num));
	}

	private function implode(vArray:Array):String{
		return(vArray.join());
	}

	private function explode(separator_str:String, string_str:String):Array{
		var vResult_array:Array = new Array();
		vResult_array = string_str.split(separator_str);
		return(vResult_array);
	}

	private function count(vArray):Number{
		return(vArray.length);
	}

	private function ctype_digit(vArg):Boolean{
		trace("CPLX IN  ctype_digit with vArg = " + vArg);
		var vResult_bool:Boolean = true;
		var vArg_array:Array = new Array();
		vArg_array = String(vArg).split("");
		for(var vCpt_num:Number = 0;vCpt_num<vArg_array.length;vCpt_num++){
			if(isNaN(vArg_array[vCpt_num])){
				vResult_bool = false;
				break;
			}
		}
		trace("CPLX OUT ctype_digit with vResult_bool = " + vResult_bool);
		return(vResult_bool);
	}

}
