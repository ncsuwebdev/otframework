<?php
/**
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * This license is also available via the world-wide-web at
 * http://itdapps.ncsu.edu/bsd.txt
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to itappdev@ncsu.edu so we can send you a copy immediately.
 *
 * @package    Ot_Image
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 * @license    http://itdapps.ncsu.edu/bsd.txt BSD License
 * @version    SVN: $Id: $
 */

/**
 *
 * @package    Ot_Image
 * @category   Model
 * @copyright  Copyright (c) 2007 NC State University Office of      
 *             Information Technology
 *
 */

// @todo - it looks like this saves the full binary in the database. That's bad.
//         Save it to disk instead and add a column for file path.
// @todo - does the resizeImage() even return anything? I don't see anything happening, especially
//         figuring out the mime type so it can be saved to the database

class Ot_Model_DbTable_Image extends Ot_Db_Table
{
    /**
     * Database table name
     *
     * @var string
     */
    protected $_name = 'tbl_ot_image';

    /**
     * Primary key for the database
     *
     * @var string
     */
    protected $_primary = 'imageId';

    public function deleteImage($imageId)
    {
        $where = $this->getAdapter()->quoteInto('imageId = ?', $imageId);

        return $this->delete($where);
    }
    
    public function resizeImage($image, $maxWidth=640, $maxHeight=480)
    {

        $size = getimagesize($image);

        $width = $size[0];
        $height = $size[1];

        // Get the ratio needed
        $xRatio = $maxWidth / $width;
        $yRatio = $maxHeight / $height;

        /* If image already meets criteria, load current values in
         * if not, use ratios to load new size info
         */
        if (($width <= $maxWidth) && ($height <= $maxHeight) ) {
            $tnWidth = $width;
            $tnHeight = $height;
        } else if (($xRatio * $height) < $maxHeight) {
            $tnHeight = ceil($xRatio * $height);
            $tnWidth = $maxWidth;
        } else {
            $tnWidth = ceil($yRatio * $width);
            $tnHeight = $maxHeight;
        }

        // Set up canvas
        $dst = imagecreatetruecolor($tnWidth, $tnHeight);
        
        // Read image
        switch ($size['mime']) {
            
            case 'image/jpeg':
                $src = imagecreatefromjpeg($image);
                break;
                
            case 'image/png':
                $src = imagecreatefrompng($image);
                
                $transparency = imagecolortransparent($dst);
                
                if ($transparency >= 0) {
                    $tcolor = imagecolorsforindex($dst, $transparency);
                    $transparency = imagecolorallocate($dst, $tcolor['red'], $tcolor['green'], $tcolor['blue']);
                    image_fill($dst, 0, 0, $transparency);
                    imagecolortransparent($dst, $transparency);
                } else {
                    imagealphablending($dst, false);
                    $color = imagecolorallocatealpha($dst, 0, 0, 0, 127);
                    imagefill($dst, 0, 0, $color);
                    imagesavealpha($dst, true);
                }                           
                break;
                
                /*case 'image/gif':
                $src = imagecreatefromgif($image);
                break;*/
                
            default:
                throw new Ot_Exception('model-image-resizeImage:typeNotSupported');
                return;
        }
                
        // Copy resized image to new canvas
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $tnWidth, $tnHeight, $width, $height);

        switch ($size['mime']) {
            case 'image/jpeg':
                if (!imagejpeg($dst, $image, 100)) {
                   throw new Ot_Exception('model-image-resizeImage:imageNotCreated');
                }
                break;
            case 'image/png':
                
                if (!imagepng($dst, $image)) {
                    throw new Ot_Exception('model-image-resizeImage:imageNotCreated');
                }
                break;          
        }
        
        // Clear out the resources
        imagedestroy($src);
        imagedestroy($dst);
    }
}
