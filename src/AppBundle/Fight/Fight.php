<?php

namespace AppBundle\Fight;

use AppBundle\Fight\Fighter;

class Fight
{
    /**
     * Total number of fighters allowed for the fight
     * @var integer
     */
    private $maxFighters;

    /**
     * Max number of rounds which defines the number of turns for each fighter
     * @var integer
     */
    private $rounds;

    /**
     * Determines the speed of the fight. Configurable so you havent got to
     * sit and wait for the fight to complete. Implemented to speed up dev
     * @var integer
     */
    private $speed;

    /**
     * List of fighters on the card for the fight
     * @var array
     */
    private $fighters = array();

    /**
     * Lists the keys of fighters in the order they will take their turn.
     * By default, this will be the order they are defined in the fighter
     * property.
     * @var array
     */
    private $order = array();

    /**
     * Initializes the fight
     */
    public function __construct($maxFighters=2, $rounds=30, $speed=1)
    {
        $this->maxFighters = $maxFighters;
        $this->rounds = $rounds;
        $this->speed = $speed;
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

        throw new \Exception('Request to undefined property on AppBundle\Fight\Fight');
    }

    /**
     * Adds a new fighter to the fight as long as fighter count has not
     * exceeded the number defined in the maxFighters proeprty.
     *
     * @param string $fighter
     * @return object AppBundle\Fight\Fight
     */
    public function setFighter($fighter)
    {
        if (
            $this->fighterCount() <= $this->maxFighters &&
            Fighter::isValid($fighter)
        ) {
            $this->fighters[] = new Fighter($fighter);
        }

        return $this;
    }

    /**
     * Returns a fighter from the fighter property by a parsed key
     *
     * @param  integer $key
     * @return object AppBundle\Fight\Fighter
     */
    public function getFighterByKey($key)
    {
        return $this->fighters[$key];
    }

    /**
     * Determines the order that fighters will take their turn
     *
     * @return object AppBundle\Fight\Fight
     */
    public function setFighterOrder()
    {
        $splitPostion = $this->getFirstFighterKey();

        $fighterKeys = array_keys($this->fighters);
        $orderBefore = array_slice($fighterKeys, $splitPostion);
        $orderAfter  = array_slice($fighterKeys, 0, $splitPostion);
        $this->order = array_merge($orderBefore, $orderAfter);

        return $this;
    }

    /**
     * Determine who throws the first punch
     *
     * @return Integer
     */
    public function getFirstFighterKey()
    {
        $fighterKey = 0;
        $fighter = null;

        foreach ($this->fighters as $key => $newFighter) {
            // No fighter has been set yet, so use the first fighter
            if ($fighter === null) {
                $fighterKey = $key;
                $fighter = $newFighter;
            }

            // Where the fighter in the loop is faster than the current fighter
            // the loop fighter becomes the current fighter
            elseif ($newFighter->speed > $fighter->speed) {
                $fighterKey = $key;
                $fighter = $newFighter;
            }

            // Where the fighter in the loop is the same speed as the current
            // fighter, check if the loop fighter is has a better defense of
            // the current fighter
            elseif (
                $newFighter->speed === $fighter->speed &&
                $newFighter->defense < $fighter->defense
            ) {
                $fighterKey = $key;
                $fighter = $newFighter;
            }
        }

        return $fighterKey;
    }

    /**
     * [getFighterOpponentKey description]
     *
     * @param integer $fighterKey
     * @return integer
     */
    public function getFighterOpponentKey($fighterKey)
    {
        return $fighterKey + 1 >= $this->fighterCount() ? 0 : $fighterKey + 1;
    }

    /**
     * Returns total number of fighters on current fight
     *
     * @return integer
     */
    public function fighterCount()
    {
        return count($this->fighters);
    }

    /**
     * Generates the fight card between the fighters currently defined on the
     * fighters property
     *
     * @return string
     */
    public function getCard()
    {
        $fightCard = "";

        foreach ($this->fighters as $key => $fighter) {
            $fightCard .= ($key ? " vs. " : "") . $fighter->name;
        }

        return $fightCard;
    }
    
    /**
     * Processes a turn for the parsed fighter and updates the opponents stats
     *
     * @return array
     */
    public function fighterTurn($fighterKey)
    {
        $fighter = $this->getFighterByKey($fighterKey);
        $opponentKey = $this->getFighterOpponentKey($fighterKey);
        $opponent = $this->getFighterByKey($opponentKey);
        

        if ($fighter->stunned) {
            $result = null;
            $fighter->setStunned(false);
        } else {
            $result = $fighter->attack($opponent);
        }

        return (object) array(
            'fighter' => $fighter,
            'result'  => $result,
        );
    }

    /**
     * Returns the result of the fight
     *
     * @return object StdClass
     */
    public function getResult()
    {
        $winner = null;
        $loser  = null;
        $ko     = false;

        foreach ($this->fighters as $fighter) {
            if (! $winner) {
                $winner = $fighter;
            } elseif ($fighter->health > $winner->health) {
                $winner = $fighter;
            }

            if ($fighter->isKnockedOut()) {
                $ko = true;
                $loser = $fighter;
            }
        }

        return (object) array(
            'winner' => $winner,
            'loser'  => $loser,
            'ko'     => $ko,
        );
    }
}
