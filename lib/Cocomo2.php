<?php

namespace CodeMetrics;

class Cocomo2
{

    const ESTIMATE_EFFORT_APPLIED   = 'EFFORT_APPLIED';
    const ESTIMATE_DEVELOPMENT_TIME = 'DEVELOPMENT_TIME';
    const ESTIMATE_PEOPLE_REQUIRED  = 'PEOPLE_REQUIRED';

    const PROJECT_ORGANIC      = 'O';
    const PROJECT_SEMIDETACHED = 'S';
    const PROJECT_EMBEDDED     = 'E';
    /**
     * Available COCOMO Project classes
     *
     * @var array
     */
    private $availableProjectClasses = [
        // Organic projects - "small" teams with "good" experience working with "less than rigid" requirements
        self::PROJECT_ORGANIC      => ['a' => 3.2, 'b' => 1.05, 'c' => 2.5, 'd' => 0.38],
        // Semi-detached projects - "medium" teams with mixed experience working with a mix of rigid and less than rigid requirements
        self::PROJECT_SEMIDETACHED => ['a' => 3.0, 'b' => 1.12, 'c' => 2.5, 'd' => 0.35],
        // Embedded projects - developed within a set of "tight" constraints. It is also combination of organic and semi-detached projects.(hardware, software, operational, ...)
        self::PROJECT_EMBEDDED     => ['a' => 2.8, 'b' => 1.20, 'c' => 2.5, 'd' => 0.32]
    ];

    /**
     * Selected project class
     *
     * @var string
     */
    private $projectClass = self::PROJECT_ORGANIC;

    const MULTIPLIER_VERY_LOW     = 'VL';
    const MULTIPLIER_LOW          = 'L';
    const MULTIPLIER_NOMINAL      = 'N';
    const MULTIPLIER_HIGH         = 'H';
    const MULTIPLIER_VERY_HIGH    = 'VH';
    const MULTIPLIER_EXTRA_HIGH   = 'XH';

    /**
     * Available COCOMO multipliers
     *
     * @var array
     */
    private $availableMultipliers = [

        'rely' => array(
            self::MULTIPLIER_VERY_LOW => "0.75",
            self::MULTIPLIER_LOW => "0.88",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "1.15",
            self::MULTIPLIER_VERY_HIGH => "1.40",
            self::MULTIPLIER_EXTRA_HIGH => "1.40"
        ), // Required software reliability

        'data' => array(
            self::MULTIPLIER_VERY_LOW => "0.94",
            self::MULTIPLIER_LOW => "0.94",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "1.08",
            self::MULTIPLIER_VERY_HIGH => "1.16",
            self::MULTIPLIER_EXTRA_HIGH => "1.16"
        ), // Size of application database

        'cplx' => array(
            self::MULTIPLIER_VERY_LOW => "0.70",
            self::MULTIPLIER_LOW => "0.85",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "1.15",
            self::MULTIPLIER_VERY_HIGH => "1.30",
            self::MULTIPLIER_EXTRA_HIGH => "1.65"
        ), // Complexity of the product

        'time' => array(
            self::MULTIPLIER_VERY_LOW => "1.00",
            self::MULTIPLIER_LOW => "1.00",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "1.11",
            self::MULTIPLIER_VERY_HIGH => "1.30",
            self::MULTIPLIER_EXTRA_HIGH => "1.66"
        ), // Run-time performance constraints

        'stor' => array(
            self::MULTIPLIER_VERY_LOW => "1.00",
            self::MULTIPLIER_LOW => "1.00",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "1.06",
            self::MULTIPLIER_VERY_HIGH => "1.21",
            self::MULTIPLIER_EXTRA_HIGH => "1.56"
        ), // Memory constraints

        'virt' => array(
            self::MULTIPLIER_VERY_LOW => "0.87",
            self::MULTIPLIER_LOW => "0.87",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "1.15",
            self::MULTIPLIER_VERY_HIGH => "1.30",
            self::MULTIPLIER_EXTRA_HIGH => "1.30"
        ), // Volatility of the virtual machine environment
        'turn' => array(
            self::MULTIPLIER_VERY_LOW => "0.87",
            self::MULTIPLIER_LOW => "0.87",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "1.07",
            self::MULTIPLIER_VERY_HIGH => "1.15",
            self::MULTIPLIER_EXTRA_HIGH => "1.15"
        ), // Required turnabout time
        'acap' => array(
            self::MULTIPLIER_VERY_LOW => "1.46",
            self::MULTIPLIER_LOW => "1.19",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "0.86",
            self::MULTIPLIER_VERY_HIGH => "0.71",
            self::MULTIPLIER_EXTRA_HIGH => "0.71"
        ), // Analyst capability
        'aexp' => array(
            self::MULTIPLIER_VERY_LOW => "1.29",
            self::MULTIPLIER_LOW => "1.13",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "0.91",
            self::MULTIPLIER_VERY_HIGH => "0.82",
            self::MULTIPLIER_EXTRA_HIGH => "0.82"
        ), // Applications experience
        'pcap' => array(
            self::MULTIPLIER_VERY_LOW => "1.42",
            self::MULTIPLIER_LOW => "1.17",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "0.86",
            self::MULTIPLIER_VERY_HIGH => "0.70",
            self::MULTIPLIER_EXTRA_HIGH => "0.70"
        ), // Software engineer capability
        'vexp' => array(
            self::MULTIPLIER_VERY_LOW => "1.21",
            self::MULTIPLIER_LOW => "1.10",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "0.90",
            self::MULTIPLIER_VERY_HIGH => "0.90",
            self::MULTIPLIER_EXTRA_HIGH => "0.90"
        ), // Virtual machine experience
        'lexp' => array(
            self::MULTIPLIER_VERY_LOW => "1.14",
            self::MULTIPLIER_LOW => "1.07",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "0.95",
            self::MULTIPLIER_VERY_HIGH => "0.95",
            self::MULTIPLIER_EXTRA_HIGH => "0.95"
        ), // Programming language experience
        'modp' => array(
            self::MULTIPLIER_VERY_LOW => "1.24",
            self::MULTIPLIER_LOW => "1.10",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "0.91",
            self::MULTIPLIER_VERY_HIGH => "0.82",
            self::MULTIPLIER_EXTRA_HIGH => "0.82"
        ), // Application of software engineering methods
        'tool' => array(
            self::MULTIPLIER_VERY_LOW => "1.24",
            self::MULTIPLIER_LOW => "1.10",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "0.91",
            self::MULTIPLIER_VERY_HIGH => "0.83",
            self::MULTIPLIER_EXTRA_HIGH => "0.83"
        ), // Use of software tools
        'sced' => array(
            self::MULTIPLIER_VERY_LOW => "1.23",
            self::MULTIPLIER_LOW => "1.08",
            self::MULTIPLIER_NOMINAL => "1.00",
            self::MULTIPLIER_HIGH => "1.04",
            self::MULTIPLIER_VERY_HIGH => "1.1",
            self::MULTIPLIER_EXTRA_HIGH => "1.1"
        ) // Required development schedule
    ];

