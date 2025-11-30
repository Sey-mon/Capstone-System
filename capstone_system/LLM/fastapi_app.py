
from fastapi import FastAPI, HTTPException, File, UploadFile, Form
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from nutrition_ai import ChildNutritionAI
from data_manager import data_manager
from nutrition_chain import get_meal_plan_with_langchain, generate_patient_assessment
from feeding_program_chain import (
    generate_feeding_program_meal_plan,
    generate_feeding_program_assessment,
    calculate_batch_nutritional_needs
)
from embedding_utils import embedding_searcher, get_contextual_nutrition_guidance
from typing import List, Optional, Dict, Any
import pdfplumber
from io import BytesIO


app = FastAPI(title="Nutritionist LLM API", description="API for LLM-powered nutrition functions", version="1.0")

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://127.0.0.1:8000", "http://localhost:8000", "*"],  # Allow Laravel app and any origin for testing
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
nutrition_ai = ChildNutritionAI()

# User Role Models
class NutritionQuestionRequest(BaseModel):
    question: str

# Parent Role Models
class MealPlanRequest(BaseModel):
    patient_id: int
    available_foods: Optional[str] = None

class ChildrenByParentRequest(BaseModel):
    parent_id: int

class MealPlansByChildRequest(BaseModel):
    patient_id: int
    most_recent: Optional[bool] = False

class MealPlanDetailRequest(BaseModel):
    plan_id: int

# Nutritionist Role Models
class NutritionAnalysis(BaseModel):
    patient_id: int

class AssessmentRequest(BaseModel):
    patient_id: int

class SaveMealPlanRequest(BaseModel):
    patient_id: int
    meal_plan: str
    duration_days: int
    parent_id: int

# Feeding Program Models (Nutritionist)
class FeedingProgramMealPlanRequest(BaseModel):
    target_age_group: str = 'all'  # 'all', '0-12months', '12-24months', '24-60months'
    program_duration_days: int = 5  # Max 5 days (optimal for token limits and cost)
    budget_level: str = 'moderate'  # 'low', 'moderate', or 'high'
    available_ingredients: Optional[str] = None
    barangay: Optional[str] = None
    total_children: Optional[int] = None

class FeedingProgramAssessmentRequest(BaseModel):
    target_age_group: str = 'all'
    barangay: Optional[str] = None
    total_children: Optional[int] = None

# Admin Role Models
class SaveAdminLogRequest(BaseModel):
    action: str
    details: dict

class KnowledgeBaseRequest(BaseModel):
    pass  # No parameters needed for get_knowledge_base

class ProcessEmbeddingsRequest(BaseModel):
    kb_ids: Optional[List[int]] = None  # Specific knowledge base IDs to process, if None processes all
    chunk_size: Optional[int] = 1000
    overlap: Optional[int] = 200
    batch_size: Optional[int] = 128

class EmbeddingStatusRequest(BaseModel):
    pass  # No parameters needed for status check

# =============================================================================
# ROOT ENDPOINT
# =============================================================================

@app.get("/")
def root():
    """Root endpoint for health check"""
    return {
        "message": "Meal Planning API is running",
        "status": "healthy",
        "version": "1.0",
        "endpoints": {
            "user": ["/get_foods_data"],
            "parent": ["/generate_meal_plan", "/get_children_by_parent", "/get_meal_plans_by_child", "/get_meal_plan_detail"],
            "nutritionist": ["/nutrition/analysis", "/assessment", "/feeding_program/meal_plan", "/feeding_program/assessment"],
            "admin": ["/process_embeddings", "/embedding_status", "/get_knowledge_base", "/upload_pdf"]
        }
    }

# =============================================================================
# USER ROLE ENDPOINTS
# =============================================================================

@app.post("/get_foods_data")
def get_foods_data():
    """Get all foods data from the database"""
    try:
        foods = data_manager.get_foods_data()
        return {"foods": foods}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# =============================================================================
