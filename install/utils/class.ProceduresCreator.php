<?php

class tao_install_utils_ProceduresCreator{
    
    private $connection;
    private $sqlParser;
    
    public function __construct($driver, $connection){
        
        $this->connection = $connection;
        switch ($driver) {
        	case 'pdo_mysql':{
        	    $this->setSQLParser(new tao_install_utils_MysqlProceduresParser());
        	    break;
        	}
        	case 'pdo_pgsql' : {
        	    $this->setSQLParser(new tao_install_utils_PostgresProceduresParser());
        	    break;
        	}
        	case 'pdo_oci' : {
        	    $this->setSQLParser(new tao_install_utils_CustomProceduresParser());
        	    break;
        	}
        	case 'pdo_sqlsrv' : {
        		$this->setSQLParser(new tao_install_utils_CustomProceduresParser());
        		break;
        	}
        	default: {
        	    throw new tao_install_utils_SQLParsingException('Could not find Parser for driver ' . $driver);
        	}
        }
        
        

        
    }
    
    private function getSQLParser(){
        return $this->sqlParser;
    }
    
    private function setSQLParser($sqlParser){
        $this->sqlParser = $sqlParser;
    }
    
    
    public function load($file){
        $parser = $this->getSQLParser();
        $parser->setFile($file);
        $parser->parse();

        foreach ($parser->getStatements() as $statement){
            $this->connection->executeUpdate($statement);
           
        }
    }

}