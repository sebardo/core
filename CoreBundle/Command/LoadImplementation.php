<?php
namespace CoreBundle\Command;

/**
 * Description of LoadImplementation
 *
 * @author sebastian
 */
interface LoadImplementation {
    
    public function getFileLocation();

    public function load($line, $lineNo);


}