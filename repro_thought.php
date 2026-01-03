<?php

require 'vendor/autoload.php';

use NeuronAI\Agent;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
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
$output->getFormatter()->setStyle('tool', new OutputFormatterStyle('magenta'));

const MODELS = [
    'gemini-3-flash-preview',
    'gemini-2.5-flash',
    'gemini-2.5-flash-lite'
];

class TestResponse {
    public function __construct(public string $answer) {}
}

class SearchTool extends Tool {
    public function __construct() {
        parent::__construct(
            name: 'search_database',
            description: 'Search for information in the database'
        );
    }

    protected function properties(): array {
        return [
            new ToolProperty(
                name: 'query',
                type: PropertyType::STRING,
                description: 'The search query',
                required: true
            )
        ];
    }

    public function __invoke(string $query): string {
        global $output;
        $output->writeln("      <tool>[Tool Called: search_database(query: '$query')]</tool>");
        return "No results found for '$query' in the database.";
    }
}

class MultiTurnAgent extends Agent {
    private string $modelName;

    public function __construct(string $modelName) {
        $this->modelName = $modelName;
    }

    public function instructions(): string {
        return "You use tools to answer questions. You MUST start by searching the database.";
    }

    protected function tools(): array {
        return [new SearchTool()];
    }

    protected function provider(): AIProviderInterface {
        return new Gemini(
            key: $_ENV['GEMINI_KEY'],
            model: $this->modelName
        );
    }

    protected function getOutputClass(): string {
        return TestResponse::class;
    }
}

$output->writeln("\n<info>Testing multi-turn tool use</info>\n");

$successfulModel = null;
$lastError = null;

foreach (MODELS as $model) {
    $output->writeln("<model>Trying model: $model</model>");
    $agent = new MultiTurnAgent($model);
    
    try {
        $response = $agent->structured(new UserMessage("Search the database for 'Project X' and give me the answer."));
        $output->writeln("  <success>✓ Success</success>");
        $output->writeln("  Answer: " . $response->answer);
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
