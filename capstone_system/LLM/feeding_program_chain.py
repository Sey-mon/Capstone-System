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
import re
import os
from dotenv import load_dotenv
from data_manager import data_manager
from datetime import datetime, timedelta

load_dotenv()


def create_feeding_program_llm():
    """Create a standardized ChatGroq instance for feeding program functions."""
    api_key = os.getenv('GROQ_API_KEY')
    if not api_key:
        raise ValueError("GROQ_API_KEY not found in environment variables")
    
    return ChatGroq(
        groq_api_key=api_key,
        model_name="meta-llama/llama-4-scout-17b-16e-instruct",
        temperature=0.1,
        max_tokens=2000  # Increased from 1500 to allow more detailed responses
    )


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
            'vegetables': ['kangkong', 'kamote tops', 'malunggay', 'ampalaya', 'sitaw'],
            'grains': ['bigas', 'mais', 'miswa', 'sotanghon'],
            'fruits': ['saging', 'papaya', 'kamote'],
            'focus': 'Cost-effective, locally available ingredients'
        },
        'moderate': {
            'proteins': ['manok', 'bangus', 'tilapia', 'itlog', 'monggo', 'baboy (select cuts)'],
            'vegetables': ['kangkong', 'malunggay', 'kalabasa', 'talong', 'sitaw', 'repolyo', 'carrots'],
            'grains': ['bigas', 'oatmeal', 'pandesal', 'miswa'],
            'fruits': ['saging', 'papaya', 'mangga (in season)', 'dalandan'],
            'focus': 'Balanced nutrition with reasonable cost'
        },
        'high': {
            'proteins': ['manok', 'bangus', 'tilapia', 'salmon', 'baka', 'baboy', 'hipon', 'itlog'],
            'vegetables': ['all varieties including broccoli', 'carrots', 'bell peppers', 'lettuce'],
            'grains': ['brown rice', 'whole wheat bread', 'oatmeal', 'quinoa'],
            'fruits': ['imported fruits', 'berries', 'avocado', 'all local fruits'],
            'focus': 'Optimal nutrition without budget constraints'
        }
    }
    
    return budget_contexts.get(budget_level, budget_contexts['moderate'])


