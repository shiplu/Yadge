<?php

namespace RandomDataGenerator;


interface Field {

    /**
     * Generates random data
     * @return random data
     */
    public function generate();
}

/**
 * Generates random string of characters from a allowed range of characters.
 */
class RangedField implements Field {

    protected $chars = "";
    protected $lmin;
    protected $lmax;

    /**
     * Instanciate a ranged data
     * @param type $chars allowed chars in generated string
     * @param type $min_length minimum length of the generated string
     * @param type $max_length maximum length of the generated string
     * @throws LengthException if lenght of chars $chars is bellow 5de
     * @throws InvalidArgumentException $min_length and $max_length must be between 5 and 50
     */
    public function __construct($chars, $min_length, $max_length) {
        if (strlen($chars) < 5)
            throw new \LengthException("Number of allowed Characters are too low");
        if ($min_length < 5 || $max_length > 50 || $max_length <= $min_length)
            throw new \InvalidArgumentException("minimum and maximum must be between 5 and 50");
        $this->chars = $chars;
        $this->lmin = $min_length;
        $this->lmax = $max_length;
    }

    /**
     * Generates a random string
     * @return string
     */
    public function generate() {
        $str = '';
        $len = strlen($this->chars);
        $length = rand($this->lmin, $this->lmax);
        while ($length--) {
            $char = $this->chars[rand(0, $len - 1)];
            $str .= $char;
        }
        return $str;
    }

    /**
     * Generates a sequence of data from ASCII table
     * @param type $start
     * @param type $stop
     */
    protected function sequence($start, $stop) {
        $result = "";
        for ($i = ord($start); $i <= ord($stop); $i++)
            $result.= chr($i);
        return $result;
    }

}

abstract class ConstantRangedField extends RangedField {

    public function __construct($min_length, $max_length) {
        parent::__construct($this->range(), $min_length, $max_length);
    }

    abstract public function range();
}

/**
 * Provides empty field
 */
class EmptyField implements Field {

    public function generate() {
        return "";
    }

}

/**
 * Provides signed integer
 */
class IntegerField implements Field {

    protected $min;
    protected $max;

    public function __construct($min = -10000, $max = 10000) {
        if (!is_int($min) || !is_int($max) || $min >= $max)
            throw new \InvalidArgumentException("minimum and maximum must be valid integer");
        $this->min = $min;
        $this->max = $max;
    }

    public function generate() {
        return rand($this->min, $this->max);
    }

}

/**
 * Provides random string from alphabets
 */
class AlphabetField extends ConstantRangedField {

    public function range() {
        return $this->sequence('a', 'z').$this->sequence('A', 'F');
    }

}

/**
 * Provides random string from alphabets and digits
 */
class AlphaNumericField extends ConstantRangedField {

    public function range() {
        return $this->sequence('0', '9').$this->sequence('a', 'z').$this->sequence('A', 'F');
    }

}

/**
 * Provides random string from space type characters
 * such as tag, vertical tab, space, new line etc
 */
class SpaceField extends ConstantRangedField {

    public function range() {
        return " \t\n\r\v\f";
    }

}

/**
 * Provides random string from hexadecimal digits
 */
class HexField extends ConstantRangedField {

    public function range() {
        return $this->sequence('0', '9').$this->sequence('A', 'F');
    }

}

/**
 * Provides random double number
 */
class DoubleField extends IntegerField {

    protected $precision = 0;

    public function __construct($min = -1000000, $max = 1000000, $precision = 3) {
        if ($precision > 9 || $precision < 1)
            throw new \InvalidArgumentException("Precision must be between 1 to 9 digits");
        parent::__construct($min, $max);
        $this->precision = $precision;
    }

    public function generate() {
        $num = parent::generate();
        $prec = rand(0, pow(10, $this->precision) - 1);
        $str = sprintf(sprintf("%%d.%%0%dd", $this->precision), $num, $prec);
        return doubleval($str);
    }

}

