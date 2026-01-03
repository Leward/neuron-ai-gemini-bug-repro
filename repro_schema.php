<?php

require 'vendor/autoload.php';

use NeuronAI\Agent;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\StructuredOutput\SchemaProperty;
use NeuronAI\Providers\AIProviderInterface;
use Dotenv\Dotenv;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$output = new ConsoleOutput();
$output->getFormatter()->setStyle('success', new OutputFormatterStyle('green', null, ['bold']));
$output->getFormatter()->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
$output->getFormatter()->setStyle('info', new OutputFormatterStyle('cyan'));
$output->getFormatter()->setStyle('model', new OutputFormatterStyle('yellow', null, ['bold']));

const MODELS = [
    'gemini-3-flash-preview',
    'gemini-2.5-flash',
    'gemini-2.5-flash-lite'
];

class SimpleResponse {
    public function __construct(
        #[SchemaProperty(description: 'A nullable date.', required: false)]
        public ?string $date = null
    ) {}
}

class BuggyAgent extends Agent {
    private string $modelName;

    public function __construct(string $modelName) {
        $this->modelName = $modelName;
    }

    public function instructions(): string {
        return "You extract dates.";
    }

    protected function provider(): AIProviderInterface {
        return new Gemini(
            key: $_ENV['GEMINI_KEY'],
            model: $this->modelName
        );
    }

    protected function getOutputClass(): string {
        return SimpleResponse::class;
    }
}

$output->writeln("\n<info>Testing structured output with nullable date field</info>\n");

$successfulModel = null;
$lastError = null;

foreach (MODELS as $model) {
    $output->writeln("<model>Trying model: $model</model>");
    $agent = new BuggyAgent($model);
    
    try {
        $response = $agent->structured(new UserMessage("Today is 2024-01-01"));
        $output->writeln("  <success>✓ Success</success>");
        $output->writeln("  Date: " . ($response->date ?? '<comment>null</comment>'));
        $successfulModel = $model;
    } catch (\Exception $e) {
        $output->writeln("  <error>✗ Failed: " . $e->getMessage() . "</error>");
        $lastError = $e;
    }
    $output->writeln("");
}

if ($successfulModel) {
    $output->writeln("\n<success>Successful with model: $successfulModel</success>");
} else {
    $output->writeln("\n<error>All models failed. Last error: " . $lastError->getMessage() . "</error>");
    exit(1);
}
