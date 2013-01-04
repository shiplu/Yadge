<?php
require 'datagenerator.php';

use \RandomDataGenerator as RDG;


$dgen = new RDG\DataGenerator();
$dgen->addField('username' , new RDG\LowerCaseAlphabetField(6, 12));
$dgen->addField('age' , new RDG\IntegerField(20, 31));
$dgen->addField('sex' , new RDG\SetField(array('Male', 'Female', 'Transexual')));
$dgen->addField('weight' , new RDG\DoubleField(50, 80, 1));
$dgen->addField('source', new RDG\SetField(array('Facebook', 'Google+', 'Twitter')));

$data = $dgen->generate(2);

print_r($data);


