"""
Feeding Program Chain for Nutritionist Role
============================================
This module handles AI-powered meal planning specifically for feeding programs,
designed for nutritionists managing multiple patients in a structured feeding program.

Key Differences from Parent Meal Plans (nutrition_chain.py):
- Batch meal planning for multiple children
- Budget-conscious ingredient selection
- Community-level food availability
- Standardized portions for feeding programs
- Focus on cost-effectiveness and scalability
- Group-based nutritional targets
"""

from langchain_core.prompts import PromptTemplate
from langchain_core.runnables import RunnableSequence
from langchain_groq import ChatGroq
from pydantic import SecretStr
import re
import os
import logging
import time
from typing import Optional, Dict, Any, List
from dotenv import load_dotenv
from data_manager import data_manager
from datetime import datetime, timedelta

load_dotenv()

# Configure logging for production
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


def create_feeding_program_llm():
    """Create a standardized ChatGroq instance for feeding program functions."""
    api_key = os.getenv('GROQ_API_KEY')
    if not api_key:
        logger.error("GROQ_API_KEY not found in environment variables")
        raise ValueError("GROQ_API_KEY not found in environment variables")
    
    try:
        return ChatGroq(
            api_key=SecretStr(api_key),
            model="meta-llama/llama-4-scout-17b-16e-instruct",
            temperature=0.3,
            max_tokens=3000,
            timeout=120,  # 2 minute timeout for production
            max_retries=2  # Retry failed requests
        )
    except Exception as e:
        logger.error(f"Failed to create ChatGroq instance: {str(e)}")
        raise


def calculate_batch_nutritional_needs(patients_data):
    """
    Calculate aggregated nutritional needs for a group of patients.
    
    Args:
        patients_data: List of patient dictionaries with nutritional information
        
    Returns:
        Dictionary with aggregated nutritional requirements
    """
    age_groups = {
        '0-6_months': [],
        '6-12_months': [],
        '12-24_months': [],
        '24-60_months': []
    }
    
    nutritional_priorities = {
        'underweight': 0,
        'stunted': 0,
        'wasted': 0,
        'overweight': 0,
        'normal': 0
    }
    
    common_allergies = {}
    medical_conditions = {}
    
    for patient in patients_data:
        age_months = patient.get('age_months', 0)
        
        # Categorize by age group
        if age_months <= 6:
            age_groups['0-6_months'].append(patient)
        elif age_months <= 12:
            age_groups['6-12_months'].append(patient)
        elif age_months <= 24:
            age_groups['12-24_months'].append(patient)
        else:
            age_groups['24-60_months'].append(patient)
        
        # Track nutritional status
        weight_status = patient.get('weight_for_age', '').lower()
        height_status = patient.get('height_for_age', '').lower()
        
        if 'underweight' in weight_status:
            nutritional_priorities['underweight'] += 1
        elif 'overweight' in weight_status:
            nutritional_priorities['overweight'] += 1
        else:
            nutritional_priorities['normal'] += 1
            
        if 'stunted' in height_status or 'short' in height_status:
            nutritional_priorities['stunted'] += 1
        
        if 'wasted' in weight_status or 'severely underweight' in weight_status:
            nutritional_priorities['wasted'] += 1
        
        # Track allergies
        allergies = patient.get('allergies', '')
        if allergies and allergies.lower() not in ['none', 'no', 'n/a', 'not specified']:
            if allergies in common_allergies:
                common_allergies[allergies] += 1
            else:
                common_allergies[allergies] = 1
        
        # Track medical conditions
        conditions = patient.get('other_medical_problems', '')
        if conditions and conditions.lower() not in ['none', 'no', 'n/a', 'not specified']:
            if conditions in medical_conditions:
                medical_conditions[conditions] += 1
            else:
                medical_conditions[conditions] = 1
    
    return {
        'age_groups': age_groups,
        'nutritional_priorities': nutritional_priorities,
        'common_allergies': common_allergies,
        'medical_conditions': medical_conditions,
        'total_patients': len(patients_data)
    }


