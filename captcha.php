<?php
 
class Captcha {

	//Sometimes tesseract can read the alpha word
	//cannot save tesseract to $value

	protected $equation = null;

	protected $image = null;
 
	protected $height = null;
 
	protected $width = null;

	protected $string = "";

	protected $answer = null;

	protected $location = "images/";

	public function build($width = 400, $height = 150){

		$this->width = $width;
	
		$this->height = $height;
	
		$image = imagecreatetruecolor($width, $height);

		$this->image = $image;
	
		$this->makeQuestion();
		
		$this->drawBackground();
	
		$this->drawText();

	}

	public function test(){

		$image = imagecreatetruecolor(400, 150);

		$color = imagecolorallocate($image, 255, 255, 255);

		$font = __DIR__ . "/fonts/" . "font" . "8" . ".ttf";

		imagettftext($image, 60, 0, 60, 60, $color, $font, "Hello");

		$location = __DIR__ . "/images/" . "test.jpg";

		imagejpeg($image, $location);

		$this->image = $image;

		$this->save("test.jpg");

		$this->string = "hello";
	}

	public function makeQuestion(){

		$options =array(array("zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine"),
	  					array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9"));
	  	
		$operators = array("+", "-");
		 
		$num1 = rand(0,9);
		$num2 = rand(0,9);
		 
		$operator = rand(0, 1);
		 
		if ($operator == 0){	//-
		 
			$this->answer = $num1 + $num2;
		 
		}else{				//+
		 
			$this->answer = $num1 - $num2;
		 
		}
		

		//keep one alpha characters and one digit
		$alphaORdigit1 = rand(0,1);

		if ($alphaORdigit1 == 0){

			$alphaORdigit2 = 1;

		}else{

			$alphaORdigit2 = 0;

		}
		 
		$this->question = array($options[$alphaORdigit1][$num1], $operators[$operator], $options[$alphaORdigit2][$num2], "=");
		$this->string = $options[$alphaORdigit1][$num1] . $operators[$operator] . $options[$alphaORdigit2][$num2];

	}

	protected function drawBackground(){
	 
		$colors = array(array("red"=>255, "green"=>51, "blue"=>51),
				array("red"=>153, "green"=>153, "blue"=>153),
				array("red"=>102, "green"=>153, "blue"=>153),
				array("red"=>0, "green"=>51, "blue"=>51));
		
		shuffle($colors);

		$color = imagecolorallocate($this->image, 255, 255, 255);
		imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $color);
		
		$shift = ($this->width - 12) / 4;
		$x1 = 0;
		$y1 = 0;
		$x2 = $shift;
		$y2 = $this->height;
		
		for($i = 0; $i < 4; $i++){
		
			$color = imagecolorallocate($this->image, $colors[$i]["red"], $colors[$i]["green"], $colors[$i]["blue"]);
		
			imagefilledrectangle($this->image, $x1, $y1, $x2, $y2, $color);
			
			$x1 += $shift + 4;
			$x2 += $shift + 4;
		
		}
	}

	protected function drawText(){
		
		$question = $this->question;
		
		$color = imagecolorallocate($this->image, 255, 255, 255);
		
		$font = __DIR__ . "/fonts/" . "font" . rand(1, 9) . ".ttf";
		
		$colorBoxWidth = ($this->width - 12) / 4;
		
		for ($i = 0; $i < 4; $i++){
		
			$size = rand(20, 35);

			$offset = rand(-15, 15);

			//Can't tell if its plus or minus
			if ($i == 1){

				$angle = 0;

			}else{

				$angle = rand(-30, 30);

			}
		
			$box = imagettfbbox($size, 0, $font, $question[$i]);

			$textWidth = $box[2] - $box[0];
		
			$textHeight = $box[1] - $box[7];
			
			$Y = ($this->height / 2) + ($textHeight / 2);
			
			$X = (($colorBoxWidth / 2) - ($textWidth / 2))  + ($colorBoxWidth * ($i));

			$offset = rand(-5, 5);

			imagettftext($this->image, $size, $angle, $X, $Y + $offset, $color, $font, $question[$i]);
		
		}
	}

	public function get($quality = 95){
 
		ob_start();
		
		$this->output($quality);
		
		return ob_get_clean();
	}

	public function output($quality = 95){
 
		if ($this->image != null){
		
			imagejpeg($this->image, null, $quality);
			
		}
	}

	public function save($filename){

		$location = __DIR__ . "/images/" . $filename;

		imagejpeg($this->image, $location);
	}

	public function inline(){
 
		return 'data:image/jpeg;base64,' . base64_encode($this->get($quality));
	
	}

	public function equation(){

		return $this->equation;

	}

	public function string(){

		return $this->string;

	}

	public function answer(){

		return $this->answer;

	}

	public function checkOCR(){

		if (!is_dir($this->location)) {
            @mkdir($this->location, 0755, true);
        }

        $tempjpg = 'ocr.jpg';
        $temppng = 'ocrReadable.png';

        $this->save($tempjpg);
        shell_exec("convert images/$tempjpg -colorspace Gray images/$temppng");
        $value = trim(strtolower(shell_exec("tesseract images/$temppng stdout")));

        echo "Tesseract read...    " . $value . "\n";

        @unlink($tempjpg);
        @unlink($temppng);

        if ($value == $this->string){

        	return true;

        } else{

        	return false;

        }

	}
}
?>