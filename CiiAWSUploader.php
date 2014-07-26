<?php

class CiiAWSUploader
{
	private $_filename = null;

	public function __construct($config, $hash=false)
	{
		// Instantiate an S3 client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => $this->decrypt(Yii::app()->params['CiiMS']['AWS_ACCESS_KEY']),
		    'secret' => $this->decrypt(Yii::app()->params['CiiMS']['AWS_SECRET_ACCESS_KEY'])
		));

		$this->_filename =md5($_FILES['file']['name']);
		
		$result = $client->putObject(array(
		    'Bucket'     => $config['bucket'],
		    'Key'        => $_FILES['file']['name'],
		    'SourceFile' => $_FILES['file']['tmp_name']
		));

		print_r($result);
		die();
	}

	public function getFileName()
	{
		return $this->_filename;
	}

	private function decrypt($param)
	{
		$key = pack('H*', "79A0B95440D6302F5844225240DE85D68300C00EFAA3E117281B494D07681A35");
		$ciphertext_dec = base64_decode($param);
	    $iv_dec = substr($ciphertext_dec, 0, $iv_size);
	    $ciphertext_dec = substr($ciphertext_dec, $iv_size);

	    return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
	}
}