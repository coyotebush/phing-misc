<?php
include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * Compresses the stream using gzencode
 *
 * Requires PHP to be compiled with Zlib support.
 * <p>
 * Sample target:<br/>
 * <pre>
 * <target name="gzip">
 *     <copy todir="compressed">
 *         <fileset dir="txt" includes="*.txt" />
 *         <mapper type="regexp" from="(.*\..*)" to="\1.gz" />
 *         <filterchain>
 *             <filterreader classname="phing.filters.GzipFilter">
 *                 <param name="compression" value="best" />
 *             </filterreader>
 *         </filterchain>
 *     </copy>
 * </target>
 * </pre>
 * @uses gzencode
 * @author Corey Ford <coyotebush22@gmail.com>
 */
class GzipFilter extends BaseParamFilterReader implements ChainableReader
{
	/** Level of compression, from 1 to 9 */
	private $compression = 6;
	
	/**
	 * Sets the zlib compression level.
	 * @param mixed $c integer from 1-9, or 'fast' or 'best'
	 * @throws BuildException if invalid value set
	 */
	public function setCompression ($c)
	{
		if ($c == 'fast')
			$c = 1;
		else if ($c == 'best')
			$c = 9;
		if (in_array($c, range(1, 9)))
			$this->compression = $c;
		else
			throw new BuildException('Compression must be in the range 1-9');
	}
	
	/**
	 * Gets the compression value.
	 * @return integer compression level, from 1-9
	 */
	public function getCompression ()
	{
		return $this->compression;
	}
	
	public function read ($len = null)
	{
		if (!function_exists('gzencode'))
			throw new BuildException ('Zlib support must be enabled in PHP to use GzipFilter');
		
		$raw = null;
		while (($data = $this->in->read($len)) !== -1)
			$raw .= $data;
		if ($raw === null)
			return -1;
		
		return gzencode($raw, $this->compression);
	}
	
	public function chain (Reader $reader)
	{
		$newFilter = new GzipFilter($reader);
		$newFilter->setCompression($this->getCompression());
		$newFilter->setInitialized(true);
		return $newFilter;
	}
	
	public function _initialize ()
	{
		$params = $this->getParameters();
		if ($params !== null)
		{
			for ($i = 0, $_i = count($params); $i < $_i; $i++)
			{
				if ($params[$i]->getType() === null)
				{
					if ($params[$i]->getName() === 'compression')
					{
						$this->setCompression($params[$i]->getValue());
					}
				}
			}
		}
	}
}
