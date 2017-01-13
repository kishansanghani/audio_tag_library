<?php

// +----------------------------------------------------------------------+
// | PHP version 4.1.0                                                    |
// +----------------------------------------------------------------------+
// | Placed in public domain by Allan Hansen, 2002. Share and enjoy!      |
// +----------------------------------------------------------------------+
// | /demo/demo.audioinfo.class.php                                       |
// |                                                                      |
// | Example wrapper class to extract information from audio files        |
// | through getID3().                                                    |
// |                                                                      |
// | getID3() returns a lot of information. Much of this information is   |
// | not needed for the end-application. It is also possible that some    |
// | users want to extract specific info. Modifying getID3() files is a   |
// | bad idea, as modifications needs to be done to future versions of    |
// | getID3().                                                            |
// |                                                                      |
// | Modify this wrapper class instead. This example extracts certain     |
// | fields only and adds a new root value - encoder_options if possible. |
// | It also checks for mp3 files with wave headers.                      |
// +----------------------------------------------------------------------+
// | Example code:                                                        |
// |   $au = new AudioInfo();                                             |
// |   print_r($au->Info('file.flac');                                    |
// +----------------------------------------------------------------------+
// | Authors: Allan Hansen <ahØartemis*dk>                                |
// +----------------------------------------------------------------------+
//



/**
* getID3() settings
*/

require_once('getid3.php');




/**
* Class for extracting information from audio files with getID3().
*/

class AudioInfo {

	/**
	* Private variables
	*/
	var $result = NULL;
	var $info   = NULL;




	/**
	* Constructor
	*/

	function AudioInfo() {

		// Initialize getID3 engine
		$this->getID3 = new getID3;
		$this->getID3->option_md5_data        = true;
		$this->getID3->option_md5_data_source = true;
		$this->getID3->encoding               = 'UTF-8';
	}




	/**
	* Extract information - only public function
	*
	* @access   public
	* @param    string  file    Audio file to extract info from.
	*/

	function Info($file) {

		// Analyze file
		$this->info = $this->getID3->analyze($file);
		
		//print_r($this->info);
		
//exit;
		// Exit here on error
		if (isset($this->info['error'])) {
			return array ('error' => $this->info['error']);
		}

		// Init wrapper object
		$this->result = array();
		$this->result['format_name']     = (isset($this->info['fileformat']) ? $this->info['fileformat'] : '').'/'.(isset($this->info['audio']['dataformat']) ? $this->info['audio']['dataformat'] : '').(isset($this->info['video']['dataformat']) ? '/'.$this->info['video']['dataformat'] : '');
		$this->result['encoder_version'] = (isset($this->info['audio']['encoder'])         ? $this->info['audio']['encoder']         : '');
		$this->result['encoder_options'] = (isset($this->info['audio']['encoder_options']) ? $this->info['audio']['encoder_options'] : '');
		$this->result['bitrate_mode']    = (isset($this->info['audio']['bitrate_mode'])    ? $this->info['audio']['bitrate_mode']    : '');
		$this->result['channels']        = (isset($this->info['audio']['channels'])        ? $this->info['audio']['channels']        : '');
		$this->result['sample_rate']     = (isset($this->info['audio']['sample_rate'])     ? $this->info['audio']['sample_rate']     : '');
		$this->result['bits_per_sample'] = (isset($this->info['audio']['bits_per_sample']) ? $this->info['audio']['bits_per_sample'] : '');
		$this->result['playing_time']    = (isset($this->info['playtime_seconds'])         ? $this->info['playtime_seconds']         : '');
		$this->result['avg_bit_rate']    = (isset($this->info['audio']['bitrate'])         ? $this->info['audio']['bitrate']         : '');
		$this->result['tags']            = (isset($this->info['tags'])                     ? $this->info['tags']                     : '');
		$this->result['comments']        = (isset($this->info['comments'])                 ? $this->info['comments']                 : '');
		$this->result['warning']         = (isset($this->info['warning'])                  ? $this->info['warning']                  : '');
		$this->result['md5']             = (isset($this->info['md5_data'])                 ? $this->info['md5_data']                 : '');

		// Post getID3() data handling based on file format
		$method = (isset($this->info['fileformat']) ? $this->info['fileformat'] : '').'Info';
		if ($method && method_exists($this, $method)) {
			$this->$method();
		}

		return $this->result;
	}




	/**
	* post-getID3() data handling for AAC files.
	*
	* @access   private
	*/

	function aacInfo() {
		$this->result['format_name']     = 'AAC';
	}




	/**
	* post-getID3() data handling for Wave files.
	*
	* @access   private
	*/

	function riffInfo() {
		if ($this->info['audio']['dataformat'] == 'wav') {

			$this->result['format_name'] = 'Wave';

		} elseif (preg_match('#^mp[1-3]$#', $this->info['audio']['dataformat'])) {

			$this->result['format_name'] = strtoupper($this->info['audio']['dataformat']);

		} else {

			$this->result['format_name'] = 'riff/'.$this->info['audio']['dataformat'];

		}
	}




	/**
	* * post-getID3() data handling for FLAC files.
	*
	* @access   private
	*/

	function flacInfo() {
		$this->result['format_name']     = 'FLAC';
	}





	/**
	* post-getID3() data handling for Monkey's Audio files.
	*
	* @access   private
	*/

	function macInfo() {
		$this->result['format_name']     = 'Monkey\'s Audio';
	}





