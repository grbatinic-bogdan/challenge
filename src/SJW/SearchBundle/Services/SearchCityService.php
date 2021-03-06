<?php

namespace SJW\SearchBundle\Services;

/**
 * Description of SearchCityService
 *
 * @author Bogdan
 */
class SearchCityService {
    
    private $filepath;
    private $contentArray = array();
    private $resultLimit = 10;
    
    /**     
     * @param mixed $filepath
     */
    public function __construct($filepath) {
        $this->filepath = $filepath;
        $this->getTextContentAsArray();
    }
    
    public function getTextContentAsArray(){
        
        if(file_exists($this->filepath) == false){
            throw new \InvalidArgumentException('File does not exist');
        }
        
        $lines = explode("\n", file_get_contents($this->filepath, true));
        
        foreach($lines as $line) {
            $result = explode(';', $line);
            if(count($result) == 3){
                $result[2] = (int) $result[2];
                array_push($this->contentArray, $result);                
            }
        }        
    }
    
    /**
     * sort data by population
     * @param type $desc
     */
    private function sortData($desc = false){
        usort($this->contentArray, array($this, 'sortContentArray'));        
        if($desc) $this->contentArray = array_reverse ($this->contentArray);
    }
    
    public function sortArray(array $array, $desc = false){
        usort($array, array($this, 'sortContentArray'));
        if($desc){
            $array = array_reverse($array);
        }
        
        return $array;
    }
    
    private function sortContentArray($a, $b){
        return $a[2] - $b[2];
    }
    
    public function setResultLimit($limit){
        $this->resultLimit = $limit;
    }
    
    /**
     * 
     * @param array $cityArray
     * @return array
     */
    public function getCitiesWithNearestPopulation(array $cityArray){
        
        $population = (int) $cityArray[2];        
        $cities = array();
        
        if($population > 0){
            
            $this->sortData();
            $resultCounter = 0;
            
            // get cities with higher population
            foreach($this->contentArray as $row){          
                if($row == $cityArray) continue;
                
                $cityPopulation = (int) $row[2];                
                #if(ceil($this->resultLimit / 2) == $resultCounter) break;
                if($this->resultLimit == $resultCounter) break;
                
                if($cityPopulation >= $population){
                    $cities[] = $row;
                    $resultCounter++;
                }
            }
            
            $this->sortData(true);
            
            // get cities with lower population
            $resultCounter = 0;
            foreach($this->contentArray as $row){
                if($row == $cityArray) continue;
                
                if($this->resultLimit == $resultCounter) break;
                $cityPopulation = (int) $row[2];
                
                if($cityPopulation <= $population){                    
                    $cities[] = $row;
                    $resultCounter++;
                }                
            }
            
            $cities = $this->sortArray($cities);            
            $cities = array_slice($cities, $this->resultLimit + 1);            
            array_unshift($cities, $cityArray);            
        }
        
        return $cities;
    }
    
    /**
     * 
     * @param type $searchString
     * @return array
     */
    public function searchCity($searchString){
        
        $searchResult = array();
        foreach($this->contentArray as $row){
            if(strstr($row[0], $searchString) || strstr($row[1], ucfirst($searchString))){                
                $searchResult[] = array(
                    $row[0]
                    ,$row[1]
                    ,$row[2]
                );
            }
        }
        return $searchResult;
        
    }
    
}
