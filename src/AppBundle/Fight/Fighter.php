<?php 

namespace AppBundle\Fight;

use AppBundle\Fight\FighterType;

class Fighter
{
    /**
     * List of valid fighter names
     * @var array
     */
    private static $items = array(
        // 'Aquaman',
        // 'Bane',
        'Batman',
        // 'Black Adam',
        // 'Catwoman',
        // 'Cyborg',
        // 'Darkseid',
        // 'Deadshot',
        // 'Deathstroke',
        // 'Doomsday',
        // 'Flash',
        // 'Green Arrow',
        // 'Green Lantern',
        // 'Harley Quinn',
        // 'Joker',
        // 'Lex Luthor',
        // 'Loboa',
        // 'Martian Manhuntera',
        // 'Nightwing',
        // 'Shazam',
        // 'Sinestro',
        // 'Solomon Grundy',
        'Superman',
        // 'Wonder Woman',
        // 'Zatanna',
    );

    /**
     * Name of the combatant
     * @var string
     */
    private $name = '';

    /**
     * Type of fighter
     * @var string
     */
    private $type = '';

    /**
     * Amount of health
     * @var integer
     */
    private $health = 100;

    /**
     * Damage that is done upon attack
     * @var integer
     */
    private $strength = 100;

    /**
     * Damage reduction during defense of an attack
     * @var integer
     */
    private $defense = 100;

    /**
     * Determines attack order
     * @var integer
     */
    private $speed = 100;

    /**
     * Affects ability to dodge an attack
     * @var float
     */
    private $luck = 1;

    /**
     * Determines if the fighter is currently stunned
     * @var boolean
     */
    private $stunned = false;

    /**
     * Initiates the fighter
     *
     * @param string $fighter Name of the fighter
     * @return object AppBundle\Fight\Fighter
     */
    public function __construct($fighter)
    {
        $this->setName($fighter)
             ->setType(FighterType::getRandomType())
             ->setStat('health')
             ->setStat('strength')
             ->setStat('defense')
             ->setStat('speed')
             ->setStat('luck')
        ;
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

        throw new \Exception('Request to undefined property on AppBundle\Fight\Fighter');
    }

    /**
     * Returns a list of the valid fighters defined in static array
     *
     * @return array AppBundle\Fight\Fighter::$items
     */
    public static function list()
    {
        return self::$items;
    }

    /**
     * Determines if the parsed fighter name is valid
     *
     * @param string $name
     * @return boolean
     */
    public static function isValid($name)
    {
        return in_array($name, self::list());
    }

    /**
     * Defines the name of the fighter
     *
     * @param string $name
     * @return object AppBundle\Fight\Fighter
     */
    private function setName($name)
    {
        if (self::isValid($name)) {
            $this->name = $name;
        }
        
        return $this;
    }

    /**
     * Defines and initiates the fighter type
     *
     * @param string $type
     * @return object AppBundle\Fight\Fighter
     */
    private function setType($type)
    {
        if (FighterType::isValid($type)) {
            $this->type = new FighterType($type);
        }

        return $this;
    }

    /**
     * Sets a parsed $stat for the fighter
     *
     * @param string $stat
     * @param integer $value
     * @return object AppBundle\Fight\Fighter
     */
    public function setStat($stat, $value=null)
    {
        $this->$stat = $value === null ? $this->type->generateStat($stat) : $value;

        return $this;
    }

    /**
     * Current fighter inflicts damage on a parsed opponent
     *
     * @param object $opponent AppBundle\Fight\Fighter
     * @return array
     */
    public function attack($opponent)
    {
        if ($this->stunned === true) {
            $this->setStunned(false);
            return null;
        }

        $damage   = 0;
        $special  = null;
        $defended = $opponent->hasDefended();

        if (! $defended) {
            $damage = $this->getBasicAttackDamage($opponent);
            $opponent->incurDamage($damage);
        }

        if (
            $this->specialSkillLuckyStrike($opponent, $defended)    ||
            $this->specialSkillStunningBlow($opponent, $defended)   ||
            $this->specialSkillCounterAttack($opponent, $defended)
        ) {
            $special = $this->type->getSpecialSkill();
        }

        return (object) array(
            'opponent' => $opponent,
            'type'     => $this->type->getRandomAttack(),
            'damage'   => $damage,
            'special'  => $special,
        );
    }

    /**
     * Determines the amount of the damage a basic attack will perform for this
     * fighter when parsed a given opponent.
     *
     * @param object $opponent AppBundle\Fight\Fighter
     * @return integer
     */
    public function getBasicAttackDamage($opponent)
    {
        return $this->strength - $opponent->defense;
    }

    /**
     * Runs the "Lucky Strike" special skill
     *
     * @param object $opponent AppBundle\Fight\Fighter
     * @param boolean $defended
     * @return object AppBundle\Fight\Fighter
     */
    public function specialSkillLuckyStrike($opponent, $defended=false)
    {
        if (
            $this->type->name === 'Swordsman'   &&
            $defended === false                 &&
            mt_rand(1, 100) <= 5
        ) {
            $opponent->incurDamage($this->strength);

            return true;
        }

        return false;
    }

    /**
     * Runs the "Stunning Blow" special skill
     *
     * @param object $opponent AppBundle\Fight\Fighter
     * @param boolean $defended
     * @return boolean
     */
    public function specialSkillStunningBlow($opponent, $defended=false)
    {
        if (
            $this->type->name === 'Brute'   &&
            $defended === false             &&
            mt_rand(1, 100) <= 2
        ) {
            $opponent->setStunned();

            return true;
        }

        return false;
    }

    /**
     * Runs the "Counter Attack" special skill
     *
     * @param object $opponent AppBundle\Fight\Fighter
     * @param boolean $defended
     * @return boolean
     */
    public function specialSkillCounterAttack($opponent, $defended=true)
    {
        if (
            $opponent->type->name === 'Grappler' &&
            $defended === true
        ) {
            $this->incurDamage(10);

            return true;
        }

        return false;
    }

    /**
     * Randomly determines if an attack against this fighter is likely to be
     * succesful.
     *
     * @return boolean
     */
    public function hasDefended()
    {
        return mt_rand(1, 100) <= $this->luck * 100;
    }

    /**
     * Updates the fighters health stat based on damage incurred
     *
     * @param integer $damage
     * @return object AppBundle\Fight\Fighter
     */
    public function incurDamage($damage)
    {
        $health = $this->health - $damage;
        $this->setStat('health', $health > 0 ? $health : 0);

        return $this;
    }

    /**
     * Determines if the fighter is Knocked Out
     *
     * @return boolean
     */
    public function isKnockedOut()
    {
        return $this->health <= 0;
    }

    /**
     * Sets the stunned property
     *
     * @return object AppBundle\Fight\Fighter
     */
    public function setStunned($val=true)
    {
        $this->stunned = $val;
        return $this;
    }
}
