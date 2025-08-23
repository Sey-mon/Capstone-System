
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from nutrition_ai import ChildNutritionAI
from data_manager import data_manager
from nutrition_chain import get_meal_plan_with_langchain, generate_patient_assessment
from typing import List, Optional


app = FastAPI(title="Nutritionist LLM API", description="API for LLM-powered nutrition functions", version="1.0")
nutrition_ai = ChildNutritionAI()

class NutritionAnalysis(BaseModel):
    patient_id: int

class MealPlanRequest(BaseModel):
    patient_id: int
    available_foods: Optional[str] = None

class NutritionQuestionRequest(BaseModel):
    question: str

class ChildrenByParentRequest(BaseModel):
    parent_id: int

class SaveMealPlanRequest(BaseModel):
    patient_id: int
    meal_plan: str
    duration_days: int
    parent_id: int

class MealPlansByChildRequest(BaseModel):
    patient_id: int
    most_recent: Optional[bool] = False

class SaveAdminLogRequest(BaseModel):
    action: str
    details: dict

class AssessmentRequest(BaseModel):
    patient_id: int

@app.post("/nutrition/analysis")
def nutrition_analysis(request: NutritionAnalysis):
    """Run nutrition analysis for a patient and return the result."""
    try:
        patient_data = data_manager.get_patient_by_id(request.patient_id)
        if not patient_data:
            raise HTTPException(status_code=404, detail="Patient not found")

        from nutrition_ai import ChildNutritionAI
        nutrition_ai = ChildNutritionAI()
        # Get latest assessment for notes and treatment
        assessments = data_manager.get_nutritionist_notes_by_patient(request.patient_id)
        latest_assessment = assessments[0] if assessments else {}
        analysis_result = nutrition_ai.analyze_child_nutrition(
            patient_id=request.patient_id,
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
            religion=patient_data.get('religion', '')
        )
        # Parse the LLM output into sections
        def parse_nutrition_analysis(text):
            import re
            # Remove markdown and split by section headers
            text = re.sub(r'\*\*([^*]+)\*\*', r'\1', text)  
            text = text.replace('\n', '\n')
            sections = {}
            current = None
            lines = text.splitlines()
            for line in lines:
                line = line.strip()
                if not line:
                    continue

                if re.match(r'^[A-Za-z].*:$', line):
                    current = line[:-1].strip().lower().replace(' ', '_')
                    sections[current] = ''
                elif current:
                    if sections[current]:
                        sections[current] += ' '
                    sections[current] += line
            return sections

        parsed = parse_nutrition_analysis(analysis_result)
        return {"patient_id": request.patient_id, "nutrition_analysis": parsed}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/generate_meal_plan")
