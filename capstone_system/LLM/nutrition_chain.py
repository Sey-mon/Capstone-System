from langchain_core.prompts import PromptTemplate
from langchain_core.runnables import RunnableSequence
from langchain_groq import ChatGroq
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
        groq_api_key=api_key,
        model_name="meta-llama/llama-4-scout-17b-16e-instruct",
        temperature=0.1,
        max_tokens=1500
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
    weight_status = patient_data.get('weight_for_age', '')
    height_status = patient_data.get('height_for_age', '')
    
    if 'underweight' in weight_status.lower() or 'wasted' in weight_status.lower():
        nutrition_queries.append("underweight children nutrition dense foods weight gain")
    elif 'overweight' in weight_status.lower():
        nutrition_queries.append("overweight children healthy eating weight management")
        
    if 'stunted' in height_status.lower() or 'short' in height_status.lower():
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
            age_in_months=patient_data.get('age_months'),
            allergies=patient_data.get('allergies'),
            other_medical_problems=patient_data.get('other_medical_problems'),
            parent_id=patient_data.get('parent_id'),
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
    
    prompt_str = """You are a Pediatric Nutritionist specializing in Filipino cuisine for children 0-5 years.

    LANGUAGE INSTRUCTIONS: You may answer in Tagalog, but it's okay to use English if you are unsure of the correct Tagalog word or if there isn't an accurate Tagalog equivalent. Use simple, everyday words that ordinary Filipino parents can understand.

    ## PRIMARY CONSTRAINT
    ONLY recommend foods from the database below. Never mention generic food groups or unlisted foods.
    
    ## CRITICAL VARIETY REQUIREMENT
    You MUST create a meal plan where NO DISH is repeated across the entire 7 days. This is MANDATORY.
    - If you use chicken, prepare it differently each time (e.g., Day 1: Tinola, Day 3: Adobo, Day 5: Fried)
    - Never repeat the same dish name (e.g., don't use "Lugaw" on Day 1 and Day 4)
    - Ensure maximum variety in vegetables, fruits, proteins, and cooking methods
    - Think of how Filipino families eat throughout the week - they vary their dishes daily

    ## FOOD DATABASE
    {food_list_str}

    {pdf_context}

    Base your response on {nutrition_analysis}

    ## MANDATORY RESPONSE FORMAT - ALL SECTIONS MUST BE INCLUDED IN THIS EXACT ORDER

    ### CHILD PROFILE (MUST include this exact header)
    **Edad**: {age_months} buwan
    **Timbang**: {weight_kg} kg
    **Taas**: {height_cm} cm
    **BMI**: {bmi_for_age}
    **Allergy**: {allergies}
    **Karamdaman**: {other_medical_problems}
    **Relihiyon**: {religion}
    **Available Ingredients**: {available_ingredients}

    AGE-SPECIFIC GUIDELINES:
    **Kasalukuyang Edad ({age_months} buwan)**:
    {age_guidelines}

    **Allergy**: {allergies} {allergy_section}

    **Relihiyon**: {religion} {religion_section}

    **Karamdaman**: {other_medical_problems} [If none, briefly state "Walang special dietary restrictions." If specified, provide brief dietary guidance.]

    ### 7-DAY MEAL PLAN (MUST include this exact header)
    
    **MANDATORY: COMPLETE ALL 7 DAYS WITH ALL MEAL CATEGORIES**
    **CRITICAL: Each day MUST have DIFFERENT dishes - NO REPETITION across all 7 days**
    
    **Day 1** (Monday - Lunes):
    - **Breakfast (Almusal)**: [Specific Filipino dish in Tagalog] ([portion]) - [benefit in Tagalog]
    - **Lunch (Tanghalian)**: [Different Filipino dish] ([portion]) - [benefit in Tagalog]
    - **Snack (Meryenda)**: [Filipino snack] ([portion]) - [benefit in Tagalog]
    - **Dinner (Hapunan)**: [Different Filipino dish] ([portion]) - [benefit in Tagalog]

    **Day 2** (Tuesday - Martes):
    - **Breakfast (Almusal)**: [DIFFERENT Filipino dish from Day 1] ([portion]) - [benefit in Tagalog]
    - **Lunch (Tanghalian)**: [DIFFERENT Filipino dish from Day 1] ([portion]) - [benefit in Tagalog]
    - **Snack (Meryenda)**: [DIFFERENT Filipino snack from Day 1] ([portion]) - [benefit in Tagalog]
    - **Dinner (Hapunan)**: [DIFFERENT Filipino dish from Day 1] ([portion]) - [benefit in Tagalog]

    **Day 3** (Wednesday - Miyerkules):
    - **Breakfast (Almusal)**: [DIFFERENT Filipino dish from Days 1-2] ([portion]) - [benefit in Tagalog]
    - **Lunch (Tanghalian)**: [DIFFERENT Filipino dish from Days 1-2] ([portion]) - [benefit in Tagalog]
    - **Snack (Meryenda)**: [DIFFERENT Filipino snack from Days 1-2] ([portion]) - [benefit in Tagalog]
    - **Dinner (Hapunan)**: [DIFFERENT Filipino dish from Days 1-2] ([portion]) - [benefit in Tagalog]

    **Day 4** (Thursday - Huwebes):
    - **Breakfast (Almusal)**: [DIFFERENT Filipino dish from Days 1-3] ([portion]) - [benefit in Tagalog]
    - **Lunch (Tanghalian)**: [DIFFERENT Filipino dish from Days 1-3] ([portion]) - [benefit in Tagalog]
    - **Snack (Meryenda)**: [DIFFERENT Filipino snack from Days 1-3] ([portion]) - [benefit in Tagalog]
    - **Dinner (Hapunan)**: [DIFFERENT Filipino dish from Days 1-3] ([portion]) - [benefit in Tagalog]

    **Day 5** (Friday - Biyernes):
    - **Breakfast (Almusal)**: [DIFFERENT Filipino dish from Days 1-4] ([portion]) - [benefit in Tagalog]
    - **Lunch (Tanghalian)**: [DIFFERENT Filipino dish from Days 1-4] ([portion]) - [benefit in Tagalog]
    - **Snack (Meryenda)**: [DIFFERENT Filipino snack from Days 1-4] ([portion]) - [benefit in Tagalog]
    - **Dinner (Hapunan)**: [DIFFERENT Filipino dish from Days 1-4] ([portion]) - [benefit in Tagalog]

    **Day 6** (Saturday - Sabado):
    - **Breakfast (Almusal)**: [DIFFERENT Filipino dish from Days 1-5] ([portion]) - [benefit in Tagalog]
    - **Lunch (Tanghalian)**: [DIFFERENT Filipino dish from Days 1-5] ([portion]) - [benefit in Tagalog]
    - **Snack (Meryenda)**: [DIFFERENT Filipino snack from Days 1-5] ([portion]) - [benefit in Tagalog]
    - **Dinner (Hapunan)**: [DIFFERENT Filipino dish from Days 1-5] ([portion]) - [benefit in Tagalog]

    **Day 7** (Sunday - Linggo):
    - **Breakfast (Almusal)**: [DIFFERENT Filipino dish from Days 1-6] ([portion]) - [benefit in Tagalog]
    - **Lunch (Tanghalian)**: [DIFFERENT Filipino dish from Days 1-6] ([portion]) - [benefit in Tagalog]
    - **Snack (Meryenda)**: [DIFFERENT Filipino snack from Days 1-6] ([portion]) - [benefit in Tagalog]
    - **Dinner (Hapunan)**: [DIFFERENT Filipino dish from Days 1-6] ([portion]) - [benefit in Tagalog]

    **CRITICAL MEAL PLAN RULES:**
    1. NEVER repeat the same dish or ingredient across different days (MANDATORY)
    2. Use only foods from the database
    3. All food names and preparation methods MUST be in Tagalog
    4. Use common, affordable Filipino dishes (pangmasa)
    5. For 6-59 months: Use mechanical diet (finely chopped food)
    6. For 0-6 months: State "Breastfeeding lamang" instead of meal plan
    7. DO NOT include calorie numbers
    8. Day 1: Prioritize available ingredients: {available_ingredients}

    **FILIPINO TRADITIONAL CUISINE EXAMPLES (Use these styles of dishes):**
    
    **Breakfast (Almusal) Options - Traditional & Healthy:**
    - Champorado (chocolate rice porridge)
    - Ginataang bilo-bilo (sweet rice balls in coconut milk)
    - Lugaw na baboy/manok (rice porridge with pork/chicken)
    - Arroz caldo (chicken rice porridge with ginger)
    - Goto (tripe rice porridge)
    - Sinangag na itlog (fried rice with egg)
    - Pandesal with kesong puti
    - Tuyo at sinangag (dried fish and fried rice)
    - Bangus at sinangag (milkfish and fried rice)
    - Tortang talong (eggplant omelette)
    - Scrambled eggs with tomatoes
    - Oatmeal/oats with saging
    - Champorado
    - Pandesal with peanut butter
    - Fresh fish with sinangag
    - Boiled egg with brown rice
    - Native rice porridge with vegetables
    
    **Lunch/Dinner (Tanghalian/Hapunan) Options - Regional Varieties:**
    
    *Adobo Varieties:*
    - Adobong manok (chicken adobo)
    - Adobong baboy (pork adobo)
    - Adobong pusit (squid adobo)
    - Adobong kangkong (water spinach adobo)
    - Adobong sitaw (string beans adobo)
    
    *Sinigang Varieties:*
    - Sinigang na baboy sa sampalok (pork in tamarind soup)
    - Sinigang na isda sa bayabas (fish in guava soup)
    - Sinigang na hipon (shrimp sinigang)
    - Sinigang na bangus belly
    - Sinigang na baka (beef sinigang)
    
    *Ginataan Dishes:*
    - Ginataang kalabasa at sitaw (squash and string beans in coconut milk)
    - Ginataang hipon (shrimp in coconut milk)
    - Ginataang manok (chicken in coconut milk)
    - Ginataang gulay (mixed vegetables in coconut milk)
    - Bicol express (spicy pork in coconut milk)
    - Laing (taro leaves in coconut milk)
    - Ginataang langka (jackfruit in coconut milk)
    
    *Soup-based:*
    - Tinola (ginger chicken soup)
    - Nilagang baka (boiled beef soup)
    - Bulalo (beef marrow soup)
    - Sinigang (sour soup varieties)
    - Pochero (Filipino beef stew)
    - Molo soup (wonton soup)
    
    *Stewed/Braised:*
    - Kare-kare (peanut stew)
    - Menudo (pork and liver stew)
    - Afritada (chicken/pork in tomato sauce)
    - Mechado (beef stew)
    - Kaldereta (goat/beef stew)
    - Asado (sweet braised pork)
    - Humba (Visayan pork stew)
    - Paksiw na isda/baboy (fish/pork in vinegar)
    
    *Grilled/Baked/Lightly Fried:*
    - Pritong isda (pan-fried fish - tilapia, bangus, galunggong)
    - Pritong manok (pan-fried chicken)
    - Inihaw na liempo (grilled pork)
    - Inihaw na manok (grilled chicken)
    - Inihaw na isda (grilled fish)
    - Pinaputok na bangus (steamed-grilled milkfish)
    - Baked chicken
    - Grilled seafood
    
    *Sautéed (Ginisa):*
    - Ginisang ampalaya at itlog (sauteed bitter melon with egg)
    - Ginisang monggo (sauteed mung beans)
    - Ginisang kangkong (sauteed water spinach)
    - Ginisang sitaw at kalabasa (sauteed string beans and squash)
    - Ginisang repolyo (sauteed cabbage)
    - Ginisang togue (sauteed bean sprouts)
    - Chopsuey (mixed vegetables stir-fry)
    
    *Vegetables:*
    - Pinakbet (vegetable stew with bagoong)
    - Dinengdeng (vegetable soup with bagoong)
    - Bulanglang (vegetable soup)
    - Ensaladang talong (eggplant salad)
    - Ensaladang mangga (green mango salad)
    - Atchara (pickled papaya)
    
    *Egg Dishes (Torta):*
    - Tortang talong (eggplant omelette)
    - Tortang giniling (ground meat omelette)
    - Tortang alamang (shrimp paste omelette)
    - Tortang dulong (silverfish omelette)
    - Pritong itlog (fried egg)
    
    *Noodle Dishes:*
    - Pancit canton (stir-fried wheat noodles)
    - Pancit bihon (rice noodles)
    - Pancit palabok (noodles with shrimp sauce)
    - Pancit malabon (seafood noodles)
    - Lomi (thick egg noodle soup)
    - Sotanghon (glass noodles)
    - Pancit luglog (similar to palabok)
    
    *Modern Filipino Fusion:*
    - Chicken curry (Filipino-style)
    - Sweet and sour fish/pork
    - Baked tahong (baked mussels)
    - Rellenong bangus (stuffed milkfish)
    - Embutido (Filipino meatloaf)
    - Morcon (beef roll)
    - Fish fillet (breaded/fried)
    
    **Snacks (Meryenda) Options - Sweet & Savory:**
    
    *Sweet Snacks (Natural & Healthy):*
    - Turon (banana spring rolls)
    - Banana cue (caramelized banana - moderate sugar)
    - Kamote cue (caramelized sweet potato - moderate sugar)
    - Maruya (banana fritters)
    - Ginataang mais (corn in coconut milk)
    - Ginataang saging (banana in coconut milk)
    - Puto (steamed rice cake)
    - Kutsinta (brown rice cake)
    - Sapin-sapin (layered rice cake)
    - Suman (sticky rice wrapped in banana leaves)
    - Biko (sweet rice cake)
    - Palitaw (sweet rice cakes)
    - Bibingka (rice cake)
    - Puto bumbong (purple rice cake)
    - Maja blanca (coconut pudding)
    - Ube halaya (purple yam jam)
    - Ginataan (various fruits/vegetables in coconut milk)
    - Buko salad (young coconut salad)
    - Fresh fruits: Saging, mangga, papaya, suha, dalandan, pakwan, melon
    - Pinaupong saging (steamed banana)
    - Nilupak (mashed cassava/sweet potato/banana)
    - Fresh fruit salad
    - Steamed sweet potato
    - Boiled saging na saba
    
    *Savory Snacks (Healthy Options):*
    - Lugaw (rice porridge)
    - Arroz caldo (chicken porridge)
    - Pancit (various noodle dishes)
    - Lumpia (spring rolls - fresh/fried)
    - Okoy (shrimp fritters)
    - Ukoy (vegetable fritters)
    - Sopas (Filipino chicken macaroni soup)
    - Champorado with tuyo
    - Pandesal with various healthy fillings
    - Monay (bread)
    - Steamed siopao (if homemade)
    - Vegetable lumpia
    - Fresh lumpia with peanut sauce
    
    **PREPARATION METHODS IN TAGALOG (Comprehensive List):**
    - **Pritong** = Fried (e.g., pritong tilapia, pritong itlog, pritong manok)
    - **Nilagang** = Boiled (e.g., nilagang saging, nilagang mais, nilagang baka)
    - **Ginisang** = Sautéed (e.g., ginisang kangkong, ginisang monggo, ginisang gulay)
    - **Ginataang** = Cooked in coconut milk (e.g., ginataang kalabasa, ginataang hipon)
    - **Inihaw** = Grilled (e.g., inihaw na manok, inihaw na liempo, inihaw na isda)
    - **Haluing** = Mashed/pureed (e.g., haluing saging para sa bata, haluing kalabasa)
    - **Sinangag** = Fried rice
    - **Hinog** = Ripe (e.g., hinog na saging, hinog na mangga)
    - **Piniritong** = Deep-fried (e.g., piniritong lumpia, piniritong manok)
    - **Pinakuluang** = Boiled/simmered (e.g., pinakuluang itlog, pinakuluang saging)
    - **Sinaing** = Steamed/cooked (e.g., sinaing na isda, sinaing na kanin)
    - **Ihaw/Inihaw** = Grilled/charcoal-grilled
    - **Pinaksiw** = Cooked in vinegar (e.g., paksiw na isda, paksiw na lechon)
    - **Binalot** = Wrapped (e.g., binalot na suman)
    - **Nilasing** = Cooked with alcohol (e.g., nilasing na hipon)
    - **Pinaupong** = Steamed upright (e.g., pinaupong saging)
    - **Sinabawan** = In broth/soupy (e.g., sinabawang gulay)
    - **Tinapa** = Smoked (e.g., tinapa na bangus)
    - **Tuyong** = Dried (e.g., tuyong isda, tuyong pusit)
    - **Sinaligang** = Made into sinigang/sour soup
    - **Inadobo** = Made into adobo
    - **Kinare-kare** = Made into kare-kare style
    - **Rebosado** = Battered (e.g., rebosadong isda)
    - **Relleno/Rellenong** = Stuffed (e.g., rellenong bangus, rellenong manok)
    - **Escabetse/Eskabetse** = Sweet and sour style
    - **Binabad** = Marinated
    - **Sinangkutsa** = With tomato sauce
    - **Sinarsahan** = With sarsang sauce
    - **Kinamatisan** = With tomatoes
    
    **AGE-APPROPRIATE FILIPINO FOOD TEXTURES:**
    - 6-8 months: Lugaw, haluing saging/papaya/kalabasa, sopas na may durog na gulay
    - 9-11 months: Lugaw with small pieces, mashed rice with ulam, soft pancit
    - 12-23 months: Kanin with finely chopped ulam, sopas, soft fruits
    - 24-59 months: Regular family foods, chopped to appropriate size

    ### REGULAR NA OBSERBAHAN (MUST include this exact header)
    
    **Araw-Araw** (MUST include this exact subheader):
    - Gana kumain (Mabuti/Katamtaman/Hindi mabuti) - Sigla ng bata - Tulog (mahimbing ba?) - Dumi (normal ba?)

    **Bawat Linggo** (MUST include this exact subheader):
    - Timbang - Paglaki - Bagong natututunan

    **Bawat Buwan**:
    - Taas - Gustong pagkain - Pagiging independent sa pagkain

    ## BALANSENG PAGKAIN PARA SA BATA

    Bawat pagkain dapat may:
    - Pagkain na nagbibigay lakas at enerhiya (tulad ng kanin, tinapay, mais)
    - Pagkain na tumutulong sa paglaki ng bata (tulad ng isda, manok, itlog)
    - Pagkain na nagpapalakas ng katawan (tulad ng gulay, prutas)
    - Tubig - laging importante

    {filipino_context}
    
    {seasonal_context}

    ### FINAL VERIFICATION CHECKLIST:
    ✓ CHILD PROFILE section is included with exact header
    ✓ 7-DAY MEAL PLAN section is included with exact header
    ✓ All 7 days (Day 1 Monday, Day 2 Tuesday, Day 3 Wednesday, Day 4 Thursday, Day 5 Friday, Day 6 Saturday, Day 7 Sunday) are included
    ✓ Each day has Breakfast (Almusal), Lunch (Tanghalian), Snack (Meryenda), Dinner (Hapunan)
    ✓ REGULAR NA OBSERBAHAN section with Araw-Araw and Bawat Linggo subheaders
    ✓ Entire response is in simple Tagalog
    ✓ Only database foods are recommended
    ✓ NO SINGLE DISH OR MAIN INGREDIENT IS REPEATED ACROSS ANY OF THE 7 DAYS (MANDATORY - Double check this!)
    ✓ All dishes are Filipino traditional cuisine that ordinary Filipinos eat regularly
    ✓ All dishes use Tagalog names and preparation methods
    ✓ No calorie numbers included
    ✓ Allergies ({allergies}) are avoided
    ✓ Religious restrictions ({religion}) are respected
    
    **DIVERSITY REQUIREMENTS:**
    - Use at least 10 different protein sources across 7 days (e.g., manok, baboy, isda, itlog, hipon, pusit, baka, etc.)
    - Use at least 15 different vegetables/fruits across 7 days
    - Vary cooking methods: adobo, sinigang, tinola, ginisa, prito, inihaw, nilaga, ginataan
    - Each breakfast should be unique (no repeating lugaw, champorado, etc.)
    - Each snack should be unique (turon one day, banana cue another, puto another, etc.)
    - Ensure traditional Filipino dishes like sinigang, adobo, tinola, kare-kare, pinakbet appear across different days
    - Main ingredients (like chicken, pork, fish) should be prepared differently each time they appear"""
        
    prompt_template = PromptTemplate(
        input_variables=["food_list_str", "pdf_context", "nutrition_analysis", "age_months", "weight_kg", "height_cm", "bmi_for_age", "allergies", "other_medical_problems", "religion", "available_ingredients", "nutrition_tags", "age_guidelines", "allergy_section", "religion_section", "filipino_context", "seasonal_context"],
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
        "available_ingredients": available_ingredients if available_ingredients else "None specified",
        "nutrition_tags": nutrition_tags_str,
        "age_guidelines": age_guidelines,
        "allergy_section": allergy_section,
        "religion_section": religion_section,
        "filipino_context": filipino_context,
        "seasonal_context": seasonal_context
    }

    llm = create_nutrition_llm()

    # Create runnable sequence using modern LangChain pattern
    chain = prompt_template | llm

    result = chain.invoke(prompt_inputs)
    
    # Handle AIMessage objects by extracting content
    if hasattr(result, 'content'):
        result = result.content
    
    # Ensure we have a string
    if not isinstance(result, str):
        result = str(result)
    
    # Validate the meal plan
    is_valid, message = validate_meal_plan_variety(result)
    diversity = calculate_diversity_score(result)
    ingredients = extract_ingredients_from_plan(result)
    
    # Print validation results
    print("\n" + "="*50)
    print("MEAL PLAN QUALITY ASSESSMENT")
    print("="*50)
    print(f"✓ Variety Check: {message}")
    print(f"✓ Diversity Score: {diversity['score']}/100")
    print(f"  - Unique Dishes: {diversity['unique_dishes']}/{diversity['total_dishes']}")
    print(f"  - Cooking Methods: {diversity['method_variety']} ({', '.join(diversity['cooking_methods_used'])})")
    print(f"✓ Ingredients Used: {len(ingredients)} types")
    if ingredients:
        print(f"  - {', '.join([f'{k}({v})' for k, v in list(ingredients.items())[:10]])}")
    if not is_valid:
        print(f"⚠ WARNING: {message}")
    if diversity['score'] < 70:
        print(f"⚠ WARNING: Low diversity score. Consider regenerating for more variety.")
    print("="*50 + "\n")
    
    return result