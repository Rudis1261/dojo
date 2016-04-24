<?php
    // Simple image wrapper
    class Image
    {
        public $im      = null;
        public $width   = null;
        public $height  = null;
        public $type    = null;
        public $mime    = null;
        public $line    = false;
        public $coords  = [];

        public function __construct(int $width, int $height)
        {
            if (empty($width) || empty($height)) {
                throw new Exception(
                    'Image size not specified. Usage:'
                    . PHP_EOL
                    . '  $image = new Image(640, 480);'
                    . PHP_EOL
                    . PHP_EOL,
                1);
            }

            $this->width = $width;
            $this->height = $height;
            $this->im = imagecreatetruecolor($this->width, $this->height);

            imagefill($this->im, 0, 0, $this->getColor('white'));
            return $this;
        }


        public function getColor($color)
        {
            $colors = [
                'red' => imagecolorallocate($this->im, 255, 0, 0),
                'green' => imagecolorallocate($this->im, 0, 255, 0),
                'blue' => imagecolorallocate($this->im, 0, 0, 255),
                'yellow' => imagecolorallocate($this->im, 255, 255, 0),
                'black' => imagecolorallocate($this->im, 0, 0, 0),
                'grey' => imagecolorallocate($this->im, 125, 125, 125),
                'white' => imagecolorallocate($this->im, 255, 255, 255),
            ];

            if (!array_key_exists($color, $colors)) {
                throw new Exception(
                    "Invalid color used, colors available:\t"
                    . PHP_EOL
                    . "  '"
                    . implode("'" . PHP_EOL . "  '", array_keys($colors))
                    . "'"
                    . PHP_EOL
                    . PHP_EOL,
                1);
            }

            return $colors[$color];
        }

        public function paint($x, $y, $color)
        {
            imagesetpixel($this->im, $x, $y, $this->getColor($color));
            return $this;
        }

        public function canDrawline()
        {
            if ($this->line === false) {
                throw new Exception('Line not started yet, usage: '
                    . PHP_EOL
                    . '  $image->line(\'black\')->from(10, 10)->to(10, 20);'
                    . PHP_EOL
                    . PHP_EOL, 1);
            }
        }

        public function line($color)
        {
            $this->coords = [];
            $this->line = $this->getColor($color);
            return $this;
        }

        public function from($x, $y)
        {
            $this->canDrawLine();
            $this->coords = [
                'x' => $x,
                'y' => $y,
            ];
            return $this;
        }

        public function to($x, $y)
        {
            $this->canDrawLine();
            imageline(
                $this->im,
                $this->coords['x'],
                $this->coords['y'],
                $x,
                $y,
                $this->line
            );

            $this->coords = [
                'x' => $x,
                'y' => $y,
            ];

            return $this;
        }

        public function end()
        {
            $this->line = false;
            $this->coords = [];
        }

        public function save($filename, $type = 'jpg', $quality = 100)
        {
            $allowed = ['jpg', 'png', 'gif'];
            switch(true) {
                case ($type == 'jpg' && (imagetypes() & IMG_JPG)):
                    imageinterlace($this->im, true);
                    return imagejpeg($this->im, $filename, $quality);
                    break;
                case ($type == 'png' && (imagetypes() & IMG_PNG)):
                    return imagepng($this->im, $filename);
                    break;
                case ($type == 'gif' && (imagetypes() & IMG_GIF)):
                    return imagegif($this->im, $filename);
                    break;
                default:
                    throw new Exception(
                        "Invalid output file type specified, valid options are: "
                        . PHP_EOL
                        . "  ["
                        . implode(', ', $allowed)
                        . "]"
                        . PHP_EOL
                        . PHP_EOL,
                    1);
                break;
            }
        }

        // Resizes an image and maintains aspect ratio.
        public function scale($new_width = null, $new_height = null)
        {
            if(!is_null($new_width) && is_null($new_height))
                $new_height = $new_width * $this->height / $this->width;
            elseif(is_null($new_width) && !is_null($new_height))
                $new_width = $this->width / $this->height * $new_height;
            elseif(!is_null($new_width) && !is_null($new_height))
            {
                if($this->width < $this->height)
                    $new_width = $this->width / $this->height * $new_height;
                else
                    $new_height = $new_width * $this->height / $this->width;
            }
            else
                return false;

            return $this->resize($new_width, $new_height);
        }

        // Resizes an image to an exact size
        public function resize($new_width, $new_height)
        {
            $dest = imagecreatetruecolor($new_width, $new_height);

            // Transparency fix contributed by Google Code user 'desfrenes'
            imagealphablending($dest, false);
            imagesavealpha($dest, true);

            if(imagecopyresampled($dest, $this->im, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height))  {
                $this->im = $dest;
                $this->width = imagesx($this->im);
                $this->height = imagesy($this->im);
                return true;
            }

            return false;
        }

        public function crop($x, $y, $w, $h)
        {
            $dest = imagecreatetruecolor($w, $h);

            if(imagecopyresampled($dest, $this->im, 0, 0, $x, $y, $w, $h, $w, $h)) {
                $this->im = $dest;
                $this->width = $w;
                $this->height = $h;
                return true;
            }

            return false;
        }

        public function cropCentered($w, $h)
        {
            $cx = $this->width / 2;
            $cy = $this->height / 2;
            $x = $cx - $w / 2;
            $y = $cy - $h / 2;
            if($x < 0) $x = 0;
            if($y < 0) $y = 0;
            return $this->crop($x, $y, $w, $h);
        }

        public function getAverageColor($precision = 10)
        {
            if (empty($this->im)) {
                return false;
            }

            $averages = [];
            foreach(range(1, ($this->width -1), $precision) as $x) {
                foreach(range(1, ($this->height - 1), $precision) as $y) {
                    $rgb = imagecolorat($this->im, $x, $y);
                    if (empty($rgb)) {
                        break;
                    }

                    $colors = imagecolorsforindex($this->im, $rgb);
                    if (empty($colors)) break;
                    if (isset($colors['alpha'])) unset($colors['alpha']);

                    $averages[] = (array_sum($colors) / count($colors));
                }
            }

            if (empty($averages)) {
                return false;
            }

            $imageColorAverage = $this->average($averages);
            //var_dump("Average Count: " . count($averages));
            //var_dump("Average: " . $imageColorAverage);

            return $imageColorAverage;
        }


        public function average($array)
        {
            return ceil(array_sum($array) / count($array));
        }
    }