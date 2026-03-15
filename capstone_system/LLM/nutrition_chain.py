from langchain_core.prompts import PromptTemplate
from langchain_core.runnables import RunnableSequence
from langchain_groq import ChatGroq
from pydantic import SecretStr
import os
from dotenv import load_dotenv
from data_manager import data_manager
from datetime import datetime
import re

load_dotenv()

def validate_meal_plan_variety(meal_plan_text):
    """Validate that no dishes are repeated across the 7-day meal plan."""
    # Extract all dishes from the meal plan
    dish_pattern = r'\*\*(Breakfast|Almusal|Lunch|Tanghalian|Snack|Meryenda|Dinner|Hapunan).*?\*\*:\s*([^\(]+)'
    dishes = re.findall(dish_pattern, meal_plan_text)
    
    # Count dish occurrences
    dish_names = [dish[1].strip().lower() for dish in dishes]
    duplicates = [dish for dish in set(dish_names) if dish_names.count(dish) > 1]
    
    if duplicates:
        return False, f"Repeated dishes found: {', '.join(duplicates)}"
    
    return True, "All dishes are unique"


def _extract_day_sections(meal_plan_text):
    """Extract Day 1..7 blocks from meal plan text."""
    pattern = re.compile(
        r'(?is)(?:\*\*)?Day\s*([1-7])(?:\*\*)?[^\n]*:\s*(.*?)'
        r'(?=(?:\n\s*(?:\*\*)?Day\s*[1-7](?:\*\*)?[^\n]*:)|\Z)'
    )
    sections = {}
    for d, block in pattern.findall(meal_plan_text or ''):
        sections[int(d)] = block.strip()
    return sections


def validate_meal_plan_structure(meal_plan_text):
    """Strictly validate: exactly 7 days, each with 4 meal categories."""
    sections = _extract_day_sections(meal_plan_text)
    missing_days = [d for d in range(1, 8) if d not in sections]
    extra_days = [d for d in sections.keys() if d < 1 or d > 7]

    if missing_days:
        return False, f"Missing day sections: {', '.join(map(str, missing_days))}"
    if extra_days:
        return False, f"Invalid day sections found: {', '.join(map(str, extra_days))}"
    if len(sections) != 7:
        return False, f"Expected 7 day sections, found {len(sections)}"

    required_meals = {
        'almusal': r'\b(almusal|breakfast)\b',
        'tanghalian': r'\b(tanghalian|lunch)\b',
        'meryenda': r'\b(meryenda|snack)\b',
        'hapunan': r'\b(hapunan|dinner)\b',
    }

    for day in range(1, 8):
        block = sections.get(day, '')
        missing = []
        for meal_name, meal_pattern in required_meals.items():
            if not re.search(meal_pattern, block, flags=re.IGNORECASE):
                missing.append(meal_name)
        if missing:
            return False, f"Day {day} missing meals: {', '.join(missing)}"

    return True, "Structure valid: 7 days x 4 meals"


def _normalize_dish_name(dish_text):
    """Normalize dish text for overlap comparison."""
    if not dish_text:
        return ''
    dish = str(dish_text).strip()
    dish = dish.split('--')[0].split(' - ')[0]
    dish = re.sub(r'\([^\)]*\)', '', dish)
    dish = re.sub(r'[\*\[\]]', '', dish)
    dish = re.sub(r'\s+', ' ', dish).strip().lower()
    return dish


def extract_dishes_for_overlap(meal_plan_text):
    """Extract normalized dish names from meal lines only."""
    lines = re.findall(
        r'(?im)^\s*-\s*\*\*(?:Breakfast|Almusal|Lunch|Tanghalian|Snack|Meryenda|Dinner|Hapunan)\*\*:\s*(.+)$',
        meal_plan_text or ''
    )
    dishes = [_normalize_dish_name(line) for line in lines]
    return {d for d in dishes if d}


def _check_similarity_against_recent_plans(candidate_plan_text, recent_plans, threshold=0.60):
    """Reject near-duplicate plans against recent plans for the same child."""
    candidate_dishes = extract_dishes_for_overlap(candidate_plan_text)
    if not candidate_dishes:
        return False, "No dishes extracted for overlap check", 1.0

    max_overlap = 0.0
    max_plan_id = None
    for prev in recent_plans or []:
        prev_text = (prev or {}).get('plan_details', '')
        prev_dishes = extract_dishes_for_overlap(prev_text)
        if not prev_dishes:
            continue
        overlap = len(candidate_dishes & prev_dishes) / max(len(candidate_dishes), len(prev_dishes), 1)
        if overlap > max_overlap:
            max_overlap = overlap
            max_plan_id = (prev or {}).get('plan_id')

    if max_overlap >= threshold:
        return False, f"High overlap with recent plan {max_plan_id}: {max_overlap * 100:.1f}%", max_overlap
    return True, f"Novelty check passed: max overlap {max_overlap * 100:.1f}%", max_overlap


def calculate_diversity_score(meal_plan_text):
    """Calculate diversity score based on variety of ingredients and cooking methods."""
    # Extract all dishes
    dishes = re.findall(r'\*\*(?:Breakfast|Almusal|Lunch|Tanghalian|Snack|Meryenda|Dinner|Hapunan).*?\*\*:\s*([^\(]+)', meal_plan_text)
    
    # Common Filipino cooking methods - expanded for more variety detection
    cooking_methods = ['adobo', 'sinigang', 'tinola', 'ginisa', 'prito', 'inihaw', 
                       'nilaga', 'ginataan', 'sinangag', 'haluing', 'pritong', 
                       'nilagang', 'ginisang', 'ginataang', 'tortang', 'inihaw na',
                       'kare-kare', 'pinakbet', 'bicol express', 'menudo', 'afritada',
                       'mechado', 'kaldereta', 'escabeche', 'relyeno', 'embutido',
                       'fried', 'steamed', 'boiled', 'grilled', 'stewed']
    
    # Count unique cooking methods used
    methods_used = set()
    for dish in dishes:
        dish_lower = dish.lower()
        for method in cooking_methods:
            if method in dish_lower:
                methods_used.add(method)
    
    # Calculate score (0-100)
    if len(dishes) == 0:
        return {
            'score': 0,
            'unique_dishes': 0,
            'total_dishes': 0,
            'cooking_methods_used': [],
            'method_variety': 0
        }
    
    unique_dishes = len(set([d.strip().lower() for d in dishes]))
    method_variety = len(methods_used)
    
    # 70% weight on unique dishes, 30% on cooking method variety
    score = (unique_dishes / len(dishes) * 70) + (method_variety / len(cooking_methods) * 30)
    
    return {
        'score': round(score, 2),
        'unique_dishes': unique_dishes,
        'total_dishes': len(dishes),
        'cooking_methods_used': sorted(list(methods_used)),
        'method_variety': method_variety
    }


