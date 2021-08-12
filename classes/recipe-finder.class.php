<?php
/*
    Recipe Finder Class
    Given a list of items in the fridge (presented as a csv list), and a collection of
    recipes (a collection of JSON formatted recipes), produce a recommendation
    for what to cook tonight.
    
    @author Sachin Gupta
    @version 1.0
*/
class RecipeFinder {
    
    //List of ingredients in the fridge
    private $fridgeList = array();
    
    //List of recipes
    private $recipes = array();
    
    //Holds any error message
    private $errorStr = '';
    
    public function __construct() {

    }
    
    /*
        Finds the best matching recipe you have the ingredients for
        
        @param $fridgeListCsvFile (string)
        @parm $recipesJsonFile (string)
        @return mixed
    */
    public function findRecipe($fridgeListCsvFile, $recipesJsonFile) {
        if(!$this->_parseFridgeList($fridgeListCsvFile)) {
            return FALSE;
        }
        if(!$this->_parseRecipes($recipesJsonFile)) {
            return FALSE;
        }
        
        //For each recipe check if we have all the ingredients in the fridge
        $matchingRecipeIndexes = array();
        foreach($this->recipes as $recipeIndex=>$recipe) {
            $ingredientsInThisRecipeMatched = 0;
            
            //Keep track of the nearest use-by date in case we need it again
            $closestUseByDate = FALSE;

            foreach($recipe->ingredients as $ingredient) {
                if(isset($this->fridgeList[strtolower($ingredient->item)])) {
                    $fridgeItem = $this->fridgeList[strtolower($ingredient->item)];
                    
                    //Check we have the same units and enough in the fridge
                    if($ingredient->unit == $fridgeItem['unit'] AND $ingredient->amount <= $fridgeItem['amount']) {
                        $ingredientsInThisRecipeMatched++;
                        
                        if($closestUseByDate === FALSE OR $fridgeItem['use-by'] < $closestUseByDate) {
                            $closestUseByDate = $fridgeItem['use-by'];
                        }
                    }
                }
            }
            
            //Do we have all the ingredits for the recipe?
            if($ingredientsInThisRecipeMatched == count($recipe->ingredients)) {
                $matchingRecipeIndexes[] = array('recipe-index' => $recipeIndex, 'closest-use-by' => $closestUseByDate);
            }
        }
        
        
        //If no recipe is found, the program should return “Order Takeout”
        if(count($matchingRecipeIndexes) == 0) {
            return 'Order Takeout';
        }
        else {
            //Sort recipes by closest use-by date if more than one
            usort($matchingRecipeIndexes, 'self::_sortByUseByDate');
            return ucwords($this->recipes[$matchingRecipeIndexes[0]['recipe-index']]->name);
        }
    }
    
    /*
        Returns any error message
    
        @return string
    */
    public function getError() {
        return $this->errorStr;
    }
    
    //Custom sort method to sort by nearest use by date
    private function _sortByUseByDate($a, $b) {
        if($a['closest-use-by'] == $b['closest-use-by']) {
            return 0;
        }
        
        return ($a['closest-use-by'] < $b['closest-use-by']) ? -1 : 1;
    }
    
    /*
        Reads and parses the fridge ingredients list
        
        @param $fridgeListCsvFile string
        @return boolean
    */ 
    private function _parseFridgeList($fridgeListCsvFile) {
        //Fix any mac issues with line endings
        ini_set("auto_detect_line_endings", true);
        
        $fridgeListHandle = @fopen($fridgeListCsvFile, 'r');
        if($fridgeListHandle === FALSE) {
            $this->errorStr = 'Unable to open fridge-list';
            return FALSE;
        }
        
        $currentTimestamp = time();
        while(($row = fgetcsv($fridgeListHandle, 1000, ',')) !== FALSE) {
            //Check its a valid item
            if(count($row) != 4) {
                continue;
            }
            
            //Replace / by . in the date so its not presumed to be in American format by php
            $useBy = strtotime(str_replace('/', '.', $row[3]));
            
            //Check its still in date - ignore out of date items
            if($useBy > $currentTimestamp)  {
                //Index by item so easier to find matches
                $this->fridgeList[strtolower($row[0])] = array(
                    'amount' => $row[1],
                    'unit' => $row[2],
                    'use-by' => $useBy,
                );
            }
        }
        fclose($fridgeListHandle);
        
        //Do we have something in the fridge to work with?
        if(count($this->fridgeList) < 1) {
            $this->errorStr = 'fridge-list is empty';
            return FALSE;
        }
        
        return TRUE;
    }
    
    /*
        Reads and parses the recipe json file
        
        @param $recipesJsonFile string
        @return boolean
    */ 
    private function _parseRecipes($recipesJsonFile) {
        $recipeListStr = @file_get_contents($recipesJsonFile);
        if($recipeListStr === FALSE) {
            $this->errorStr = "Unable to open recipe-list";
            return FALSE;
        }
        
        $this->recipes = json_decode($recipeListStr);
        
        if(!is_array($this->recipes) OR empty($this->recipes)) {
            $this->errorStr = "Unable to parse recipe-list or its empty";
            return FALSE;
        }
        
        return TRUE;
    }
    
}
?>