def generate_feeding_program_meal_plan(
    target_age_group='all',  # 'all', '0-12months', '12-24months', '24-60months'
    program_duration_days=7,
    budget_level='moderate',
    available_ingredients=None,
    barangay=None,
    total_children=None
):
    """
    Generate a GENERIC meal plan for a feeding program focused on Filipino children.
    This creates a standardized meal plan suitable for community feeding programs.
    
    Args:
        target_age_group: Age group focus ('all', '0-12months', '12-24months', '24-60months')
        program_duration_days: Number of days for the feeding program (max 7)
        budget_level: 'low', 'moderate', or 'high'
        available_ingredients: Optional list of available ingredients
        barangay: Barangay name for location-specific recommendations
        total_children: Estimated number of children (for shopping list quantities)
        
    Returns:
        Structured meal plan for the feeding program
    """
    
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
    except Exception as e:
        print(f"Error retrieving feeding program guidance: {str(e)}")
        pdf_context = ""
    
    # Get food database
    foods_data = data_manager.get_foods_data()
    food_names = [f.get('food_name_and_description', '') for f in foods_data if f.get('food_name_and_description')]
    food_list_str = '\n- '.join(food_names[:100]) if food_names else 'Use common Filipino ingredients'  # Limit to avoid token overflow
    
    # Age group descriptions
    age_group_info = {
        'all': 'Mixed age groups (0-5 years) - provide adaptations for all',
        '0-12months': 'Infants (0-12 months) - focus on pureed/mashed foods',
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

BUDGET CONSTRAINTS ({budget_level}):
- Focus: {budget_context['focus']}
- Recommended Proteins: {', '.join(budget_context['proteins'])}
- Recommended Vegetables: {', '.join(budget_context['vegetables'])}
- Recommended Grains: {', '.join(budget_context['grains'])}
- Recommended Fruits: {', '.join(budget_context['fruits'])}

AVAILABLE INGREDIENTS:
{available_ingredients or 'Use budget-recommended ingredients above'}

FOOD DATABASE:
{food_list_str}

{pdf_context}

═══════════════════════════════════════════════════════════════════
YOUR TASK: Create a {program_duration_days}-day GENERIC FEEDING PROGRAM meal plan
═══════════════════════════════════════════════════════════════════

CRITICAL REQUIREMENTS:

1. **NO DISH REPETITION (MANDATORY):**
   - Each meal across the ENTIRE {program_duration_days}-day period must be UNIQUE
   - Example: If "Tinolang Manok" appears on Day 1, it CANNOT appear anywhere else
   - Track all dishes to ensure zero repetition
   - Use different cooking methods for same ingredients (Adobo, Sinigang, Tinola, Prito, Ginisa, etc.)

2. **BATCH FEEDING FORMAT:**
   - Design meals for large-scale preparation (50-100+ children)
   - Simple cooking methods suitable for community kitchens
   - Ingredients that are easy to purchase in bulk
   - Scalable portions (provide quantities per 50 children)

3. **AGE-APPROPRIATE VARIATIONS:**
   - Provide texture modifications for each meal:
     * 6-12 months: Pureed/mashed consistency
     * 12-24 months: Soft, small pieces (finger foods)
     * 24-60 months: Regular family food texture
   - Note: 0-6 months receive breast milk/formula only

4. **NUTRITIONAL BALANCE:**
   - Meet general nutritional needs for Filipino children
   - Include iron-rich foods (e.g., malunggay, liver, monggo)
   - Include calcium sources (e.g., milk, dilis, malunggay)
   - Include Vitamin A sources (e.g., kalabasa, carrots, papaya)
   - Ensure adequate protein, carbohydrates, and healthy fats

5. **BUDGET CONSCIOUSNESS:**
   - Prioritize ingredients from the budget-recommended list
   - Use seasonal, locally available ingredients
   - Minimize food waste through smart planning
   - Cost-effective protein sources (monggo, itlog, galunggong)

6. **FILIPINO CUISINE FOCUS:**
   - Use ONLY traditional Filipino dishes
   - Common breakfast: Lugaw, Champorado, Sinangag, Pandesal
   - Common lunch/dinner: Adobo, Sinigang, Tinola, Nilaga, Ginataang gulay
   - Common snacks: Turon, Banana cue, Ginataang mais, Puto
   - Use Filipino cooking methods and flavors

7. **MEAL VARIETY & DIVERSITY:**
   - Rotate proteins: Manok, Isda (bangus, tilapia, galunggong), Itlog, Monggo, Baboy
   - Rotate vegetables: Kangkong, Malunggay, Kalabasa, Sitaw, Talong, Ampalaya
   - Vary cooking methods each day
   - Different dishes for breakfast, lunch, snack, and dinner EVERY DAY

7. **PRACTICAL IMPLEMENTATION:**
   - Include simple preparation instructions
   - Consider food safety for batch cooking
   - Specify storage and reheating guidelines if needed

OUTPUT FORMAT (IN TAGALOG AND ENGLISH):

# Week [Number] Feeding Program Meal Plan

## Monday
**Almusal (Breakfast):**
- Main Dish: [Dish name in Tagalog and English]
- Ingredients: [List]
- Age Adaptations:
  * 6-12 months: [Texture modification]
  * 12-24 months: [Texture modification]
  * 24-60 months: [Regular serving]
- Approximate Portions: [Portions per age group]

**Tanghalian (Lunch):**
[Same format as breakfast]

**Meryenda (Snack):**
[Same format]

**Hapunan (Dinner):**
[Same format]

[Repeat for each day of the week]

## Weekly Shopping List
[Consolidated ingredient list with estimated quantities]

## Preparation Tips for Batch Cooking
[Practical tips for feeding program staff]

## Nutritional Summary
[Brief overview of how the week's menu addresses the group's nutritional needs]

BEGIN MEAL PLAN:
"""
    
    # Create LLM and generate
    llm = create_feeding_program_llm()
    
    try:
        response = llm.invoke(prompt_template)
        meal_plan_content = response.content if hasattr(response, 'content') else str(response)
        
        return {
            'success': True,
            'meal_plan': meal_plan_content,
            'batch_analysis': batch_analysis,
            'program_duration_days': program_duration_days,
            'budget_level': budget_level,
            'generated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        }
    
    except Exception as e:
        return {
            'success': False,
            'error': str(e),
            'meal_plan': None
        }


def generate_feeding_program_assessment(target_age_group='all', barangay=None, total_children=None):
    """
    Generate a generic assessment report for a feeding program.
    
    Args:
        target_age_group: Age group focus
        barangay: Barangay name
        total_children: Estimated number of children
        
    Returns:
        Assessment report with insights and recommendations
    """
    
    # Get knowledge base context
    from embedding_utils import embedding_searcher
    
    query = "community nutrition assessment feeding program evaluation children malnutrition Philippines"
    
    try:
        search_results = embedding_searcher.search_similar_chunks(query, k=4)
        pdf_context = "\n".join([chunk for chunk, score, _ in search_results if score > 0.4])
    except Exception as e:
        print(f"Error retrieving assessment guidance: {str(e)}")
        pdf_context = ""
    
    age_group_info = {
        'all': 'Mixed age groups (0-5 years)',
        '0-12months': 'Infants (0-12 months)',
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
    
    llm = create_feeding_program_llm()
    
    try:
        response = llm.invoke(prompt_template)
        assessment_content = response.content if hasattr(response, 'content') else str(response)
        
        return {
            'success': True,
            'assessment': assessment_content,
            'target_age_group': target_age_group,
            'generated_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        }
    
    except Exception as e:
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
