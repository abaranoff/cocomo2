
This is COCOMO II software cost estimation script.

<?php

require_once(__DIR__.'/lib/Cocomo2.php');
use CodeMetrics\Cocomo2;

$getOptions = array(
    'sloc:',   // Source lines of code (required)
    'class::', // Project class
    'rely::',  // Required software reliability
    'data::',  // Size of application database
    'cplx::',  // Complexity of the product
    'time::',  // Run-time performance constraints
    'stor::',  // Memory constraints
    'virt::',  // Volatility of the virtual machine environment
    'turn::',  // Required turnabout time
    'acap::',  // Analyst capability
    'aexp::',  // Applications experience
    'pcap::',  // Software engineer capability
    'vexp::',  // Virtual machine experience
    'lexp::',  // Programming language experience
    'modp::',  // Application of software engineering methods
    'tool::',  // Use of software tools
    'sced::'   // Required development schedule
);

$options = getopt("", $getOptions);

$isValidParams = isset($options['sloc']);

if (!$isValidParams) {
    ?>
Error: SLOC is required!
    <?php
    exit;
}

$projectClass = $options['class'];

try {
    $cocomo2 = new Cocomo2($projectClass, $options);

    $sloc = (int) $options['sloc'];

    $estimation = $cocomo2->estimate($sloc);
    foreach ($estimation as $k => $e) {
        echo $k." = ".$e."\n";
    }
}
catch(\Exception $e) {
    echo $e->getMessage();
}