def get_feeding_program_budget_context(budget_level='moderate'):
    """
    Provide food recommendations based on feeding program budget constraints.
    
    Args:
        budget_level: 'low', 'moderate', or 'high'
    """
    budget_contexts = {
        'low': {
            'proteins': ['itlog', 'monggo', 'galunggong', 'tuyo', 'dilis', 'tokwa'],
            'vegetables': ['kangkong', 'kamote tops', 'malunggay', 'ampalaya', 'sitaw', 'talong', 'kalabasa'],
            'grains': ['bigas', 'mais', 'whole grain miswa', 'sotanghon'],
            'fruits': ['saging', 'papaya', 'kamote', 'mango (in season)', 'watermelon'],
            'focus': 'Cost-effective, locally available, whole ingredients'
        },
        'moderate': {
            'proteins': ['manok', 'bangus', 'tilapia', 'itlog', 'monggo', 'baboy (select cuts)', 'beef'],
            'vegetables': ['kangkong', 'malunggay', 'kalabasa', 'talong', 'sitaw', 'repolyo', 'carrots', 'sayote'],
            'grains': ['bigas', 'whole grain oatmeal', 'whole wheat pandesal', 'brown rice'],
            'fruits': ['saging', 'papaya', 'mangga (in season)', 'dalandan', 'bayabas', 'pineapple'],
            'focus': 'Balanced nutrition with minimally processed ingredients'
        },
        'high': {
            'proteins': ['manok', 'bangus', 'tilapia', 'salmon', 'baka', 'baboy', 'hipon', 'itlog', 'organic meats'],
            'vegetables': ['all varieties including broccoli', 'carrots', 'bell peppers', 'lettuce', 'organic vegetables'],
            'grains': ['brown rice', 'whole wheat bread', 'whole grain oatmeal', 'quinoa'],
            'fruits': ['imported fruits', 'berries', 'avocado', 'all local fruits', 'organic fruits'],
            'focus': 'Optimal nutrition using whole, minimally processed ingredients'
        }
    }
    
    return budget_contexts.get(budget_level, budget_contexts['moderate'])


