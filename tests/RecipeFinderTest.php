<?php
/*
    PHPUnit test cases for RecipeFinder class
*/

class RecipeFinderTest extends \PHPUnit\Framework\TestCase {
    
    private $recipeFinder;
    private $testInputDir;

    protected function setUp(): void {
        //Initialize the test case
        //Called for every defined test
        require_once(dirname(__FILE__).'/../classes/recipe-finder.class.php');
        $this->recipeFinder = new RecipeFinder();
        
        $this->testInputDir = dirname(__FILE__) . '/../test-input/';
    }
    
    public function testMissingInput() {
        //Both missing
        $result = $this->recipeFinder->findRecipe('whatever.csv', 'something.json');
        $this->assertEquals(FALSE, $result);
        
        //Ingredients missing
        $result = $this->recipeFinder->findRecipe('whatever.csv', $this->testInputDir.'sample/recipes.json');
        $this->assertEquals(FALSE, $result);
        
        //Recipe list missing
        $result = $this->recipeFinder->findRecipe($this->testInputDir.'sample/fridge-list.csv', 'something.json');
        $this->assertEquals(FALSE, $result);
    }
    
    public function testEmptyInput() {
        //Both empty
        $result = $this->recipeFinder->findRecipe($this->testInputDir.'empty/fridge-list.csv', $this->testInputDir.'empty/recipes.json');
        $this->assertEquals(FALSE, $result);
        
        //Ingredients empty
        $result = $this->recipeFinder->findRecipe($this->testInputDir.'empty/fridge-list.csv', $this->testInputDir.'sample/recipes.json');
        $this->assertEquals(FALSE, $result);
        
        //Recipe list empty
        $result = $this->recipeFinder->findRecipe($this->testInputDir.'sample/fridge-list.csv', $this->testInputDir.'empty/recipes.json');
        $this->assertEquals(FALSE, $result);
    }
    
    public function testNoMatchingIngredients() {
        //Fridge list has no matching ingredients
        $result = $this->recipeFinder->findRecipe($this->testInputDir.'sample/fridge-list.csv', $this->testInputDir.'sample/recipes.json');
        $this->assertEquals('Order Takeout', $result, 'no-matching-ingredients');
    }
    
    public function testSortByUseByDate() {
        //Peanut Butter On Toast has closest sell by date
        $result = $this->recipeFinder->findRecipe($this->testInputDir.'sample/fridge-list.csv', $this->testInputDir.'sample/recipes.json');
        $this->assertEquals('Grilled Cheese On Toast', $result, 'use-by-date');
    }
    
    public function testExpiredIngredient() {
        //Mixed salad has expired, so instead of salad sandwich we should get cheese on toast
        $result = $this->recipeFinder->findRecipe($this->testInputDir.'sample/fridge-list.csv', $this->testInputDir.'sample/recipes.json');
        $this->assertEquals('Salad Sandwich', $result, 'expired-ingredient');
    }

    // Clean up the test case, called for every defined test
    public function tearDown(): void { }

    // Clean up the whole test class
    public static function tearDownAfterClass(): void { }
}
?>