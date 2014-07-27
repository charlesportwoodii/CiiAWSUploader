<?php

class CiiAWSUploader
{
	private $_filename = null;

	private $allowedExtensions = array(
        'png',
        'jpeg',
        'jpg',
        'gif',
        'bmp'
    );

    private $sizeLimit = 10485760;

    private $_config = null;

	public function __construct($config)
	{
		$this->_config = $config;
	}

	public function upload()
	{

		$size = $_FILES['file']['size'];

		$pathinfo = pathinfo($_FILES['file']['name']);
        $filename = $pathinfo['filename'];
        $ext = $pathinfo['extension'];

        if ($size == 0) 
            return array('error' => Yii::t('ciims.misc', 'File is empty'));
        
        if ($size > $this->sizeLimit) 
            return array('error' => Yii::t('ciims.misc', 'File is too large'));

        if(!in_array(strtolower($ext), $this->allowedExtensions))
        {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => Yii::t('ciims.misc', "File has an invalid extension, it should be one of {{these}}.", array('{{these}}' => $these)));
        }

		// Instantiate an S3 client
		$client = Aws\S3\S3Client::factory(array(
		    'key'    => $this->decrypt(Yii::app()->params['CiiMS']['AWS_ACCESS_KEY']),
		    'secret' => $this->decrypt(Yii::app()->params['CiiMS']['AWS_SECRET_ACCESS_KEY'])
		));

		$this->_filename = md5(md5(CII_CONFIG).md5($filename) .md5(time()));
		
		try {
			$result = $client->putObject(array(
			    'Bucket'     => $this->_config['bucket'],
			    'Key'        => CII_CONFIG.'/'.$this->_filename.'.'.$ext,
			    'SourceFile' => $_FILES['file']['tmp_name'],
			    'ACL'        => 'public-read',
			    'ContentType' => 'image/'.$ext
			));
		} catch (Exception $e) {
			return array(
				'error' => $e->getMessage()
			);
		}

		if (isset($this->_config['cdn_domain']))
		{
			$url = parse_url($result['ObjectURL']);
			return array('url' => $this->_config['cdn_domain'] . $url['path']);
		}

		return array('url' => $result['ObjectURL']);
	}

	private function decrypt($param)
	{
		$key = pack('H*', "79A0B95440D6302F5844225240DE85D68300C00EFAA3E117281B494D07681A35");
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

		$ciphertext_dec = base64_decode($param);
	    $iv_dec = substr($ciphertext_dec, 0, $iv_size);
	    $ciphertext_dec = substr($ciphertext_dec, $iv_size);

	    return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
	}
}