def generate_meal_plan(request: MealPlanRequest):
    """Generate a meal plan for a patient using LangChain prompt template, using nutrition analysis for guidance, but only return the meal plan."""
    try:
        # Fetch patient data for context
        patient_data = data_manager.get_patient_by_id(request.patient_id)
        if not patient_data:
            raise HTTPException(status_code=404, detail="Patient not found")
        # Extract all relevant info from patient and parent
        name = data_manager.format_full_name(
            patient_data.get('first_name', ''),
            patient_data.get('middle_name', ''),
            patient_data.get('last_name', '')
        )
        age_months = patient_data.get('age_months')
        weight_kg = patient_data.get('weight_kg')
        height_cm = patient_data.get('height_cm')
        other_medical_problems = patient_data.get('other_medical_problems')
        parent_id = patient_data.get('parent_id')
        religion = data_manager.get_religion_by_parent(parent_id) if parent_id else None

        # Nutrition analysis (LLM) for internal use only
        if age_months is not None:
            _ = nutrition_ai.analyze_child_nutrition(
                patient_id=request.patient_id,
                age_in_months=age_months,
                allergies=patient_data.get('allergies'),
                other_medical_problems=other_medical_problems,
                parent_id=parent_id,
                notes=None,
                treatment=None,
                sex=patient_data.get('sex', ''),
                weight_for_age=patient_data.get('weight_for_age', ''),
                height_for_age=patient_data.get('height_for_age', ''),
                bmi_for_age=patient_data.get('bmi_for_age', ''),
                breastfeeding=patient_data.get('breastfeeding', ''),
                religion=religion if religion else ''
            )
        # Generate meal plan (LangChain) with all context
        meal_plan_text = get_meal_plan_with_langchain(
            patient_id=request.patient_id,
            available_ingredients=request.available_foods
        )

        def clean_meal_plan_text(text):
            import re
            # Remove markdown headers and join sections as a single line
            lines = text.splitlines()
            result = []
            current_section = None
            section_content = []
            for line in lines:
                line = line.strip()
                if not line:
                    continue
                # Detect section headers (markdown or all-caps with colon)
                header_match = re.match(r'^(#+)\s*(.*)', line)
                alt_header_match = re.match(r'^([A-Z][A-Z\- ]+):$', line)
                if header_match:
                    # Save previous section
                    if current_section:
                        result.append(f"{current_section}: {' '.join(section_content).strip()}")
                    current_section = header_match.group(2).strip().upper()
                    section_content = []
                elif alt_header_match:
                    if current_section:
                        result.append(f"{current_section}: {' '.join(section_content).strip()}")
                    current_section = alt_header_match.group(1).strip().upper()
                    section_content = []
                else:
                    section_content.append(line)
            # Add last section
            if current_section:
                result.append(f"{current_section}: {' '.join(section_content).strip()}")
            # Join all sections with a space, no embedded \n
            return ' '.join(result)

        cleaned_meal_plan = clean_meal_plan_text(meal_plan_text)
        return {
            "meal_plan": cleaned_meal_plan
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/assessment")
def generate_assessment(request: AssessmentRequest):
    """Generate a comprehensive pediatric dietary assessment for a patient."""
    try:
        # Fetch patient data
        patient_data = data_manager.get_patient_by_id(request.patient_id)
        if not patient_data:
            raise HTTPException(status_code=404, detail="Patient not found")
        
        # Generate assessment using LangChain
        assessment = generate_patient_assessment(patient_id=request.patient_id)
        
        return {
            "patient_id": request.patient_id,
            "assessment": assessment
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# Combined endpoint: returns all foods
@app.post("/get_foods_data")
def get_foods_data():
    try:
        foods = data_manager.get_foods_data()
        return {"foods": foods}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/get_children_by_parent")
def get_children_by_parent(request: ChildrenByParentRequest):
    try:
        children = data_manager.get_children_by_parent(request.parent_id)
        return {"children": children}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/get_meal_plans_by_child")
def get_meal_plans_by_child(request: MealPlansByChildRequest):
    try:
        import json
        plans = data_manager.get_meal_plans_by_patient(request.patient_id)
        def parse_plan_details(plan):
            try:
                details = plan.get('plan_details')
                if details:
                    # If details is a JSON string, parse it
                    parsed = json.loads(details)
                    # If parsed is a dict with a 'text' key, try to further parse the text into days/meals
                    if isinstance(parsed, dict) and 'text' in parsed:
                        import re
                        text = parsed['text']
                        days = {}
                        current_day = None
                        current_meals = {}
                        for line in text.splitlines():
                            line = line.strip()
                            if not line:
                                continue
                            day_match = re.match(r'^Day (\d+):', line)
                            if day_match:
                                if current_day and current_meals:
                                    days[current_day] = current_meals
                                current_day = f"Day {day_match.group(1)}"
                                current_meals = {}
                            elif current_day and ':' in line:
                                meal, desc = line.split(':', 1)
                                meal = meal.strip().replace('-', '').replace(' ', '_').lower()
                                desc = desc.strip()
                                current_meals[meal] = desc
                        if current_day and current_meals:
                            days[current_day] = current_meals
                        parsed['parsed_days'] = days
                    return parsed
            except Exception:
                pass
            return plan.get('plan_details')

        for plan in plans:
            plan['parsed_plan_details'] = parse_plan_details(plan)

        if request.most_recent:
            # Return only the most recent plan (if any)
            if plans:
                return {"meal_plans": [plans[0]]}
            else:
                return {"meal_plans": []}
        return {"meal_plans": plans}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

class KnowledgeBaseRequest(BaseModel):
    pass  # No parameters needed for get_knowledge_base

@app.post("/get_knowledge_base")
def get_knowledge_base(request: KnowledgeBaseRequest):
    try:
        kb = data_manager.get_knowledge_base()
        # Parse ai_summary for each document if present
        import re
        def parse_ai_summary(text):
            if not text:
                return text
            # Split into sections by double newlines
            sections = re.split(r'\n\n+', text)
            parsed = {}
            for section in sections:
                section = section.strip()
                if not section:
                    continue
                # If section starts with a header
                header_match = re.match(r'^(.*?)(:|\n)', section)
                if header_match:
                    header = header_match.group(1).strip().lower().replace(' ', '_')
                    # Remove header from section
                    content = section[len(header_match.group(0)):].strip()
                    # Split bullet points
                    bullets = [line.lstrip('*').strip() for line in content.split('\n') if line.strip()]
                    parsed[header] = bullets if len(bullets) > 1 else content
                else:
                    # Just a list of bullets
                    bullets = [line.lstrip('*').strip() for line in section.split('\n') if line.strip()]
                    if bullets:
                        parsed.setdefault('insights', []).extend(bullets)
            return parsed

        for doc in kb.values():
            if 'ai_summary' in doc:
                doc['parsed_ai_summary'] = parse_ai_summary(doc['ai_summary'])
        return {"knowledge_base": kb}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

class MealPlanDetailRequest(BaseModel):
    plan_id: int

@app.post("/get_meal_plan_detail")
def get_meal_plan_detail(request: MealPlanDetailRequest):
    try:
        import json
        plan = data_manager.get_meal_plan_by_id(request.plan_id)
        if plan and isinstance(plan, dict) and 'plan_details' in plan:
            try:
                plan['plan_details'] = json.loads(plan['plan_details']) if plan['plan_details'] else None
            except Exception:
                # If parsing fails, keep as string
                pass
        return {"meal_plan": plan}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))