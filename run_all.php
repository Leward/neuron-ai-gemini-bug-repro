<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$output = new ConsoleOutput();
$output->getFormatter()->setStyle('header', new OutputFormatterStyle('white', 'blue', ['bold']));
$output->getFormatter()->setStyle('section', new OutputFormatterStyle('cyan', null, ['bold']));

$output->writeln("");
$output->writeln("<header>                                          </header>");
$output->writeln("<header>  RUNNING GEMINI MODEL FALLBACK TESTS     </header>");
$output->writeln("<header>                                          </header>");
$output->writeln("");

$output->writeln("<section>1. Testing Schema/Protocol Compatibility:</section>");
passthru("php repro_schema.php", $schemaResult);

$output->writeln("\n" . str_repeat("=", 50) . "\n");

$output->writeln("<section>2. Testing Multi-turn Tool Context:</section>");
passthru("php repro_thought.php", $thoughtResult);

$output->writeln("");
$output->writeln("<header>                                          </header>");
$output->writeln("<header>  TESTS COMPLETE                          </header>");
$output->writeln("<header>                                          </header>");
$output->writeln("");

if ($schemaResult !== 0 || $thoughtResult !== 0) {
    exit(1);
}
