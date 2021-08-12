# recipe-finder
Given a list of items in the fridge (presented as a csv list), and a collection of
recipes (a collection of JSON formatted recipes), produce a recommendation
for what to cook tonight.

##Usage
Run as a CLI script
````
    php recipe-finder.php fridge-list.csv recipes.json
````

##Requirements
PHP 5 or newer

##Unit Testing
Full unit tests provided in /tests requires PHPUnit to be installed via composer.
./vendor/bin/phpunit tests/RecipeFinderTest.php