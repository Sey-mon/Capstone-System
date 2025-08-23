import os
from groq import Groq
from dotenv import load_dotenv
from data_manager import data_manager
from typing import Dict, List, Optional
from langchain.prompts import PromptTemplate
from langchain.chains import LLMChain
from langchain_groq import ChatGroq

# Load environment variables
load_dotenv()

class ChildNutritionAI:
    def analyze_child_nutrition(
        self,
        patient_id: int = None,
        age_in_months: int = None,
        allergies: str = None,
        other_medical_problems: str = None,
        parent_id: str = None,
        notes: str = None,
        treatment: str = None,
        sex: str = None,
        weight_for_age: str = None,
        height_for_age: str = None,
        bmi_for_age: str = None,
        breastfeeding: str = None,
        religion: str = None
    ) -> str:
        """Analyze a child's nutrition profile and return a summary or recommendations. No name or location info is used. Patient ID is included for database association only."""
        try:
            prompt_template = PromptTemplate(
                input_variables=[
                    "patient_id", "age_in_months", "allergies", "other_medical_problems", "parent_id", "notes", "treatment", "sex", "weight_for_age", "height_for_age", "bmi_for_age", "breastfeeding", "religion"
                ],
                template="""You are a pediatric nutrition expert. Analyze the following child's nutrition profile and provide a summary of their nutritional status, potential concerns, and general recommendations. Do NOT include or request any personal names or location information. Patient ID is included for database association only.

CHILD PROFILE:
- Patient ID: {patient_id}
- Age (months): {age_in_months}
- Sex: {sex}
- Weight-for-Age: {weight_for_age}
- Height-for-Age: {height_for_age}
- BMI-for-Age: {bmi_for_age}
- Breastfeeding: {breastfeeding}
- Allergies: {allergies}
- Religion: {religion}
- Other Medical Problems: {other_medical_problems}
- Parent ID: {parent_id}
- Notes: {notes}
- Treatment: {treatment}

Give practical, parent-friendly advice and highlight any red flags or areas for improvement."""
            )
            chain = LLMChain(
                llm=self.llm,
                prompt=prompt_template
            )
            result = chain.run(
                patient_id=patient_id,
                age_in_months=age_in_months,
                allergies=allergies,
                other_medical_problems=other_medical_problems,
                parent_id=parent_id,
                notes=notes,
                treatment=treatment,
                sex=sex,
                weight_for_age=weight_for_age,
                height_for_age=height_for_age,
                bmi_for_age=bmi_for_age,
                breastfeeding=breastfeeding,
                religion=religion
            )
            return result
        except Exception as e:
            return f"Error analyzing child nutrition: {str(e)}"
    """
    Enhanced Nutrition AI for children (0-5 years) with BMI, allergies, and medical conditions
    """
    
    def __init__(self):
        self.api_key = os.getenv('GROQ_API_KEY')
        if not self.api_key:
            raise ValueError("GROQ_API_KEY not found in environment variables")
        
        self.client = Groq(api_key=self.api_key)
        
        # Initialize LangChain LLM
        self.llm = ChatGroq(
            groq_api_key=self.api_key,
            model_name="meta-llama/llama-4-scout-17b-16e-instruct",
            temperature=0.3
        )
    
    def summarize_pdf_for_nutrition_knowledge(self, pdf_text: str, pdf_name: str) -> List[str]:
        """
        Use LLM to extract nutrition and health information relevant to 0-5 year old children
        Returns a list of bullet points/key insights
        """
        try:
            # Create LangChain prompt template
            prompt_template = PromptTemplate(
                input_variables=["pdf_name", "pdf_text"],
                template="""You are a pediatric nutrition expert. I'm uploading a PDF document titled "{pdf_name}" to build a knowledge base for meal planning for children aged 0-5 years.

Please analyze the following text and extract ONLY information that is relevant to:
- Nutrition for children 0-5 years old
- Health guidelines for toddlers and preschoolers
- Food safety for young children
- Feeding recommendations for infants and toddlers
- Filipino/Asian nutrition practices for children
- Child development and nutrition

TEXT TO ANALYZE:
{pdf_text}

INSTRUCTIONS:
1. Extract key insights as bullet points (each bullet should be 1-2 sentences max)
2. Focus ONLY on information relevant to 0-5 year old children's nutrition and health and religion and allergy compliance
3. Include specific food recommendations, portion sizes, or feeding guidelines if mentioned
4. Include any warnings or contraindications for young children
5. If the document contains no relevant information for 0-5 year olds, return "NO_RELEVANT_CONTENT"
6. Each bullet point should be actionable or informative for meal planning

"""
            )
            
            # Create LangChain chain
            chain = LLMChain(
                llm=self.llm,
                prompt=prompt_template
            )
            
            # Execute the chain
            response = chain.run(
                pdf_name=pdf_name,
                pdf_text=pdf_text
            )
            
            content = response.strip()
            
            # Try to parse as JSON
            import json
            try:
                bullet_points = json.loads(content)
                if isinstance(bullet_points, list):
                    return bullet_points
                else:
                    return [content]  # Fallback if not a list
            except json.JSONDecodeError:
                # If not valid JSON, return as single item
                if content == "NO_RELEVANT_CONTENT":
                    return []
                return [content]
                
        except Exception as e:
            return [f"Error processing PDF content: {str(e)}"]

    def generate_patient_meal_plan(
        self,
        patient_id: str,
        duration_days: int = 7,
        parent_recipes: List[str] = None
    ) -> str:
        """Generate meal plan specifically for a patient based on their profile"""
        # Get patient data
        patient_data = data_manager.get_patient_by_id(patient_id)
        if not patient_data:
            return "Error: Patient data not found"
        
        # Get knowledge base for Filipino nutrition guidelines
        knowledge_base = data_manager.get_knowledge_base()
        filipino_foods = knowledge_base.get('filipino_foods', {})
        
        # Get AI-processed PDF insights for enhanced recommendations
        pdf_insights_context = ""
        if knowledge_base:
            all_insights = []
            for kb in knowledge_base.values():
                ai_summary = kb.get('ai_summary', '')
                
                # ai_summary is now just text, split by lines to get individual insights
                if ai_summary:
                    insights = [line.strip() for line in ai_summary.split('\n') if line.strip()]
                    all_insights.extend(insights)
            
            if all_insights:
                # Limit to most relevant insights to avoid token limit
                relevant_insights = all_insights[:15]  # Use first 15 insights
                pdf_insights_context = f"\n\nAI-Extracted Nutrition Guidelines from Knowledge Base:\n" + "\n".join(relevant_insights)
        
        # Prepare parent recipes context
        parent_recipes_context = ""
        if parent_recipes:
            parent_recipes_context = f"\n\nParent Recipes to Consider:\n" + "\n".join(parent_recipes)
        
        # Build Filipino foods context
        filipino_context = ""
        if filipino_foods:
            filipino_recipes = []
            for recipe in filipino_foods.values():
                filipino_recipes.append(f"- {recipe['name']}: {recipe['nutrition_facts']}")
            filipino_context = f"\n\nFilipino Food Options:\n" + "\n".join(filipino_recipes)

if __name__ == "__main__":

    try:
        nutrition_ai = ChildNutritionAI()
        print("✅ Child Nutrition AI initialized successfully!")
        
    except Exception as e:
        print(f"❌ Error: {e}")
        print("Make sure GROQ_API_KEY is set in .env file")
