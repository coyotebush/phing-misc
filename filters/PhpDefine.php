<?php
include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/types/TokenSource.php';
include_once 'phing/filters/ChainableReader.php';

/*
 * Modifies PHP constant definitions in the input with
 * user-supplied values.
 *
 * Example:

 * <pre><filterreader classname="phing.filters.PhpDefine">
 *   <param type="constant" key="VERSION" value="3.2.1b" />
 *   <param type="constant" key="DB_SERVER" value="db.example.com" />
 * </filterreader></pre>
 *
 * @author    Corey Ford <coyotebush22@gmail.com>
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author    hans lellelid, hans@velum.net
 * @access    public
 * @see       ReplaceTokens
 * @see       BaseParamFilterReader
 * @package   phing.filters
 */
class PhpDefine extends BaseParamFilterReader implements ChainableReader {

    /**
     * Array to hold the replacee-replacer pairs (String to String).
     * @var array
     */
    private $_tokens = array();


    /**
     * Performs lookup on key and returns appropriate replacement string.
     * @param array $matches Array of 1 el containing key to search for.
     * @return string     Text with which to replace key or value of key if none is found.
     * @access private
     */
    private function replaceTokenCallback($matches) {
                
        $key = $matches[3];
        $oldVal = $matches[5];
        $before = $matches[1];
        $after = $matches[6];
        $tokens = $this->_tokens;

        $replaceWith = null;
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->getKey() === $key) {
                $replaceWith = $tokens[$i]->getValue();
            }
        }

        if ($replaceWith === null) {
            $this->log("No new value defined for \""  . $key . "\"");
            return $matches[0]; // Return entire statement unchanged
        }
        
        $this->log("Changed value of \"". $key
        	."\" from \"" . $oldVal. "\" to \"".$replaceWith."\"");
        return $before.$replaceWith.$after;
    }

    /**
     * Returns stream with constants having been redefined with appropriate values.
     * If a replacement value is not found for a constant, it remains untouched.
     * 
     * @return mixed filtered stream, -1 on EOF.
     */
    function read($len = null) {
        if ( !$this->getInitialized() ) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        // read from next filter up the chain
        $buffer = $this->in->read($len);

        if($buffer === -1) {
            return -1;
        }    
        
        // filter buffer
        $buffer = preg_replace_callback(
            '/(define\s*\(\s*([\'"])'
            	.'([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)'
            	.'\2\s*,\s*([\'"]?))(.+?)(\4\s*\))/',
            array($this, 'replaceTokenCallback'), $buffer, -1);

        return $buffer;
    } 
  
    /**
     * Adds a token element to the map of tokens to replace.
     * 
     * @return object The token added to the map of replacements.
     *               Must not be <code>null</code>.
     */
    function createConstant() {
        $num = array_push($this->_tokens, new Token());
        return $this->_tokens[$num-1];
    }
    
    /**
     * Sets the map of tokens to replace.
     * ; used by ReplaceTokens::chain()
     *
     * @param array A map (String->String) of token keys to replacement
     *              values. Must not be <code>null</code>.
     */
    function setConstants($tokens) {
        // type check, error must never occur, bad code of it does
        if ( !is_array($tokens) ) {
            throw new Exception("Excpected 'array', got something else");
        }

        $this->_tokens = $tokens;
    }

    /**
     * Returns the map of tokens which will be replaced.
     * ; used by ReplaceTokens::chain()
     *
     * @return array A map (String->String) of token keys to replacement values.
     */
    function getConstants() {
        return $this->_tokens;
    }

    /**
     * Creates a new PhpDefine using the passed in
     * Reader for instantiation.
     * 
     * @param object A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     * 
     * @return object A new filter based on this configuration, but filtering
     *         the specified reader
     */
    function chain(Reader $reader) {
        $newFilter = new PhpDefine($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setConstants($this->getConstants());
        $newFilter->setInitialized(true);
        return $newFilter;
    }

    /**
     * Initializes tokens and loads the replacee-replacer hashtable.
     * This method is only called when this filter is used through
     * a <filterreader> tag in build file.
     */
    private function _initialize() {
        $params = $this->getParameters();
        if ( $params !== null ) {
            for($i = 0 ; $i<count($params) ; $i++) {
                if ( $params[$i] !== null ) {
                    $type = $params[$i]->getType();
                    if ( $type === "constant" ) {
                        $name  = $params[$i]->getName();
                        $value = $params[$i]->getValue();

                        $tok = new PhpConstant();
                        $tok->setKey($name);
                        $tok->setValue($value);

                        array_push($this->_tokens, $tok);
                    }
                }
            }
        }
    }
}

/**
 * Holds a constant.
 */
class PhpConstant {

    /**
     * Constant name.
     * @var string
     */
    private $_key;

    /**
     * Token value.
     * @var string
     */
    private $_value;

    /**
     * Sets the token key.
     * 
     * @param string $key The key for this token. Must not be <code>null</code>.
     */
    function setKey($key) {
        $this->_key = (string) $key;
    }

    /**
     * Sets the token value.
     * 
     * @param string $value The value for this token. Must not be <code>null</code>.
     */
    function setValue($value) {
        $this->_value = (string) $value;
    }

    /**
     * Returns the key for this token.
     * 
     * @return string The key for this token.
     */
    function getKey() {
        return $this->_key;
    }

    /**
     * Returns the value for this token.
     * 
     * @return string The value for this token.
     */
    function getValue() {
        return $this->_value;
    }
}


