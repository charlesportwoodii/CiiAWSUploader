<?php

/**
 * Allows CiiMS to upload files to AWS S3 CDN
 */
Yii::import('cii.utilities.CiiUploader');
class CiiAWSUploader extends CiiUploader
{
	public function upload()
	{
		$key = defined('CII_CONFIG') ? CII_CONFIG : $_SERVER['HTTP_HOST'];

		$check = $this->verifyFile();

        if (isset($check['error']))
            return $check;
        
        $filename = $check['success'];
        $fullFileName = $filename.'.'.$this->file->getExtension();

		// Instantiate an S3 client
		$client = Aws\S3\S3Client::factory(array(
			'key'    => $this->AWS_ACCESS_KEY,
			'secret' => $this->AWS_SECRET_ACCESS_KEY
		));

		$factory = new CryptLib\Random\Factory;
		$file = preg_replace('/[^\da-z]/i', '', $factory->getLowStrengthGenerator()->generateString(32));
		$cdnFilename = $file.'.'.$this->file->getExtension();

		try {
			$result = $client->putObject(array(
				'Bucket'      => $this->bucket,
				'Key'         => $key.'/'.$cdnFilename,
				'SourceFile'  => $this->file->tmp_name,
				'ACL'         => 'public-read',
				'ContentType' => 'image/'.$this->file->getExtension()
			));
		} catch (Exception $e) {
			return array(
				'error' => $e->getMessage()
			);
		}

		if ($this->cdn_domain != NULL)
		{
			$url = parse_url($result['ObjectURL']);
			return array('success' => true, 'url' => $this->cdn_domain . $url['path'], 'filename' => $file);
		}

		return array('success' => true, 'url' => $result['ObjectURL'], 'filename' => $file);
	}
}