# PARENT ROLE ENDPOINTS
# =============================================================================

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

        # Get contextual guidance using top-K similarity search for meal planning
        guidelines_context = get_contextual_nutrition_guidance(patient_data, "meal_plan", k=5)
        
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
                religion=religion if religion else '',
                guidelines_context=guidelines_context
            )
        # Generate meal plan (LangChain) with all context
        meal_plan_text = get_meal_plan_with_langchain(
            patient_id=request.patient_id,
            available_ingredients=request.available_foods
        )

        def clean_meal_plan_text(text):
            import re
            # Handle AIMessage objects by extracting content
            if hasattr(text, 'content'):
                text = text.content
            
            # Ensure we have a string
            if not isinstance(text, str):
                text = str(text)
            
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

@app.post("/get_children_by_parent")
def get_children_by_parent(request: ChildrenByParentRequest):
    """Get children data for a specific parent"""
    try:
        children = data_manager.get_children_by_parent(request.parent_id)
        return {"children": children}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/get_meal_plans_by_child")
def get_meal_plans_by_child(request: MealPlansByChildRequest):
    """Get meal plans for a specific child/patient"""
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

@app.post("/get_meal_plan_detail")
def get_meal_plan_detail(request: MealPlanDetailRequest):
    """Get detailed information for a specific meal plan"""
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

# =============================================================================
# NUTRITIONIST ROLE ENDPOINTS
# =============================================================================

@app.post("/nutrition/analysis")
def nutrition_analysis(request: NutritionAnalysis):
    """Run nutrition analysis for a patient and return the result."""
    try:
        patient_data = data_manager.get_patient_by_id(request.patient_id)
        if not patient_data:
            raise HTTPException(status_code=404, detail="Patient not found")

        # Get contextual guidance using top-K similarity search
        guidelines_context = get_contextual_nutrition_guidance(patient_data, "analysis", k=4)
        
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
            religion=patient_data.get('religion', ''),
            guidelines_context=guidelines_context
        )
        
        # Handle AIMessage objects by extracting content
        if hasattr(analysis_result, 'content'):
            analysis_result = analysis_result.content
        
        # Ensure we have a string
        if not isinstance(analysis_result, str):
            analysis_result = str(analysis_result)
        
        # Parse the LLM output into sections
        def parse_nutrition_analysis(text):
            import re
            # Handle AIMessage objects by extracting content
            if hasattr(text, 'content'):
                text = text.content
            
            # Ensure we have a string
            if not isinstance(text, str):
                text = str(text)
                
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



