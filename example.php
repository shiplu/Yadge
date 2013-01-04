<?php
require 'datagenerator.php';

use \RandomDataGenerator\*;


$dgen = new DataGenerator();
$dgen->addField('username' , new LowerCaseAlphabetField(6, 12));
$dgen->addField('age' , new IntegerField(20, 31));
$dgen->addField('sex' , new SetField(array('Male', 'Female', 'Transexual')));
$dgen->addField('weight' , new DoubleField(50, 80, 1));
$dgen->addField('source', new SetField(array('Facebook', 'Google+', 'Twitter')));

$data = $dgen->generate(2);

print_r($data);


