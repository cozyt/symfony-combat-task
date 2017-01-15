<?php 

namespace AppBundle\Fight;

class FighterType
{
    /**
     * List of valid fighter types and stats
     * @var array
     */
    private static $items = array(
        'Swordsman' => array(
            'health'    => array(40,60),
            'strength'  => array(60,70),
            'defense'   => array(20,30),
            'speed'     => array(90,100),
            'luck'      => array(.3,.5),
            'attacks'   => array('slashed', 'stabbed', 'punched',),
            'special'   => 'Lucky Strike',
        ),
        'Brute'     => array(
            'health'    => array(90,100),
            'strength'  => array(65,75),
            'defense'   => array(40,50),
            'speed'     => array(40,65),
            'luck'      => array(.3,.35),
            'attacks'   => array('threw', 'squeezed', 'pounded',),
            'special'   => 'Stunning Blow',
        ),
        'Grappler'  => array(
            'health'    => array(60,100),
            'strength'  => array(75,80),
            'defense'   => array(35,40),
            'speed'     => array(60,80),
            'luck'      => array(.3,.4),
            'attacks'   => array('punched', 'kicked', 'headbutted',),
            'special'   => 'Counter Attack',
        ),
    );

    /**
     * Defined fighter type name
     * @var string
     */
    private $name = '';

    /**
     * Initiates the fighter type
     *
     * @param string $type Name of the type
     * @return object AppBundle\Fighter\FighterType
     */
    public function __construct($type)
    {
        $this->set($type);
    }

    /**
     * Handles requests for out of scope properties
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        throw new \Exception('Request to undefined property on AppBundle\Fight\FighterType');
    }

    /**
     * Returns a list of the valid fighter types defined in static array
     *
     * @return array AppBundle\Fighter\FighterType::$items
     */
    public static function list()
    {
        return self::$items;
    }

    /**
     * Returns keys from the items array
     *
     * @return array
     */
    public static function keys()
    {
        return array_keys(self::list());
    }

    /**
     * Determines if the parsed type is valid
     *
     * @param string $type
     * @return boolean
     */
    public static function isValid($type)
    {
        return in_array($type, self::keys());
    }

    /**
     * Returns a random fighter type from a list of valid types
     *
     * @return string
     */
    public static function getRandomType()
    {
        $keys = self::keys();
        return $keys[array_rand($keys)];
    }


    /**
     * Defines and initiates the fighter type
     *
     * @param string $type
     * @return object AppBundle\Fighter\Fighter
     */
    private function set($type)
    {
        if (self::isValid($type)) {
            $this->name = $type;
        }

        return $this;
    }

    /**
     * Returns fighter type properties from items array.
     *
     * @return null|object
     */
    public function get()
    {
        if (self::isValid($this->name)) {
            $items = self::list();
            return (object) $items[$this->name];
        }

        return;
    }

    /**
     * Returns 'stat' for the fighter type based on the range for the named stat
     *
     * @param  string $stat
     * @return integer
     */
    public function generateStat($stat)
    {
        list($min, $max) = $this->get()->$stat;

        $n = mt_rand($min * 100, $max * 100) / 100;

        if (
            gettype($min) === 'double' &&
            gettype($max) === 'double'
        ) {
            return $n;
        }

        return round($n);
    }

    /**
     * Returns a random attack from the set type, if not fighter type is set
     * then a default "hit" is returned.
     *
     * @return string
     */
    public function getRandomAttack()
    {
        $type = $this->get();
        return $type ? $type->attacks[array_rand(array_keys($type->attacks))] : 'hit';
    }

    /**
     * Returns the special skill for the fighter type
     *
     * @return ...
     */
    public function getSpecialSkill()
    {
        $type = $this->get();
        return $type ? $type->special : '';
    }
}