@app.post("/assessment")
def generate_assessment(request: AssessmentRequest):
    """Generate a comprehensive pediatric dietary assessment for a patient."""
    try:
        # Fetch patient data
        patient_data = data_manager.get_patient_by_id(request.patient_id)
        if not patient_data:
            raise HTTPException(status_code=404, detail="Patient not found")
        
        # Get contextual guidance using top-K similarity search for assessment
        guidelines_context = get_contextual_nutrition_guidance(patient_data, "assessment", k=4)
        
        # Generate assessment using LangChain with enhanced context
        assessment = generate_patient_assessment(patient_id=request.patient_id)

        return {
            "patient_id": request.patient_id,
            "assessment": assessment,
            "context_guidance": guidelines_context.strip() if guidelines_context else None
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# =============================================================================
# FEEDING PROGRAM ENDPOINTS (Nutritionist)
# =============================================================================

@app.post("/feeding_program/meal_plan")
def generate_feeding_program_meal_plan_endpoint(request: FeedingProgramMealPlanRequest):
    """
    Generate a GENERIC batch meal plan for a Filipino children feeding program.
    
    This endpoint creates standardized meal plans suitable for community feeding programs
    without requiring specific patient data. Perfect for nutritionists planning programs.
    """
    try:
        # Validate budget level
        if request.budget_level not in ['low', 'moderate', 'high']:
            raise HTTPException(status_code=400, detail="Budget level must be 'low', 'moderate', or 'high'")
        
        # Validate age group
        valid_age_groups = ['all', '0-12months', '12-24months', '24-60months']
        if request.target_age_group not in valid_age_groups:
            raise HTTPException(status_code=400, detail=f"Age group must be one of: {', '.join(valid_age_groups)}")
        
        # Generate feeding program meal plan
        result = generate_feeding_program_meal_plan(
            target_age_group=request.target_age_group,
            program_duration_days=request.program_duration_days,
            budget_level=request.budget_level,
            available_ingredients=request.available_ingredients,
            barangay=request.barangay,
            total_children=request.total_children
        )
        
        if not result['success']:
            raise HTTPException(status_code=500, detail=result.get('error', 'Failed to generate meal plan'))
        
        return {
            "success": True,
            "meal_plan": result['meal_plan'],
            "program_duration_days": result['program_duration_days'],
            "budget_level": result['budget_level'],
            "target_age_group": result.get('target_age_group', request.target_age_group),
            "generated_at": result['generated_at']
        }
    
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error generating feeding program meal plan: {str(e)}")


@app.post("/feeding_program/assessment")
def generate_feeding_program_assessment_endpoint(request: FeedingProgramAssessmentRequest):
    """
    Generate a generic assessment report for a Filipino children feeding program.
    
    Provides general nutritional needs assessment and recommendations for
    community feeding programs serving Filipino children.
    """
    try:
        # Validate age group
        valid_age_groups = ['all', '0-12months', '12-24months', '24-60months']
        if request.target_age_group not in valid_age_groups:
            raise HTTPException(status_code=400, detail=f"Age group must be one of: {', '.join(valid_age_groups)}")
        
        # Generate feeding program assessment
        result = generate_feeding_program_assessment(
            target_age_group=request.target_age_group,
            barangay=request.barangay,
            total_children=request.total_children
        )
        
        if not result['success']:
            raise HTTPException(status_code=500, detail=result.get('error', 'Failed to generate assessment'))
        
        return {
            "success": True,
            "assessment": result['assessment'],
            "target_age_group": result.get('target_age_group', request.target_age_group),
            "generated_at": result['generated_at']
        }
    
    except HTTPException:
        raise
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error generating feeding program assessment: {str(e)}")

# =============================================================================
# ADMIN ROLE ENDPOINTS
# =============================================================================

@app.post("/process_embeddings")
def process_embeddings(request: ProcessEmbeddingsRequest):
    """Process embeddings from PDF texts in knowledge base with chunk overlap.
    
    This endpoint will:
    1. Extract PDF text from knowledge_base table
    2. Chunk the text with specified overlap
    3. Create embeddings using sentence transformers
    4. Build FAISS index for similarity search
    5. Cache results for future use
    """
    try:
        from embedding_utils import embedding_searcher
        
        # Get knowledge base data
        knowledge_base = data_manager.get_knowledge_base()
        
        if not knowledge_base:
            raise HTTPException(status_code=404, detail="No knowledge base entries found")
        
        # Filter by specific kb_ids if provided
        if request.kb_ids:
            filtered_kb = {kb_id: entry for kb_id, entry in knowledge_base.items() 
                          if int(kb_id) in request.kb_ids}
            if not filtered_kb:
                raise HTTPException(status_code=404, detail="No matching knowledge base entries found")
            knowledge_base = filtered_kb
        
        # Process each PDF text with chunking
        all_chunks = []
        all_metadata = []
        total_chars = 0
        
        for kb_id, kb_entry in knowledge_base.items():
            pdf_text = kb_entry.get('pdf_text', '')
            if pdf_text and pdf_text.strip():
                # Chunk the PDF text with overlap
                chunks = data_manager.chunk_pdf_text_with_overlap(
                    pdf_text, 
                    chunk_size=request.chunk_size, 
                    overlap=request.overlap
                )
                
                total_chars += len(pdf_text)
                
                for chunk in chunks:
                    if chunk.strip():  # Only add non-empty chunks
                        all_chunks.append(chunk.strip())
                        all_metadata.append({
                            'kb_id': int(kb_id),
                            'pdf_name': kb_entry.get('pdf_name', ''),
                            'ai_summary': kb_entry.get('ai_summary', ''),
                            'user_id': kb_entry.get('user_id'),
                            'added_at': str(kb_entry.get('added_at', ''))
                        })
        
        if not all_chunks:
            raise HTTPException(status_code=400, detail="No valid PDF text content found for embedding")
        
        # Check if embeddings are already cached and valid
        status_info = embedding_searcher.check_embeddings_status()
        if status_info["status"] == "ready":
            return {
                "status": "success",
                "message": "Embeddings already exist and are up to date",
                "stats": {
                    "total_documents": len(knowledge_base),
                    "total_chunks": status_info["chunks_count"],
                    "total_characters": total_chars,
                    "chunk_size": request.chunk_size,
                    "overlap": request.overlap,
                    "embedding_dimension": embedding_searcher.index.ntotal if embedding_searcher.index else 0,
                    "batch_size": request.batch_size,
                    "cached": True
                }
            }
        
        # Use the proper embedding building function that handles caching
        print(f"Building embeddings for {len(all_chunks)} chunks...")
        result = embedding_searcher.build_embeddings_from_knowledge_base(batch_size=request.batch_size)
        
        # Use the proper embedding building function that handles caching
        print(f"Building embeddings for {len(all_chunks)} chunks...")
        result = embedding_searcher.build_embeddings_from_knowledge_base(batch_size=request.batch_size)
        
        if not result:
            raise HTTPException(status_code=500, detail="Failed to build embeddings")
        
        # Save to cache
        embedding_searcher._save_embeddings()
        
        return {
            "status": "success",
            "message": "Embeddings processed successfully",
            "stats": {
                "total_documents": len(knowledge_base),
                "total_chunks": len(embedding_searcher.chunks),
                "total_characters": total_chars,
                "chunk_size": request.chunk_size,
                "overlap": request.overlap,
                "embedding_dimension": embedding_searcher.index.d if embedding_searcher.index else 0,
                "batch_size": request.batch_size,
                "cached": False
            }
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error processing embeddings: {str(e)}")

@app.post("/embedding_status")
def get_embedding_status(request: EmbeddingStatusRequest):
    """Get the current status of embeddings cache and processing."""
    try:
        from embedding_utils import embedding_searcher
        
        # Check embeddings status
        status_info = embedding_searcher.check_embeddings_status()
        
        # Get knowledge base stats
        knowledge_base = data_manager.get_knowledge_base()
        kb_stats = {
            "total_documents": len(knowledge_base),
            "documents_with_text": sum(1 for entry in knowledge_base.values() 
                                     if entry.get('pdf_text', '').strip()),
            "total_characters": sum(len(entry.get('pdf_text', '')) 
                                  for entry in knowledge_base.values())
        }
        
        return {
            "embedding_status": status_info,
            "knowledge_base_stats": kb_stats,
            "cache_directory": embedding_searcher.cache_dir
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error checking embedding status: {str(e)}")


class ReembedRequest(BaseModel):
    batch_size: Optional[int] = 128


@app.post("/reembed_missing")
def reembed_missing(request: ReembedRequest):
    """Re-embed only PDFs that are not yet fully embedded. Returns per-PDF report or a message if all embedded."""
    try:
        from embedding_utils import embedding_searcher

        result = embedding_searcher.reembed_missing_pdfs(batch_size=request.batch_size)

        # If all embedded, return a friendly message
        if result.get('status') == 'all_embedded':
            return {"status": "all_embedded", "message": "All knowledge base PDFs are already embedded"}

        return {"status": result.get('status'), "message": result.get('message'), "per_kb": result.get('per_kb')}
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error re-embedding missing PDFs: {str(e)}")

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

@app.post("/upload_pdf")
async def upload_pdf(
    file: UploadFile = File(..., description="PDF file to upload to knowledge base"),
    uploaded_by_id: Optional[int] = Form(1, description="ID of the user uploading the PDF (defaults to 1)")
):
    """
    Upload a PDF file to the knowledge base with AI-generated nutrition summary.
    
    This endpoint:
    1. Accepts a PDF file upload
    2. Extracts text from the PDF using pdfplumber
    3. Generates an AI summary focused on 0-5 year old nutrition content
    4. Saves the PDF, extracted text, and AI summary to the knowledge base
    5. Returns the saved knowledge base entry details
    """
    try:
        # Validate file type
        if not file.filename.lower().endswith('.pdf'):
            raise HTTPException(status_code=400, detail="Only PDF files are allowed")

        # Read PDF content
        pdf_content = await file.read()

        # Extract text from PDF using pdfplumber
        try:
            with pdfplumber.open(BytesIO(pdf_content)) as pdf:
                all_text = "\n".join(page.extract_text() or "" for page in pdf.pages)
        except Exception as e:
            raise HTTPException(status_code=400, detail=f"Failed to extract text from PDF: {str(e)}")

        # Check if PDF has extractable text
        if not all_text or not all_text.strip():
            raise HTTPException(status_code=400, detail="PDF does not contain extractable text")

        # Check for duplicate by pdf_text (hash for efficiency)
        import hashlib
        new_pdf_hash = hashlib.sha256(all_text.strip().encode('utf-8')).hexdigest()
        knowledge_base = data_manager.get_knowledge_base()
        for entry in knowledge_base.values():
            existing_text = entry.get('pdf_text', '')
            if existing_text and hashlib.sha256(existing_text.strip().encode('utf-8')).hexdigest() == new_pdf_hash:
                raise HTTPException(status_code=400, detail="PDF already exists in the knowledge base")

        # Generate AI summary using nutrition AI
        try:
            nutrition_ai_instance = ChildNutritionAI()
            ai_summary = nutrition_ai_instance.summarize_pdf_for_nutrition_knowledge(all_text, file.filename)
            # Convert list to string if needed
            if isinstance(ai_summary, list):
                ai_summary_text = "\n".join(ai_summary) if ai_summary else "No relevant nutrition content found for 0-5 year olds."
            else:
                ai_summary_text = str(ai_summary) if ai_summary else "No relevant nutrition content found for 0-5 year olds."
        except Exception as e:
            raise HTTPException(status_code=500, detail=f"Failed to generate AI summary: {str(e)}")

        # Save to knowledge base
        try:
            kb_id = data_manager.save_knowledge_base(
                pdf_text=all_text,
                pdf_name=file.filename,
                uploaded_by='api_user',
                uploaded_by_id=uploaded_by_id,
                ai_summary=ai_summary_text
            )
        except Exception as e:
            raise HTTPException(status_code=500, detail=f"Failed to save to knowledge base: {str(e)}")

        # Get the saved entry to return
        try:
            knowledge_base = data_manager.get_knowledge_base()
            saved_entry = knowledge_base.get(kb_id)
            if not saved_entry:
                # Fallback response if we can't retrieve the saved entry
                saved_entry = {
                    'kb_id': kb_id,
                    'pdf_name': file.filename,
                    'ai_summary': ai_summary_text,
                    'pdf_text': all_text[:500] + "..." if len(all_text) > 500 else all_text,  # Truncate for response
                    'uploaded_by_id': uploaded_by_id,
                    'added_at': 'just now'
                }
        except Exception as e:
            # Fallback response if retrieval fails
            saved_entry = {
                'kb_id': kb_id,
                'pdf_name': file.filename,
                'ai_summary': ai_summary_text,
                'uploaded_by_id': uploaded_by_id,
                'message': 'PDF uploaded successfully but could not retrieve full details'
            }

        return {
            "status": "success",
            "message": f"PDF '{file.filename}' uploaded and processed successfully",
            "kb_id": kb_id,
            "knowledge_base_entry": saved_entry,
            "stats": {
                "filename": file.filename,
                "text_length": len(all_text),
                "ai_summary_length": len(ai_summary_text),
                "uploaded_by_id": uploaded_by_id
            }
        }

    except HTTPException:
        # Re-raise HTTP exceptions as-is
        raise
    except Exception as e:
        # Catch any other unexpected errors
        raise HTTPException(status_code=500, detail=f"Unexpected error processing PDF: {str(e)}")