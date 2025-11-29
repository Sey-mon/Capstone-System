# 🍎 AI-Powered Child Nutrition Management System

An advanced nutrition management system for children aged 0-5 years, powered by **Groq LLM**, **LangChain**, and **semantic search** capabilities. Features role-based FastAPI endpoints, embedding-based contextual guidance, and comprehensive nutrition analysis.

## 🏗️ System Architecture

### **Core Components:**
1. **FastAPI Application** (`fastapi_app.py`) - Role-based REST API with comprehensive endpoints
2. **AI Nutrition Engine** (`nutrition_ai.py`) - Modern LangChain-powered nutrition analysis
3. **Embedding System** (`embedding_utils.py`) - FAISS + Sentence Transformers for contextual guidance
4. **Data Management** (`data_manager.py`) - MySQL database layer with text chunking
5. **Nutrition Chain** (`nutrition_chain.py`) - LangChain meal planning for parents (individual children)
6. **Feeding Program Chain** (`feeding_program_chain.py`) - LangChain meal planning for nutritionists (feeding programs)

### **Key Features:**
- 🤖 **AI-Powered**: Groq LLM (Llama) with modern LangChain integration
- 🔍 **Semantic Search**: FAISS + Sentence Transformers for contextual nutrition guidance
- 📋 **Role-Based Access**: User, Parent, Nutritionist, and Admin endpoints
- 📄 **PDF Processing**: Upload and process nutrition guidelines with AI summarization
- 🇵🇭 **Filipino-Focused**: Built-in Filipino nutrition knowledge and cultural considerations
- 🍽️ **Individual Meal Plans**: 7-day personalized meal plans for parents (via `nutrition_chain.py`)
- 👥 **Feeding Program Plans**: Batch meal planning for community feeding programs (via `feeding_program_chain.py`)
- 🥘 **Authentic Filipino Cuisine**: Traditional dishes like sinigang, adobo, tinola, kare-kare, pancit, etc.
- 💰 **Budget-Conscious**: Cost-effective ingredient selection for feeding programs
- 🔐 **Privacy-First**: Medical data only, no personal identifiers in AI processing
- 📊 **Evidence-Based**: WHO nutrition guidelines integration via PDF knowledge base
- 🔄 **Diverse Menus**: Automatic variety control ensuring no dish repetition

## 📋 Quick Start

### 1. **Environment Setup**
```bash
# Clone and navigate to directory
cd C:\xampp\htdocs\Capstone-System\Groq_Meal_Plan

# Install dependencies
pip install -r requirements.txt

# Create .env file
echo "GROQ_API_KEY=your_actual_groq_api_key_here" > .env
```

### 2. **Database Setup**
```sql
-- Ensure MySQL tables exist: patients, users, meal_plans, assessments, knowledge_base, etc.
-- Run your existing database schema setup
```

### 3. **Build Knowledge Base Embeddings**
```python
# In Python shell or script
from embedding_utils import embedding_searcher
from data_manager import data_manager

# Upload PDF documents via /upload_pdf endpoint first, then:
result = embedding_searcher.build_embeddings_from_knowledge_base()
print("Embeddings built:", result)
```

### 4. **Start FastAPI Server**
```bash
# Development server
uvicorn fastapi_app:app --reload --port 8000
```

## 🛠️ API Endpoints

### **👤 User Role Endpoints**
- `POST /get_foods_data` - Get all available foods from database

### **👨‍👩‍👧‍👦 Parent Role Endpoints**
- `POST /generate_meal_plan` - AI-generated 7-day personalized meal plans with contextual guidance
- `POST /get_children_by_parent` - Get children for specific parent
- `POST /get_meal_plans_by_child` - Get meal plan history for child
- `POST /get_meal_plan_detail` - Get specific meal plan details

### **👩‍⚕️ Nutritionist Role Endpoints**
- `POST /nutrition/analysis` - Comprehensive nutrition analysis with evidence-based guidance
- `POST /assessment` - Generate structured pediatric dietary assessments
- `POST /feeding_program/meal_plan` - Generate batch meal plans for feeding programs
- `POST /feeding_program/assessment` - Generate feeding program assessment reports

### **🛠️ Admin Role Endpoints**
- `POST /upload_pdf` - Upload and process nutrition PDFs with AI summarization
- `POST /process_embeddings` - Build/rebuild FAISS embeddings from knowledge base
- `POST /embedding_status` - Check embedding system status
- `POST /get_knowledge_base` - Retrieve all knowledge base documents

## 🧠 AI & Machine Learning Features

### **Semantic Search System**
- **Model**: `sentence-transformers/all-MiniLM-L6-v2`
- **Vector Store**: FAISS with cosine similarity
- **Chunking**: LangChain RecursiveCharacterTextSplitter
- **Context**: Top-K retrieval for contextual nutrition guidance

