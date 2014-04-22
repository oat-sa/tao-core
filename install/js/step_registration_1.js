/*  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
function onLoad(){
    
	install.onNextable = function(){
		$('#submitForm').removeClass('disabled')
                    .addClass('enabled')
                    .attr('disabled', false);
		$('#submitForm').attr('value', 'Next');
	}
	
	install.onUnnextable = function(){
		$('#submitForm').removeClass('enabled')
                    .addClass('disabled')
                    .attr('disabled', true);
		$('#submitForm').attr('value', 'Next');
	}
        
	// Nextable, unless default choice is already registered
        //install.setNextable(true);
	
        $('form').bind('submit', function(){
            if (install.isNextable()){
                    install.setTemplate('step_requirements');
            }

            return false;
        });
    
	/*$('input#submitForm').removeClass('disabled')
            .addClass('enabled')
            .attr('disabled', false);*/
	$('p#formComment').hide();

	$('input#radio-noreg').click(function() {
            
            $('ul#support_fields').fadeOut(150); //.hide();
            $('ul#registration_fields').fadeOut(150);
            $('p#formComment').fadeOut(150); //.hide();

            $('input#submitForm').removeClass('disabled')
                .addClass('enabled')
                .attr('disabled', false);   

            $('form').unbind('submit').bind('submit', function(){
                if (install.isNextable()){
                        install.setTemplate('step_finalization');
                }

                return false;
            });
            
            $('#flag_notreg').val('');
            $('#support_login').removeClass('tao-input');
            $('#support_password').removeClass('tao-input');
            install.stateChange();
	});

	$('input#radio-askreg').click(function() {
            $('ul#support_fields').fadeOut(150); //.hide();
            $('p#formComment').fadeOut(150); //.hide();
            $('ul#registration_fields').fadeIn(300);

            //$('ul#registration_fields').load('http://tao.vhost/TAOForgeRegistration.html');

            // disable next, unless the credentials check is incorrect
            $('input#submitForm').removeClass('disabled')
                .addClass('enabled')
                .attr('disabled', false);
        
                $('form').unbind('submit').bind('submit', function(){
                    if (install.isNextable()){
                            install.setTemplate('step_registration_2');
                    }

                    return false;
                });
            
            $('#flag_notreg').val('');
            $('#support_login').removeClass('tao-input');
            $('#support_password').removeClass('tao-input');
            install.stateChange();
	});
        
        $('input#radio-alreadyreg').click(function() {
            $('ul#support_fields').fadeIn(300);//.show();
            $('ul#registration_fields').fadeOut(150);
            $('p#formComment').fadeIn(300);//.show();
            
            /*$('input#submitForm').removeClass('enabled')
                .addClass('disabled')
                .attr('enabled', false);*/
            $('input#submitForm').removeClass('disabled')
                    .addClass('enabled')
                    .attr('disabled', false);  
            
            // TEST
            $('ul#support_fields').load('http://tao.vhost/TAOForgeCheckAccount.html div#forms_content form', function( response, status, xhr ) {
                if ( status == "error" ) {
                var msg = "Sorry but there was an error: \r\n";
                alert( msg + xhr.status + " " + xhr.statusText );
                        //$( "#error" ).html( msg + xhr.status + " " + xhr.statusText );
                }
            });
            
            //TODO add credentials check
            $('form').unbind('submit').bind('submit', function(){
                
                // set a spinner up.
                $forgeAccountMsg = $('#forge-account');
                $forgeAccountMsg.css('visibility', 'visible');
                var spinner = new Spinner(getSpinnerOptions('small')).spin($forgeAccountMsg[0]);

                setTimeout(function(){ 
                        var host = 'http://forge.taotesting.com';
                        var user = install.getData('support_login');
                        var password = install.getData('support_password');

                        var check = {   host: host,
                                        user: user,
                                        password: password
                                    };

                        install.checkRedmineAccount(check, function(status, data){
                                $forgeAccountMsg.css('visibility', 'hidden');
                                spinner.stop();

                                if (data.value.status == 'valid'){
                                        // Great! We could connect to the TAO Forge using the provided credentials.
                                        if (install.isNextable()){
                                                install.setTemplate('step_finalization');
                                        }
                                }
                                else if (data.value.status == 'invalid-noconnection'){
                                        // No connection could be established.
                                        var msg  = "Unable to connect to the TAO Forge ("+host+"). Please check external network availability.";
                                        displayTaoError(msg);
                                }
                                else if (data.value.status == 'invalid-not-existing'){
                                        // Connection could be established but credentials check failed.
                                        var msg = "TAO Forge credentials check failed. Please re-check your login and password!";
                                        displayTaoError(msg);
                                }
                        });
                }, 2000);    // fake additional delay for user (1000ms).
                
                /*if (install.isNextable()){
                        install.setTemplate('step_finalization');
                }*/

                return false;
            });
            
            $('#flag_notreg').val('');
            $('#support_login').addClass('tao-input');
            $('#support_password').addClass('tao-input');
            install.stateChange();
        });
    
        // option 2 hidden by default, no need to display these fields
        $('ul#support_fields').hide();

        // Initialize 'tao-input's.
	
	var firstValues = {};
	$('.tao-input').each(function(){
		$this = $(this);
		// Provide a data getter/setter for API handshake.
		install.getDataGetter(this);
		install.getDataSetter(this);
		
		// Get labelifed values from raw DOM.
		if ($this.prop('tagName').toLowerCase() == 'input' && $this.attr('type') == 'text'){
			firstValues[this.id] = this.getData();
		}
	});
	
	// Backward management.
	$('#install_seq li a').each(function(){
		$(this).bind('click', onBackward);
	});
	
	// Register inputs.
	$('.tao-input').each(function(){
		if (typeof(firstValues[this.id]) != 'undefined'){
			this.firstValue = firstValues[this.id];
		}
		
		switch (this.id){
			
			case 'support_login':
				install.getValidator(this, {dataType: 'string', min: 3, max: 30});
				validify(this);
			break;
			
			case 'support_password':
				install.getValidator(this, {dataType: 'string', min: 8, max: 30});
				validify(this);
			break;
			
                        case 'flag_notreg':
                                install.getValidator(this, {dataType: 'regexp', 'pattern': '[0-9]+'});
				validify(this);
			break;
                        
			default:
				install.getValidator(this);
			break;
		}
		
		install.register(this);
		
		// When data is changed, tell the Install API.
		$(".tao-input[type=text], .tao-input[type=password]").bind('keyup click change paste blur', function(event){
                    console.log('text state changed');
                    install.stateChange();
		});
		
		$(".tao-input[type=radio]").bind("change", function(event){
                    console.log('checkbox state changed');
                    install.stateChange();
		});
	});
	
        // Populate form elements from API's data store.
	// (do not forget to restyle)
	$(install.populate()).each(function(){
		$(this).removeClass('helpTaoInputLabel');
	});
        
        // Initial state: encourage to register
        $('input#radio-askreg').click();
        $('#flag_notreg').val('1');
        /*$('input#radio-alreadyreg').click();
        $('#flag_notreg').val('');*/
        $('support_login').removeClass('tao-input');
        $('support_password').removeClass('tao-input');
        install.stateChange();

        $('input#submitForm').focus();

        initHelp();
}

function initHelp(){	
        install.addHelp('tpl_support_login', 'Your current login on TAO forge.');
	install.addHelp('tpl_support_password', 'Your current password on TAO forge. Used jointly with your login to grant you access to this TAO forge account.');
}