def get_seasonal_foods():
    """Get currently available seasonal Filipino foods."""
    month = datetime.now().month
    
    seasonal_foods = {
        'dry_season': {
            'fruits': ['mangga', 'santol', 'suha', 'dalandan', 'melon', 'watermelon'],
            'vegetables': ['kalabasa', 'sitaw', 'talong', 'okra']
        },
        'wet_season': {
            'fruits': ['rambutan', 'lanzones', 'marang', 'langka', 'durian', 'mangosteen'],
            'vegetables': ['kangkong', 'pechay', 'mustasa', 'ampalaya']
        }
    }
    
    # June to November is wet season, December to May is dry season
    if 6 <= month <= 11:
        season = seasonal_foods['wet_season']
        season_name = 'Tag-ulan'
    else:
        season = seasonal_foods['dry_season']
        season_name = 'Tag-init'
    
    return {
        'season': season_name,
        'fruits': season['fruits'],
        'vegetables': season['vegetables'],
        'all': season['fruits'] + season['vegetables']
    }


def extract_ingredients_from_plan(meal_plan_text):
    """Extract main ingredients from the meal plan for analysis."""
    ingredients_db = {
        'manok': 'chicken', 'baboy': 'pork', 'isda': 'fish', 'tilapia': 'tilapia',
        'bangus': 'milkfish', 'galunggong': 'round scad', 'itlog': 'egg', 
        'saging': 'banana', 'kalabasa': 'squash', 'sitaw': 'string beans', 
        'kangkong': 'water spinach', 'ampalaya': 'bitter melon', 'talong': 'eggplant', 
        'monggo': 'mung beans', 'hipon': 'shrimp', 'baka': 'beef', 'mais': 'corn', 
        'papaya': 'papaya', 'mangga': 'mango', 'pusit': 'squid', 'tahong': 'mussels',
        'pechay': 'bok choy', 'repolyo': 'cabbage', 'patatas': 'potato',
        'kamote': 'sweet potato', 'gabi': 'taro'
    }
    
    used_ingredients = {}
    meal_plan_lower = meal_plan_text.lower()
    
    for tagalog, english in ingredients_db.items():
        count = meal_plan_lower.count(tagalog)
        if count > 0:
            used_ingredients[english] = count
    
    return used_ingredients


def create_nutrition_llm():
    """Create a standardized ChatGroq instance for nutrition functions."""
    api_key = os.getenv('GROQ_API_KEY')
    if not api_key:
        raise ValueError("GROQ_API_KEY not found in environment variables")
    
    return ChatGroq(
        api_key=SecretStr(api_key),
        model="meta-llama/llama-4-scout-17b-16e-instruct",
        temperature=0.1,
        max_tokens=1500,
        timeout=120,
        max_retries=2
    )

def create_meal_plan_llm(temperature: float = 0.35):
    """Dedicated ChatGroq for 7-day meal plan generation.
    Accepts optional temperature so retry attempts can progressively lower it,
    forcing more conservative/structured output on each subsequent try.
    """
    api_key = os.getenv('GROQ_API_KEY')
    if not api_key:
        raise ValueError("GROQ_API_KEY not found in environment variables")
    return ChatGroq(
        api_key=SecretStr(api_key),
        model="meta-llama/llama-4-scout-17b-16e-instruct",
        temperature=temperature,
        max_tokens=4000,
        timeout=180,
        max_retries=2,
    )


def _derive_nutrition_priority(weight_for_age: str, height_for_age: str,
                               bmi_for_age: str, age_months: int) -> str:
    """Map clinical growth indicators to concrete dietary priority instructions."""
    wfa = (weight_for_age or '').lower()
    hfa = (height_for_age or '').lower()
    bfa = (bmi_for_age or '').lower()
    lines = []

    # Weight-for-age / wasting
    if any(k in wfa for k in ['severely underweight', 'severe wasting', 'severe']):
        lines.append(
            'MALNUTRITION ALERT - Severely underweight: EVERY meal must be calorie-dense. '
            'Prioritize energy-rich foods: kanin, kamote, saging, gata ng niyog, itlog, karne. '
            'No watery soups without a dense protein/starch side. '
            'Add healthy fats (gata, egg yolk) to every meal.'
        )
    elif any(k in wfa for k in ['underweight', 'wasted', 'moderately']):
        lines.append(
            'NUTRITIONAL PRIORITY - Underweight: choose calorie-dense dishes every meal. '
            'Prefer: lugaw na may itlog at gata, kanin na may karne/isda, '
            'kamote-based snacks, monggo na may atay, ginataang gulay na may karne.'
        )
    elif any(k in wfa for k in ['overweight', 'obese']):
        lines.append(
            'NUTRITIONAL PRIORITY - Overweight: choose vegetable-forward, lower-fat dishes. '
            'Prefer: sinigang, tinola, ginisang gulay, steamed fish. '
            'Limit fried dishes to at most 2 out of 7 days. No extra oil/gata in most meals.'
        )
    else:
        lines.append(
            'NUTRITIONAL STATUS - Normal weight: aim for balanced variety '
            'across proteins, vegetables, fruits, and grains every day.'
        )

    # Height-for-age / stunting
    if any(k in hfa for k in ['severely stunted', 'stunted']):
        lines.append(
            'GROWTH PRIORITY - Stunted: maximize zinc, calcium, iron, and protein for linear growth. '
            'Include DAILY: isda/baboy/manok for protein, malunggay/kangkong for micronutrients, '
            'gata for healthy fats. Use bone broth (sabaw ng buto) as soup base where possible.'
        )

    # BMI-for-age / acute malnutrition
    if any(k in bfa for k in ['severely wasted', 'wasted']):
        lines.append(
            'BMI PRIORITY - Acute malnutrition: every single meal must include '
            'a protein source (isda, manok, baboy, itlog, monggo, atay). '
            'No protein-free meals allowed.'
        )

    return '\n'.join(lines) if lines else (
        'NUTRITIONAL STATUS: Normal - maintain balance across all food groups.'
    )


def _age_portions_guide(age_months: int) -> str:
    """Return age-appropriate portion size string for the meal plan prompt."""
    if age_months < 8:
        return '2-3 kutsara (mashed o pureed lamang)'
    elif age_months <= 12:
        return '3-4 kutsara (malambot, walang malalaking tipak)'
    elif age_months <= 24:
        return '1/4 tasa (pinong tinadtad, malambot na finger food pwede na)'
    elif age_months <= 36:
        return '1/3 tasa (maliliit na tipak, family food texture)'
    else:
        return '1/2 tasa (regular na texture, tamang sukat na tipak)'


