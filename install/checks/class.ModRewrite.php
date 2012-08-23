<?php
class tao_install_checks_ModRewrite extends common_configuration_Component {
    
    public function check (){
        $status = null;
        $message = '';
        $modRewrite = false;
        $report = null;
        
        if (function_exists('apache_get_modules')){
            $modules = apache_get_modules();
            if (in_array('mod_rewrite', $modules)){
                $modRewrite = true;
            }
        }
        // TAO Main .htaccess file sets the HTTP_MOD_REWRITE.
        else if ((getenv('HTTP_MOD_REWRITE')=='On' ? true : false) == true){
            $modRewrite = true;
        }
        
        if ($modRewrite == true){
            $report = new common_configuration_Report(common_configuration_Report::VALID,
                                                      'Apache mod_rewrite is enabled.',
                                                      $this);
        }
        else{
            $report = new common_configuration_Report(common_configuration_Report::INVALID,
                                                      'Apache mod_rewrite is disabled.',
                                                      $this);
        }
        
        return $report;
    }
}
?>