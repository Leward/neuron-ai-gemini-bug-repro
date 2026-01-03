# Gemini 3 Protocol Bug Reproduction

> **Note:** Most of this repository is AI-generated to help reproduce a bug.

This project reproduces a protocol incompatibility between the `neuron-ai` library and Google's `gemini-3-flash-preview` experimental model.

## The Bug (Fixed)

This project originally reproduced protocol incompatibilities between the `neuron-ai` library and Google's newer Gemini models on the `v1beta` endpoint.

### Issues That Were Fixed

1. **Schema Type Arrays**: The library was sending `"type": ["string", "null"]` for nullable fields, while the API strictly requires a single string type (e.g., `"type": "string"`) plus a `"nullable": true` flag. This has been fixed to properly support nullable fields.

2. **Tool Calling + Structured Output**: Newer Gemini models now support using tool calling and structured output at the same time. The library has been updated to handle this capability.

## Setup

1. Install dependencies:
   ```bash
   composer install
   ```

2. Configure environment:
   ```bash
   cp .env.example .env
   # Ensure your GEMINI_KEY is set in .env
   ```

## Running the Reproductions

All scripts automatically try multiple models in succession:
1. `gemini-3-flash-preview`
2. `gemini-2.5-flash`
3. `gemini-2.5-flash-lite`

The scripts will stop at the first successful model and display colored output for better readability.

### 1. Schema / Naming Bug
This script attempts to extract a date into a class with a nullable property.
```bash
php repro_schema.php
```

### 2. Tool / Thought Signature (Multi-Turn) Bug
This script simulates an agent with a tool.
```bash
php repro_thought.php
```

### 3. Comprehensive Testing
Run both tests in sequence:
```bash
php run_all.php
```


