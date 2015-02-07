# CiiAWSUploader Class

This class enables CiiMS to upload files to Amazon S3, isntead of uploading files to the uploads directory

## Installation

This class should be installed with composer. After installing/uploading CiiMS, run this class

```
# composer require ciims-plugins/awsuploader dev-master # DEV
composer require ciims-plugins/awsuploader {release} # Versioned
```

## How to Use

To use this class, you need to make a configuration change to your ```protected/config/params.php``` file.

```
<?php return array(

	[...]
	'ciims_plugins' => array(
		'upload' => array(
			'class' => 'CiiAWSUploader',
			'options' => array(
				'bucket' => ''
				'AWS_ACCESS_KEY' => '',
				'AWS_SECRET_ACCESS_KEY' => '',
				'cdn_domain' => 'https://cdn.example.com' // Optional
			)
		)
	)
	[...]
);
```

# Options

The following options are available for this class:

__AWS_ACCESS_KEY__ (required)

Your AWS Access Key

__AWS_SECRET_ACCESS_KEY__ (required)

Your AWS Secret Key

__bucket__ (required)

The bucket name

__cdn_domain__ (optional)

If you have a custom domain infront of AWS, you can specify it here. CiiMS will use this domain for the CDN urls instead of the default AWS domain
