<?php

require 'captcha.php';

$captcha = new Captcha;

//$captcha->build();

$captcha->test();

//echo $captcha->string() . "\n";
//echo $captcha->answer() . "\n";

//$captcha->save("test.jpg");

if ($captcha->checkOCR()){

	echo "Tesseract can read it.\n";

}else{

	echo "Tesseract failed to read.\n";
}

?>