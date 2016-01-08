<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Imagify
{
    /**
     * @var Imagify
     */
    static protected $instance;

    /**
     * @static
     * @return Imagify
     */
    static public function instance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected $_allowed_options = array(
        'w' => 'width',
        'h' => 'height',
        'm' => 'min-width',
        'n' => 'min-height',
        'c' => 'crop',
        's' => 'scale'
    );

    protected $_default_options = array(
        'width'     => 0,
        'height'    => 0,
        'min-width' => 0,
        'min-height' => 0,
        'crop'      => array('horizontal' => 'none', 'vertical' => 'none'),
        'scale'     => 'auto'
    );

    protected $_allowed_crop = array(
        'horizontal' => array(
            'n' => 'none',
            'c' => 'center',
            'l' => 'left',
            'r' => 'right',
        ),
        'vertical' => array(
            'n' => 'none',
            'c' => 'center',
            't' => 'top',
            'b' => 'bottom'
        ),
    );

    protected $_allowed_scale = array(
        'n' => 'none',
        'a' => 'auto',
        'i' => 'inverse'
    );

    protected $_keywords        = array();
    protected $_presets         = array();
    protected $_presets_only    = false;

    /** @var Image */
    protected $_image;

    /**
     * Obviously enough, creates class instance
     * All defined presets canonized at this step, if required
     */
    protected function __construct()
    {
        $config = Kohana::$config->load('imagify');

        $this->_keywords    = $config->get('keywords', array());
        $this->_presets_only= (bool)$config->get('presets_only', false);
        if ($this->_presets_only) // we will load and optimize presets only if needed
        {
            $this->_presets     = $config->get('allowed_presets', array());
            foreach ($this->_presets as $key => $preset) // canonize defined presets
            {
                $this->_presets[$key] = $this->canonize($preset);
            }
        }
    }

    /**
     * Canonize from keyword or incomplete option string to full options string
     *
     * @param $string
     * @return string
     */
    public function canonize($string)
    {
        if (is_string($string))
        {
            if ($this->is_keyword($string)) // change keyword for it's value
            {
                $pr = $this->options_from_keyword($string);
            }
            else
            {
                $pr = $this->parse_options($string);
            }
        }
        else
        {
            $pr = $string;
        }
        return $this->canonize_options($pr);
    }

    /**
     * Checks if preset is allowed. Always returns true, if presets disabled
     *
     * @param $preset
     * @return bool
     */
    public function is_allowed_preset($preset)
    {
        if (! $this->_presets_only)
        {
            return true; // it's always true if check is disabled
        }

        return in_array($this->canonize($preset), $this->_presets);
    }

    /**
     * Checks if it is known keyword
     *
     * @param $keyword
     * @return bool
     */
    public function is_keyword($keyword)
    {
        return in_array($keyword, array_keys($this->_keywords));
    }

    /**
     * Creates options array from keyword
     *
     * @param $keyword
     * @return array
     * @throws Imagify_Exception
     */
    public function options_from_keyword($keyword)
    {
        if (!$this->is_keyword($keyword))
        {
            throw new Imagify_Exception("Keyword [:keyword] is not in known keywords list: [:list]", array(':keyword' => $keyword, ':list' => implode(', ', array_keys($this->_keywords))));
        }
        return $this->parse_options($this->_keywords[$keyword]);
    }

    /**
     * Parses option string, set default values for non-set values
     *
     * @param $param_string
     * @return array of params
     * @throws Imagify_Exception
     */
    public function parse_options($param_string)
    {
        $params = $this->_default_options;
        $temp_params = explode('-', strtolower($param_string));
        foreach($temp_params as $temp_param)
        {
            $letter = substr($temp_param, 0, 1);
            if (in_array($letter, array_keys($this->_allowed_options))) // allowed option
            {
                $value = substr($temp_param, 1);
                $param_name = $this->_allowed_options[$letter];
                if ($value === false) // all options should have values defined!
                {
                    throw new Imagify_Exception('Option :option should have value defined', array(':option' => $param_name));
                }
                switch ($letter)
                {
                    case 'm': // max-width
                    case 'n': // max-height
                    case 'h': // width
                    case 'w': // and height must be integer
                        $params[$param_name] = max((int)$value, 0); // unsigned positive >= 0
                        break;
                    case 's': // scale
                        if (in_array($value, array_keys($this->_allowed_scale)))
                        {
                            $params[$param_name] = $this->_allowed_scale[$value];
                        }
                        else
                        {
                            throw new Imagify_Exception("Scale option can't be :value, it must be first letter of one of the following: :list", array(':value' => $value, ':list' => implode(', ', $this->_allowed_scale)));
                        }
                        break;
                    case 'c': // crop
                        $crop_1 = substr($value, 0, 1); // first crop option
                        $crop_2 = substr($value, 1, 1); // second crop option, if not set == false
                        if (($crop_2 !== false) and (in_array($crop_1, array_keys($this->_allowed_crop['horizontal']))) and (in_array($crop_2, array_keys($this->_allowed_crop['vertical']))))
                        {
                            $params[$param_name] = array('horizontal' => $this->_allowed_crop['horizontal'][$crop_1], 'vertical' => $this->_allowed_crop['vertical'][$crop_2]);
                        }
                        else
                        {
                            throw new Imagify_Exception("Crop option can't be [:value], it must be two letters for horizontal and vertical crop respectively, can be first letter of one of the following: [:hlist] for horizontal, [:vlist] for vertical crop",
                                array(':value' => $value, ':hlist' => implode(', ', $this->_allowed_crop['horizontal']), ':vlist' => implode(', ', $this->_allowed_crop['vertical'])));
                        }
                        break;
                }
            }
        }

        return $params;
    }

    /**
     * Creates string from given options, sorting them in predifined order. Used for presets comparition
     *
     * @param array $option
     * @return string
     */
    public function canonize_options(array $option)
    {
        $items = array();
        foreach ($this->_allowed_options as $key => $title)
        {
            $value = isset($option[$title])?$option[$title]:$this->_default_options[$title];
            switch($key)
            {
                case 'n':
                case 'm':
                case 'w':
                case 'h':
                    $value = max((int)$value, 0); // ensure positive integer
                    break;
                case 'c':
                    $value = implode('', array(substr($value['horizontal'], 0, 1), substr($value['vertical'], 0, 1))); // glue crop values
                    break;
                case 's':
                    $value = substr($value, 0, 1);
                    break;
            }
            $items[] = $key.$value;
        }

        return implode('-', $items);
    }

    /**
     * Actually processes image
     *
     * @param $options
     * @param $image
     * @return Kohana_Imagify
     * @throws Imagify_Exception
     */
    public function process($options, $image)
    {
        if (is_string($options))
        {
            $options = $this->parse_options($this->canonize($options)); // not optimal, I know
        }

        if(! $this->is_allowed_preset($options)) { throw new Imagify_Exception('Sorry, your preset is not in the allowed list'); }

        if (!is_file($image))
        {
            throw new Imagify_Exception("Can't find image file [:file]", array(':file' => $image));
        }

        $image = Image::factory($image);
        $width  = $options['width'] == 0 ? null: $options['width'];
        $height = $options['height'] == 0 ? null: $options['height'];
        $master = Image::AUTO;
        switch ($options['scale'])
        {
            case 'inverse':
                $master = Image::INVERSE;
                break;
            case 'none':
                $master = Image::NONE;
                break;
        }

        if (($image->width >= $options['min-width']) && ($image->height >= $options['min-height']))
        {
            $image->resize($width, $height, $master);
        }

        if (($options['crop']['horizontal'] != 'none') and ($options['crop']['vertical'] != 'none'))
        {
            $width  = $options['width'] == 0 ? $image->width: $options['width'];
            $height = $options['height'] == 0 ? $image->height: $options['height'];
            $crop_h_offset = null;
            $crop_v_offset = null;
            if (($options['crop']['horizontal'] != 'none') && ($options['crop']['horizontal'] != 'center'))
            {
                $crop_h_offset = ($options['crop']['horizontal'] == 'left')?0:true;

            }
            if (($options['crop']['vertical'] != 'none') && ($options['crop']['vertical'] != 'center'))
            {
                $crop_v_offset = ($options['crop']['vertical'] == 'top')?0:true;

            }
            $image->crop($width, $height, $crop_h_offset, $crop_v_offset);
        }

        $this->_image = $image;

        return $this;
    }

    /**
     * Saves image to defined location with defined quality
     *
     * @param $location
     * @param int $quality
     * @return Kohana_Imagify
     * @throws Imagify_Exception
     */
    public function save($location, $quality = 80)
    {
        if (!isset($this->_image))
        {
            throw new Imagify_Exception('Image is not yet created to save');
        }
        $this->_image->save($location, $quality);

        return $this;
    }

    /**
     * Sends headers for current image
     *
     * @return Kohana_Imagify
     * @throws Imagify_Exception
     */
    public function headers()
    {
        if (!isset($this->_image))
        {
            throw new Imagify_Exception('Image is not yet created to send headers');
        }

        $ct = 'image/jpeg';
        switch ($this->_image->type){
            case IMAGETYPE_GIF:
                $ct = 'image/gif';
                break;
            case IMAGETYPE_PNG:
                $ct = 'image/png';
                break;
        }


        header('Content-Type:'. $ct, true);
        return $this;
    }

    /**
     * Renders image
     *
     * @param int $quality
     * @throws Imagify_Exception
     * @return string
     */
    public function render($quality = 80)
    {
        if (!isset($this->_image))
        {
            throw new Imagify_Exception('Image is not yet created to render it');
        }

        echo $this->_image->render(null, $quality);
    }
}