<?php
namespace CoreBundle\Command;

/**
 * Description of AbstractImplementation
 *
 * @author sebastian
 */
abstract class AbstractImplementation  {
    
    protected $manager;
    
    protected $input;
    
    protected $output;
        
    public function __construct($manager, $input, $output) {
        $this->manager = $manager;
        $this->input = $input;
        $this->output = $output;
    }
    
    public function load($line, $lineNo){
         
    }
    
    public function getFileLocation(){}
}
