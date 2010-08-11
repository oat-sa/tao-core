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
    	$post = microtime();
	    if ($this->pre != null) {
	      $duration = $post - $this->pre;
	      // how can post time be less than pre?  assuming zero if this happens..
	      if ($post < $this->pre) $duration = 0;
	      print $this->_getIndent(1);
	      print "<time>$duration</time>\n";
	    }
	    parent::paintMethodEnd($test_name);
	    $this->pre = null;
  	}

}
?>