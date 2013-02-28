<?php
include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Inserts text or the contents of a file
 * at the beginning or end of a stream.
 * <p>
 * Sample target:<br/>
 * <pre>
 * <target name="copyright">
 *     <reflexive>
 *        <fileset dir="files" includes="*.txt" />
 *        <filterchain>
 *            <filterreader class="phing.filters.InsertFilter">
 *                <param name="text" value="Copyright ${YEAR} ${my.name}" />
 *                <param name="location" value="start" />
 *            </filterreader>
 *        </filterchain>
 *     </reflexive>
 * </target>
 * </pre>
 * @author Corey Ford <coyotebush22@gmail.com>
 */
class InsertFilter extends BaseParamFilterReader implements ChainableReader
{
	/**
	 * File to insert.
	 * @var PhingFile
	 */
	private $file;
	
	/**
	 * Text to insert.
	 * @var string
	 */
	private $text;
	
	/**
	 * Whether to prepend instead of append.
	 * @var boolean
	 */
	private $prepend;
	
	/**
	 * Whether insertion is done already.
	 * @var boolean;
	 */
	private $_done;
	
	public function setFile ($f)
	{
		if ($f instanceof PhingFile)
			$this->file = $f;
		else
			$this->file = new PhingFile($f);
	}
	
	public function getFile ()
	{
		return $this->file;
	}
	
	public function setText ($t)
	{
		$this->text = $t;
	}
	
	public function getText ()
	{
		return $this->text;
	}
	
	public function setLocation ($l)
	{
		$this->prepend = ($l == 'start');
	}
	
	public function getLocation ($l)
	{
		return $this->prepend ? 'start' : 'end';
	}
	
	private function textToAdd ()
	{
		if (is_object($this->file) && !empty($this->text))
			throw new BuildException ('Cannot use both text and file parameters');
		if (is_object($this->file))
			return $this->file->contents();
		if (!empty($this->text))
			return $this->text;
		throw new BuildException ('Either a filename or a text string must be provided');
	}
	
	
	function read ($len = null)
	{
		if (!$this->getInitialized())
		{
			$this->_initialize();
			$this->setInitialized(true);
		}
		
		$buffer = $this->in->read($len);
		
		// Prepend
		if ($this->prepend)
		{
			if ($buffer === -1)
				return -1;
			
			if (!$this->_done)
			{
				$this->_done = true;
				return $this->textToAdd().$buffer;
			}
			return $buffer;
		}
		// Append
		else
		{
			if ($buffer === -1)
			{
				if ($this->_done)
					return -1;
				$this->_done = true;
				return $this->textToAdd();
			}
			return $buffer;
		}
	}
	
	public function chain (Reader $reader)
	{
		$newFilter = new InsertFilter($reader);
		$newFilter->setFile($this->getFile());
		$newFilter->setText($this->getText());
		$newFilter->setLocation($this->getLocation());
		$newFilter->setInitialized(true);
		return $newFilter;
	}
	
	private function _initialize ()
	{
		$params = $this->getParameters();
		if ($params !== null)
		{
			for ($i = 0, $_i = count($params); $i < $_i; $i++)
			{
				if ($params[$i]->getType() === null)
				{
					if ($params[$i]->getName() === 'file')
					{
						$this->setFile($params[$i]->getValue());
					}
					else if ($params[$i]->getName() === 'text')
					{
						$this->setText($params[$i]->getValue());
					}
					else if ($params[$i]->getName() === 'location')
					{
						$this->setLocation($params[$i]->getValue());
					}
				}
			}
		}
	}
	
}