### **AI-Powered Features**
1. **Nutrition Analysis**: Personalized assessment based on growth metrics, allergies, medical conditions
2. **Meal Planning**: 7-day culturally appropriate meal plans with available ingredients
3. **PDF Summarization**: Extract 0-5 year nutrition content from uploaded guidelines
4. **Contextual Guidance**: Evidence-based recommendations from knowledge base

## 📁 Project Structure

```
LLM/
├── fastapi_app.py              # Main FastAPI application with role-based endpoints
├── nutrition_ai.py             # Core AI nutrition analysis (modern LangChain)
├── nutrition_chain.py          # LangChain meal planning for parents (individual children)
├── feeding_program_chain.py    # LangChain meal planning for nutritionists (feeding programs)
├── embedding_utils.py          # FAISS + Sentence Transformers semantic search
├── data_manager.py             # MySQL database operations with chunking
├── db.py                       # Database connection utilities
├── requirements.txt            # All dependencies with versions
├── .env                        # Environment variables (GROQ_API_KEY)
├── README.md                   # This file
└── embeddings_cache/          # FAISS index and chunks cache (auto-created)
    ├── faiss_index.idx
    ├── chunks.pkl
    ├── metadata.pkl
    └── kb_hash.txt
```

## 🔧 Core Dependencies

### **AI & Language Models**
- `groq>=0.4.0` - Groq LLM API client
- `langchain>=0.1.0` - Classic LangChain framework
- `langchain-core>=0.1.0` - Modern LangChain core components
- `langchain-groq>=0.1.0` - Groq integration for LangChain
- `langchain-text-splitters>=0.1.0` - Advanced text chunking

### **Machine Learning**
- `sentence-transformers>=5.1.0` - Embedding model for semantic search
- `faiss-cpu>=1.12.0` - Vector similarity search engine
- `numpy>=1.24.0` - Numerical computing

### **Web Framework**
- `fastapi>=0.104.0` - Modern async web framework
- `uvicorn[standard]>=0.24.0` - ASGI server
- `pydantic>=2.5.0` - Data validation
- `python-multipart>=0.0.6` - File upload support

### **Data Processing**
- `mysql-connector-python>=8.2.0` - MySQL database connectivity
- `pdfplumber>=0.9.0` - PDF text extraction
- `pandas>=1.5.0` - Data manipulation

##  Semantic Search Features

### **Contextual Guidance System**
The system provides evidence-based nutrition guidance using semantic search:

1. **Query Building**: Automatically creates targeted queries based on:
   - Child's age (0-6 months, 6-12 months, 1-2 years, 2-5 years)
   - Growth status (underweight, overweight, stunted)
   - Medical conditions and allergies
   - Cultural/religious considerations

2. **Top-K Retrieval**: Returns most relevant nutrition guidance chunks
3. **Source Attribution**: Tracks which documents provide each recommendation
4. **Similarity Filtering**: Only includes high-confidence matches (>0.4 similarity)

### **Knowledge Base Management**
- Upload WHO nutrition guidelines, research papers, cultural nutrition guides
- AI automatically extracts 0-5 year relevant content
- Semantic chunking with overlap for better context
- Cached embeddings for fast retrieval

---

## 🇵🇭 Filipino Cuisine Enhancement (Updated)

### **7-Day Meal Plan Improvements**

The meal plan generator has been significantly enhanced to ensure:

#### ✅ **Non-Repeating Dishes**
- **MANDATORY**: No dish is repeated across the entire 7-day period
- Each day features completely unique meals for breakfast, lunch, snack, and dinner
- Ingredients may overlap, but preparation methods and dish names must be different
- Example: If chicken appears, it's prepared differently each time (Tinola Day 1, Adobo Day 3, Pritong Manok Day 5)

#### 🍽️ **Traditional Filipino Cuisine Categories**

**Breakfast Options (Almusal):**
- Champorado (chocolate rice porridge)
- Arroz caldo (chicken rice porridge with ginger)
- Lugaw na baboy/manok (rice porridge)
- Sinangag at itlog (fried rice with egg)
- Pandesal with kesong puti
- Tuyo at sinangag (dried fish and fried rice)
- Tapa at itlog (cured beef and egg)

**Main Meals (Tanghalian/Hapunan):**
- Adobong manok/baboy (chicken/pork adobo)
- Sinigang na baboy/isda (sour soup)
- Tinola (ginger chicken soup)
- Pinakbet (vegetable stew with bagoong)
- Ginataang kalabasa (squash in coconut milk)
- Nilagang baka (boiled beef soup)
- Kare-kare (peanut stew)
- Pancit canton/bihon (stir-fried noodles)
- Ginataang hipon (shrimp in coconut milk)
- Pritong isda (fried fish - tilapia, bangus, galunggong)
- Tortang talong (eggplant omelette)
- Menudo, Afritada, Mechado (various stews)