def generate_feeding_program_meal_plan(
    target_age_group: str = 'all',
    program_duration_days: int = 7,
    budget_level: str = 'moderate',
    available_ingredients: Optional[str] = None,
    barangay: Optional[str] = None,
    total_children: Optional[int] = None
) -> Dict[str, Any]:
    """
    Generate a GENERIC meal plan for a feeding program focused on Filipino children.
    This creates a standardized meal plan suitable for community feeding programs.
    
    Args:
        target_age_group: Age group focus ('all', '6-12months', '12-24months', '24-60months')
        program_duration_days: Number of days for the feeding program (1-5)
        budget_level: 'low', 'moderate', or 'high'
        available_ingredients: Optional list of available ingredients
        barangay: Barangay name for location-specific recommendations
        total_children: Estimated number of children (for shopping list quantities)
        
    Returns:
        Dict containing success status, meal plan, and metadata
        
    Raises:
        ValueError: If input parameters are invalid
    """
    
    # Input validation
    valid_age_groups = ['all', '6-12months', '12-24months', '24-60months']
    valid_budget_levels = ['low', 'moderate', 'high']
    
    if target_age_group not in valid_age_groups:
        logger.warning(f"Invalid age group '{target_age_group}', defaulting to 'all'")
        target_age_group = 'all'
    
    if budget_level not in valid_budget_levels:
        logger.warning(f"Invalid budget level '{budget_level}', defaulting to 'moderate'")
        budget_level = 'moderate'
    
    if not (1 <= program_duration_days <= 5):
        logger.error(f"Invalid program duration: {program_duration_days}. Must be 1-5 days")
        return {
            'success': False,
            'error': 'Program duration must be between 1 and 5 days',
            'meal_plan': None
        }
    
    if total_children is not None and total_children <= 0:
        logger.warning(f"Invalid total_children: {total_children}, setting to None")
        total_children = None
    
    logger.info(f"Generating meal plan: age={target_age_group}, days={program_duration_days}, budget={budget_level}, barangay={barangay}")
    
    # Create generic batch analysis based on age group
    batch_analysis = {
        'target_age_group': target_age_group,
        'total_children': total_children or 'Not specified',
        'focus': 'Generic feeding program for Filipino children'
    }
    
    # Get budget context
    budget_context = get_feeding_program_budget_context(budget_level)
    
    # Get knowledge base context for feeding programs
    from embedding_utils import embedding_searcher
    
    # Create targeted query for feeding program guidance
    feeding_queries = [
        "community feeding program nutrition guidelines Filipino children",
        "batch meal planning nutritional requirements",
        "cost effective nutrition intervention programs",
        "malnutrition prevention feeding programs Philippines"
    ]
    
    combined_query = " ".join(feeding_queries)
    
    try:
        search_results = embedding_searcher.search_similar_chunks(combined_query, k=5)
        unique_chunks = []
        seen = set()
        
        for chunk, score, metadata in search_results:
            if score > 0.4 and chunk not in seen:
                seen.add(chunk)
                source_info = f" (Source: {metadata.get('pdf_name', 'Unknown')})" if metadata.get('pdf_name') else ""
                unique_chunks.append(f"{chunk.strip()}{source_info}")
        
        pdf_context = ""
        if unique_chunks:
            pdf_context = f"\nEVIDENCE-BASED FEEDING PROGRAM GUIDANCE:\n" + "\n---\n".join(unique_chunks[:5])
            logger.info(f"Retrieved {len(unique_chunks)} relevant guidance chunks")
    except Exception as e:
        logger.error(f"Error retrieving feeding program guidance: {str(e)}")
        pdf_context = ""
    
    # Get food database
    foods_data = data_manager.get_foods_data()
    food_names = [f.get('food_name_and_description', '') for f in foods_data if f.get('food_name_and_description')]
    food_list_str = '\n- '.join(food_names[:100]) if food_names else 'Use common Filipino ingredients'  # Limit to avoid token overflow
    
    # Age group descriptions
    age_group_info = {
        'all': 'Mixed age groups (6 months - 5 years) - provide adaptations for all',
        '6-12months': 'Infants (6-12 months) - focus on pureed/mashed foods',
        '12-24months': 'Toddlers (12-24 months) - soft, small pieces',
        '24-60months': 'Preschoolers (24-60 months) - regular textures'
    }
    
    target_description = age_group_info.get(target_age_group, age_group_info['all'])
    
    # Build the prompt
    prompt_template = f"""You are a Pediatric Nutritionist designing a GENERIC FEEDING PROGRAM meal plan for Filipino children.

FEEDING PROGRAM OVERVIEW:
========================
- Target Population: Filipino children in community feeding programs
- Target Age Group: {target_description}
- Estimated Children: {total_children or 'Variable (design for scalability)'}
- Program Duration: {program_duration_days} days
- Budget Level: {budget_level.upper()}
- Location: {barangay or 'General Philippines'}

═══════════════════════════════════════════════════════════════════
🔴 INGREDIENT PRIORITIZATION RULES
═══════════════════════════════════════════════════════════════════

AVAILABLE INGREDIENTS:
{available_ingredients if available_ingredients else '❌ None specified'}

{'⚠️ MANDATORY REQUIREMENT - AVAILABLE INGREDIENTS HAVE ABSOLUTE PRIORITY:' if available_ingredients else '✅ NO SPECIFIC INGREDIENTS PROVIDED:'}
{'''- You MUST use the available ingredients listed above as the PRIMARY ingredients
- Build ALL meals around the available ingredients listed above
- Every meal MUST prominently feature available ingredients as main components
- Design dishes specifically to showcase the available ingredients
- Examples:
  * If "manok, kangkong, kamote" → Plan "Tinolang Manok with Kangkong" 
  * If "bangus, sitaw, kalabasa" → Plan "Sinigang na Bangus with Sitaw and Kalabasa"
- Only supplement with budget recommendations if available ingredients alone are insufficient
- DO NOT ignore available ingredients in favor of budget recommendations
- Track which available ingredients you've used to ensure all are utilized''' if available_ingredients else '''- Use the budget recommendations below as your ingredient guide
- Select ingredients appropriate for the budget level
- Focus on cost-effective, nutritious, locally available Filipino ingredients
- Prioritize seasonal ingredients for better value
- Design varied meals using the recommended ingredient categories'''}

BUDGET CONSTRAINTS ({budget_level}):
- Focus: {budget_context['focus']}
- Recommended Proteins: {', '.join(budget_context['proteins'])}
- Recommended Vegetables: {', '.join(budget_context['vegetables'])}
- Recommended Grains: {', '.join(budget_context['grains'])}
- Recommended Fruits: {', '.join(budget_context['fruits'])}

FOOD DATABASE:
{food_list_str}

{pdf_context}

═══════════════════════════════════════════════════════════════════
YOUR TASK: Create a {program_duration_days}-day GENERIC FEEDING PROGRAM meal plan
═══════════════════════════════════════════════════════════════════

CRITICAL REQUIREMENTS:

1. **🔴 PRIORITIZE AVAILABLE INGREDIENTS (ABSOLUTE PRIORITY):**
   - If available ingredients are specified, they are the FOUNDATION of your meal plan
   - Each meal MUST prominently feature available ingredients as main components
   - Design dishes specifically around the available ingredients
   - Examples:
     * If "manok, kangkong, kamote" are available → Plan "Tinolang Manok with Kangkong" NOT generic chicken dishes
     * If "bangus, sitaw, kalabasa" are available → Plan "Sinigang na Bangus with Sitaw and Kalabasa"
   - Only use budget recommendations as SUPPLEMENTS, not replacements
   - Track which available ingredients you've used to ensure all are utilized throughout the program

2. **NO DISH REPETITION (MANDATORY):**
   - Each meal across the ENTIRE {program_duration_days}-day period must be UNIQUE
   - Example: If "Tinolang Manok" appears on Day 1, it CANNOT appear anywhere else
   - Track all dishes to ensure zero repetition
   - Use different cooking methods for same ingredients (Adobo, Sinigang, Tinola, Prito, Ginisa, etc.)

3. **BATCH FEEDING FORMAT:**
   - Design meals for large-scale preparation (50-100+ children)
   - Simple cooking methods suitable for community kitchens
   - Ingredients that are easy to purchase in bulk
   - Scalable portions (provide quantities per 50 children)

4. **AGE-APPROPRIATE VARIATIONS:**
   - Provide texture modifications for each meal:
     * 6-12 months: Pureed/mashed consistency
     * 12-24 months: Soft, small pieces (finger foods)
     * 24-60 months: Regular family food texture

5. **NUTRITIONAL BALANCE:**
   - Meet general nutritional needs for Filipino children
   - Include iron-rich foods (e.g., malunggay, liver, monggo)
   - Include calcium sources (e.g., milk, dilis, malunggay)
   - Include Vitamin A sources (e.g., kalabasa, carrots, papaya)
   - Ensure adequate protein, carbohydrates, and healthy fats

6. **BUDGET CONSCIOUSNESS:**
   - Prioritize ingredients from the budget-recommended list
   - Use seasonal, locally available ingredients
   - Minimize food waste through smart planning
   - Cost-effective protein sources (monggo, itlog, galunggong)

6.5. **🚫 PROHIBITED INGREDIENTS - STRICTLY AVOID:**
   - **NO processed meats:** hotdog, spam, luncheon meat, ham, bacon, sausage
   - **NO canned goods:** canned sardines, canned tuna, canned corned beef, canned pork and beans
   - **NO instant/processed foods:** instant noodles, instant mami, cup noodles, 3-in-1 beverages
   - **NO processed snacks:** chips, crackers, cookies, candy, instant pancit canton
   - **NO artificial drinks:** powdered juice drinks, soda, artificial flavored beverages
   - **NO MSG-heavy seasonings:** magic sarap, ajinomoto, artificial bouillon cubes
   - **EXCEPTION:** Traditional preserved foods (tuyo, dilis, bagoong) are acceptable in moderation as traditional Filipino ingredients
   - **USE INSTEAD:** Whole meats, whole fish, whole grains, natural seasonings (garlic, onion, ginger, herbs)
   - **FOCUS ON:** Whole, unprocessed ingredients prepared from scratch

7. **FILIPINO CUISINE FOCUS - SPECIFIC COMPLETE DISHES ONLY:**
   - Use ONLY traditional, complete Filipino dishes with proper names
   - **AVOID generic descriptions** like "sinangag na kanin na may hito" or "sinangag na itlog"
   - **USE SPECIFIC DISH NAMES** with complete preparations:
   
   **Breakfast Examples:**
   - Lugaw (specify: Arroz Caldo with Chicken, Goto with Beef Tripe, Lugaw with Tokwa)
   - Champorado with Tuyo or Dilis (traditional preserved fish acceptable)
   - Tocilog (homemade Tocino, sinangag, itlog)
   - Tapsilog (Beef Tapa, sinangag, itlog)
   - Longsilog (homemade Longganisa, sinangag, itlog)
   - Bangsilog (Bangus, sinangag, itlog)
   - Whole Wheat Pandesal with Palaman (peanut butter, cheese)
   - ❌ AVOID: Cornsilog (uses canned corned beef), instant breakfast items
   
   **Lunch/Dinner Examples:**
   - Adobong Manok (chicken adobo with complete sauce)
   - Sinigang na Baboy sa Sampalok
   - Tinolang Manok with Malunggay
   - Nilagang Baka with Vegetables
   - Ginataang Kalabasa at Sitaw
   - Pakbet (Pinakbet)
   - Bicol Express
   - Kare-Kare
   - Menudo
   - Afritada
   - Mechado
   
   **Snacks Examples:**
   - Turon na Saging (banana spring rolls)
   - Banana Cue
   - Ginataang Mais with Sago
   - Puto Pao
   - Puto with Cheese
   - Palitaw
   - Biko
   - Sapin-Sapin
   
   **Fish Dishes (complete preparations):**
   - Pritong Bangus (fried milkfish)
   - Sinigang na Bangus
   - Rellenong Bangus (stuffed milkfish)
   - Inihaw na Tilapia
   - Paksiw na Isda
   - Escabeche

8. **MEAL VARIETY & DIVERSITY:**
   - Rotate proteins: Manok, Isda (bangus, tilapia, galunggong), Itlog, Monggo, Baboy
   - Rotate vegetables: Kangkong, Malunggay, Kalabasa, Sitaw, Talong, Ampalaya
   - Vary cooking methods each day (Adobo, Sinigang, Tinola, Pritong, Ginisa, Nilaga, Ginataang)
   - Different **COMPLETE DISHES** for breakfast, lunch, snack, and dinner EVERY DAY
   - Each dish must be a recognized Filipino recipe, not a combination of ingredients

9. **PRACTICAL IMPLEMENTATION:**
   - Include simple preparation instructions
   - Consider food safety for batch cooking
   - Specify storage and reheating guidelines if needed
   - Each meal must be a **complete, recognizable Filipino dish**

**FORBIDDEN OUTPUT EXAMPLES (DO NOT USE):**
❌ "Sinangag na kanin na may hito" (too generic)
❌ "Sinangag na itlog" (incomplete dish name)
❌ "Kanin at isda" (not a specific dish)
❌ "Pritong itlog with rice" (use proper silog name instead)
❌ "Cornsilog" (uses canned corned beef - PROHIBITED)
❌ "Instant Pancit Canton" (processed - PROHIBITED)
❌ "Spam Fried Rice" (processed meat - PROHIBITED)
❌ "Sardinas con Huevo" (canned sardines - PROHIBITED)
❌ "Hotsilog" (processed hotdog - PROHIBITED)

**CORRECT OUTPUT EXAMPLES (USE THESE):**
✅ "Bangsilog (Pritong Bangus, Sinangag, Itlog)"
✅ "Arroz Caldo with Chicken and Egg"
✅ "Adobong Manok sa Gata"
✅ "Sinigang na Tilapia sa Miso"
✅ "Tocilog (Homemade Tocino, Garlic Fried Rice, Sunny-Side Up Egg)"
✅ "Lugaw with Tokwa and Egg"

OUTPUT FORMAT - JSON STRUCTURE:

🔴 CRITICAL: You MUST respond with VALID JSON ONLY. No markdown, no explanations, just pure JSON.

Return a JSON object with this exact structure:

{{
  "meal_plan": [
    {{
      "day": 1,
      "meals": [
        {{
          "meal_type": "breakfast",
          "dish_name": "Lugaw with Chicken and Egg",
          "ingredients": [
            "5 cups uncooked rice",
            "1.5 kg chicken breast or thighs, diced",
            "10 eggs, beaten",
            "2 cups mixed vegetables (carrots, peas)",
            "4 cups chicken broth",
            "2 tablespoons vegetable oil",
            "Salt and pepper to taste"
          ]
        }},
        {{
          "meal_type": "lunch",
          "dish_name": "Adobong Manok",
          "ingredients": [
            "1.5 kg chicken breast or thighs, cut into bite-sized pieces",
            "1/2 cup vinegar",
            "1/2 cup soy sauce",
            "4 cloves garlic, minced",
            "1 tablespoon vegetable oil",
            "Salt and pepper to taste"
          ]
        }},
        {{
          "meal_type": "snack",
          "dish_name": "Turon na Saging",
          "ingredients": [
            "50 pieces saba (plantain) sliced",
            "1 pack spring roll wrappers",
            "1/2 cup vegetable oil",
            "1/2 cup sugar"
          ]
        }},
        {{
          "meal_type": "dinner",
          "dish_name": "Sinigang na Bangus",
          "ingredients": [
            "10 pieces bangus (milkfish)",
            "2 cups tamarind broth",
            "1 cup mixed vegetables (sitaw, kangkong)",
            "Salt and pepper to taste"
          ]
        }}
      ]
    }},
    {{
      "day": 2,
      "meals": [
        ... (continue for all {program_duration_days} days)
      ]
    }}
  ],
  "shopping_list": {{
    "rice": "15 cups",
    "chicken": "4.5 kg",
    "eggs": "20 pieces"
  }},
  "nutritional_summary": "Balanced nutrition with protein, carbohydrates, and vegetables for Filipino children."
}}

⚠️ CRITICAL JSON RULES:
1. Return ONLY valid JSON - no markdown, no code blocks, no extra text
2. Use "breakfast", "lunch", "snack", "dinner" for meal_type (lowercase)
3. Each ingredient is a simple string in the array
4. Include ALL {program_duration_days} days
5. Ensure proper JSON escaping for quotes
6. Keep it clean and parseable

BEGIN JSON OUTPUT:
- Ingredients: [List]
- Preparation Method: [Steps]
- Age Adaptations: [Same format]
- Portions: [Amount]

**Hapunan (Dinner):**
- Main Dish: [COMPLETE Filipino dish name - e.g., "Tinolang Manok with Malunggay and Papaya"]
- Description: [Brief description]
- Ingredients: [List with measurements]
- Preparation Method: [Brief steps]
- Age Adaptations: [Same format]
- Approximate Portions: [Portions]

---

## Day 2

[Continue same format for remaining days]

⚠️ FORMATTING REQUIREMENTS:
1. Use ## for day headers (## Day 1, ## Day 2, etc.)
2. Use **Meal Name:** format (Almusal, Tanghalian, Meryenda, Hapunan)
3. ALWAYS use "- Main Dish:" followed by the dish name
4. ALWAYS use "- Ingredients (for 50 children):" as header
5. List each ingredient on separate line with "  - " (2 spaces, dash, space)
6. Keep ingredient lists clean - just quantity and item name
7. Use --- to separate days
8. NO extra asterisks, NO embedded markdown formatting
9. Keep consistent spacing and indentation

## Weekly Shopping List
[Consolidated ingredient list with estimated quantities]

## Preparation Tips for Batch Cooking
[Practical tips for feeding program staff]

## Nutritional Summary
[Brief overview of how the week's menu addresses the group's nutritional needs]

BEGIN MEAL PLAN:
"""
    
    # Create LLM and generate with retry logic
    max_retries = 3
    retry_delay = 2  # seconds
    
    for attempt in range(max_retries):
        try:
            logger.info(f"Attempting meal plan generation (attempt {attempt + 1}/{max_retries})")
            llm = create_feeding_program_llm()
            
            start_time = time.time()
            response = llm.invoke(prompt_template)
            generation_time = time.time() - start_time
            
            meal_plan_content = response.content if hasattr(response, 'content') else str(response)
            
            # Validate response
            if not meal_plan_content or len(meal_plan_content) < 100:
                logger.warning(f"Generated meal plan too short ({len(meal_plan_content)} chars)")
                if attempt < max_retries - 1:
                    time.sleep(retry_delay)
                    continue
            
            logger.info(f"Meal plan generated successfully in {generation_time:.2f}s")
            
            return {
                'success': True,
                'meal_plan': meal_plan_content,
                'batch_analysis': batch_analysis,
                'target_age_group': target_age_group,
                'program_duration_days': program_duration_days,
                'budget_level': budget_level,
                'barangay': barangay,
                'total_children': total_children,
                'available_ingredients': available_ingredients,
                'generated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
                'generation_time_seconds': round(generation_time, 2)
            }
        
        except Exception as e:
            logger.error(f"Attempt {attempt + 1} failed: {str(e)}")
            
            if attempt < max_retries - 1:
                logger.info(f"Retrying in {retry_delay} seconds...")
                time.sleep(retry_delay)
                retry_delay *= 2  # Exponential backoff
            else:
                logger.error(f"All {max_retries} attempts failed")
                return {
                    'success': False,
                    'error': f'Failed after {max_retries} attempts: {str(e)}',
                    'meal_plan': None
                }
    
    # Fallback return (should never reach here, but ensures all paths return)
    logger.error("Unexpected code path - no meal plan generated")
    return {
        'success': False,
        'error': 'Unexpected error: meal plan generation failed',
        'meal_plan': None
    }