def _available_ingredients_rule(available_ingredients) -> str:
    """Build the available-ingredients constraint block for the prompt."""
    if not available_ingredients or not str(available_ingredients).strip():
        return (
            'AVAILABLE INGREDIENTS: Walang specific na ingredients ang ibinigay ng magulang. '
            'Gumamit ng seasonal Filipino foods mula sa food database.'
        )
    return (
        'AVAILABLE INGREDIENTS - MAHIGPIT NA PANUNTUNAN (Valid sa LAHAT ng 7 araw):\n'
        f'Mga available na ingredients: {available_ingredients}\n\n'
        'Mga dapat sundin:\n'
        '- Gamitin LAMANG ang mga nakalista bilang pangunahing sangkap (protein, gulay, prutas)\n'
        '- HUWAG gumamit ng ibang protein, gulay, o starch na WALA sa listahan\n'
        '- OK lang ang basic na seasonings/condiments bilang pampalasa: '
        'bawang, sibuyas, asin, toyo, patis, gata, luya, suka, mantika\n'
        '- Halimbawa: kung "manok, kangkong, kamote" lamang ang nakalista - '
        'TANGING iyon lamang ang gagamitin; walang bangus, monggo, o ibang gulay'
    )


def get_relevant_pdf_chunks(query, k=4):
    """Retrieve relevant PDF text using semantic similarity search."""
    from embedding_utils import embedding_searcher
    
    try:
        results = embedding_searcher.search_similar_chunks(query, k=k)
        if results:
            # Return the text chunks from semantic search, filtered by similarity threshold
            return [chunk for chunk, score, metadata in results if score > 0.4]
        
        # Return empty list if no results found
        return []
    except Exception as e:
        print(f"Error retrieving PDF chunks: {str(e)}")
        print("Note: If no embeddings found, run 'python build_embeddings.py' to create them.")
        return []

def clean_section_text(text):
    """Clean section text by removing markdown formatting and extra whitespace."""
    if not text:
        return ""
    
    # Remove markdown headers and formatting
    text = re.sub(r'^#+\s*', '', text, flags=re.MULTILINE)
    text = re.sub(r'\*\*(.*?)\*\*', r'\1', text)  # Bold text
    text = re.sub(r'\*(.*?)\*', r'\1', text)      # Italic text
    text = re.sub(r'`(.*?)`', r'\1', text)        # Code formatting
    
    # Clean up whitespace but preserve paragraph breaks
    text = re.sub(r'\n\s*\n', '\n\n', text)      # Multiple newlines to double
    text = re.sub(r'\n{3,}', '\n\n', text)       # Limit to max 2 newlines
    text = text.strip()
    
    return text

def parse_assessment_sections(assessment_text):
    """Parse the assessment text into structured sections."""
    # Handle AIMessage objects by extracting content
    if hasattr(assessment_text, 'content'):
        assessment_text = assessment_text.content
    
    # Ensure we have a string
    if not isinstance(assessment_text, str):
        assessment_text = str(assessment_text)
    
    sections = {
        "patient_profile_summary": "",
        "nutritional_priorities": "",
        "age_appropriate_guidelines": "",
        "practical_tips": "",
        "seven_day_meal_plan": "",
        "assessment_history": "",
        "next_assessment": ""
    }
    
    # Define section markers (case insensitive)
    section_markers = {
        "patient_profile_summary": [r"patient\s+profile\s+summary", r"profile\s+summary"],
        "nutritional_priorities": [r"nutritional\s+priorities", r"nutrition\s+priorities"],
        "age_appropriate_guidelines": [r"age[\s-]?appropriate\s+guidelines", r"age\s+guidelines"],
        "practical_tips": [r"practical\s+tips", r"feeding\s+tips"],
        "seven_day_meal_plan": [r"7[\s-]?day\s+meal\s+plan", r"seven[\s-]?day\s+meal\s+plan", r"meal\s+plan"],
        "assessment_history": [r"assessment\s+history", r"history\s+review"],
        "next_assessment": [r"next\s+assessment", r"next\s+steps", r"monitoring"]
    }
    
    # Split text into potential sections
    lines = assessment_text.split('\n')
    current_section = None
    current_content = []
    
    for line in lines:
        line_lower = line.lower().strip()
        
        # Check if this line is a section header
        found_section = None
        for section_key, patterns in section_markers.items():
            for pattern in patterns:
                if re.search(pattern, line_lower):
                    found_section = section_key
                    break
            if found_section:
                break
        
        if found_section:
            # Save previous section content
            if current_section and current_content:
                sections[current_section] = clean_section_text('\n'.join(current_content))
            
            # Start new section
            current_section = found_section
            current_content = []
        else:
            # Add line to current section
            if current_section:
                current_content.append(line)
    
    # Save the last section
    if current_section and current_content:
        sections[current_section] = clean_section_text('\n'.join(current_content))
    
    # If no sections were found, try to extract content by looking for key phrases
    if all(not content for content in sections.values()):
        # Fallback: put all content in the appropriate section based on content
        clean_text = clean_section_text(assessment_text)
        if "meal plan" in assessment_text.lower():
            sections["seven_day_meal_plan"] = clean_text
        else:
            sections["patient_profile_summary"] = clean_text
    
    return sections