**Snacks (Meryenda):**
- Turon (banana spring rolls)
- Banana cue (caramelized banana)
- Ginataang mais (corn in coconut milk)
- Puto (steamed rice cake)
- Suman (sticky rice in banana leaves)
- Fresh fruits: Saging, mangga, papaya

#### 🔪 **Filipino Cooking Methods (Tagalog)**
- **Pritong** = Fried
- **Nilagang** = Boiled
- **Ginisang** = Sautéed
- **Ginataang** = Cooked in coconut milk
- **Inihaw** = Grilled
- **Haluing** = Mashed/pureed (for babies)
- **Sinangag** = Fried rice

#### 👶 **Age-Appropriate Textures**
- **6-8 months**: Lugaw, haluing saging/papaya/kalabasa, sopas with mashed vegetables
- **9-11 months**: Lugaw with small pieces, mashed rice with ulam, soft pancit
- **12-23 months**: Kanin with finely chopped ulam, sopas, soft fruits
- **24-59 months**: Regular family foods, appropriately chopped

#### 🎯 **Diversity Requirements**
The system enforces:
- At least 10 different protein sources across 7 days
- At least 15 different vegetables/fruits across 7 days
- Varied cooking methods throughout the week
- Unique breakfast items each day
- Unique snacks each day
- Traditional Filipino dishes distributed across different days

#### 📝 **Implementation Details**
Enhanced prompt in `nutrition_chain.py` includes:
- Explicit non-repetition rules
- Comprehensive Filipino dish examples
- Day-by-day variety checking instructions
- Cultural appropriateness guidelines
- Age-specific Filipino food preparations

---

## 🔄 Dual Meal Planning Systems

### **1. Parent Meal Plans (`nutrition_chain.py`)**
**Purpose:** Individual, personalized 7-day meal plans for parents

**Features:**
- ✅ Personalized for single child
- ✅ Considers home cooking environment
- ✅ Based on available home ingredients
- ✅ Detailed nutritional analysis per child
- ✅ Age-specific texture adaptations
- ✅ Allergy and religious considerations

**Use Case:** Parents generating meal plans at home via the parent dashboard

**Example:**
```python
from nutrition_chain import get_meal_plan_with_langchain

meal_plan = get_meal_plan_with_langchain(
    patient_id=123,
    available_ingredients="manok, saging, kangkong",
    religion="Catholic"
)
```

### **2. Feeding Program Plans (`feeding_program_chain.py`)**
**Purpose:** Batch meal planning for community feeding programs managed by nutritionists

**Features:**
- ✅ Designed for multiple children (batch cooking)
- ✅ Budget-conscious ingredient selection (low/moderate/high)
- ✅ Community-level food availability focus
- ✅ Age group adaptations (not individual children)
- ✅ Group nutritional assessments
- ✅ Weekly shopping lists for bulk purchasing
- ✅ Batch cooking preparation tips
- ✅ Cost-effectiveness tracking

**Use Case:** Nutritionists managing barangay feeding programs with multiple enrolled children

**Example:**
```python
from feeding_program_chain import generate_feeding_program_meal_plan

# Get patients enrolled in feeding program
patients_data = get_feeding_program_patients(barangay="Barangay 1")

# Generate 4-week feeding program meal plan
result = generate_feeding_program_meal_plan(
    patients_data=patients_data,
    program_duration_weeks=4,
    budget_level='moderate',
    available_ingredients="manok, bangus, monggo, kangkong, saging",
    barangay="Barangay 1"
)
```

**Key Differences:**

| Feature | Parent Plans | Feeding Program Plans |
|---------|-------------|---------------------|
| **Target** | Individual child | Multiple children (batch) |
| **User Role** | Parent | Nutritionist |
| **Focus** | Personalization | Cost-effectiveness & scalability |
| **Portions** | Single child portions | Batch portions per age group |
| **Budget** | Not primary concern | Primary consideration |
| **Ingredients** | Home availability | Community/bulk availability |
| **Duration** | 7 days (weekly) | 4+ weeks (program duration) |
| **Shopping List** | Individual ingredients | Bulk shopping list |
| **Analysis** | Individual nutrition | Group assessment |

---

## 📄 License

This project is part of a capstone system for child nutrition management in the Philippines.

---

**🚀 Ready to revolutionize child nutrition with AI! Start by setting up your environment and uploading your first nutrition guideline PDF.**