<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\Table;
use AppBundle\Fight\Fight;
use AppBundle\Fight\Fighter;

class DojCommand extends ContainerAwareCommand
{
    private $input;
    private $output;
    private $fight;

    protected function configure()
    {
        $this->setName('doj')
             ->setDescription('Dawn of Justice');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        // Start a new fight
        $this->fight = new Fight(2, 30, 3);

        $this->showHeader();
        $this->setFighters();
        $this->showFightCard();
        $this->letBattleCommence();
        $this->showFightResult();
    }

    /**
     * Sleeps the script by seconds but more precisely than using sleep()
     *
     * @param integer $secs
     * @return void
     */
    private function sleep($secs)
    {
        usleep($secs * 1000000 * $this->fight->speed);
    }

    /**
     * Sets the fighters for the fight
     *
     * @return string
     */
    private function setFighters()
    {
        for ($i=0; $i < $this->fight->maxFighters; $i++) {
            // $n just helps to make feedback read a bit nicer, there's no
            // functional purpose other than user feedback
            $n = $i+1;

            // Ask the question
            $helper = $this->getHelper('question');

            $question = new ChoiceQuestion(
                "<info>Choose fighter {$n}:</info>",
                Fighter::list(),
                0
            );

            $question->setErrorMessage('<error>Fighter %s is invalid. Please choose again</error>');

            $this->fight->setFighter($helper->ask($this->input, $this->output, $question));
        }

        $this->fight->setFighterOrder();
    }

    /**
     * Just renders a nice header for the application
     *
     * @return void
     */
    private function showHeader()
    {
        $this->output->writeln(array(
            "<comment>===============================================================================</comment>",
            "<comment>    ____                                __       _           _   _            </comment>",
            "<comment>   |  _ \\  __ ___      ___ __     ___  / _|     | |_   _ ___| |_(_) ___ ___   </comment>",
            "<comment>   | | | |/ _` \\ \\ /\\ / / '_ \\   / _ \\| |_   _  | | | | / __| __| |/ __/ _ \\  </comment>",
            "<comment>   | |_| | (_| |\\ V  V /| | | | | (_) |  _| | |_| | |_| \\__ \\ |_| | (_|  __/  </comment>",
            "<comment>   |____/ \\__,_| \\_/\\_/ |_| |_|  \\___/|_|    \\___/ \\__,_|___/\\__|_|\\___\\___|  </comment>",
            "",
            // "<comment>D A W N - O F - J U S T I C E</comment>",
            "<comment>===============================================================================</comment>",
        ));
    }

    /**
     * Displays fight card
     *
     * @return void
     */
    private function showFightCard()
    {
        $fightCard = $this->fight->getCard();

        $this->output->writeln(array(
            "<comment>===============================================================================</comment>",
            "<comment>F I G H T - N I G H T</comment>",
            "<comment>-------------------------------------------------------------------------------</comment>",
        ));

        $this->sleep(1);

        $this->output->writeln(array(
            "<info>{$fightCard}</info>",
        ));

        $this->sleep(1);

        // Display the selected fighters stats in a nice table
        $this->showFightStats();

        $this->sleep(1);

        $this->output->writeln(array(
            "",
            "<info>LET THEM FIGHT!</info>",
            "<comment>===============================================================================</comment>",
        ));

        $this->sleep(1);
    }

    /**
     * Displays the current stats for the selected fighters
     *
     * @return void
     */
    private function showFightStats()
    {
        $this->output->writeln("");

        $fighters = array();
        $table = new Table($this->output);

        foreach ($this->fight->fighters as $key => $fighter) {
            $fighters[] = array(
                $key+1,
                $fighter->name,
                $fighter->type->name,
                $fighter->health,
                $fighter->strength,
                $fighter->defense,
                $fighter->speed,
                $fighter->luck,
                $fighter->type->getSpecialSkill(),
            );
        }

        $table->setHeaders(array('No.', 'Name', 'Type', 'Health', 'Strength', 'Defense', 'Speed', 'Luck', 'Special',))
              ->setRows($fighters);

        $table->render();
    }

    /**
     * Displays a message to show who threw the first punch
     *
     * @return void
     */
    private function showFirstFighter()
    {
        $firstFighter = $this->fight->getFighterByKey($this->fight->getFirstFighterKey());
        $this->output->writeln("<info>- {$firstFighter->name} threw the first punch!</info>");
    }

    /**
     * Starts the battle between the defined fighters.
     *
     * @return void
     */
    private function letBattleCommence()
    {
        for ($round=1; $round <= $this->fight->rounds; $round++) {
            $this->output->writeln(array(
                "<comment>R O U N D - {$round}</comment>",
                "<comment>-------------------------------------------------------------------------------</comment>",
            ));

            $this->sleep(1);

            // Declare who hit first and threw the first punch
            if ($round === 1) {
                $this->showFirstFighter();
                $this->sleep(1);
            }

            foreach ($this->fight->order as $fighterKey) {
                $turn = $this->fight->fighterTurn($fighterKey);

                if ($turn->result === null) {
                    $this->output->writeln("<info>- {$turn->fighter->name} didn't attack because they were stunned</info>");
                    continue;
                }

                // Handle general messaging
                if ($turn->result->opponent->isKnockedOut()) {
                    $result = ", knocking {$turn->result->opponent->name} out!";
                } elseif ($turn->result->damage) {
                    $result = ", wounding {$turn->result->opponent->name} and inflicting {$turn->result->damage} damage points.";
                } else {
                    $result = " but {$turn->result->opponent->name} managed to dodge the attack and it missed.";
                }

                $this->output->writeln("<info>- {$turn->fighter->name} {$turn->result->type} {$turn->result->opponent->name}{$result}</info>");

                // Handle special skill feedback messaging
                if ($turn->result->special !== null) {
                    switch ($turn->result->special) {
                        case 'Lucky Strike':
                            $this->output->writeln("<info>- {$turn->fighter->name} got in a lucky strike</info>");
                            break;
                        case 'Stunning Blow':
                            $this->output->writeln("<info>- {$turn->fighter->name}'s attack has taken {$turn->result->opponent->name} by suprise and stunned the opponent</info>");
                            break;
                        case 'Counter Attack':
                            $this->output->writeln("<info>- {$turn->result->opponent->name} countered {$turn->fighter->name}'s attack</info>");
                            break;
                    }
                }
                
                if ($turn->result->opponent->isKnockedOut()) {
                    break;
                }

                $this->sleep(1);
            }

            $this->showFightStats();

            $this->output->writeln("<comment>===============================================================================</comment>");

            if ($turn->result->opponent->isKnockedOut()) {
                break;
            }

            $this->sleep(2);
        }
    }

    /**
     * Displays the result of the fight
     *
     * @return void
     */
    private function showFightResult()
    {
        $this->output->writeln(array(
            "<comment>F I G H T - R E S U L T</comment>",
            "<comment>-------------------------------------------------------------------------------</comment>",
        ));

        $fightResult = $this->fight->getResult();

        if ($fightResult->ko) {
            $this->output->writeln("<info>The winner, by way of knock out is:</info> {$fightResult->winner->name}");
        } else {
            $this->output->writeln("<info>The fight ended as a draw</info>");
        }

        $this->output->writeln("<comment>===============================================================================</comment>");
    }
}