def generate_patient_assessment(patient_id):
    """
    Generate a comprehensive pediatric dietary assessment for a patient using LangChain and Groq LLM.
    Privacy-focused: Only includes medically necessary information, no names or location data.
    Returns structured sections instead of a single markdown string.
    """
    api_key = os.getenv('GROQ_API_KEY')
    if not api_key:
        raise ValueError("GROQ_API_KEY not found in environment variables")

    # Get patient data
    patient_data = data_manager.get_patient_by_id(patient_id)
    if not patient_data:
        return {"error": "Patient data not found"}

    # Get assessment data
    assessment_data = data_manager.get_nutritionist_notes_by_patient(patient_id)
    latest_assessment = assessment_data[0] if assessment_data else {}

    # Get meal plans
    meal_plans = data_manager.get_meal_plans_by_patient(patient_id)
    latest_meal_plan = meal_plans[0] if meal_plans else {}

    # Get meal plan notes
    meal_plan_notes = ""
    if latest_meal_plan:
        plan_id = latest_meal_plan.get('plan_id')
        if isinstance(plan_id, str) and plan_id:
            notes = data_manager.get_notes_for_meal_plan(plan_id)
            if notes:
                meal_plan_notes = "; ".join([note.get('notes', '') for note in notes])

    # Get foods data for context
    foods_data = data_manager.get_foods_data()
    food_context = ""
    if foods_data:
        # Limit to first 10 foods to avoid token limits
        sample_foods = foods_data[:10]
        food_list = []
        for food in sample_foods:
            food_info = f"{food.get('food_name_and_description', '')} - {food.get('energy_kcal', 0)} kcal"
            if food.get('nutrition_tags'):
                food_info += f" ({food.get('nutrition_tags')})"
            food_list.append(food_info)
        food_context = "AVAILABLE FOODS SAMPLE:\n" + "\n".join(food_list) + "\n"

    # Get knowledge base context using optimized search
    from embedding_utils import embedding_searcher
    
    # Create targeted query based on patient characteristics
    query_parts = []
    age_months = patient_data.get('age_months', 0)
    allergies = patient_data.get('allergies', '')
    medical_problems = patient_data.get('other_medical_problems', '')
    weight_status = patient_data.get('weight_for_age', '')
    height_status = patient_data.get('height_for_age', '')
    
    # Age-specific query
    if age_months <= 6:
        query_parts.append("exclusive breastfeeding infant nutrition 0-6 months")
    elif age_months <= 12:
        query_parts.append("complementary feeding introduction 6-12 months iron rich foods")
    elif age_months <= 24:
        query_parts.append("toddler nutrition 12-24 months feeding practices")
    else:
        query_parts.append("young child nutrition 2-5 years dietary guidelines")
    
    query_parts.append("pediatric dietary assessment comprehensive evaluation")
    
    # Add condition-specific queries
    if allergies and str(allergies).lower() not in ['none', 'no', 'n/a', 'not specified']:
        query_parts.append(f"food allergies children {allergies} alternative foods")

    if medical_problems and str(medical_problems).lower() not in ['none', 'no', 'n/a', 'not specified']:
        query_parts.append(f"child nutrition {medical_problems} dietary management")

    if weight_status and ('underweight' in str(weight_status).lower() or 'wasted' in str(weight_status).lower()):
        query_parts.append("underweight children nutrition dense foods weight gain")
    elif weight_status and 'overweight' in str(weight_status).lower():
        query_parts.append("overweight children healthy eating weight management")
    if height_status and ('stunted' in str(height_status).lower() or 'short' in str(height_status).lower()):
        query_parts.append("stunting prevention linear growth nutrition")
    
    query = " ".join(query_parts)
    
    try:
        kb_results = embedding_searcher.search_similar_chunks(query, k=4)
        relevant_kb = []
        for chunk, score, metadata in kb_results:
            if score > 0.4:  # Filter by similarity threshold
                source_info = f" (Source: {metadata.get('pdf_name', 'Unknown')})" if metadata.get('pdf_name') else ""
                relevant_kb.append(f"{chunk.strip()}{source_info}")
        
        kb_context = ""
        if relevant_kb:
            kb_context = "NUTRITION KNOWLEDGE BASE:\n" + "\n---\n".join(relevant_kb) + "\n"
    except Exception as e:
        print(f"Error retrieving knowledge base context: {str(e)}")
        print("Note: If no embeddings found, run 'python build_embeddings.py' to create them.")
        kb_context = ""

    prompt_template = PromptTemplate(
        input_variables=[
            "patient_id", "age_months", "sex", "weight_kg", "height_cm", "weight_for_age", 
            "height_for_age", "bmi_for_age", "breastfeeding", "allergies", "religion", 
            "other_medical_problems", "edema", "assessment_date", "treatment", 
            "recovery_status", "notes", "plan_id", "plan_details", "meal_plan_notes", 
            "generated_at", "food_context", "kb_context"
        ],
        template="""You are a Pediatric Dietary Assistant. Generate a comprehensive assessment with clear sections. Do not include personal names or location information for privacy protection.

PATIENT PROFILE:
- Patient ID: {patient_id}
- Age: {age_months} months
- Sex: {sex}
- Weight: {weight_kg} kg
- Height: {height_cm} cm
- Weight-for-Age: {weight_for_age}
- Height-for-Age: {height_for_age}
- BMI-for-Age: {bmi_for_age}
- Breastfeeding: {breastfeeding}
- Allergies: {allergies}
- Religion: {religion}
- Other Medical Problems: {other_medical_problems}
- Edema: {edema}

ASSESSMENT DATA:
- Assessment Date: {assessment_date}
- Treatment: {treatment}
- Recovery Status: {recovery_status}
- Notes: {notes}

MEAL PLAN DATA:
- Plan ID: {plan_id}
- Plan Details: {plan_details}
- Notes: {meal_plan_notes}
- Generated At: {generated_at}

{food_context}
{kb_context}

INSTRUCTIONS: Generate a comprehensive assessment with these EXACT section headers:

PATIENT PROFILE SUMMARY:
Brief overview of the child's current nutritional status based on age, measurements, and medical conditions.

NUTRITIONAL PRIORITIES:
Key nutritional needs and priorities based on age, growth metrics, allergies, and medical conditions.

AGE-APPROPRIATE GUIDELINES:
Specific feeding guidelines for this child's age group with developmental considerations.

PRACTICAL TIPS:
Practical feeding tips for parents, preparation guidelines, and safety considerations.

7-DAY MEAL PLAN:
Detailed 7-day meal plan with age-appropriate foods, portions, and cultural considerations. Include breakfast, lunch, dinner, and snacks for each day.

ASSESSMENT HISTORY:
Review of previous assessments, growth progression, and current meal plan effectiveness.

NEXT ASSESSMENT:
Recommendations for next assessment timing, monitoring instructions, and what parents should watch for.

IMPORTANT: 
- Use age-specific recommendations (0-6 months: breastfeeding; 7-12 months: soft foods; 13-24 months: finger foods; 25-59 months: family meals)
- Strictly avoid allergens listed: {allergies}
- Consider religious/cultural preferences: {religion}
- Account for medical conditions: {other_medical_problems}
- Provide practical, actionable advice for parents"""
    )

    # Prepare template variables - ONLY medical and nutritional data, no personal identifiers
    template_vars = {
        "patient_id": str(patient_id),
        "age_months": str(patient_data.get('age_months', 'Unknown')),
        "sex": patient_data.get('sex', 'Unknown'),
        "weight_kg": str(patient_data.get('weight_kg', 'Unknown')),
        "height_cm": str(patient_data.get('height_cm', 'Unknown')),
        "weight_for_age": patient_data.get('weight_for_age', 'Unknown'),
        "height_for_age": patient_data.get('height_for_age', 'Unknown'),
        "bmi_for_age": patient_data.get('bmi_for_age', 'Unknown'),
        "breastfeeding": patient_data.get('breastfeeding', 'Unknown'),
        "allergies": patient_data.get('allergies', 'None'),
        "religion": patient_data.get('religion', 'Unknown'),
        "other_medical_problems": patient_data.get('other_medical_problems', 'None'),
        "edema": patient_data.get('edema', 'Unknown'),
        "assessment_date": str(latest_assessment.get('assessment_date', 'No previous assessment')),
        "treatment": latest_assessment.get('treatment', 'None recorded'),
        "recovery_status": latest_assessment.get('recovery_status', 'Unknown'),
        "notes": latest_assessment.get('notes', 'No previous notes'),
        "plan_id": str(latest_meal_plan.get('plan_id', 'No meal plan')),
        "plan_details": latest_meal_plan.get('plan_details', 'No meal plan generated')[:500] + "..." if len(str(latest_meal_plan.get('plan_details', ''))) > 500 else latest_meal_plan.get('plan_details', 'No meal plan generated'),
        "meal_plan_notes": meal_plan_notes or 'No notes on meal plan',
        "generated_at": str(latest_meal_plan.get('generated_at', 'No meal plan date')),
        "food_context": food_context,
        "kb_context": kb_context
    }

    # Create LLM using shared factory function
    llm = create_nutrition_llm()

    # Create runnable sequence using modern LangChain pattern
    chain = prompt_template | llm

    try:
        # Generate assessment
        result = chain.invoke(template_vars)
        
        # Handle AIMessage objects by extracting content
        if hasattr(result, 'content'):
            result = result.content
        
        # Ensure we have a string
        if not isinstance(result, str):
            result = str(result)
        
        # Parse the result into structured sections
        sections = parse_assessment_sections(result)
        
        return sections
    except Exception as e:
        return {
            "patient_profile_summary": f"Error generating assessment: {str(e)}",
            "nutritional_priorities": "",
            "age_appropriate_guidelines": "",
            "practical_tips": "",
            "seven_day_meal_plan": "",
            "assessment_history": "",
            "next_assessment": ""
        }

