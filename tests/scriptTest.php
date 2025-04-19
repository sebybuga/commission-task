<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Process\Process;
use PHPUnit\Framework\TestCase;

class ScriptTest extends TestCase
{

    function testCommissionCalculation()
    {
        $expectedOutput = [
            0.60,
            3.00,
            0.00,
            0.06,
            1.50,
            0,
            0.70,
            0.30,
            0.30,
            3.00,
            0.00,
            0.00,
            8612
        ];

        // Run the CLI script with the provided input.csv file
        $process = new Process(['php', 'script.php', __DIR__ . '/../input.csv', '--use-exchange-test']);
        $process->run();

        // Get output and clean it
        $output = trim($process->getOutput());
        $outputLines = explode("\n", $output);

        // Remove any extra messages (e.g., validation errors or debug lines)
        $cleaned = array_filter($outputLines, function ($line) {
            return is_numeric(trim($line));
        });

        // Check if the result matches
        if ($cleaned == $expectedOutput) {
            echo "ScriptTest passed.\n";
        } else {
            echo "ScriptTest failed.\nExpected:\n";
            print_r($expectedOutput);
            echo "Got:\n";
            print_r($cleaned);
        }
        $this->assertEquals($cleaned, $expectedOutput);
    }
}
