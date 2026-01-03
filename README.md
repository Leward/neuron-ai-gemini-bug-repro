# Gemini 3 Protocol Bug Reproduction

> **Note:** Most of this repository is AI-generated to help reproduce a bug.

This project reproduces a protocol incompatibility between the `neuron-ai` library and Google's `gemini-3-flash-preview` experimental model.

## The Bug

The error occurs because the library uses a payload format that newer Gemini models reject on the `v1beta` endpoint.

1. **Snake vs Camel Case**: The library sends `generation_config` instead of the strictly required `generationConfig`.
2. **Schema Type Arrays**: The library sends `"type": ["string", "null"]` for nullable fields, while the API strictly requires a single string type (e.g., `"type": "string"`) plus a `"nullable": true` flag.
3. **Thought Signatures**: Gemini 3 requires returning the `thought_signature` in multi-turn tool conversations, which the library currently omits.

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

## Solution / Mitigation
The library has been updated locally (via the `neuron-ai` link) to support the stricter requirements of newer models. All models listed in `run_all.php` should now pass successfully with both structured output and multi-turn tool use.
