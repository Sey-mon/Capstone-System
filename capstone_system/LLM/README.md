# AI Nutrition LLM Module

This folder contains the Python/FastAPI AI services used by the Capstone System for:

1. Personalized 7-day child meal plans (parent flow)
2. Feeding Program meal plans (nutritionist flow)

The stack uses Groq LLM + LangChain + semantic retrieval from your knowledge base.

## What This Module Handles

### Personalized Meal Plan (`nutrition_chain.py`)
- Generates an individual meal plan for one child.
- Uses patient profile, assessment context, and available foods.
- Designed for parent-facing plan generation via `POST /generate_meal_plan`.

### Feeding Program Meal Plan (`feeding_program_chain.py`)
- Generates a batch/community meal plan (1 to 5 days).
- Uses age group, budget level, barangay, child count, and optional available ingredients.
- Designed for nutritionist-facing generation via `POST /feeding_program/meal_plan`.

## Current Feeding Program Production Guards

The feeding program generator now includes strict controls:

1. Canonical ingredient normalization (done in Laravel before LLM call)
2. Input sanitization in UI and API (invalid or gibberish ingredients are blocked)
3. Structured JSON generation mode
4. Smart retry with validation feedback injection
5. Duplicate/prohibited dish checks
6. Strict ingredient-mode post-generation gate (hard enforcement)
7. Fail-closed behavior: invalid output is rejected, not returned as success
8. Rate limiting + circuit breaker support in Laravel layer

## Seeds (File-Based)

Seed behavior for feeding program is local folder based:

- Path: `LLM/seeds/feeding_program_seeds.jsonl`
- Auto-create: yes (folder/file created on first successful save)
- Retention: capped at 50 records (oldest trimmed)
- Git: ignored via `LLM/.gitignore`

Enable/disable:

- `FEEDING_PROGRAM_SEEDS_ENABLED=true` to enable
- In production/staging, default is OFF unless explicitly enabled

Note: seeds are saved only for clean, validated outputs.

## API Endpoints (This LLM Service)

### Parent
- `POST /generate_meal_plan`

### Nutritionist
- `POST /nutrition/analysis`
- `POST /assessment`
- `POST /feeding_program/meal_plan`
- `POST /feeding_program/assessment`

### Utilities/Admin
- `POST /get_foods_data`
- `POST /upload_pdf`
- `POST /process_embeddings`
- `POST /embedding_status`
- `POST /get_knowledge_base`

## Environment Variables (LLM Service)

Required:

- `GROQ_API_KEY=...`
- DB connection values used by `db.py` (`DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`)

Optional:

- `FEEDING_PROGRAM_SEEDS_ENABLED=true|false`
- `APP_ENV=local|production|staging`

## Local Run

From `LLM/`:

```bash
pip install -r requirements.txt
uvicorn fastapi_app:app --reload --port 8000
```

## Quality Tests

Feeding program validation tests are in:

- `LLM/test_feeding_program_quality.py`

Run:

```bash
cd LLM
python -m unittest -v test_feeding_program_quality.py
```

CI workflow:

- `.github/workflows/feeding-program-quality.yml`

## Folder Structure

```text
LLM/
|- fastapi_app.py
|- nutrition_chain.py
|- feeding_program_chain.py
|- nutrition_ai.py
|- embedding_utils.py
|- data_manager.py
|- db.py
|- requirements.txt
|- test_feeding_program_quality.py
|- .env
|- .gitignore
|- README.md
|- embeddings_cache/
`- seeds/                      (auto-created when enabled)
```

## Notes

- Personalized flow and feeding-program flow are intentionally separate.
- Feeding program is optimized for reliability and safety over free-form creativity.
- Keep this README aligned whenever endpoint contracts or validation behavior changes.