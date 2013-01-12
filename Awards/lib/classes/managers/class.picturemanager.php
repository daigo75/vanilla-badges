<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Implements some methods to manage the pictures required by the plugin.
 */
class PictureManager extends BaseManager {
	/**
	 * Retrieves the picture file uploaded with a form and returns the full URL
	 * to it. If a file has not been uploaded, the the method builds a URL uses
	 * a default picture file name to build the URL.
	 *
	 * @param string DestinationDir The destination directory where the picture
	 * wille be saved.
	 * @param string PictureField The name of the form field containing the
	 * picture.
	 * @param string DefaultPictureURL The Picture URL to return by default if
	 * no picture was uploaded.
	 * @return string The URL of the uploaded picture, or the default Picture URL.
	 * @throws An Exception if the the uploaded picture is not valid, or if it
	 * could not be saved.
	 */
	public static function GetPictureURL($DestinationDir, $PictureField = 'Picture', $DefaultPictureURL = null) {
		// If no file was uploaded, return the value of the Default Picture field
		if(!array_key_exists($PictureField, $_FILES) ||
			 empty($_FILES['Picture']['name'])) {
			return $DefaultPictureURL;
		}

		$UploadImage = new Gdn_UploadImage();

		// Validate the upload
		$TmpImage = $UploadImage->ValidateUpload('Picture');
		$TargetImage = $UploadImage->GenerateTargetName(PATH_LOCAL_UPLOADS, '', TRUE);

		// Save the uploaded image
		$ParsedValues = $UploadImage->SaveImageAs($TmpImage,
																						basename($TargetImage),
																						50,
																						50,
																						array('Crop' => true));

		// TODO Check that uploaded image is cropped and saved correctly

		$UploadedFileName = $UploadImage->GetUploadedFileName();
		$PictureFileName = realpath($DestinationDir) . '/' . $UploadedFileName;

		/* Move the uploaded file into a subfolder inside plugin's folder. This
		 * will allow to easily export all Awards' pictures by simply copying the
		 * whole folder plugin.
		 * Note: it's not necessary to use move_uploaded_file() because such
		 * command was already invoked by Gdn_UploadImage::SaveAs(). The file we
		 * are moving here is, therefore.
		 */
		if(rename($ParsedValues['SaveName'], $PictureFileName) === false) {
			$Msg = sprintf('Could not rename file "%s" to "%s". Please make sure ' .
										 'that the destination directory exists and that it is writable',
										 $ParsedValues['SaveName'],
										 $PictureFileName);
			$this->Log->error($Msg);
			throw new Exception($Msg);
		}

		// Build a picture URL from the uploaded file
		return $DestinationDir . '/' . $UploadedFileName;
	}
}
