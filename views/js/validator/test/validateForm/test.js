define(['lodash', 'jquery', 'jquery.validator'], function(_, $){

    var CL = console.log;

    test('parse rule', function(){
        
        expect(0);

        var validateStr = $('#text1').data('validate');
        
        var rulesStr = validateStr.split(/;\s+/);
        var rules = {};
        _.each(rulesStr, function(ruleStr){
            var ruleName,
                rightStr = ruleStr.replace(/\s*\$(\w*)/, function($0, name){
                ruleName = name;
                rules[ruleName] = {options : {}};
                return '';
            });

            if(ruleName){
                rightStr.replace(/\s*\(([^\)]*)\)/, function($0, optionsStr){
                    optionsStr.replace(/(\w*)=([^\s]*)(,)?/g, function($0, optionName, optionValue){
                        if(optionValue.charAt(optionValue.length-1) === ','){
                            optionValue = optionValue.substring(0, optionValue.length-1);
                        }
                        rules[ruleName].options[optionName] = optionValue;
                    });
                });
            }
        });
        
    });
    
    test('validate form', function(){
        
        expect(0);
        
        $('#text1').validator();
        CL($('#text1').validator('getValidator'));
        
    });

});