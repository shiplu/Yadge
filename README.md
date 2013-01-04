Yadge
=====

Yet another data generator.


Usage
=====

    require 'datagenerator.php';
    $dgen = new \RandomDataGenerator\DataGenerator();
    $dgen->addField('username', new \RandomDataGenerator\AlphabetField(6, 12));
    $dgen->addField('age', new \RandomDataGenerator\IntegerField(10, 30));
    $dgen->addField('weight', new \RandomDataGenerator\DoubleField(50, 80, 2));
    $dgen->addField('sex', new \RandomDataGenerator\SetField(array('Male', 'Female')));

    print_r($dgen->generate());
