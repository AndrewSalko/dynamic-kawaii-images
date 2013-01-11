<?php

// Class detects available image sizes which
// can be cut-resize from source image.
class KawaiiResolutionDetector
{
	public $resolutions = array(
		'720x1280' => array('width' =>720, 'height'=>1280, 'description'=>'Samsung Galaxy S3, HTC Windows Phone, Sony Xperia'),
		'640x1136' => array('width' =>640, 'height'=>1136, 'description'=>'iPhone 5, iPod touch 5'),

		'640x960' => array('width' =>640, 'height'=>960, 'description'=>'iPhone 4, iPod touch 4'),
		'480x800' => array('width' =>480, 'height'=>800,'description'=>'Nokia Lumia, Samsung Galaxy S II'),
		'480x640' => array('width' =>480, 'height'=>640, 'description'=>'HTC Touch Diamond'),

		'360x640' => array('width' =>360, 'height'=>640, 'description'=>'Nokia 5800'),
		'360x480' => array('width' =>360, 'height'=>480, 'description'=>'BlackBerry'),

		//'320x568' => array('width' =>320, 'height'=>568),//iPhone 5
		
		'320x480' => array('width' =>320, 'height'=>480, 'description'=>'iPhone 3G, iPod touch 3'),
		'320x455' => array('width' =>320, 'height'=>455, 'description'=>'Samsung Galaxy Ace, SonyEricsson Xperia Arc'),
		'320x401' => array('width' =>320, 'height'=>401),
		'320x240' => array('width' =>320, 'height'=>240),

        '240x400' => array('width' =>240, 'height'=>400, 'description'=>'Sony Ericsson, Samsung, LG'),

		'240x320' => array('width' =>240, 'height'=>320, 'description'=>'Fly, Nokia Asha'),
		);


	public function GetResolutionDescription($srcWidth, $srcHeight)
	{
		$result=$srcWidth . 'x' . $srcHeight;
		//�������� �� ����������, ������� �� �������� ������ ��� ������
		//(������ ������ ��� �����, � ������ ������ ��� �����)
		foreach ($this->resolutions as $resName => $resParams)
		{
			$itWidth=$resParams['width'];
			$itHeight=$resParams['height'];

			if($itWidth==$srcWidth && $itHeight==$srcHeight)
			{
				if (array_key_exists('description', $resParams))
				{
					$result.=' (' . $resParams['description']. ')';
					break;
				}
			}
		}//foreach
		return	$result;
	}

	//
	// Checks if given source image can be resize-cutted to destination image.
	//
	public function IsResolutionAvailable($srcWidth, $srcHeight, $destWidth, $destHeight)
	{		
		if(($srcWidth<$destWidth || $srcHeight<$destHeight) ||
			($srcWidth==$destWidth && $srcHeight==$destHeight))
		{
			return FALSE;
		}	
		
		return TRUE;
	}

	//
	// $srcWidth, $srcHeight - source image
	// $destWidth, $destHeight - destination image , $destWidth, $destHeight
	public function GetAvailableResolutions($srcWidth, $srcHeight)
	{
		$arrResult=array();

		//�������� �� ����������, ������� �� �������� ������ ��� ������
		//(������ ������ ��� �����, � ������ ������ ��� �����)
		foreach ($this->resolutions as $resName => $resParams)
		{
			$itWidth=$resParams['width'];
			$itHeight=$resParams['height'];
			if($this->IsResolutionAvailable($srcWidth,$srcHeight,$itWidth,$itHeight)==FALSE)
			{
				continue;
			}		
		
			$arrResult[$resName]=$resParams;

			//�������� ������� "�������������", ������� �������������? 
			//������� ��������������: ����� ������� ������ � �����.
			//������������: ���� ����� �� "���������"
			//if($koeff-$koeffDest<=0.01)
			//{
			//}			
		}//foreach
			
		return $arrResult;

	}//GetAvailableResolutions

	//
	// ����� �� �� ������� ������� ������, ���� ��������� ��������
	//
	public function IsSimpleResize($srcWidth, $srcHeight, $destWidth, $destHeight)
	{		
		//��������� ���������������� ����������� src-image:
		$koeff=$srcWidth/$srcHeight;
		
		//��������� ����������� ����������� ������� ����� ��������
		$koeffDest=$destWidth/$destHeight;
		
		if(abs($koeff-$koeffDest)<0.01)
		{
			return TRUE;
		}

		return FALSE;
	}//IsSimpleResize


	//
	// ���������, ������� �� "���������" ��� ����� ������ 
	// ��������� �� ������ (�������� �� ������, �������� ������)
	//
	// ������������ ��������: ������, ������� ����� �������� �����������,
	// ������� ��� ����� (������ ��������� �� �� ��������), � ������
	// ���� ��� ������ ������, �� ������ 0.
	public function GetCutHeight($srcWidth, $srcHeight, $destWidth, $destHeight)
	{
		$koeffDest=$destWidth/$destHeight;//320/240  =1.333
		$testHeight=(int)($srcWidth/$koeffDest);//640/1.333=480
		if ($testHeight<=$srcHeight)
		{
			//����� ������ �������� �� ������
			//�������� �� ������� ������ � ������ testHeight,
			//��������� ������ � ����� ������, �����.
			return $testHeight;
		}	
		return 0;
	}//GetCutHeight

}


