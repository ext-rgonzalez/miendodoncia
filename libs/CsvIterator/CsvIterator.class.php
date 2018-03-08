<?php
/**
 * source: http://www.php.net/manual/en/function.fgetcsv.php#57802
 * 
 * => can import a csv file and iterate over the items
 * => allows to specify the separator and the enclosure
 * 
 * Requires at least PHP 4.3
 * 
 * HowTo use example:
	$csvIterator = new CsvIterator('/path/to/file', true, '|', '"');
	while ($csvIterator->next()) {
		print_r($csvIterator->current());
	}
 * 
 * @author MO mortanon at gmail dot com, 14-Oct-2005 08:05
 * @author TK Tobias Kluge, http://enarion.net
 * 
 * @version 1.1 - 2006-04-22 by TK
 * 		- backport to PHP 4
 * 		- added usage of enclosure
 * 		- added support of headers 
 * @version 1.0 - 2005-10-14 by MO
 * 
 */

class CsvIterator 
{
   var $ROW_SIZE = 4096;
   /**
     * The pointer to the cvs file.
     * @var resource
     * @access private
     */
   var $filePointer = null;
   /**
     * The current element, which will
     * be returned on each iteration.
     * @var array
     * @access private
     */
   var $currentElement = null;
   /**
     * The row counter.
     * @var int
     * @access private
     */
   var $rowCounter = null;
   /**
     * The delimiter for the csv file.
     * @var str
     * @access private
     */
   var $delimiter = null;
   /**
     * The enclosure for the csv file.
     * @var str
     * @access private
     */
   var $enclosure = null;
   /**
     * Specifies if headers should be read and used.
     * @var bool
     * @access private
     */
   var $withHeaders = false;
   /**
    * Contains the headers if headers are used.
    * @var array
    * @access private
    */
   var $header = array();


    /**
     * This is the constructor.It try to open the csv file.The method throws an exception
     * on failure.
     *
     * @access public
     * @param string $file The csv file.
     * @param bool $withHeaders Specify if file contains header and should be used.
     * @param string $delimiter The delimiter.
     * @param string $enclosure The enclosure.
     * @param bool $skipEncodingBytes
     * @return CsvIterator
     */
   function CsvIterator($file, $withHeaders = true, $delimiter=',', $enclosure='"', $skipEncodingBytes = false)
   {
       $this->filePointer = fopen($file, 'r');
       if ($this->filePointer === false) {
       		return 'The file "'.$file.'" cannot be read.';
       }

       if($skipEncodingBytes){
           $this->_skipEncodingHeaderBytes();
       }
       
       $this->delimiter = $delimiter;
       $this->enclosure = $enclosure;
       $this->withHeaders = $withHeaders;
   }

    private function _skipEncodingHeaderBytes(){

        $encodeHeaders = array();
        $encodeHeaders['UTF-8']     = "\xEF\xBB\xBF";
        $encodeHeaders['UTF-16LE']  = "\xFF\xFE";
        $encodeHeaders['UTF-16BE']  = "\xFE\xFF";
        $encodeHeaders['UTF-32LE']  = "\xFF\xFE\x00\x00";
        $encodeHeaders['UTF-32BE']  = "\x00\x00\xFE\xFF";

        //to get the max size of the hexadecimal headers in $encodeHeaders, to read until it.
        $maxLengthHeader = max(array_map('strlen', $encodeHeaders));

        $text = fread($this->filePointer, $maxLengthHeader);
        rewind($this->filePointer);

        foreach($encodeHeaders as $header){
            $lenHeader = strlen($header);

            if(substr($text, 0, $lenHeader) == $header){
                fseek($this->filePointer, $lenHeader);
                break;
            }

        }

    }

   /**
     * This method resets the file pointer.
     *
     * @access public
     */
   function rewind() {
       $this->rowCounter = 0;
       rewind($this->filePointer);
   }

   /**
     * This method returns the current csv row as a 2 dimensional array
     *
     * @access public
     * @return array The current csv row as a 2 dimensional array
     */
   function current() {
	   if ($this->withHeaders && $this->rowCounter == 0) {   	
       		$this->header = $this->currentElement;
       		$this->next();
	   }	   

       // check for invalid currentElement
       if (!is_array($this->currentElement)) {
       		// var_dump($this->currentElement);
       		return $this->currentElement;
       }

	   // handle headers       
       if ($this->withHeaders) {
       		// apply headers to currentElement
			$tmp = array();       		
       		
       		foreach ($this->currentElement as $i => $value) {
       			// $tmp[$i] = $value; // uncomment this line to use indices as array keys
       			//remove the " from the header
       			$header = str_replace('"','',$this->header[$i]);
       			$tmp[trim($header)] = $value;
       		}
       		$this->currentElement = $tmp;
       }
       $this->rowCounter++;
       return $this->currentElement;
   }

   /**
     * This method returns the current row number.
     *
     * @access public
     * @return int The current row number
     */
   function key() {
       return $this->rowCounter;
   }

   /**
     * This method checks if the end of file is reached.
     *
     * @access public
     * @return boolean Returns true on EOF reached, false otherwise.
     */
   function next() {
       $this->currentElement = fgetcsv($this->filePointer, $this->ROW_SIZE, $this->delimiter, $this->enclosure);
       // eof is reached <=> result of fgetcsv is false 
       return $this->currentElement !== FALSE;
   }

   /**
     * This method checks if the next row is a valid row.
     *
     * @access public
     * @return boolean If the next row is a valid row.
     */
   function valid() {
       if (!$this->next()) {
           fclose($this->filePointer);
           return false;
       }
       return true;
   }
   
   /**
    * This method closes all open files and shuts this object down.
    * 
    * @access public
    * @return void no result
    */
   function tearDown() {
   	   fclose($this->filePointer);
   }
}
