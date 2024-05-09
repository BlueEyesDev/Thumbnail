<?php
class Thumbnail {
    private $RealFileExtension = [
        "ffd8ffe0" => ["MIME" => "image/jpeg", "EXTENSION" => "jpeg"],
        "89504e47" => ["MIME" => "image/png", "EXTENSION" => "png"],
        "47494638" => ["MIME" => "image/gif", "EXTENSION" => "gif"],
        "424d" => ["MIME" => "image/bmp", "EXTENSION" => "bmp"]
    ];
    private $File_Path;
    private $MIME;
    private $EXTENSION;
    private $Thumbnail;
    function __construct($Path){
        if (!file_exists($Path))
            trigger_error("Le chemin d'accès : $Path, et invalid", E_USER_ERROR);
        $this->File_Path = $Path;
    }
    public function Generate($Width, $Height, $Base64 = false, $EnableRatio = false) {

        $file = fopen($this->File_Path, "r");
        $contents = fread($file, filesize($this->File_Path));
        fclose($file);

        $EightByte = substr(bin2hex($contents), 0, 8);
        $FourByte = substr(bin2hex($contents), 0, 4);
        //  8 Bytes
        if (isset($this->RealFileExtension[$EightByte])){
            $this->MIME = $this->RealFileExtension[$EightByte]["MIME"];
            $this->EXTENSION = $this->RealFileExtension[$EightByte]["EXTENSION"];
        } 
        // 4 Bytes
        else if (isset($this->RealFileExtension[$FourByte])){
            $this->MIME = $this->RealFileExtension[$EightByte]["MIME"];
            $this->EXTENSION = $this->RealFileExtension[$EightByte]["EXTENSION"];
        } else {
            trigger_error("L'extension du fichier et invalide", E_USER_ERROR);
        }
        
        switch($this->EXTENSION) {
            case 'jpeg':
                $Image = @imagecreatefromjpeg($this->File_Path);
                break;
            case 'png':
                $Image = @imagecreatefrompng($this->File_Path);
                break;
            case 'gif':
                $Image = @imagecreatefromgif($this->File_Path);
                break;
            case 'bmp':
                $Image = @imagecreatefrombmp($this->File_Path);
                break;
        }


        $ImageWidth = imagesx($Image);
        $ImageHeight = imagesy($Image);

        if ($EnableRatio){ 
            $ratio =  $ImageWidth / $ImageHeight;
            if ($Width / $Height > $ratio) {
                $NewWidth = $Height * $ratio;
                $NewHeight = $Height;
            } else {
                $NewWidth = $Width;
                $NewHeight = $Width / $ratio;
            }
            $this->Thumbnail = imagecreatetruecolor($NewWidth, $NewHeight);
            imagecopyresampled($this->Thumbnail, $Image, 0, 0, 0, 0, $NewWidth, $NewHeight, $ImageWidth, $ImageHeight);
        } else {
            $this->Thumbnail = imagecreatetruecolor($Width, $Height);
            imagecopyresampled($this->Thumbnail, $Image, 0, 0, 0, 0, $Width, $Height, $ImageWidth, $ImageHeight);
        }

        if ($Base64){
            ob_start();
            switch($this->EXTENSION) {
                case 'jpeg':
                    imagejpeg($this->Thumbnail);
                    break;
                case 'png':
                    imagepng($this->Thumbnail);
                    break;
                case 'gif':
                    imagegif($this->Thumbnail);
                    break;
                case 'bmp':
                   imagebmp($this->Thumbnail);
                    break;
            }
            $Imagedata = ob_get_contents();
            ob_end_clean();
            imagedestroy($this->Thumbnail);
            return "data:".$this->MIME.";base64,".base64_encode($Imagedata);
        } else {
            return $this->Thumbnail;
        }
    }

    public function Save($to, $Width, $Height, $EnableRatio){
        $Create = $this->Generate($Width, $Height, false,  $EnableRatio);
        switch($this->EXTENSION) {
            case 'jpeg':
                imagejpeg($this->Thumbnail, $to);
                break;
            case 'png':
                imagepng($this->Thumbnail, $to);
                break;
            case 'gif':
                imagegif($this->Thumbnail, $to);
                break;
            case 'bmp':
               imagebmp($this->Thumbnail, $to);
                break;
        }
        imagedestroy($this->Thumbnail);
    }
}
$Thumbnail = new Thumbnail("teste.jpg");
$Thumbnail->Save("resize.jpg", 100,100, true);

?>
