# AI Training & Fine-Tuning Pipeline

This directory contains the universal message extractor and dataset preparation pipeline for fine-tuning or training custom AI models (OpenAI, Google Gemini, Ollama, Hugging Face, Unsloth, LLaMA-Factory) on real WhatsApp conversations.

All generated training output files are automatically saved to `training/output/` and are ignored by `.gitignore` to keep your git tree clean.

---

## 🚀 Quick Start Extractor

Extract conversations for **Sanjeet Yadav** (User ID: 57):

```bash
# 1. Standard OpenAI / Gemini chat completions JSONL format (User -> AI reply pairs)
php training/extractor.php --user="Sanjeet Yadav" --format=chat --mode=pairs

# 2. Multi-turn session format (preserves entire continuous conversation thread)
php training/extractor.php --user="Sanjeet Yadav" --format=chat --mode=multi_turn

# 3. ShareGPT format (for Unsloth / Axolotl / LLaMA-Factory)
php training/extractor.php --user="Sanjeet Yadav" --format=sharegpt

# 4. Alpaca instruction format (instruction, input, output)
php training/extractor.php --user="Sanjeet Yadav" --format=alpaca

# 5. CSV export (for review in Excel or Google Sheets)
php training/extractor.php --user="Sanjeet Yadav" --format=csv

# 6. Extract ALL chats across the entire database
php training/extractor.php --all --format=chat
```

---

## 📁 Output Location
All dataset exports are automatically saved in:
`training/output/training_<user_slug>_<mode>_<format>.<ext>`

Example:
`training/output/training_sanjeet_yadav_pairs_chat.jsonl`

---

## 🎯 Fine-Tuning Integration Guides

### 1. OpenAI Fine-Tuning (`gpt-4o-mini`, `gpt-3.5-turbo`)
Upload the generated `.jsonl` directly to OpenAI:
```bash
curl https://api.openai.com/v1/files \
  -H "Authorization: Bearer $OPENAI_API_KEY" \
  -F purpose="fine-tune" \
  -F file="@training/output/training_sanjeet_yadav_pairs_chat.jsonl"
```

### 2. Google Vertex AI / Gemini Tuning
In Google AI Studio or Vertex AI Console:
1. Navigate to **Model Tuning**.
2. Select your base model (e.g., `gemini-1.5-flash` or `gemini-2.5-flash`).
3. Upload `training_sanjeet_yadav_pairs_chat.jsonl`.
4. Start Tuning.

### 3. Local Training / Ollama / Unsloth (LoRA)
Use the `sharegpt` format with Unsloth or LLaMA-Factory:
```bash
php training/extractor.php --user="Sanjeet Yadav" --format=sharegpt --mode=multi_turn
```
Pass the resulting `.json` file to your training script or Google Colab notebook.
