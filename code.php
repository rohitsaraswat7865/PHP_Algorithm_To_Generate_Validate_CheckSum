<?php

	 const CHECKSUM_GENERATOR_ID = "234567";
     const CHECKSUM_VALIDITY = 6;
	 
	
	function sumAlphanumeric($string)
    {
       $ascii = NULL;
	   $ascii = sha1($ascii);
       for ($i = 0; $i < strlen($string); $i++) 
       { 
    	$ascii += ord($string[$i]); 
       }
       $ascii = "$ascii";
	   $ascii = mask($ascii);
	   return($ascii);
    }
	
	function mask($str)
	{
		if(strpos($str,'.') !== false)
		{
			$str = roundString($str);
		}
		$str = truncate($str);
        $str = append($str);
		return($str);
	}
    function append($str)
	{
		$str = "1".$str."1";
		return $str;
	}
    function maskAmount($str)//mask the amount
	{ 
		if(strpos($str,'.') !== false)
		{
			$str = str_replace(".","1",$str);
		}		
		return($str);
	}
    function roundString($str)//round around point
	{
		$index = strpos($str,".");
		$str = substr($str,0,$index);
		return($str);
	}
    function truncate($str)
	{
		if(strlen($str) > 6)
		{
			$str = substr($str,-6);
		}
		return($str);
	}
    function toInt($str)
	{
		$str = intval(str_replace(" ","",$str));
		return($str);
	}
    function unmask($str)
	{
		$str = "$str";
		return(substr($str,1,strlen($str)-2));
	}
	function decToHex($str)
	{
		$str = dechex(toInt($str));
		$str = "$str";
		return($str);
	}
	function hexToDec($str)
	{
		$str = hexdec($str);
		return($str);		
	}
	
	function getChecksum( $cardNumber , $amount , $invoiceNumber )
    {
        $amount = maskAmount($amount);
        $hours = mask(date('H'));
        $milliseconds = mask(microtime(true)*1000);
        $checkSumGeneratorId = mask(CHECKSUM_GENERATOR_ID);
        $clientServerConstant = toInt($checkSumGeneratorId) ^ toInt($hours) ^ toInt($milliseconds);
        $checkSum_1 = toInt($clientServerConstant) ^ toInt(sumAlphanumeric($amount)) ^ toInt(sumAlphanumeric($cardNumber)) ^ toInt(sumAlphanumeric($invoiceNumber));
        $checkSum_1 = append($checkSum_1);
        $checkSum_2 = decToHex($hours)."_".decToHex($milliseconds)."_".decToHex($checkSum_1);
        return($checkSum_2);
    }
	
	function validateChecksum( $checkSum_2 , $cardNumber , $amount ,$invoiceNumber)
    {
		$checkSumGeneratorId = mask(CHECKSUM_GENERATOR_ID);
        $amount = maskAmount($amount);
		$hours = hexToDec(substr( $checkSum_2 , 0 , strpos($checkSum_2,"_")));
		$milliseconds = hexToDec(substr( $checkSum_2 , (strpos($checkSum_2,"_"))+1 , (strrpos($checkSum_2,"_"))-((strpos($checkSum_2,"_"))+1)));
		$checksum_1 = unmask(hexToDec(substr( $checkSum_2 , (strrpos($checkSum_2,"_"))+1 , (strlen($checkSum_2))-((strrpos($checkSum_2,"_"))+1))));
		//region verify hours
		$currentTime = date('H');
		if(($currentTime > unmask($hours)))
		{
		  if(!(($currentTime - unmask($hours)) <= CHECKSUM_VALIDITY))
			{
				return(0);
			}
		}
		if(($currentTime < unmask($hours)))
		{
			if(!(((24 + $currentTime) - unmask($hours)) <= CHECKSUM_VALIDITY))
			{
				return(0);
			}						
		}
        //end region
        $clientServerConstant = toInt($checksum_1) ^ toInt(sumAlphanumeric($amount)) ^ toInt(sumAlphanumeric($cardNumber)) ^ toInt(sumAlphanumeric($invoiceNumber));
		
        if($clientServerConstant != (toInt($checkSumGeneratorId) ^ toInt($hours) ^ toInt($milliseconds)))
        {
			
            return(0);
        }
        else
        {
            if(toInt(sumAlphanumeric($amount)) != (toInt($checksum_1) ^ toInt($clientServerConstant)  ^ toInt(sumAlphanumeric($cardNumber)) ^ toInt(sumAlphanumeric($invoiceNumber))))
                {
                    return(0);
                }
			if(toInt(sumAlphanumeric($amount)) == (toInt($checksum_1) ^ toInt($clientServerConstant) ^ toInt(sumAlphanumeric($cardNumber)) ^ toInt(sumAlphanumeric($invoiceNumber))))
			    {
				    return(1);
			    }
        }
		
    }  
	//$check = getChecksum( "2224442220000004.009abc" , "1500.sd" , "aabbcc" );
	//echo $check;echo " ";
	
	$validate =  validateChecksum( "3f3_d3375f_a4b0529" , "2224442220000004.009abc" , "1500.sd" , "aabbcc" );
	//echo getChecksum( "2224442220000001" , "999.999" , "1234567" );echo " ";
	echo $validate; echo " ";



	
	
?>