    /**
     * Selected project class
     *
     * @var array
     */
    private $multipliers = array();

    /**
     * Constructor
     *
     * You can setup project class using $projectClass providing values: O, S or E (organic, semi-detached aor embedded)
     *
     * You can adjust multipliers using $multipliers providing values from VL to XH for keys:
     *
     *   - 'rely' // Required software reliability
     *   - 'data' // Size of application database
     *   - 'cplx' // Complexity of the product
     *   - 'time' // Run-time performance constraints
     *   - 'stor' // Memory constraints
     *   - 'virt' // Volatility of the virtual machine environment
     *   - 'turn' // Required turnabout time
     *   - 'acap' // Analyst capability
     *   - 'aexp' // Applications experience
     *   - 'pcap' // Software engineer capability
     *   - 'vexp' // Virtual machine experience
     *   - 'lexp' // Programming language experience
     *   - 'modp' // Application of software engineering methods
     *   - 'tool' // Use of software tools
     *   - 'sced' // Required development schedule
     *
     * Ex.
     *  $projectClass = CodeMetrics\Cocomo2::PROJECT_EMBEDDED;
     *
     *  $multipliers = array(
     *      'rely' => CodeMetrics\Cocomo2::MULTIPLIER_HIGH
     *  );
     *
     *  $cocomo2 = new CodeMetrics\Cocomo2($projectClass, $multipliers);
     *
     *  $sloc = 30000;
     *  $estimation = $cocomo2->estimate($sloc);
     *  var_dump($estimation);
     *
     * @param string $projectClass
     * @param array $multipliers
     *
     * @throws \UnexpectedValueException
     *
     * @return void
     */
    public function __construct($projectClass = self::PROJECT_ORGANIC, array $multipliers = array())
    {
        if (!array_key_exists($projectClass, $this->availableProjectClasses)) {
            throw new \UnexpectedValueException('Invalid project class provided!');
        }
        $this->projectClass = $projectClass;

        foreach ($this->availableMultipliers as $name => $values) {

            $isUserProvideValue = isset($multipliers[$name]);
            $isValueValid       = array_key_exists($multipliers[$name], $this->availableMultipliers[$name]);

            if ($isUserProvideValue && $isValueValid) {
                $this->multipliers[$name] = $this->availableMultipliers[$name][$multipliers[$name]];
            }
            else {
                $this->multipliers[$name] = $values[self::MULTIPLIER_NOMINAL];
            }
        }
    }

    /**
     * Calculates estimation and returns estimation details in array
     *
     * Returns array:
     *
     * array(
     *     CodeMetrics\Cocomo2::ESTIMATE_EFFORT_APPLIED   => {value}
     *     CodeMetrics\Cocomo2::ESTIMATE_DEVELOPMENT_TIME => {value}
     *     CodeMetrics\Cocomo2::ESTIMATE_PEOPLE_REQUIRED  => {value}
     * );
     *
     * @param string $sloc number of source code lines
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function estimate($sloc)
    {
        if (!is_int($sloc)) {
            throw new \InvalidArgumentException('SLOC must be an integer!');
        }

        $eaf = 1;

        foreach ($this->multipliers as $m) {
            $eaf *= $m;
        }

        $projectClassConstants = $this->availableProjectClasses[$this->projectClass];

        $ai = $projectClassConstants['a'];
        $bi = $projectClassConstants['b'];
        $ci = $projectClassConstants['c'];
        $di = $projectClassConstants['d'];

        $effortApplied   = $ai * pow($sloc / 1000, $bi)*$eaf;
        $developmentTime = $ci * pow($effortApplied, $di);
        $peopleRequired  = $effortApplied / $developmentTime;

        return array(
            self::ESTIMATE_EFFORT_APPLIED   => $effortApplied,
            self::ESTIMATE_DEVELOPMENT_TIME => $developmentTime,
            self::ESTIMATE_PEOPLE_REQUIRED  => $peopleRequired
        );
    }
}