	/**
	* post-getID3() data handling for Lossless Audio files.
	*
	* @access   private
	*/

	function laInfo() {
		$this->result['format_name']     = 'La';
	}





	/**
	* post-getID3() data handling for Ogg Vorbis files.
	*
	* @access   private
	*/

	function oggInfo() {
		if ($this->info['audio']['dataformat'] == 'vorbis') {

			$this->result['format_name']     = 'Ogg Vorbis';

		} else if ($this->info['audio']['dataformat'] == 'flac') {

			$this->result['format_name'] = 'Ogg FLAC';

		} else if ($this->info['audio']['dataformat'] == 'speex') {

			$this->result['format_name'] = 'Ogg Speex';

		} else {

			$this->result['format_name'] = 'Ogg '.$this->info['audio']['dataformat'];

		}
	}




	/**
	* post-getID3() data handling for Musepack files.
	*
	* @access   private
	*/

	function mpcInfo() {
		$this->result['format_name']     = 'Musepack';
	}




	/**
	* post-getID3() data handling for MPEG files.
	*
	* @access   private
	*/

	function mp3Info() {
		$this->result['format_name']     = 'MP3';
	}




	/**
	* post-getID3() data handling for MPEG files.
	*
	* @access   private
	*/

	function mp2Info() {
		$this->result['format_name']     = 'MP2';
	}





	/**
	* post-getID3() data handling for MPEG files.
	*
	* @access   private
	*/

	function mp1Info() {
		$this->result['format_name']     = 'MP1';
	}




	/**
	* post-getID3() data handling for WMA files.
	*
	* @access   private
	*/

	function asfInfo() {
		$this->result['format_name']     = strtoupper($this->info['audio']['dataformat']);
	}



	/**
	* post-getID3() data handling for Real files.
	*
	* @access   private
	*/

	function realInfo() {
		$this->result['format_name']     = 'Real';
	}





	/**
	* post-getID3() data handling for VQF files.
	*
	* @access   private
	*/

	function vqfInfo() {
		$this->result['format_name']     = 'VQF';
	}

	/*
	Information of mp3 if file tag version is ID3
*/
function tagReader($file){
    $id3v23 = array("TIT2","TALB","TPE1","TRCK","TDRC","TLEN","USLT");
    $id3v22 = array("TT2","TAL","TP1","TRK","TYE","TLE","ULT");
    $fsize = filesize($file);
    $fd = fopen($file,"r");
    $tag = fread($fd,$fsize);
    $tmp = "";

    fclose($fd);
    $result = array();
    if (substr($tag,0,3) == "ID3") {
        $result['FileName'] = $file;
        $result['TAG'] = substr($tag,0,3);
        $result['Version'] = hexdec(bin2hex(substr($tag,3,1))).".".hexdec(bin2hex(substr($tag,4,1)));
    }
    if(array_key_exists('Version', $result) && ($result['Version'] == "4.0" || $result['Version'] == "3.0")){
        for ($i=0;$i<count($id3v23);$i++){
            if (strpos($tag,$id3v23[$i].chr(0))!= FALSE){
                $pos = strpos($tag, $id3v23[$i].chr(0));
                $len = hexdec(bin2hex(substr($tag,($pos+5),3)));
                $data = substr($tag, $pos, 9+$len);
                for ($a=0;$a<strlen($data);$a++){
                    $char = substr($data,$a,1);
                    if($char >= " " && $char <= "~") $tmp.=$char;
                }
                if(substr($tmp,0,4) == "TIT2") $result['Title'] = substr($tmp,4);
                if(substr($tmp,0,4) == "TALB") $result['Album'] = substr($tmp,4);
                if(substr($tmp,0,4) == "TPE1") $result['Author'] = substr($tmp,4);
                if(substr($tmp,0,4) == "TRCK") $result['Track'] = substr($tmp,4);
                if(substr($tmp,0,4) == "TDRC") $result['Year'] = substr($tmp,4);
                if(substr($tmp,0,4) == "TLEN") $result['Lenght'] = substr($tmp,4);
                if(substr($tmp,0,4) == "USLT") $result['Lyric'] = substr($tmp,7);
                $tmp = "";
            }
        }
    }
    if(array_key_exists('Version', $result) && $result['Version'] == "2.0"){
        for ($i=0;$i<count($id3v22);$i++){
            if (strpos($tag,$id3v22[$i].chr(0))!= FALSE){
                $pos = strpos($tag, $id3v22[$i].chr(0));
                $len = hexdec(bin2hex(substr($tag,($pos+3),3)));
                $data = substr($tag, $pos, 6+$len);
                for ($a=0;$a<strlen($data);$a++){
                    $char = substr($data,$a,1);
                    if($char >= " " && $char <= "~") $tmp.=$char;
                }
                if(substr($tmp,0,3) == "TT2") $result['Title'] = substr($tmp,3);
                if(substr($tmp,0,3) == "TAL") $result['Album'] = substr($tmp,3);
                if(substr($tmp,0,3) == "TP1") $result['Author'] = substr($tmp,3);
                if(substr($tmp,0,3) == "TRK") $result['Track'] = substr($tmp,3);
                if(substr($tmp,0,3) == "TYE") $result['Year'] = substr($tmp,3);
                if(substr($tmp,0,3) == "TLE") $result['Lenght'] = substr($tmp,3);
                if(substr($tmp,0,3) == "ULT") $result['Lyric'] = substr($tmp,6);
                $tmp = "";
            }
        }
    }
    return $result;
}

}

