<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

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
