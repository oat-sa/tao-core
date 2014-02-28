define(['lodash', 'validate'], function(_, Validator){

    var CL = console.log;

    test('validate', function(){
        
        var r, validator = new Validator(['notEmpty', 'numeric']);

        equal(_.size(validator.rules), 2, 'rules set');

        r = validator.validate('a');
        equal(_.size(r), 1);

        r = validator.validate('');
        equal(_.size(r), 2);

        r = validator.validate(null);
        equal(_.size(r), 2);

        r = validator.validate(undefined);
        equal(_.size(r), 2);

        r = validator.validate(0);
        equal(_.size(r), 0);

        r = validator.validate(3);
        equal(_.size(r), 0);

    });

    test('validate with options', function(){
        
        var r, validator = new Validator([
            {
                name : 'pattern',
                options : {
                    pattern : '[A-Z][a-z]{3,}',
                    modifier : 'igm'
                }
            }
        ]);
        
        r = validator.validate('York');
        equal(_.size(r), 0);
        
        r = validator.validate('Aee');
        equal(_.size(r), 1);
        
        r = validator.validate('02');
        equal(_.size(r), 1);
    });

});