<?php

/**
 * Extended test reporter to be integrated with CI build softs 
 */
class XmlTimeReporter extends XmlReporter {
  
	public $pre;

  	function paintMethodStart($test_name) {
    	$this->pre = microtime();
    	parent::paintMethodStart($test_name);
  	}

  	function paintMethodEnd($test_name) {
    	
	    if ($this->pre != null) {
	    	 
	    	  print $this->getIndent(1);
	    	  $post = microtime();
		      $duration = $post - $this->pre;
		      // how can post time be less than pre?  assuming zero if this happens..
		      if ($post < $this->pre) $duration = 0;
	     	  print "<time>$duration</time>\n";
	    }
	    parent::paintMethodEnd($test_name);
	    $this->pre = null;
  	}

}
?>