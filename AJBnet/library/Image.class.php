<?php

/**
 * Image ... stores image metadata, uses File to access storage and general file metadata
 * 
 * Notes on Constants:
 * 
 * 001-004 : Image Formats
 * 100-104 : Scale Modes
 * 200-209 : Alignment Options
 * 
 * @todo Polite mode needs work
 * @todo the rest of the modes need to be completed
 * @todo DB has to be completed so we can reference modded iamges
 * @todo watermarking
 * @todo storage has to be finished so we can cache and log versions
 * 
 * @package AJBnet
 * @subpackage Library
 * @author Avery Brooks <avery@ajbnet.com>
 * @copyright 2009
 *
 */
class Image extends AJBnet_RPC { // extends AJBnet_DAO {

	const GIF = 001;
	const JPG = 002;
	const PNG24 = 003;
	const PNG8 = 004;
	const JPEG = Image::JPG;

	const SCALE = 101; // scale without regard for aspect ratio 
	const SCALE_ASPECT = 102; // Maintain X/Y ratio
	const POLITE = 103; // Maintain ratio, then crop @ center
	const COMPLEX = 104; // Maintain ratio scale, then crop @ specified X/Y coords

	// Alignment options, for polite mode, watermark etc
	const ALIGN_TOP_LEFT = 201;
	const ALIGN_TOP_MIDDLE = 202;
	const ALIGN_TOP_RIGHT = 203;
	const ALIGN_MIDDLE_LEFT = 204;
	const ALIGN_MIDDLE_MIDDLE = 205;
	const ALIGN_MIDDLE_RIGHT = 206;
	const ALIGN_BOTTOM_LEFT = 207;
	const ALIGN_BOTTOM_MIDDLE = 208;
	const ALIGN_BOTTOM_RIGHT = 209;

	// This one is a real beauty.
	const ALIGN_CENTER = Image::ALIGN_MIDDLE_MIDDLE;
	
	// const MAX_X = 1000 ?
	// const MAX_Y = 1000 ?
	
	protected $_ReadableTypes = array(

		Image::SCALE => "Scale",
		Image::SCALE_ASPECT => "Aspect",
		Image::POLITE => "Polite",
		Image::COMPLEX => "Complex",
		
		Image::JPG => "jpeg"

	);
	
	protected $File;

	/**
	 * Do I need to define FK relationships explicitly in the SQL ?
	 * Or is that handled by INNODB w/ R_TableID ?
	 * 
	 * Also, why can't I use {$this->Var} inside the inline definition of a later member variable ??
	 * I'm getting an error ... it would be nice to internally reference the primary tablename in case
	 * it changes for some reason so we don't have to rewrite the SQL.  (and have the format remain tidy)
	 * 
	 * @var string Primary table for DAO.
	 */	
	protected $Table = "Image";

	/**
	 * Image table contains metadata about the current image,
	 * as well as foreign key to find the File storage information
	 * of the current version, as well as File storage information
	 * of the original file.
	 * 
	 * @var array Table CREATE MySQL for Image.
	 */
	protected $Tables = array(
	"
		CREATE TABLE `Image` (
			`ImageID` INT( 10 ) NOT NULL AUTO_INCREMENT,
			`R_FileID_Source` INT( 10 ),
			`R_FileID_Storage` INT( 10 ),
			`Format` INT( 1 ),
			`Transformation` INT( 2 ) NOT NULL,
			`Width` INT( 5 ) NOT NULL,
			`Height` INT( 5 ) NOT NULL,
			`Compression` INT( 3 ) NOT NULL,
			`Created` DATETIME NOT NULL,
			`Updated` DATETIME NOT NULL,
			PRIMARY KEY ( `ImageID` )
		) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci
	"
	);
	
	/**
	 * @param AJBnet $AJBnet Reference to the controller.
	 */
	public function __construct($AJBnet = null) {
		parent::__construct(&$AJBnet);
		// $this->File = new File();
	}
	
