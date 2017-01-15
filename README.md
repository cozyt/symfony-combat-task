# Dawn of Justice

Pit 2 fighters against each other in the greatest galdiator match up in the history of the world. 

## Dependencies

- Composer
- Symfony 3

## How to play

- Run `php bin/console doj` from the root of the project in your terminal
- Select your fighters from the list
- Watch the fight unfold

## Considerations

Unit tests are required for the application 
Should adhere to PSR-2 standards

### Introducing new fighters
It should be very easy to introduce a new battler by adding to the `AppBundle\Fight\Fighter::$items` static property, some examples are already listed.

### Introducing new battler types
The same can be said about adding new battler types by adding to the `AppBundle\Fight\FighterType::$items` static property and duplicating the values found for other types.

### Assigning multiple types to a fighter
This would require a bit of refactoring, though I believe most of this would be in the `AppBundle\Fight\Fighter` class when setting a type and defining stats in the constructor.

### Decoupled logic
Most of the logic that handles the fight, fighters and fighter types are handled in separate classes found under the `AppBundle\Fight` namespace. 
The command is largely used to request input from the user and provide feedback in the terminal. 
I believe that it would be fairly straightforward to build a graphical UI with routes at a later date, with mainly the feedback elements in the command needing refactoring. A large portion of the logic could remain untouched.
