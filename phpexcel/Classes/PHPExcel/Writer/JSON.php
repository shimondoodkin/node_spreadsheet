<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2010 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Writer
 * @copyright  Copyright (c) 2006 - 2010 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.4, 2010-08-26
 */


/**
 * PHPExcel_Writer_CSV
 *
 * @category   PHPExcel
 * @package    PHPExcel_Writer
 * @copyright  Copyright (c) 2006 - 2010 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Writer_JSON implements PHPExcel_Writer_IWriter {
	/**
	 * PHPExcel object
	 *
	 * @var PHPExcel
	 */
	private $_phpExcel;

	/**
	 * Delimiter
	 *
	 * @var string
	 */
	private $_delimiter;

	/**
	 * Enclosure
	 *
	 * @var string
	 */
	private $_enclosure;

	/**
	 * Line ending
	 *
	 * @var string
	 */
	private $_lineEnding;

	/**
	 * Sheet index to write
	 *
	 * @var int
	 */
	private $_sheetIndex;

	/**
	 * Pre-calculate formulas
	 *
	 * @var boolean
	 */
	private $_preCalculateFormulas = true;

	/**
	 * Whether to write a BOM (for UTF8).
	 *
	 * @var boolean
	 */
	private $_useBOM = false;

	/**
	 * Create a new PHPExcel_Writer_CSV
	 *
	 * @param 	PHPExcel	$phpExcel	PHPExcel object
	 */
	public function __construct(PHPExcel $phpExcel) {
		$this->_phpExcel 	= $phpExcel;
		$this->_delimiter 	= ',';
		$this->_enclosure 	= '"';
		$this->_lineEnding 	= PHP_EOL;
		$this->_sheetIndex 	= 0;
	}

	/**
	 * Save PHPExcel to file
	 *
	 * @param 	string 		$pFileName
	 * @throws 	Exception
	 */
	public function save0($pFilename = null) {
		
		// Fetch sheet
		$sheet = $this->_phpExcel->getSheet($this->_sheetIndex);

		$saveDebugLog = PHPExcel_Calculation::getInstance()->writeDebugLog;
		PHPExcel_Calculation::getInstance()->writeDebugLog = false;
		$saveArrayReturnType = PHPExcel_Calculation::getArrayReturnType();
		PHPExcel_Calculation::setArrayReturnType(PHPExcel_Calculation::RETURN_ARRAY_AS_VALUE);

		// Open file
		$fileHandle = fopen($pFilename, 'w');
		if ($fileHandle === false) {
			throw new Exception("Could not open file $pFilename for writing.");
		}

		if ($this->_useBOM) {
			// Write the UTF-8 BOM code
			fwrite($fileHandle, "\xEF\xBB\xBF");
		}

		// Convert sheet to array
		$cellsArray = $sheet->toArray('', $this->_preCalculateFormulas);

		// Write rows to file
		foreach ($cellsArray as $row) {
			
			$this->_writeLine($fileHandle, $row);
			
		}

		// Close file
		fclose($fileHandle);

		PHPExcel_Calculation::setArrayReturnType($saveArrayReturnType);
		PHPExcel_Calculation::getInstance()->writeDebugLog = $saveDebugLog;
	}
	
	/**
	 * To check date format
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function isDate( $value )
	{
		return preg_match( '`^\d{1,2}/\d{1,2}/\d{4}$`' , $value );
	}
	
	public function isnumber( $value )
	{
		return preg_match( '`^-?(0|[1-9]\d+|[1-9]\d+\.\d*|0\.\d+)$`' , $value );
	}

	
	/**
	 * Save PHPExcel to file
	 *
	 * @param 	string 		$pFileName
	 * @throws 	Exception
	 */
	public function save($pFilename = null) {
		
		$finalData = "";
		// Fetch sheet
		$sheet = $this->_phpExcel->getSheet($this->_sheetIndex);

		$saveDebugLog = PHPExcel_Calculation::getInstance()->writeDebugLog;
		PHPExcel_Calculation::getInstance()->writeDebugLog = false;
		$saveArrayReturnType = PHPExcel_Calculation::getArrayReturnType();
		PHPExcel_Calculation::setArrayReturnType(PHPExcel_Calculation::RETURN_ARRAY_AS_VALUE);

		// Open file
		$fileHandle = fopen($pFilename, 'w');
		if ($fileHandle === false) {
			throw new Exception("Could not open file $pFilename for writing.");
		}

		if ($this->_useBOM) {
			// Write the UTF-8 BOM code
			fwrite($fileHandle, "\xEF\xBB\xBF");
		}

		// Convert sheet to array
		$prevdateformat=$sheet->getToArrayDateFormat();
		
    $sheet->setToArrayDateFormat('Y-m-d\TH:i:s\Z');
		$cellsArray = $sheet->toArray('', $this->_preCalculateFormulas);
		$sheet->setToArrayDateFormat($prevdateformat);
  
		$maxcells=0;
		foreach( $cellsArray as $row )
    {
      $maxcell=0;
      for($i = 0; $i < count($row); $i++) {
      	if(trim($row[$i])!=="") {
         $maxcell=$i;	
        }
      }
      if($maxcell>$maxcells) $maxcells=$maxcell;
		}
		$maxcells+=$maxcells+1;
		
	              //10/12/2010 -> new Date("2010-10-02T16:06:41.667Z")
            		//.. // --
            		//1055446  >>   number
            		// 0 // number
            		// 1 // number
            		///isnumberic &&  strlen <6  then print it without quots
            		//else print with quots as string
            		//+052512341234112>>52512341234112  // should be string
            		// 0234523452345234   // should be string
            		/// if it looks like date 
            		// make it a date       new Date(333/33T23'')
                      		
    $finalData="[";
		// Write rows to file
		$count = 0;
		foreach ($cellsArray as $row)
    {
      $data = "\n["; 
      for($i = 0; $i < $maxcells && $i<count($row); $i++)
      {
       if($i>0)$data .= ",";
       //$time = strtotime($row[$i]);
     	 //$row[$i] = "new Date('" . date("Y-m-d\TH:i:s\Z", $time) . "')";
       if($this->isnumber($row[$i]))
       {
        $data .= $row[$i];
       }
       else
       {
        $data .= "\"" . addslashes($row[$i]) . "\"";
       }
      }
      $data .= "]"; 
      $data=str_replace("\\'", "'", $data);
			//$this->_writeLine($fileHandle, $row);
			$finalData .= ($count>0?",":"").$data;
			$count++;
		}
    $finalData.="\n]\n";

		fwrite($fileHandle, $finalData );
		
		// Close file
		fclose($fileHandle);

		PHPExcel_Calculation::setArrayReturnType($saveArrayReturnType);
		PHPExcel_Calculation::getInstance()->writeDebugLog = $saveDebugLog;
	}

	/**
	 * Get delimiter
	 *
	 * @return string
	 */
	public function getDelimiter() {
		return $this->_delimiter;
	}

	/**
	 * Set delimiter
	 *
	 * @param	string	$pValue		Delimiter, defaults to ,
	 * @return PHPExcel_Writer_CSV
	 */
	public function setDelimiter($pValue = ',') {
		$this->_delimiter = $pValue;
		return $this;
	}

	/**
	 * Get enclosure
	 *
	 * @return string
	 */
	public function getEnclosure() {
		return $this->_enclosure;
	}

	/**
	 * Set enclosure
	 *
	 * @param	string	$pValue		Enclosure, defaults to "
	 * @return PHPExcel_Writer_CSV
	 */
	public function setEnclosure($pValue = '"') {
		if ($pValue == '') {
			$pValue = null;
		}
		$this->_enclosure = $pValue;
		return $this;
	}

	/**
	 * Get line ending
	 *
	 * @return string
	 */
	public function getLineEnding() {
		return $this->_lineEnding;
	}

	/**
	 * Set line ending
	 *
	 * @param	string	$pValue		Line ending, defaults to OS line ending (PHP_EOL)
	 * @return PHPExcel_Writer_CSV
	 */
	public function setLineEnding($pValue = PHP_EOL) {
		$this->_lineEnding = $pValue;
		return $this;
	}

	/**
	 * Get whether BOM should be used
	 *
	 * @return boolean
	 */
	public function getUseBOM() {
		return $this->_useBOM;
	}

	/**
	 * Set whether BOM should be used
	 *
	 * @param	boolean	$pValue		Use UTF-8 byte-order mark? Defaults to false
	 * @return PHPExcel_Writer_CSV
	 */
	public function setUseBOM($pValue = false) {
		$this->_useBOM = $pValue;
		return $this;
	}

	/**
	 * Get sheet index
	 *
	 * @return int
	 */
	public function getSheetIndex() {
		return $this->_sheetIndex;
	}

	/**
	 * Set sheet index
	 *
	 * @param	int		$pValue		Sheet index
	 * @return PHPExcel_Writer_CSV
	 */
	public function setSheetIndex($pValue = 0) {
		$this->_sheetIndex = $pValue;
		return $this;
	}

	/**
	 * Write line to CSV file
	 *
	 * @param	mixed	$pFileHandle	PHP filehandle
	 * @param	array	$pValues		Array containing values in a row
	 * @throws	Exception
	 */
	private function _writeLine($pFileHandle = null, $pValues = null) {
		if (!is_null($pFileHandle) && is_array($pValues)) {
			// No leading delimiter
			$writeDelimiter = false;

			// Build the line
			$line = '';

			foreach ($pValues as $element) {
				// Escape enclosures
				$element = str_replace($this->_enclosure, $this->_enclosure . $this->_enclosure, $element);

				// Add delimiter
				if ($writeDelimiter) {
					$line .= $this->_delimiter;
				} else {
					$writeDelimiter = true;
				}

				// Add enclosed string
				$line .= $this->_enclosure . $element . $this->_enclosure;
			}

			// Add line ending
			$line .= $this->_lineEnding;

			// Write to file
			fwrite($pFileHandle, $line);
		} else {
			throw new Exception("Invalid parameters passed.");
		}
	}

    /**
     * Get Pre-Calculate Formulas
     *
     * @return boolean
     */
    public function getPreCalculateFormulas() {
    	return $this->_preCalculateFormulas;
    }

    /**
     * Set Pre-Calculate Formulas
     *
     * @param boolean $pValue	Pre-Calculate Formulas?
     * @return PHPExcel_Writer_CSV
     */
    public function setPreCalculateFormulas($pValue = true) {
    	$this->_preCalculateFormulas = $pValue;
    	return $this;
    }
}
