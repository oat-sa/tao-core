define(['lodash', 'jquery', 'jquery.validator'], function(_, $) {

    var CL = console.log;

    test('validate form', function() {

        //set test value;

        $('#text1').validator();
        ok($('#text1').validator('getValidator'), 'validator bound');

        stop();
        $('#text1').val('York');
        $('#text1').validator('validate', {}, function(res) {
            start();

            equal(_.size(res), 2, 'validated');

            var report1 = res.shift();
            equal(report1.type, 'success');
            equal(report1.data.validator, 'notEmpty');

            var report2 = res.shift();
            equal(report2.type, 'success');
            equal(report2.data.validator, 'pattern');
        });


        stop();
        $('#text1').val('');
        $('#text1').validator('validate', {}, function(res) {
            start();

            equal(_.size(res), 2, 'validated');

            var report1 = res.shift();
            equal(report1.type, 'failure');
            equal(report1.data.validator, 'notEmpty');

            var report2 = res.shift();
            equal(report2.type, 'failure');
            equal(report2.data.validator, 'pattern');
        });

        stop();
        $('#text1').val('Yor');
        $('#text1').validator('validate', {}, function(res) {
            start();

            equal(_.size(res), 2, 'validated');

            var report1 = res.shift();
            equal(report1.type, 'success');
            equal(report1.data.validator, 'notEmpty');

            var report2 = res.shift();
            equal(report2.type, 'failure');
            equal(report2.data.validator, 'pattern');
        });

        //reset test value:
        $('#text1').val('');
    });

    test('element event', function() {

        stop();
        $('#text1').on('validated', function(e, data) {
            start();
            equal(e.type, 'validated', 'event type ok');
            equal(data.elt, this, 'validated element ok');
            equal(_.size(data.results), 2, 'results ok');
        });
        $('#text1').validator('validate');

    });

    test('form event', function() {

        stop();
        $('#form1').on('validated', function(e, data) {
            start();
            equal(e.type, 'validated', 'event type ok');
            equal(data.elt, $('#text1')[0], 'validated element ok');
            equal(_.size(data.results), 2, 'results ok');
        });
        $('#text1').validator('validate');

    });
    
    test('build options', function(){
        
        expect(0);
        
        $('#text2').validator();
        
        
    });
});