	private function _GetMode($str) {
		// see controller for ref on how we are handling multiple params in a path
		// basic test on OS-X 10.6 (unix deriv) has comma working pretty well.
		// maybe some research on this would be good, so we don't break any systems!
		foreach($this->_ReadableTypes as $k => $v) {
			if ($str === $v)
				return $k;
		}
		return false;
	}

	/**
	 * View an image.
	 * 
	 * This will create an image based on path based parameters,
	 * and write it to our disk cache.
	 * 
	 * Usage: /Image/Load/[Mode/][Width/][Height/][Compression/]id[.format]
	 */
	public function View() {

		// I think we already have this Path deal inside of the controller, but here is my rant
		
		// we may need to go through the controller to get to the path
		// but that might be much more clear if we made the
		// parent constructor handle that sort of crap
		// so we have a nice clear internal reference to path
		
	
		$Mode = $this->_GetMode($this->Path[2]);
		
		switch ($Mode) :

			default:
			case Image::POLITE:

				// Figure out options

				$Width = (int) $this->Path[3];
				$Height = (int) $this->Path[4];
				$Compression = (int) $this->Path[5];
				$Image = $this->GetID();
				$Format = Image::JPG;

				$Filename = "storage/images/sample.jpg";
				
				// Load
				
				$ImgSrc = imagecreatefromjpeg($Filename);
				$ImgDst = imagecreate($Width,$Height);

				// Scale
				
				$SrcX = imagesx($ImgSrc);
				$SrcY = imagesy($ImgSrc);

				$Ratio = $SrcX / $SrcY;
				
				// $NewX = $Ratio * $SrcY;
				// $NewY = $Ratio / $SrcX;
				
				if ($SrcX > $SrcY) {
					$NewX = (int) $Width;
					$NewY = (int) $SrcY * $Ratio;
				} else {
					$NewX = (int) $SrcX * $Ratio;
					$NewY = (int) $Height;
				}
				
				$this->WriteLog(
					"X - {$SrcX} to {$NewX}, " .
					"Y - {$SrcY} to {$NewY}"				
				);
				
				imagecopyresampled($ImgDst,$ImgSrc,0,0,0,0,$NewX,$NewY,$SrcX,$SrcY);

				// imagecopyresampled(dest,src,dstx,dsty,srcx,srcy,dstw,dsth,srcw,srch)
				
				// Crop
				
				// ?????!@?#?!@#?
				
				// Output
	
				// we can also use this as a time to insert a row
				// in to a cache tracking table
				// that will be a blast
				// for selective
				// purge
				// of
				// cache
				// for fun
	
				// $target_file = implode($this->Path,"/");
				// need a prep dir function ... maybe make this in File::PrepDirectory ( Path ) etc


				header("Content-type: " . MimeTypes::JPEG);
				imagejpeg($ImgDst,null,$Compression);

				break;

		endswitch;

		$this->WriteLog(
			"I would like to use Mode '" . $this->_ReadableTypes[$Mode]
			. "' to scale Image " . $this->GetID()
			. " to {$Width}px by {$Height}px"
			. " in format '" . $this->_ReadableTypes[$Format] . "'"
			. " at compression '{$Compression}'"
		);

		exit();
		// return "image happens";
		
		// IMPORTANT:
		// __ MODE DETERMINES PARAM ORDER __
		// Thanks.
		
		/*
		 * 
		 * request is as follows:
		 * 
		 * /Image/Load/[Mode/][Width/][Height/][Compression/]id[.format]
		 * 
		 * eg:
		 * 
		 * /Image/Load/Mode/100/1.jpg == scale source image #1 to 100xY
		 * /Image/Load/Mode/100/100/1.png == scale scource image #1 to 100x100
		 *
		 * Find - R_FileID_Source
		 * Find - Width / Height / Compression / Format
		 *
		 * Generate & cache
		 *
		 */

	}
	
}