/**
 * Provides random string from distinguishable characters. 
 * No one, zero, lowercase l, o or uppercase I will be used
 */
class CaptchaField extends ConstantRangedField {

    public function range() {
       return "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789";
    }

}

/**
 * Provides random string from uppercase alphabets
 */
class UpperCaseAlphabetField extends ConstantRangedField {

    public function range() {
        return $this->sequence('A', 'Z');
    }

}

/**
 * Provides random string from lowercase alphabets
 */
class LowerCaseAlphabetField extends ConstantRangedField {

    public function range() {
        return $this->sequence('a', 'z');
    }

}

/**
 * Provides random string from punctuation characters.
 * That is character which are not space of alphaneumeric
 */
class PunctuationField extends ConstantRangedField {

    public function range() {
        return "`~!@#$%^&*()_+|\\=-{}[];':\",./<>?";
    }

}

/**
 * Provides user a way to pass callback function to supply their own data
 */
class UserCallbackField implements Field {

    protected $callback = "";

    public function __construct($func) {
        if (!is_callable($func))
            throw new \InvalidArgumentException("Callback '$func' is not callable");

        $this->callback = $func;
    }

    public function generate() {
        return call_user_func($this->callback);
    }

}

/**
 * Provides random datum from a set of predefiend data.
 */
class SetField implements Field {

    protected $set = null;

    /**
     * @param array $set array of possible values in set
     */
    public function __construct($set) {
        if (!is_array($set) && count(array_unique(array_values($set))) < 2)
            throw new \InvalidArgumentException("You must provide a set with at least 2 unique values");
        $this->set = $set;
    }

    public function generate() {
        $pos = array_rand($this->set);
        return $this->set[$pos];
    }

}

class DataGenerator {

    const FORMAT_ARRAY = 1;
    const FORMAT_JSON = 2;

    private $fields = array();
    private $data = array();

    /**
     * adds a single field
     * @param string $name name of the field
     * @param Field $type field type spec
     * @throws InvalidArgumentException
     */
    public function addField($name, Field $type) {
        if (is_null($name) || strlen($name) < 2)
            throw new \InvalidArgumentException("Filed name must be at least 2 characters long");
        if (!$type instanceof Field)
            throw new \InvalidArgumentException("Type must be Field instance");

        $this->fields[$name] = $type;
    }

    /**
     * Removes a string
     * @param string $name name of the field to remove
     */
    public function removeField($name) {
        if (isset($this->fields[$name]))
            unset($this->fields[$name]);
    }

    protected function format_data($format) {
        switch ($format) {
            case self::FORMAT_ARRAY:
                return $this->data;
                break;
            case self::FORMAT_JSON:
                return json_encode($this->data);
                break;
            default:
                return $this->data;
        }
    }

    /**
     * Generate random data 
     * @param int $count number of rows to generate
     * @param int $format output format. 
     * @return mixed depend on $format
     */
    public function generate($count = 10, $format = DataGenerator::FORMAT_ARRAY) {
        if ($count < 10) {
            $count = 10;
        }
        if ($count > 200) {
// I know this is hard-coded.
// But I think its okay as I dont want to hang my program
            $count = 200;
        }
        $num_data = $count;
        while ($num_data--) {
            $row = array();
            foreach ($this->fields as $fname => $fspec) {
                $row[$fname] = $fspec->generate();
            }
            $this->data[] = $row;
        }

        $formatted_data = $this->format_data($format);
        return $formatted_data;
    }

}

$dgen = new \SOTest\DataGenerator();
$dgen->addField('username', new \SOTest\AlphabetField(6, 12));
$dgen->addField('age', new \SOTest\IntegerField(10, 30));
$dgen->addField('weight', new \SOTest\DoubleField(50, 80, 2));
$dgen->addField('sex', new \SOTest\SetField(array('Male', 'Female')));

print_r($dgen->generate());
?>
