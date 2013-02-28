<?php
require_once 'phing/tasks/system/CopyTask.php';
include_once 'phing/system/io/FileReader.php';
include_once 'phing/system/io/FileWriter.php';
include_once 'phing/filters/GzipFilter.php';

/**
 * Implements a Gzip compression filter while copying files.
 *
 * This is a shortcut for calling the <copy> task with the GzipFilter used
 * in the <filterchains> section.
 * 
 * @author    Corey Ford <coyotebush22@gmail.com>
 * @package   phing.tasks.system
 */
class GzipTask extends CopyTask {
    
    /** PhingFilterReader object */
    private $filter;
    
    /**
     * Setup the filterchains w/ GzipFilter that we will use while copying the files.
     */
    function init() {
        $f = new PhingFilterReader();
        $f->setClassName('phing.filters.GzipFilter');
        $chain = $this->createFilterChain($this->getProject());
        $chain->addFilterReader($f);
        $this->filter = $f;        
    }
    
    /**
     * @see CopyTask::main()
     */
    function main() {        
        $this->log("Performing gzip compression", Project::MSG_VERBOSE);
        parent::main();
    }
    
    /**
     * Set the level of compression
     * @param PhingFile $style
     */
    function setLevel($level) {
    	$param = new Parameter ();
    	$param->setName('compression');
    	$param->setValue($level);
        $this->filter->addParam($param);
    }
}