def get_meal_plan_with_langchain(patient_id, available_ingredients=None, religion=None):
    """
    Use LangChain to generate a meal plan for a patient using Groq LLM and a nutritionist-style prompt.

    Optionally includes available ingredients provided by the parent.
    """
    api_key = os.getenv('GROQ_API_KEY')
    if not api_key:
        raise ValueError("GROQ_API_KEY not found in environment variables")

    # Get patient data
    patient_data = data_manager.get_patient_by_id(patient_id)
    if not patient_data:
        return "Error: Patient data not found"
    
    # Get latest assessment for nutritional indicators (they moved to assessments table)
    latest_assessment = data_manager.get_latest_assessment_for_patient(patient_id)
    
    # Merge assessment data into patient_data for backward compatibility
    if latest_assessment:
        patient_data['weight_for_age'] = latest_assessment.get('weight_for_age', 'Unknown')
        patient_data['height_for_age'] = latest_assessment.get('height_for_age', 'Unknown')
        patient_data['bmi_for_age'] = latest_assessment.get('bmi_for_age', 'Unknown')
    else:
        patient_data['weight_for_age'] = 'Unknown'
        patient_data['height_for_age'] = 'Unknown'
        patient_data['bmi_for_age'] = 'Unknown'

    # Get Filipino foods from knowledge base
    knowledge_base = data_manager.get_knowledge_base()
    filipino_foods = knowledge_base.get('filipino_foods', {})
    filipino_context = ""
    if filipino_foods:
        filipino_recipes = []
        for recipe in list(filipino_foods.values())[:5]:
            filipino_recipes.append(f"- {recipe['name']}: {recipe['nutrition_facts']}")
        filipino_context = "\nFilipino Food Options:\n" + "\n".join(filipino_recipes)

    def get_age_specific_guidelines(age_months):
        if age_months <= 6:
            return "Breast milk exclusively or appropriate infant formula. On-demand feeding (8-12x/day). No solids. Monitor weight gain."
        elif age_months <= 12:
            return "Introduce soft, pureed, mashed foods. 2-3 meals/day + milk. Iron-rich foods, soft fruits/veggies. No honey/nuts."
        elif age_months <= 24:
            return "Soft finger foods, small pieces, family food consistency. 3 meals + 2-3 snacks/day. Encourage self-feeding."
        else:
            return "Regular family food textures. 3 meals + 1-2 snacks/day. Encourage independence, social eating, nutrition education."

    # Helper: Allergy section
    allergy_val = patient_data.get('allergies', 'None')
    if allergy_val and allergy_val.lower() not in ['none', 'no', 'n/a', 'not specified']:
        allergy_section = f"Avoid {allergy_val}. Check all ingredients."
    else:
        allergy_section = "No known allergies. Monitor for new reactions."

    # Helper: Religion section
    religion_val = patient_data.get('religion', '') or religion or 'Not specified'
    if religion_val and religion_val.lower() not in ['none', 'no', 'n/a', 'not specified']:
        religion_section = f"Follow {religion_val} dietary guidelines."
    else:
        religion_section = "No specific religious dietary restrictions."

    # Get religion from parent
    if not religion:
        parent_id = patient_data.get('parent_id')
        religion = data_manager.get_religion_by_parent(parent_id) if parent_id else ""

    # Retrieve relevant PDF knowledge for this patient with multiple targeted queries
    age_months = patient_data.get('age_months', 0)
    allergies = patient_data.get('allergies', '')
    medical_problems = patient_data.get('other_medical_problems', '')
    
    # Create targeted nutrition queries for better context
    nutrition_queries = []
    
    # Age-specific query
    if age_months <= 6:
        nutrition_queries.append("exclusive breastfeeding infant nutrition 0-6 months")
    elif age_months <= 12:
        nutrition_queries.append("complementary feeding introduction 6-12 months iron rich foods")
    elif age_months <= 24:
        nutrition_queries.append("toddler nutrition 12-24 months feeding practices meal planning")
    else:
        nutrition_queries.append("young child nutrition 2-5 years dietary guidelines")
    
    # Add specific queries for medical conditions
    if allergies and allergies.lower() not in ['none', 'no', 'n/a', 'not specified']:
        nutrition_queries.append(f"food allergies children {allergies} alternative foods")
    
    if medical_problems and medical_problems.lower() not in ['none', 'no', 'n/a', 'not specified']:
        nutrition_queries.append(f"child nutrition {medical_problems} dietary management")
    
    # Growth-related queries based on patient data
    weight_status = patient_data.get('weight_for_age', '') or ''
    height_status = patient_data.get('height_for_age', '') or ''
    
    if weight_status and ('underweight' in weight_status.lower() or 'wasted' in weight_status.lower()):
        nutrition_queries.append("underweight children nutrition dense foods weight gain")
    elif weight_status and 'overweight' in weight_status.lower():
        nutrition_queries.append("overweight children healthy eating weight management")
        
    if height_status and ('stunted' in height_status.lower() or 'short' in height_status.lower()):
        nutrition_queries.append("stunting prevention linear growth nutrition")
    
    # Collect all relevant knowledge using unified embedding search
    from embedding_utils import embedding_searcher
    
    # Combine all nutrition queries into a single comprehensive query
    combined_query = " ".join(nutrition_queries)
    
    try:
        # Use embedding searcher directly for better efficiency (only uses cached embeddings)
        search_results = embedding_searcher.search_similar_chunks(combined_query, k=6)
        unique_chunks = []
        seen = set()
        
        for chunk, score, metadata in search_results:
            if score > 0.4 and chunk not in seen:  # Filter by similarity threshold and remove duplicates
                seen.add(chunk)
                source_info = f" (Source: {metadata.get('pdf_name', 'Unknown')})" if metadata.get('pdf_name') else ""
                unique_chunks.append(f"{chunk.strip()}{source_info}")
        
        pdf_context = ""
        if unique_chunks:
            pdf_context = f"\nEVIDENCE-BASED NUTRITION GUIDANCE (from WHO guidelines - for context only):\n" + "\n---\n".join(unique_chunks[:6])  # Limit to 6 most relevant chunks
    except Exception as e:
        print(f"Error retrieving nutrition guidance for meal planning: {str(e)}")
        print("Note: If no embeddings found, run 'python build_embeddings.py' to create them.")
        pdf_context = ""

    # Get all food names, energy, and nutrition_tags from the database
    foods_data = data_manager.get_foods_data()
    food_names = []
    all_nutrition_tags = set()
    for food in foods_data:
        name = food.get('food_name_and_description')
        kcal = food.get('energy_kcal')
        tags = food.get('nutrition_tags')
        if tags:
            # Split tags by comma or semicolon, strip whitespace
            for tag in re.split(r'[;,]', tags):
                tag = tag.strip()
                if tag:
                    all_nutrition_tags.add(tag)
        if name:
            if kcal is not None:
                food_names.append(f"{name} (Energy: {kcal} kcal)")
            else:
                food_names.append(name)
    food_list_str = '\n- '.join(food_names)
    if food_list_str:
        food_list_str = 'FOOD DATABASE (only recommend foods from this list):\n- ' + food_list_str + '\n'
    else:
        food_list_str = ''
    nutrition_tags_str = ', '.join(sorted(all_nutrition_tags))

    # --- Nutrition analysis
    from nutrition_ai import ChildNutritionAI
    nutrition_analysis = ""
    try:
        nutrition_ai = ChildNutritionAI()
        # Get latest assessment for notes and treatment
        assessments = data_manager.get_nutritionist_notes_by_patient(patient_id)
        latest_assessment = assessments[0] if assessments else {}
        # Custom prompt for structured output
        analysis_result = nutrition_ai.analyze_child_nutrition(
            patient_id=patient_id,
            age_in_months=int(patient_data.get('age_months') or 0),
            allergies=str(patient_data.get('allergies') or ''),
            other_medical_problems=str(patient_data.get('other_medical_problems') or ''),
            parent_id=str(patient_data.get('parent_id') or ''),
            notes=latest_assessment.get('notes', ''),
            treatment=latest_assessment.get('treatment', ''),
            sex=patient_data.get('sex', ''),
            weight_for_age=patient_data.get('weight_for_age', ''),
            height_for_age=patient_data.get('height_for_age', ''),
            bmi_for_age=patient_data.get('bmi_for_age', ''),
            breastfeeding=patient_data.get('breastfeeding', ''),
            religion=patient_data.get('religion', ''),
            guidelines_context=pdf_context,
            custom_prompt="""
        Provide a comprehensive nutrition analysis in the following structured format:

        ## NUTRITIONAL STATUS:
        [Provide overall assessment based on growth indicators]

        ## POTENTIAL CONCERNS:
        [List any nutritional concerns or deficiencies identified]

        ## DIETARY RESTRICTIONS:

        ### Allergy-Related Restrictions:
        [If allergies present: List specific foods to avoid and safety reminders]
        [If no allergies: State "No known allergies"]

        ### Religious Dietary Requirements:
        [If religious restrictions apply: List specific dietary guidelines]
        [If none: State "No religious dietary restrictions"]

        ### Medical Condition Restrictions:
        [If medical conditions present: List foods to avoid and foods that are beneficial]
        [If none: State "No medical dietary restrictions"]

        ## NUTRITIONAL RECOMMENDATIONS:

        ### Growth-Specific Needs:
        - **Height Development**: [If height-for-age is low, specify nutrients needed for linear growth]
        - **Weight Management**: [If weight-for-age is concerning, specify appropriate interventions]

        ### Age-Appropriate Guidelines:

        **0-6 months:**
        - **Primary Nutrition**: Exclusively breast milk or formula
        - **Feeding Style**: Breastfeeding on demand; practice responsive feeding by responding to the infant's hunger cues

        **6-12 months:**
        - **Introduction of Solids**: Start introducing small amounts of pureed or mashed, nutrient-dense foods
        - **Foods to Offer**: Iron-fortified infant cereals, fruits, vegetables, and lean proteins like finely mashed meat or fish
        - **Breast Milk/Formula**: Continues as the primary source of nutrition
        - **Feeding Environment**: Introduce solids in a calm setting, with the infant sitting upright and moderately hungry

        **1-2 years:**
        - **Solid Foods**: Increase variety in texture and consistency. Most children can eat the same foods as the family, with appropriate preparation
        - **Whole Milk**: Begin offering whole cow's milk
        - **Meal Schedule**: Aim for 3 meals and 1-2 snacks per day

        **2-5 years:**
        - **Diverse Diet**: Continue offering a variety of healthy foods from all food groups
        - **Whole Grains**: Gradually increase the introduction of wholegrain foods
        - **Milk**: Offer low-fat milk after age 2
        - **Responsibility**: Maintain the division of responsibility: the caregiver provides healthy food, and the child decides how much to eat

        ### Key Nutrients to Focus On:
        [List specific vitamins, minerals, and macronutrients needed based on the child's current nutritional status]

        ## FEEDING RECOMMENDATIONS:
        [Provide practical, age-appropriate feeding advice and meal suggestions specific to this child's needs]

        ## FOLLOW-UP RECOMMENDATIONS:
        [Suggest monitoring schedule and when to reassess nutritional status]
        """
        )
        nutrition_analysis = f"NUTRITION ANALYSIS FOR THIS CHILD (ID: {patient_id}):\n{analysis_result}"
    except Exception as e:
        nutrition_analysis = ""


    # Calculate next assessment date based on age
    def calculate_next_assessment_date(age_months, created_at):
        from datetime import datetime, timedelta
        if isinstance(created_at, str):
            created_date = datetime.strptime(created_at, '%Y-%m-%d %H:%M:%S')
        else:
            created_date = created_at or datetime.now()
        if age_months is None:
            age_months = 0
        if age_months <= 24:
            next_date = created_date + timedelta(days=30)
        else:
            next_date = created_date + timedelta(days=180)
        return next_date.strftime('%Y-%m-%d')

    current_date = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    next_assessment = calculate_next_assessment_date(patient_data.get('age_months', 0), current_date)

    # Calculate age in years and months for display
    age_months = patient_data.get('age_months', 0)
    if isinstance(age_months, str):
        try:
            age_months = int(age_months)
        except Exception:
            age_months = 0
    age_years = age_months // 12
    age_months_remainder = age_months % 12

    # Get seasonal foods context
    seasonal = get_seasonal_foods()
    seasonal_context = f"\n**SEASONAL FOODS ({seasonal['season']}) - PRIORITIZE THESE:**\n"
    seasonal_context += f"- Prutas: {', '.join(seasonal['fruits'])}\n"
    seasonal_context += f"- Gulay: {', '.join(seasonal['vegetables'])}\n"
    
    # Create the streamlined prompt - separate f-string variables from template variables
    age_guidelines = get_age_specific_guidelines(age_months)

    # -- Prompt helpers -------------------------------------------------------
    nutrition_priority      = _derive_nutrition_priority(
        patient_data.get('weight_for_age', ''),
        patient_data.get('height_for_age', ''),
        patient_data.get('bmi_for_age', ''),
        age_months,
    )
    portions_guide             = _age_portions_guide(age_months)
    available_ingredients_rule = _available_ingredients_rule(available_ingredients)

    # Recent plans context for anti-repetition (same child, last 3 plans)
    recent_plans = data_manager.get_meal_plans_by_patient(patient_id, months_back=6)[:3]
    recent_dish_pool = set()
    for _plan in recent_plans:
        recent_dish_pool.update(extract_dishes_for_overlap((_plan or {}).get('plan_details', '')))
    recent_dishes_to_avoid = ', '.join(sorted(recent_dish_pool)[:30]) if recent_dish_pool else 'Walang recent dish history'
    # -------------------------------------------------------------------------

    prompt_str = """You are a Pediatric Nutritionist specializing in Filipino cuisine for children 0-5 years.
Language: Sagutin sa Tagalog. English ay OK para sa medikal na termino o kapag walang Tagalog equivalent.
Gamitin ang simpleng salita na naiintindihan ng ordinaryong Filipino na magulang.

================================================================
CHILD PROFILE
================================================================
Edad: {age_months} buwan  |  Timbang: {weight_kg} kg  |  Taas: {height_cm} cm
BMI-for-Age: {bmi_for_age}
Allergy: {allergies}
Karamdaman: {other_medical_problems}
Relihiyon: {religion}

================================================================
NUTRITIONAL PRIORITY -- BASAHIN ITO MUNA BAGO MAG-GENERATE
================================================================
{nutrition_priority}

================================================================
AGE GUIDELINES AT TAMANG SUKAT NG PAGKAIN
================================================================
{age_guidelines}
Portion bawat serving: {portions_guide}

================================================================
{available_ingredients_rule}
================================================================

FOOD DATABASE -- Gumamit lamang ng pagkain mula sa listahan na ito:
{food_list_str}
{pdf_context}
Nutrition context: {nutrition_analysis}
{seasonal_context}

ANTI-REPETITION CONTEXT (recent plans for this same child):
Iwasan hangga't maaari ang mga dish na ito para hindi paulit-ulit ang output:
{recent_dishes_to_avoid}

================================================================
HAKBANG 1 -- PRE-PLAN: PUMILI NG 28 NATATANGING DISHES BAGO MAGSIMULA
================================================================
Bago isulat ang buong plano, punan ang 28 slots na ito.

Mga panuntunan:
- Bawat slot ay NAIIBANG dish -- WALANG PAULIT-ULIT sa lahat ng 28 slots
- Sundin: allergies ({allergies}), relihiyon ({religion}), at available ingredients rule sa itaas
- Baguhin ang protein bawat araw (manok, baboy, isda, itlog, hipon, baka, monggo, atay, etc.)
- Baguhin ang paraan ng pagluto (adobo, sinigang, tinola, inihaw, ginisa, ginataan, nilaga, prito, etc.)
- Almusal = rice porridge, sinangag dishes, lugaw -- HINDI main ulam
- Meryenda = magaang na pagkain (prutas, kakanin, porridge) -- HINDI full meal

Day 1 (Lunes):     Almusal=[  ]  Tanghalian=[  ]  Meryenda=[  ]  Hapunan=[  ]
Day 2 (Martes):    Almusal=[  ]  Tanghalian=[  ]  Meryenda=[  ]  Hapunan=[  ]
Day 3 (Miyerkules):Almusal=[  ]  Tanghalian=[  ]  Meryenda=[  ]  Hapunan=[  ]
Day 4 (Huwebes):   Almusal=[  ]  Tanghalian=[  ]  Meryenda=[  ]  Hapunan=[  ]
Day 5 (Biyernes):  Almusal=[  ]  Tanghalian=[  ]  Meryenda=[  ]  Hapunan=[  ]
Day 6 (Sabado):    Almusal=[  ]  Tanghalian=[  ]  Meryenda=[  ]  Hapunan=[  ]
Day 7 (Linggo):    Almusal=[  ]  Tanghalian=[  ]  Meryenda=[  ]  Hapunan=[  ]

================================================================
HAKBANG 2 -- ISULAT ANG BUONG 7-ARAW NA MEAL PLAN
================================================================
Gamitin ang EKSAKTONG 28 dishes mula sa Hakbang 1 (walang pagpapalit o dagdag).

### CHILD PROFILE
**Edad**: {age_months} buwan
**Timbang**: {weight_kg} kg  |  **Taas**: {height_cm} cm  |  **BMI-for-Age**: {bmi_for_age}
**Allergy**: {allergies}  |  **Karamdaman**: {other_medical_problems}
**Relihiyon**: {religion}

### 7-DAY MEAL PLAN

**Day 1** (Lunes):
- **Almusal**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Tanghalian**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Meryenda**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Hapunan**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]

**Day 2** (Martes):
- **Almusal**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Tanghalian**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Meryenda**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Hapunan**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]

**Day 3** (Miyerkules):
- **Almusal**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Tanghalian**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Meryenda**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Hapunan**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]

**Day 4** (Huwebes):
- **Almusal**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Tanghalian**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Meryenda**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Hapunan**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]

**Day 5** (Biyernes):
- **Almusal**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Tanghalian**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Meryenda**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Hapunan**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]

**Day 6** (Sabado):
- **Almusal**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Tanghalian**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Meryenda**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Hapunan**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]

**Day 7** (Linggo):
- **Almusal**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Tanghalian**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Meryenda**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]
- **Hapunan**: [dish mula Hakbang 1] ({portions_guide}) -- [1 pangungusap na benepisyo sa Tagalog]

### REKOMENDASYON SA PAGKAIN
[3-5 bullet points ng praktikal na payo sa pagpapakain sa Tagalog, batay sa nutritional status ng bata]

PINAKAMAHALAGANG PANUNTUNAN:
1. Gamitin ang EKSAKTONG dishes mula Hakbang 1 -- WALANG pagpapalit
2. WALANG dish na paulit-ulit sa lahat ng 7 araw
3. Lahat ng pangalan ng pagkain sa Tagalog
4. HUWAG isulat ang bilang ng calories
5. Mahigpit na iwasan ang mga allergen: {allergies}
6. Sundin ang dietary guidelines ng relihiyon: {religion}"""

    prompt_template = PromptTemplate(
        input_variables=[
            "food_list_str", "pdf_context", "nutrition_analysis",
            "age_months", "weight_kg", "height_cm", "bmi_for_age",
            "allergies", "other_medical_problems", "religion",
            "nutrition_priority", "portions_guide", "available_ingredients_rule",
            "age_guidelines", "seasonal_context", "recent_dishes_to_avoid",
        ],
        template=prompt_str
    )

    prompt_inputs = {
        "food_list_str": food_list_str,
        "pdf_context": pdf_context,
        "nutrition_analysis": nutrition_analysis,
        "age_months": age_months,
        "weight_kg": patient_data.get('weight_kg', 'Unknown'),
        "height_cm": patient_data.get('height_cm', 'Unknown'),
        "bmi_for_age": patient_data.get('bmi_for_age', 'Unknown'),
        "allergies": patient_data.get('allergies', 'None'),
        "other_medical_problems": patient_data.get('other_medical_problems', 'None'),
        "religion": religion_val,
        "nutrition_priority": nutrition_priority,
        "portions_guide": portions_guide,
        "available_ingredients_rule": available_ingredients_rule,
        "age_guidelines": age_guidelines,
        "seasonal_context": seasonal_context,
        "recent_dishes_to_avoid": recent_dishes_to_avoid,
    }

    # Progressive temperature: first attempt uses 0.35 for variety;
    # each retry drops lower to force more structured, conservative output.
    import logging as _log
    _retry_temps = [0.35, 0.20, 0.10]
    result = ""
    _is_valid, _msg = False, "No attempts made"
    _structure_ok, _structure_msg = False, "No attempts made"
    _variety_ok, _variety_msg = False, "No attempts made"
    _similarity_ok, _similarity_msg, _max_overlap = True, "No recent plans to compare", 0.0
    for _attempt in range(3):
        llm = create_meal_plan_llm(_retry_temps[_attempt])
        chain = prompt_template | llm
        _raw = chain.invoke(prompt_inputs)
        if hasattr(_raw, 'content'):
            _raw = _raw.content
        if not isinstance(_raw, str):
            _raw = str(_raw)
        result = _raw

        _structure_ok, _structure_msg = validate_meal_plan_structure(result)
        _variety_ok, _variety_msg = validate_meal_plan_variety(result)
        _similarity_ok, _similarity_msg, _max_overlap = _check_similarity_against_recent_plans(
            result,
            recent_plans,
            threshold=0.60
        )

        _is_valid = _structure_ok and _variety_ok and _similarity_ok
        _msg = f"{_structure_msg}; {_variety_msg}; {_similarity_msg}"
        if _is_valid:
            break
        if _attempt < 2:
            _failed_checks = []
            if not _structure_ok:
                _failed_checks.append(f"structure: {_structure_msg}")
            if not _variety_ok:
                _failed_checks.append(f"variety: {_variety_msg}")
            if not _similarity_ok:
                _failed_checks.append(f"novelty: {_similarity_msg}")
            _log.getLogger(__name__).warning(
                f"Meal plan validation failed (attempt {_attempt + 1}/3): {' | '.join(_failed_checks)}. "
                f"Retrying at temperature {_retry_temps[_attempt + 1]}..."
            )

    # Reuse validation result already computed in the loop (avoids a redundant re-check)
    is_valid, message = _is_valid, _msg
    diversity = calculate_diversity_score(result)
    ingredients = extract_ingredients_from_plan(result)
    
    # Print validation results
    print("\n" + "="*50)
    print("MEAL PLAN QUALITY ASSESSMENT")
    print("="*50)
    print(f"✓ Structure Check: {_structure_msg}")
    print(f"✓ Variety Check: {_variety_msg}")
    print(f"✓ Novelty Check: {_similarity_msg}")
    print(f"✓ Diversity Score: {diversity['score']}/100")
    print(f"  - Unique Dishes: {diversity['unique_dishes']}/{diversity['total_dishes']}")
    print(f"  - Cooking Methods: {diversity['method_variety']} ({', '.join(diversity['cooking_methods_used'])})")
    print(f"✓ Ingredients Used: {len(ingredients)} types")
    if ingredients:
        print(f"  - {', '.join([f'{k}({v})' for k, v in list(ingredients.items())[:10]])}")
    if not is_valid:
        print(f"⚠ WARNING: {message}")
    if not _structure_ok:
        print("⚠ WARNING: Output structure is invalid (expected 7 days x 4 meals).")
    if not _similarity_ok:
        print(f"⚠ WARNING: Output too similar to recent plans ({_max_overlap * 100:.1f}% overlap).")
    if diversity['score'] < 70:
        print(f"⚠ WARNING: Low diversity score. Consider regenerating for more variety.")
    print("="*50 + "\n")
    
    return result