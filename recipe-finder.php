<?php
/*
    Recipe Finder run script
    
    Usage: recipe-finder.php fridge-list recipe-list
*/
require_once(dirname(__FILE__).'/classes/recipe-finder.class.php');

//Check we have the correct inputs
if(count($argv) != 3) {
    echo "Incorrect arguments. Correct usage: recipe-finder.php fridge-list.csv recipes.json";
    exit;
}

//Input directory path
$testInputDir = dirname(__FILE__) . '/test-input/sample/';
$arg1 = $testInputDir.''.$argv[1];
$arg2 = $testInputDir.''.$argv[2];

$recipeFinder = new RecipeFinder();
$recipe = $recipeFinder->findRecipe($arg1, $arg2);

if($recipe !== FALSE) {
    echo $recipe;
}
else {
    echo $recipeFinder->getError();
}

?>