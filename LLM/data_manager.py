from db import get_connection
from datetime import datetime, timedelta
from typing import Dict, List, Optional
import uuid
import json
from langchain_text_splitters import RecursiveCharacterTextSplitter
from functools import wraps


def with_connection(func):
    """Decorator to provide a fresh (connection, cursor) for each DataManager method call.

    This ensures connections are not shared across threads or requests which caused
    'Lost connection to MySQL server during query' under concurrency.
    The decorated function should accept (self, *args, conn, cursor, **kwargs) or we inject
    conn and cursor as keyword-only args.
    """
    @wraps(func)
    def wrapper(self, *args, **kwargs):
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            # Inject conn and cursor into kwargs for the wrapped function
            kwargs['conn'] = conn
            kwargs['cursor'] = cursor
            return func(self, *args, **kwargs)
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass
    return wrapper

class DataManager:
    def update_food(self, food_id, food_data):
        """Update a food in the foods table with the allowed columns."""
        sql = """
            UPDATE foods SET
                food_name_and_description = %s,
                alternate_common_names = %s,
                energy_kcal = %s,
                nutrition_tags = %s
            WHERE food_id = %s
        """
        params = (
            food_data.get('food_name_and_description', ''),
            food_data.get('alternate_common_names', ''),
            food_data.get('energy_kcal', 0),
            food_data.get('nutrition_tags', ''),
            food_id
        )
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(sql, params)
            conn.commit()
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass
    
    @with_connection
    def get_foods_data(self, conn=None, cursor=None):
        """Get all foods from the foods table, ordered by food_id."""
        cursor.execute("SELECT food_id, food_name_and_description, alternate_common_names, energy_kcal, nutrition_tags FROM foods ORDER BY food_id")
        return cursor.fetchall()

    @with_connection
    def get_food_by_id(self, food_id, conn=None, cursor=None):
        """Get a specific food by its ID."""
        cursor.execute("SELECT food_id, food_name_and_description, alternate_common_names, energy_kcal, nutrition_tags FROM foods WHERE food_id = %s", (food_id,))
        return cursor.fetchone()

    @with_connection
    def search_foods(self, search_term="", conn=None, cursor=None):
        """Search foods by name, description, or tags."""
        conditions = []
        params = []
        if search_term:
            conditions.append("(food_name_and_description LIKE %s OR alternate_common_names LIKE %s OR nutrition_tags LIKE %s)")
            search_pattern = f"%{search_term}%"
            params.extend([search_pattern, search_pattern, search_pattern])
        where_clause = "WHERE " + " AND ".join(conditions) if conditions else ""
        sql = f"SELECT food_id, food_name_and_description, alternate_common_names, energy_kcal, nutrition_tags FROM foods {where_clause} ORDER BY food_name_and_description"
        cursor.execute(sql, params)
        return cursor.fetchall()
    
    @with_connection
    def get_nutritionists(self, conn=None, cursor=None) -> list:
        """Get all nutritionists from MySQL, all columns."""
        cursor.execute("SELECT user_id, role_id, first_name, middle_name, last_name, birth_date, sex, email, email_verified_at, password, contact_number, address, is_active, remember_token, license_number, years_experience, qualifications, professional_experience, professional_id_path, verification_status, rejection_reason, verified_at, verified_by, account_status, deleted_at, created_at, updated_at FROM users WHERE role_id = (SELECT role_id FROM roles WHERE role_name = 'nutritionist')")
        return cursor.fetchall()
    
    @with_connection
    def get_meal_plan_by_id(self, plan_id: int, conn=None, cursor=None) -> Optional[Dict]:
        """Get a single meal plan by its plan_id."""
        cursor.execute("SELECT plan_id, patient_id, plan_details, generated_at FROM meal_plans WHERE plan_id = %s", (plan_id,))
        return cursor.fetchone()

    @with_connection
    def get_nutritionist_notes_by_patient(self, patient_id: int, conn=None, cursor=None) -> List[Dict]:
        """Get all nutritionist notes for a given patient_id from assessments.notes."""
        cursor.execute("SELECT assessment_id, nutritionist_id, patient_id, assessment_date, weight_kg, height_cm, notes, treatment, recovery_status, completed_at, created_at, updated_at FROM assessments WHERE patient_id = %s", (patient_id,))
        return cursor.fetchall()
        
    """
    Manages MySQL-based data storage for the nutrition system
    """
    def __init__(self):
        # Do not open a persistent connection here. Use per-call connections via the
        # @with_connection decorator to be safe for concurrent FastAPI requests.
        pass

    @with_connection
    def get_barangay_name(self, barangay_id: int, conn=None, cursor=None) -> str:
        """Get barangay name by barangay_id."""
        try:
            cursor.execute("SELECT barangay_name FROM barangays WHERE barangay_id = %s", (barangay_id,))
            result = cursor.fetchone()
            return result['barangay_name'] if result else f"Barangay {barangay_id}"
        except Exception:
            return f"Barangay {barangay_id}"

    @with_connection
    def get_all_barangays(self, conn=None, cursor=None) -> Dict:
        """Get all barangays as a dictionary {barangay_id: barangay_name}."""
        try:
            cursor.execute("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name")
            rows = cursor.fetchall()
            return {row['barangay_id']: row['barangay_name'] for row in rows}
        except Exception:
            return {}

    @staticmethod
    def format_full_name(first_name: str = '', middle_name: str = '', last_name: str = '') -> str:
        """Format a full name properly, excluding empty/None middle names."""
        parts = []
        
        if first_name and first_name.strip():
            parts.append(first_name.strip())
        
        if middle_name and middle_name.strip() and middle_name.strip().lower() not in ['none', 'null', '']:
            parts.append(middle_name.strip())
        
        if last_name and last_name.strip():
            parts.append(last_name.strip())
        
        return ' '.join(parts)

    # Parents Data Management

    def get_parents_data(self) -> Dict:
        """Get all parents data from MySQL, including all columns as per schema."""
        # Use per-call connection
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT user_id, role_id, first_name, middle_name, last_name, birth_date, sex, email, email_verified_at, password, contact_number, address, is_active, remember_token, license_number, years_experience, qualifications, professional_experience, professional_id_path, verification_status, rejection_reason, verified_at, verified_by, account_status, deleted_at, created_at, updated_at FROM users WHERE role_id = (SELECT role_id FROM roles WHERE role_name = 'parent')")
            rows = cursor.fetchall()
            return {str(row['user_id']): row for row in rows}
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    @with_connection
    def get_parent_by_id(self, parent_id: str, conn=None, cursor=None) -> Optional[Dict]:
        """Get specific parent data from MySQL, all columns."""
        cursor.execute("SELECT user_id, role_id, first_name, middle_name, last_name, birth_date, sex, email, email_verified_at, password, contact_number, address, is_active, remember_token, license_number, years_experience, qualifications, professional_experience, professional_id_path, verification_status, rejection_reason, verified_at, verified_by, account_status, deleted_at, created_at, updated_at FROM users WHERE user_id = %s AND role_id = (SELECT role_id FROM roles WHERE role_name = 'parent')", (parent_id,))
        row = cursor.fetchone()
        return row

    def get_religion_by_parent(self, parent_id: str) -> Optional[str]:
        parent = self.get_parent_by_id(parent_id)
        if parent:
            # Religion is not stored in users table, so return None for now
            return None
        return None

    # Children Data Management

    def get_children_data(self) -> Dict:
        """Get all children data from MySQL (patients table), all columns."""
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT patient_id, first_name, middle_name, last_name, barangay_id, contact_number, age_months, sex, date_of_admission, total_household_adults, total_household_children, total_household_twins, is_4ps_beneficiary, weight_kg, height_cm, weight_for_age, height_for_age, bmi_for_age, breastfeeding, allergies, religion, other_medical_problems, edema, created_at, updated_at, parent_id FROM patients")
            rows = cursor.fetchall()
            return {str(row['patient_id']): row for row in rows}
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    @with_connection
    def get_children_by_parent(self, parent_id: str, conn=None, cursor=None) -> List[Dict]:
        """Get all children for a specific parent from MySQL, all columns."""
        cursor.execute("SELECT patient_id, first_name, middle_name, last_name, barangay_id, contact_number, age_months, sex, date_of_admission, total_household_adults, total_household_children, total_household_twins, is_4ps_beneficiary, weight_kg, height_cm, weight_for_age, height_for_age, bmi_for_age, breastfeeding, allergies, religion, other_medical_problems, edema, created_at, updated_at, parent_id FROM patients WHERE parent_id = %s", (parent_id,))
        return cursor.fetchall()

    @with_connection
    def get_children_ids_by_parent(self, parent_id: str, conn=None, cursor=None) -> List[str]:
        """Get all children IDs for a specific parent from MySQL"""
        cursor.execute("SELECT patient_id FROM patients WHERE parent_id = %s", (parent_id,))
        rows = cursor.fetchall()
        return [str(row['patient_id']) for row in rows]

    @with_connection
    def get_patient_by_id(self, patient_id: str, conn=None, cursor=None) -> Optional[Dict]:
        """Get specific patient data from MySQL, all columns."""
        cursor.execute("SELECT patient_id, first_name, middle_name, last_name, barangay_id, contact_number, age_months, sex, date_of_admission, total_household_adults, total_household_children, total_household_twins, is_4ps_beneficiary, weight_kg, height_cm, weight_for_age, height_for_age, bmi_for_age, breastfeeding, allergies, religion, other_medical_problems, edema, created_at, updated_at, parent_id FROM patients WHERE patient_id = %s", (patient_id,))
        row = cursor.fetchone()
        return row

    # Meal Plans Management
    def get_meal_plans(self) -> Dict:
        """Get all meal plans from MySQL, all columns."""
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT plan_id, patient_id, plan_details, generated_at FROM meal_plans")
            rows = cursor.fetchall()
            return {str(row['plan_id']): row for row in rows}
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    def save_meal_plan(self, patient_id: str, meal_plan: str, duration_days: int, parent_id: str) -> str:
        """Save a new meal plan to MySQL"""
        sql = """
            INSERT INTO meal_plans (patient_id, plan_details, generated_at)
            VALUES (%s, %s, %s)
        """
        conn = None
        cursor = None
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(sql, (patient_id, meal_plan, now))
            conn.commit()
            return str(cursor.lastrowid)
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    @with_connection
    def get_meal_plans_by_patient(self, patient_id: str, months_back: int = 6, conn=None, cursor=None) -> List[Dict]:
        """Get meal plans for a patient within the last X months from MySQL, all columns."""
        cutoff_date = (datetime.now() - timedelta(days=months_back * 30)).strftime('%Y-%m-%d %H:%M:%S')
        cursor.execute(
            "SELECT plan_id, patient_id, plan_details, generated_at FROM meal_plans WHERE patient_id = %s AND generated_at >= %s ORDER BY generated_at DESC",
            (patient_id, cutoff_date)
        )
        return cursor.fetchall()

    @with_connection
    def get_meal_plans_by_parent(self, parent_id: str, conn=None, cursor=None) -> List[Dict]:
        """Get all recent meal plans for a parent's children from MySQL, all columns."""
        cursor.execute("SELECT plan_id, patient_id, plan_details, generated_at FROM meal_plans WHERE patient_id IN (SELECT patient_id FROM patients WHERE parent_id = %s) ORDER BY generated_at DESC", (parent_id,))
        return cursor.fetchall()

    # Parent Recipes Management

    def get_parent_recipes(self) -> Dict:
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT id, parent_id, name, description, created_at FROM parent_recipes")
            rows = cursor.fetchall()
            return {str(row['id']): row for row in rows}
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    def save_parent_recipe(self, parent_id: str, recipe_name: str, recipe_description: str) -> str:
        sql = """
            INSERT INTO parent_recipes (parent_id, name, description, created_at)
            VALUES (%s, %s, %s, %s)
        """
        conn = None
        cursor = None
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(sql, (parent_id, recipe_name, recipe_description, now))
            conn.commit()
            return str(cursor.lastrowid)
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    def get_recipes_by_parent(self, parent_id: str) -> List[Dict]:
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT id, parent_id, name, description, created_at FROM parent_recipes WHERE parent_id = %s", (parent_id,))
            return cursor.fetchall()
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    # Nutritionist Notes Management
    def get_nutritionist_notes(self) -> Dict:
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute("SELECT assessment_id, nutritionist_id, patient_id, assessment_date, weight_kg, height_cm, notes, treatment, recovery_status, completed_at, created_at, updated_at FROM assessments")
            rows = cursor.fetchall()
            return {str(row['assessment_id']): row for row in rows}
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    def save_nutritionist_note(self, plan_id: str, patient_id: str, nutritionist_id: str, note: str) -> str:
        """
        Save nutritionist note for a patient. Since assessments table doesn't have plan_id,
        we'll check for existing assessment by patient_id and nutritionist_id only.
        """
        conn = None
        cursor = None
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            # Check for existing assessment (without plan_id since it doesn't exist in assessments table)
            cursor.execute(
                "SELECT assessment_id, notes FROM assessments WHERE patient_id = %s AND nutritionist_id = %s ORDER BY created_at DESC LIMIT 1",
                (patient_id, nutritionist_id)
            )
            row = cursor.fetchone()
            if row:
                # Append new note to existing notes (newline separator)
                existing_notes = row.get('notes') or ''
                if existing_notes.strip():
                    updated_notes = existing_notes.rstrip() + '\n- ' + note.strip()
                else:
                    updated_notes = note.strip()
                cursor.execute(
                    "UPDATE assessments SET notes = %s, updated_at = %s WHERE assessment_id = %s",
                    (updated_notes, now, row['assessment_id'])
                )
                conn.commit()
                return str(row['assessment_id'])
            else:
                # Insert new row (without plan_id since it doesn't exist in assessments table)
                sql = """
                    INSERT INTO assessments (patient_id, nutritionist_id, notes, assessment_date, created_at, updated_at)
                    VALUES (%s, %s, %s, %s, %s, %s)
                """
                cursor.execute(sql, (patient_id, nutritionist_id, note.strip(), now, now, now))
                conn.commit()
                return str(cursor.lastrowid)
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    def get_notes_for_meal_plan(self, plan_id: str) -> List[Dict]:
        # Since assessments table doesn't have plan_id, we'll return empty list for now
        # This function may need to be redesigned to link assessments to meal plans differently
        return []

    # Knowledge Base Management
    def get_knowledge_base(self) -> Dict:
        """Get all knowledge base entries with admin full names who uploaded them."""
        sql = """
            SELECT 
                kb.kb_id, 
                kb.user_id,
                kb.ai_summary, 
                kb.pdf_name, 
                kb.pdf_text, 
                kb.added_at,
                CONCAT(u.first_name, 
                       CASE 
                           WHEN u.middle_name IS NOT NULL AND u.middle_name != '' 
                           THEN CONCAT(' ', u.middle_name, ' ') 
                           ELSE ' ' 
                       END, 
                       u.last_name) as uploaded_by_name
            FROM knowledge_base kb
            LEFT JOIN users u ON kb.user_id = u.user_id
            ORDER BY kb.added_at DESC
        """
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(sql)
            rows = cursor.fetchall()
            return {str(row['kb_id']): row for row in rows}
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    def save_knowledge_base(self, ai_summary, pdf_name, pdf_text=None, uploaded_by=None, uploaded_by_id=None):
        """Save knowledge base entry with user_id."""
        sql = "INSERT INTO knowledge_base (user_id, ai_summary, pdf_name, pdf_text, added_at) VALUES (%s, %s, %s, %s, %s)"
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        # Convert ai_summary to plain text if it's a list
        if isinstance(ai_summary, list):
            ai_summary_text = "\n".join(ai_summary)
        else:
            ai_summary_text = str(ai_summary) if ai_summary else ""
        
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(sql, (
                uploaded_by_id,  # Store the user_id of the admin who uploaded
                ai_summary_text,
                pdf_name,
                pdf_text,
                now
            ))
            conn.commit()
            return str(cursor.lastrowid)
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    def delete_knowledge_base_entry(self, kb_id):
        """Delete a knowledge base entry by its ID"""
        sql = "DELETE FROM knowledge_base WHERE kb_id = %s"
        conn = None
        cursor = None
        try:
            conn = get_connection()
            cursor = conn.cursor(dictionary=True)
            cursor.execute(sql, (kb_id,))
            conn.commit()
            return True
        finally:
            try:
                if cursor:
                    cursor.close()
            except Exception:
                pass
            try:
                if conn:
                    conn.close()
            except Exception:
                pass

    def chunk_pdf_text_with_overlap(self, text: str, chunk_size: int = 1000, overlap: int = 200) -> List[str]:
        """Split PDF text into overlapping chunks using LangChain's RecursiveCharacterTextSplitter."""
        if not text or not text.strip():
            return []
        
        # Use LangChain's RecursiveCharacterTextSplitter for better chunking
        splitter = RecursiveCharacterTextSplitter(
            chunk_size=chunk_size,
            chunk_overlap=overlap,
            length_function=len,
            separators=["\n\n", "\n", ". ", " ", ""]
        )
        
        chunks = splitter.split_text(text.strip())
        
        # Remove any empty chunks
        return [chunk for chunk in chunks if chunk.strip()]

data_manager = DataManager()