def generate_feeding_program_assessment(
    target_age_group: str = 'all',
    barangay: Optional[str] = None,
    total_children: Optional[int] = None
) -> Dict[str, Any]:
    """
    Generate a generic assessment report for a feeding program.
    
    Args:
        target_age_group: Age group focus
        barangay: Barangay name
        total_children: Estimated number of children
        
    Returns:
        Dict containing success status and assessment content
    """
    
    logger.info(f"Generating feeding program assessment for {target_age_group}")
    
    # Get knowledge base context
    from embedding_utils import embedding_searcher
    
    query = "community nutrition assessment feeding program evaluation children malnutrition Philippines"
    
    try:
        search_results = embedding_searcher.search_similar_chunks(query, k=4)
        pdf_context = "\n".join([chunk for chunk, score, _ in search_results if score > 0.4])
        logger.info("Successfully retrieved assessment guidance from knowledge base")
    except Exception as e:
        logger.error(f"Error retrieving assessment guidance: {str(e)}")
        pdf_context = ""
    
    age_group_info = {
        'all': 'Mixed age groups (6 months - 5 years)',
        '6-12months': 'Infants (6-12 months)',
        '12-24months': 'Toddlers (12-24 months)',
        '24-60months': 'Preschoolers (24-60 months)'
    }
    
    target_description = age_group_info.get(target_age_group, age_group_info['all'])
    
    prompt_template = f"""You are a Pediatric Nutritionist conducting a FEEDING PROGRAM NEEDS ASSESSMENT for Filipino children.

PROGRAM OVERVIEW:
=================
- Target Population: {target_description}
- Estimated Children: {total_children or 'Variable'}
- Location: {barangay or 'General Philippines'}
- Assessment Date: {datetime.now().strftime('%B %d, %Y')}

EVIDENCE-BASED GUIDANCE:
{pdf_context}

═══════════════════════════════════════════════════════════════════
TASK: Generate a generic feeding program needs assessment
═══════════════════════════════════════════════════════════════════

OUTPUT FORMAT:

# Feeding Program Needs Assessment

## Target Population
[Description of Filipino children typically enrolled in feeding programs]

## Common Nutritional Challenges in the Philippines

### Malnutrition Prevalence
[Overview of common nutritional issues affecting Filipino children 0-5 years]

### Key Nutritional Deficiencies
[Iron, Vitamin A, Iodine, etc.]

## Feeding Program Recommendations

### Meal Frequency and Timing
[Recommended feeding schedule for the target age group]

### Essential Nutrients to Prioritize
[Which nutrients to focus on and why]

### Budget-Friendly Protein Sources
[Filipino protein sources suitable for feeding programs]

### Local Vegetable Recommendations
[Seasonal, affordable vegetables rich in nutrients]

## Program Design Guidelines

### Kitchen Setup Requirements
[Equipment and space needed for batch cooking]

### Food Safety Protocols
[Safe food handling for large-scale preparation]

### Monitoring and Evaluation
[How to track program effectiveness]

## Expected Outcomes
[Realistic nutrition improvement goals]

BEGIN ASSESSMENT:
"""
    
    try:
        llm = create_feeding_program_llm()
        start_time = time.time()
        
        response = llm.invoke(prompt_template)
        generation_time = time.time() - start_time
        
        assessment_content = response.content if hasattr(response, 'content') else str(response)
        
        logger.info(f"Assessment generated successfully in {generation_time:.2f}s")
        
        return {
            'success': True,
            'assessment': assessment_content,
            'target_age_group': target_age_group,
            'generated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
            'generation_time_seconds': round(generation_time, 2)
        }
    
    except Exception as e:
        logger.error(f"Failed to generate assessment: {str(e)}")
        return {
            'success': False,
            'error': str(e),
            'assessment': None
        }


# Helper function to get patients for a feeding program
def get_feeding_program_patients(barangay=None, nutritional_status=None, age_range=None):
    """
    Retrieve patients for a feeding program based on criteria.
    
    Args:
        barangay: Filter by barangay
        nutritional_status: Filter by status ('underweight', 'stunted', etc.)
        age_range: Tuple of (min_months, max_months)
        
    Returns:
        List of patient data dictionaries
    """
    # This would integrate with your data_manager
    # For now, returning a placeholder structure
    # You'll need to implement the actual database query
    
    # Example implementation:
    # patients = data_manager.get_patients_by_criteria(
    #     barangay=barangay,
    #     nutritional_status=nutritional_status,
    #     age_range=age_range
    # )
    
    return []  # Placeholder


if __name__ == "__main__":
    # Example usage
    print("Feeding Program Chain Module Loaded")
    print("=" * 60)
    print("Available functions:")
    print("1. generate_feeding_program_meal_plan()")
    print("2. generate_feeding_program_assessment()")
    print("3. calculate_batch_nutritional_needs()")
    print("4. get_feeding_program_budget_context()")
    print("=" * 60)
