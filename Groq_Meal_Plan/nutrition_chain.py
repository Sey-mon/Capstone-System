from langchain.prompts import PromptTemplate
from langchain.chains import LLMChain
from langchain_groq import ChatGroq
import os
from dotenv import load_dotenv
from data_manager import data_manager
from datetime import datetime
import re

load_dotenv()

def get_relevant_pdf_chunks(query, k=4):
    """Retrieve relevant PDF text using simple keyword matching."""
    try:
        # Get PDF text directly from knowledge base
        knowledge_base = data_manager.get_knowledge_base()
        if not knowledge_base:
            return []
        
        query_keywords = query.lower().split()
        scored_chunks = []
        
        for kb in knowledge_base.values():
            ai_summary = kb.get('ai_summary', '')
            if ai_summary:
                # Simple scoring based on keyword matches
                summary_lower = ai_summary.lower()
                score = sum(1 for keyword in query_keywords if keyword in summary_lower)
                
                if score > 0:
                    # Split into smaller chunks if needed
                    if len(ai_summary) > 500:
                        chunks = ai_summary.split('\n')
                        for chunk in chunks:
                            if chunk.strip():
                                chunk_score = sum(1 for keyword in query_keywords if keyword in chunk.lower())
                                if chunk_score > 0:
                                    scored_chunks.append((chunk.strip(), chunk_score))
                    else:
                        scored_chunks.append((ai_summary, score))
        
        # Sort by score and return top k
        scored_chunks.sort(key=lambda x: x[1], reverse=True)
        return [chunk[0] for chunk in scored_chunks[:k]]
        
    except Exception as e:
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

    # Get knowledge base context
    query = f"child nutrition {patient_data.get('age_months', '')} months assessment dietary recommendations"
    relevant_kb = get_relevant_pdf_chunks(query, k=3)
    kb_context = ""
    if relevant_kb:
        kb_context = "NUTRITION KNOWLEDGE BASE:\n" + "\n---\n".join(relevant_kb) + "\n"

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

    # Create LLM
    llm = ChatGroq(
        groq_api_key=api_key,
        model_name="meta-llama/llama-4-scout-17b-16e-instruct",
        temperature=0.3,
        max_tokens=4000
    )

    # Create chain
    chain = LLMChain(
        llm=llm,
        prompt=prompt_template
    )

    try:
        # Generate assessment
        result = chain.run(**template_vars)
        
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
        allergy_section = f"Strictly avoid: {allergy_val}. List all allergen-containing foods from the database. Prevent cross-contamination. Emergency plan ready."
    else:
        allergy_section = "No known allergies. Monitor for new reactions."

    # Helper: Religion section
    religion_val = patient_data.get('religion', '') or religion or 'Not specified'
    if religion_val and religion_val.lower() not in ['none', 'no', 'n/a', 'not specified']:
        religion_section = f"Respect dietary restrictions for {religion_val}. List allowed/forbidden foods if any."
    else:
        religion_section = "No specific religious dietary restrictions."

    # Get religion from parent
    if not religion:
        parent_id = patient_data.get('parent_id')
        religion = data_manager.get_religion_by_parent(parent_id) if parent_id else ""

    # Retrieve relevant PDF knowledge for this patient
    query = f"child nutrition {patient_data.get('age_months', '')} months {patient_data.get('bmi_for_age', '')} {patient_data.get('allergies', '')} {patient_data.get('other_medical_problems', '')}"
    relevant_pdf_chunks = get_relevant_pdf_chunks(query, k=4)
    pdf_context = ""
    if relevant_pdf_chunks:
        pdf_context = "\nBACKGROUND KNOWLEDGE (for your reference only, do NOT mention or cite this in your response):\n" + "\n---\n".join(relevant_pdf_chunks)

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

    # Create the streamlined prompt
    prompt_str = f"""You are a Pediatric Nutritionist specializing in Filipino cuisine for children 0-5 years.

    ## PRIMARY CONSTRAINT
    ONLY recommend foods from the database below. Never mention generic food groups or unlisted foods.

    ## FOOD DATABASE
    {food_list_str}
    
    Based your response to {nutrition_analysis}

    ## CHILD PROFILE
    - Age: {age_months} months
    - Weight: {{weight_kg}} kg | Height: {{height_cm}} cm | BMI: {{bmi_for_age}}
    - Allergies: {{allergies}} | Medical: {{other_medical_problems}} | Religion: {{religion}}
    - Available Ingredients: {{available_ingredients}}

    ## COMPREHENSIVE NUTRITION PLAN

    Based on {nutrition_analysis}, find fitted foods based on {{nutrition_tags}} and use it in suggesting foods.

    Give estimated kcal needed for the patient based on the prompt

    ### AGE-SPECIFIC FEEDING GUIDELINES
    **Current Age Group ({age_months} months)**:
    {get_age_specific_guidelines(age_months)}

    ### ALLERGY COMPLIANCE
    **Allergies: {{allergies}}**
    {allergy_section}

    ### RELIGIOUS DIETARY COMPLIANCE
    **Religion: {{religion}}**
    {religion_section}

    ### 7-DAY MEAL PLAN
    **CRITICAL: Provide complete details for ALL 7 days. No summaries or shortcuts.**

    **Day 1-7: Format for each day:**
    - **Breakfast**: [Specific dish] ([portion]) - [Nutrition benefit + kcal]
    - **Lunch**: [Specific dish] ([portion]) - [Nutrition benefit + kcal]
    - **Snack**: [Specific item] ([portion]) - [Purpose + kcal]
    - **Dinner**: [Specific dish] ([portion]) - [Evening focus + kcal]
    - **Daily Total**: [Sum all kcal from energy_kcal values]

    **Day 1**: Use available ingredients {{available_ingredients}}
    **Days 2-7**: Vary using database foods, different themes daily

    ### PARENT OBSERVATION TRACKING
    **Daily**: Appetite (Good/Fair/Poor), Energy levels, Sleep quality, Bowel movements
    **Weekly**: Weight check, Growth observations, Skill development
    **Monthly**: Height measurement, Food preferences, Feeding independence

    ### RED FLAGS & EMERGENCY PROTOCOLS
    **Immediate Care**: Severe allergic reactions, Choking, Persistent vomiting, Dehydration, High fever with poor feeding
    **Concerning Signs**: Weight loss, Growth stagnation, Feeding aversion, Digestive issues
    **Emergency Protocol**: Call emergency services → Contact pediatrician → Nutritionist follow-up

    {filipino_context}

    **FINAL VERIFICATION**: All recommendations use only database foods, respect allergies/religion, and are age-appropriate."""
    prompt_template = PromptTemplate(
        input_variables=["weight_kg", "height_cm", "bmi_for_age", "allergies", "other_medical_problems", "religion", "available_ingredients", "nutrition_tags"],
        template=prompt_str
    )

    prompt_inputs = {
        "weight_kg": patient_data.get('weight_kg', 'Unknown'),
        "height_cm": patient_data.get('height_cm', 'Unknown'),
        "bmi_for_age": patient_data.get('bmi_for_age', 'Unknown'),
        "allergies": patient_data.get('allergies', 'None'),
        "other_medical_problems": patient_data.get('other_medical_problems', 'None'),
        "religion": religion_val,
        "available_ingredients": available_ingredients if available_ingredients else "None specified",
        "nutrition_tags": nutrition_tags_str
    }

    llm = ChatGroq(
        groq_api_key=api_key,
        model_name="meta-llama/llama-4-scout-17b-16e-instruct",
        temperature=0.3,
        max_tokens=4000
    )

    chain = LLMChain(
        llm=llm,
        prompt=prompt_template
    )

    result = chain.run(**prompt_inputs)
    return result