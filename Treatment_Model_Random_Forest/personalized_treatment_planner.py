"""
Enhanced Treatment Plan Generator
Generates realistic, personalized treatment plans based on Random Forest predictions
and comprehensive patient assessment
"""

import sys
import os
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

import pandas as pd
import numpy as np
from typing import Dict, List, Any
import json
from datetime import datetime, timedelta

class PersonalizedTreatmentPlanner:
    """Generates personalized treatment plans based on comprehensive assessment"""
    
    def __init__(self):
        """Initialize treatment planner with evidence-based protocols"""
        
        # Load treatment protocols
        self.protocols = self._load_treatment_protocols()
        
        # Age-specific feeding guidelines
        self.feeding_guidelines = {
            'infant': (0, 6),      # 0-6 months
            'young_child': (6, 24), # 6-24 months  
            'child': (24, 60)      # 24-60 months
        }
        
        # Medication protocols by weight
        self.medication_protocols = {
            'rutf_daily': {  # Ready-to-Use Therapeutic Food
                'sache_per_kg': 1.0,  # 1 sachet per kg body weight
                'calories_per_sachet': 500,
                'duration_weeks': 8
            },
            'vitamin_a': {
                'dose_under_12m': '100,000 IU',
                'dose_over_12m': '200,000 IU',
                'frequency': 'Every 6 months'
            },
            'iron_folate': {
                'dose_per_kg': '2-6 mg/kg/day',
                'duration_weeks': 12
            }
        }
        
    def _load_treatment_protocols(self):
        """Load evidence-based treatment protocols"""
        return {
            'sam_protocol': {
                'name': 'Severe Acute Malnutrition Protocol',
                'phases': ['stabilization', 'rehabilitation', 'follow_up'],
                'duration_weeks': 8,
                'success_criteria': 'Weight gain >5g/kg/day for 3 consecutive days'
            },
            'mam_protocol': {
                'name': 'Moderate Acute Malnutrition Protocol', 
                'phases': ['supplementary_feeding', 'monitoring'],
                'duration_weeks': 12,
                'success_criteria': 'Reach WHZ >-2 and maintain for 2 weeks'
            },
            'normal_protocol': {
                'name': 'Preventive Nutrition Protocol',
                'phases': ['maintenance', 'monitoring'],
                'duration_weeks': 4,
                'success_criteria': 'Maintain normal growth velocity'
            }
        }
    
    def generate_comprehensive_treatment_plan(self, patient_data: Dict, ml_result: Dict, 
                                           risk_assessment: Dict, who_assessment: Dict) -> Dict:
        """Generate a comprehensive, personalized treatment plan"""
        
        # Determine primary diagnosis and severity
        primary_diagnosis = ml_result.get('prediction', 'Unknown')
        confidence = max(ml_result.get('probabilities', {}).values()) if ml_result.get('probabilities') else 0
        
        # Patient characteristics
        age_months = patient_data.get('age_months', 12)
        weight = patient_data.get('weight', 10)
        sex = patient_data.get('sex', 'unknown')
        has_edema = patient_data.get('edema', False)
        breastfeeding = patient_data.get('breastfeeding', 'No')
        
        # Risk factors
        total_risk_score = sum(r.get('risk_score', 0) for r in risk_assessment.values())
        high_risk_factors = [k for k, v in risk_assessment.items() 
                           if v.get('risk_level') in ['High', 'Very High']]
        
        # Generate treatment plan
        treatment_plan = {
            'patient_info': {
                'name': patient_data.get('name', 'Patient'),
                'age_months': age_months,
                'diagnosis': primary_diagnosis,
                'confidence_level': confidence,
                'assessment_date': datetime.now().strftime('%Y-%m-%d'),
                'plan_created_by': 'AI-Enhanced Malnutrition Assessment System'
            },
            
            'immediate_actions': self._get_immediate_actions(primary_diagnosis, has_edema, confidence),
            
            'nutrition_plan': self._generate_nutrition_plan(primary_diagnosis, age_months, weight, 
                                                          breastfeeding, has_edema),
            
            'medical_interventions': self._generate_medical_plan(primary_diagnosis, age_months, 
                                                               weight, has_edema, high_risk_factors),
            
            'monitoring_schedule': self._generate_monitoring_schedule(primary_diagnosis, confidence, 
                                                                    total_risk_score),
            
            'follow_up_plan': self._generate_follow_up_plan(primary_diagnosis, age_months),
            
            'family_education': self._generate_family_education(patient_data, primary_diagnosis),
            
            'success_criteria': self._define_success_criteria(primary_diagnosis, age_months),
            
            'discharge_criteria': self._define_discharge_criteria(primary_diagnosis),
            
            'emergency_signs': self._define_emergency_signs(age_months)
        }
        
        return treatment_plan
    
    def get_available_protocols(self) -> Dict[str, Any]:
        """Get available treatment protocols for API endpoints"""
        return {
            'protocols': list(self.protocols.keys()),
            'feeding_guidelines': self.feeding_guidelines,
            'medication_protocols': self.medication_protocols,
            'available_assessments': [
                'malnutrition_status',
                'nutrition_plan',
                'medical_interventions',
                'monitoring_schedule',
                'follow_up_plan',
                'family_education',
                'success_criteria',
                'emergency_signs'
            ]
        }
    
    def _get_immediate_actions(self, diagnosis: str, has_edema: bool, confidence: float) -> List[str]:
        """Generate immediate action items based on diagnosis"""
        actions = []
        
        if has_edema:
            actions.append("🚨 URGENT: Immediate medical evaluation for edema - possible SAM")
            actions.append("Admit to stabilization center immediately")
            
        if 'Severe' in diagnosis:
            actions.extend([
                "Start RUTF (Ready-to-Use Therapeutic Food) immediately",
                "Check for medical complications",
                "Ensure adequate hydration",
                "Monitor vital signs every 4 hours"
            ])
        elif 'Moderate' in diagnosis:
            actions.extend([
                "Enroll in supplementary feeding program",
                "Provide specialized nutritious foods",
                "Assess feeding practices with caregiver"
            ])
        else:  # Normal
            actions.extend([
                "Continue current feeding practices",
                "Provide nutrition counseling to prevent malnutrition"
            ])
            
        if confidence < 0.6:
            actions.append("⚠️ Low confidence - Obtain clinical second opinion")
            
        return actions
    
    def _generate_nutrition_plan(self, diagnosis: str, age_months: int, weight: float, 
                               breastfeeding: str, has_edema: bool) -> Dict[str, Any]:
        """Generate detailed nutrition plan"""
        
        # Determine age group
        if age_months <= 6:
            age_group = 'infant'
        elif age_months <= 24:
            age_group = 'young_child'
        else:
            age_group = 'child'
            
        plan: Dict[str, Any] = {
            'age_group': age_group,
            'current_weight': f"{weight} kg",
            'target_weight': f"{self._calculate_target_weight(age_months, weight)} kg"
        }
        
        if 'Severe' in diagnosis:
            plan['phase'] = 'Therapeutic feeding'
            plan['rutf_sachets_daily'] = max(2, int(weight * 1.0))  # 1 sachet per kg
            plan['rutf_calories_daily'] = max(1000, int(weight * 500))  # 500 cal per sachet
            plan['feeding_frequency'] = '6 times per day'
            plan['feeding_schedule'] = 'Every 2-3 hours during day, 4-hour gap at night'
            plan['special_instructions'] = [
                'Start with smaller frequent feeds if poor appetite',
                'Ensure RUTF is given undiluted',
                'Monitor for signs of refeeding syndrome'
            ]
        elif 'Moderate' in diagnosis:
            plan['phase'] = 'Supplementary feeding'
            plan['supplementary_food'] = 'Specialized nutritious foods (SNF)'
            plan['daily_ration'] = f"{int(weight * 75)} kcal/kg/day supplementary"
            plan['feeding_frequency'] = '3 main meals + 2 snacks'
            plan['foods_to_increase'] = [
                'Protein-rich foods (eggs, fish, meat, legumes)',
                'Energy-dense foods (nuts, oils, avocado)',
                'Micronutrient-rich foods (vegetables, fruits)'
            ]
        else:  # Normal
            plan['phase'] = 'Maintenance nutrition'
            plan['feeding_frequency'] = '3 main meals + 1-2 snacks'
            plan['diet_diversity'] = 'Include foods from all food groups daily'
            plan['portion_guidance'] = 'Age-appropriate portions'
        
        # Breastfeeding recommendations
        if age_months <= 24:
            if breastfeeding.lower() == 'yes':
                plan['breastfeeding'] = 'Continue exclusive/complementary breastfeeding'
            else:
                plan['breastfeeding'] = 'Restart breastfeeding if possible, or ensure adequate milk intake'
        
        return plan
    
    def _generate_medical_plan(self, diagnosis: str, age_months: int, weight: float, 
                             has_edema: bool, high_risk_factors: List[str]) -> Dict:
        """Generate medical intervention plan"""
        
        plan = {
            'routine_medications': [],
            'therapeutic_medications': [],
            'medical_monitoring': [],
            'specialist_referrals': []
        }
        
        # Routine medications for all malnourished children
        if 'Normal' not in diagnosis:
            plan['routine_medications'].extend([
                f"Vitamin A: {'100,000 IU' if age_months < 12 else '200,000 IU'} immediately, then every 6 months",
                f"Iron + Folate: 2-6 mg/kg/day for 12 weeks",
                "Zinc: 10-20mg daily for 10-14 days if diarrhea present"
            ])
        
        # Specific medications for SAM
        if 'Severe' in diagnosis:
            plan['therapeutic_medications'].extend([
                "Amoxicillin: 15mg/kg TID for 7 days (routine antibiotic)",
                "Deworming: Albendazole 400mg single dose (if >2 years)",
                "Measles vaccination if not up to date"
            ])
            
            if has_edema:
                plan['therapeutic_medications'].append("Monitor electrolytes closely - risk of refeeding syndrome")
                
        # Medical monitoring based on severity
        if 'Severe' in diagnosis:
            plan['medical_monitoring'] = [
                "Daily weight monitoring",
                "Temperature, pulse, respiratory rate every 4 hours",
                "Watch for signs of infection",
                "Monitor for refeeding syndrome",
                "Assess for medical complications daily"
            ]
        elif 'Moderate' in diagnosis:
            plan['medical_monitoring'] = [
                "Weekly weight monitoring",
                "Bi-weekly health assessment",
                "Monitor for signs of deterioration"
            ]
        else:
            plan['medical_monitoring'] = [
                "Monthly growth monitoring",
                "Routine health checks as per schedule"
            ]
            
        # Specialist referrals based on risk factors
        if 'anthropometric_risk' in high_risk_factors:
            plan['specialist_referrals'].append("Pediatric nutritionist consultation")
        if 'clinical_risk' in high_risk_factors:
            plan['specialist_referrals'].append("Pediatrician evaluation for underlying conditions")
        if 'socioeconomic_risk' in high_risk_factors:
            plan['specialist_referrals'].append("Social worker assessment for family support")
            
        return plan
    
    def _generate_monitoring_schedule(self, diagnosis: str, confidence: float, 
                                   total_risk_score: int) -> Dict[str, Any]:
        """Generate monitoring and follow-up schedule"""
        
        if 'Severe' in diagnosis:
            schedule: Dict[str, Any] = {
                'phase_1_stabilization': {
                    'duration': '7-10 days',
                    'frequency': 'Daily visits',
                    'assessments': [
                        'Weight, height, MUAC',
                        'Appetite test',
                        'Medical examination',
                        'RUTF intake monitoring'
                    ]
                },
                'phase_2_rehabilitation': {
                    'duration': '6-8 weeks', 
                    'frequency': 'Weekly visits',
                    'assessments': [
                        'Weight monitoring',
                        'RUTF consumption',
                        'Medical complications screen'
                    ]
                }
            }
        elif 'Moderate' in diagnosis:
            schedule = {
                'supplementary_feeding': {
                    'duration': '8-12 weeks',
                    'frequency': 'Weekly visits first month, then bi-weekly',
                    'assessments': [
                        'Weight gain monitoring',
                        'Food distribution and consumption',
                        'Feeding practice assessment'
                    ]
                }
            }
        else:
            schedule = {
                'preventive_monitoring': {
                    'duration': 'Ongoing',
                    'frequency': 'Monthly growth monitoring',
                    'assessments': [
                        'Growth monitoring (weight, height)',
                        'Feeding practice review',
                        'General health assessment'
                    ]
                }
            }
            
        # Adjust frequency based on confidence and risk
        if confidence < 0.6 or total_risk_score > 10:
            schedule['special_note'] = 'Increased monitoring frequency due to uncertainty/high risk'
            
        return schedule
    
    def _generate_follow_up_plan(self, diagnosis: str, age_months: int) -> Dict:
        """Generate long-term follow-up plan"""
        
        today = datetime.now()
        
        if 'Severe' in diagnosis:
            return {
                'next_assessment': (today + timedelta(days=3)).strftime('%Y-%m-%d'),
                'key_milestone_1': (today + timedelta(weeks=2)).strftime('%Y-%m-%d') + ' - Appetite return',
                'key_milestone_2': (today + timedelta(weeks=6)).strftime('%Y-%m-%d') + ' - Target weight achievement',
                'discharge_evaluation': (today + timedelta(weeks=8)).strftime('%Y-%m-%d'),
                'post_discharge_followup': (today + timedelta(weeks=12)).strftime('%Y-%m-%d')
            }
        elif 'Moderate' in diagnosis:
            return {
                'next_assessment': (today + timedelta(days=7)).strftime('%Y-%m-%d'),
                'monthly_review': (today + timedelta(weeks=4)).strftime('%Y-%m-%d'),
                'target_achievement': (today + timedelta(weeks=12)).strftime('%Y-%m-%d'),
                'graduation_evaluation': (today + timedelta(weeks=16)).strftime('%Y-%m-%d')
            }
        else:
            return {
                'next_routine_checkup': (today + timedelta(weeks=4)).strftime('%Y-%m-%d'),
                'growth_monitoring': 'Every 3 months until 5 years old',
                'nutritional_counseling': 'Annual or as needed'
            }
    
    def _generate_family_education(self, patient_data: Dict, diagnosis: str) -> List[str]:
        """Generate family education and counseling points"""
        
        education = [
            "Importance of consistent feeding and medication compliance",
            "Signs of improvement to watch for",
            "Warning signs requiring immediate medical attention"
        ]
        
        age_months = patient_data.get('age_months', 12)
        
        # Age-specific education
        if age_months <= 6:
            education.extend([
                "Exclusive breastfeeding techniques and benefits",
                "Proper positioning and attachment for breastfeeding",
                "When and how to introduce complementary foods"
            ])
        elif age_months <= 24:
            education.extend([
                "Continued breastfeeding alongside complementary foods",
                "Appropriate food textures and consistency for age",
                "Responsive feeding practices"
            ])
        else:
            education.extend([
                "Family meal planning for nutritious diets",
                "Food hygiene and safety practices",
                "Encouraging self-feeding skills"
            ])
            
        # Diagnosis-specific education
        if 'Severe' in diagnosis:
            education.extend([
                "Critical importance of RUTF compliance",
                "How to prepare and store RUTF properly",
                "Signs of medical complications"
            ])
        elif 'Moderate' in diagnosis:
            education.extend([
                "Local foods that are energy and nutrient dense",
                "Cost-effective ways to improve diet quality",
                "Recipe modifications to increase nutritional value"
            ])
            
        # Socioeconomic considerations
        if patient_data.get('4ps_beneficiary') == 'Yes':
            education.append("Available social support programs and how to access them")
            
        return education
    
    def _define_success_criteria(self, diagnosis: str, age_months: int) -> Dict:
        """Define measurable success criteria for treatment"""
        
        if 'Severe' in diagnosis:
            return {
                'short_term': [
                    'Weight gain ≥5g/kg/day for 3 consecutive days',
                    'Improved appetite and increased RUTF consumption',
                    'Absence of medical complications'
                ],
                'medium_term': [
                    'WHZ score improvement >-2 SD',
                    'MUAC ≥125mm (if applicable)',
                    'Sustained weight gain for 2 weeks'
                ],
                'long_term': [
                    'Normal growth velocity maintenance',
                    'No relapse for 6 months post-discharge',
                    'Age-appropriate developmental milestones'
                ]
            }
        elif 'Moderate' in diagnosis:
            return {
                'short_term': [
                    'Consistent weight gain ≥3g/kg/day',
                    'Improved dietary diversity',
                    'Good program compliance'
                ],
                'medium_term': [
                    'WHZ score ≥-2 SD',
                    'Maintained weight gain for 4 weeks',
                    'Improved feeding practices'
                ],
                'long_term': [
                    'Normal growth pattern establishment',
                    'Sustained nutritional improvement',
                    'Family self-sufficiency in nutrition management'
                ]
            }
        else:
            return {
                'ongoing': [
                    'Maintain normal growth velocity',
                    'Prevent malnutrition occurrence',
                    'Optimal feeding practice continuation'
                ]
            }
    
    def _define_discharge_criteria(self, diagnosis: str) -> List[str]:
        """Define criteria for program discharge/graduation"""
        
        if 'Severe' in diagnosis:
            return [
                'WHZ score ≥-2 SD maintained for 2 consecutive weeks',
                'Weight gain ≥5g/kg/day for minimum 3 consecutive days',
                'Absence of edema for 2 weeks',
                'Good appetite and eating habits established',
                'No medical complications present',
                'Caregiver demonstrates proper feeding practices'
            ]
        elif 'Moderate' in diagnosis:
            return [
                'WHZ score ≥-2 SD for 2 consecutive measurements',
                'Consistent weight gain over 4 weeks',
                'Improved dietary diversity demonstrated',
                'Family shows good feeding practices',
                'No signs of deterioration'
            ]
        else:
            return [
                'Continued normal growth trajectory',
                'Optimal feeding practices maintained',
                'No nutritional concerns identified'
            ]
    
    def _define_emergency_signs(self, age_months: int) -> List[str]:
        """Define emergency warning signs for immediate medical attention"""
        
        general_signs = [
            'High fever (>38.5°C)',
            'Difficulty breathing or fast breathing',
            'Vomiting everything eaten',
            'Blood in stool',
            'Severe dehydration',
            'Convulsions or unconsciousness',
            'Severe edema or rapid edema increase'
        ]
        
        if age_months < 12:
            general_signs.extend([
                'Not breastfeeding or drinking',
                'Lethargy or difficult to wake',
                'Sunken fontanelle'
            ])
        else:
            general_signs.extend([
                'Not eating or drinking anything',
                'Extreme weakness',
                'Persistent crying'
            ])
            
        return general_signs
    
    def _calculate_target_weight(self, age_months: int, current_weight: float) -> float:
        """Calculate realistic target weight based on age"""
        
        # Simplified target weight calculation
        # This should ideally use WHO growth charts
        if age_months <= 12:
            target_weight = (age_months * 0.5) + 3.5  # Approximate formula
        elif age_months <= 24:
            target_weight = ((age_months - 12) * 0.25) + 9.5
        else:
            target_weight = ((age_months - 24) * 0.2) + 12.5
            
        # Target should be at least 10% higher than current if malnourished
        return max(target_weight, current_weight * 1.1)

def demonstrate_treatment_plan():
    """Demonstrate comprehensive treatment plan generation"""
    
    print("🏥 COMPREHENSIVE TREATMENT PLAN DEMONSTRATION")
    print("=" * 70)
    
    # Sample patient data
    patient_data = {
        'name': 'Sofia Mendez',
        'age_months': 18,
        'sex': 'female', 
        'weight': 8.0,
        'height': 75.0,
        'municipality': 'Quezon City',
        'total_household': 5,
        'adults': 2,
        'children': 3,
        'twins': 0,
        '4ps_beneficiary': 'Yes',
        'breastfeeding': 'No',
        'edema': False,
        'tuberculosis': 'No',
        'malaria': 'No',
        'congenital_anomalies': 'No',
        'other_medical_problems': 'No',
        'whz_score': -2.8
    }
    
    # Mock ML result
    ml_result = {
        'prediction': 'Moderate Acute Malnutrition (MAM)',
        'probabilities': {
            'Normal': 0.15,
            'Moderate Acute Malnutrition (MAM)': 0.70,
            'Severe Acute Malnutrition (SAM)': 0.15
        },
        'bmi': 14.22
    }
    
    # Mock risk assessment
    risk_assessment = {
        'anthropometric_risk': {'risk_level': 'High', 'risk_score': 6},
        'clinical_risk': {'risk_level': 'Low', 'risk_score': 0},
        'socioeconomic_risk': {'risk_level': 'Moderate', 'risk_score': 3},
        'environmental_risk': {'risk_level': 'Moderate', 'risk_score': 2}
    }
    
    # Mock WHO assessment
    who_assessment = {
        'classifications': {'nutritional_status': 'Moderate Acute Malnutrition (MAM)'},
        'confidence': {'confidence_level': 'High'}
    }
    
    # Generate treatment plan
    planner = PersonalizedTreatmentPlanner()
    treatment_plan = planner.generate_comprehensive_treatment_plan(
        patient_data, ml_result, risk_assessment, who_assessment
    )
    
    # Display the treatment plan
    print_treatment_plan(treatment_plan)

def print_treatment_plan(plan: Dict):
    """Print formatted treatment plan"""
    
    print(f"\n📋 PERSONALIZED TREATMENT PLAN")
    print(f"=" * 50)
    
    # Patient info
    info = plan['patient_info']
    print(f"👤 PATIENT: {info['name']}")
    print(f"📅 Date: {info['assessment_date']}")
    print(f"🎯 Diagnosis: {info['diagnosis']}")
    print(f"📊 Confidence: {info['confidence_level']:.1%}")
    
    # Immediate actions
    print(f"\n🚨 IMMEDIATE ACTIONS:")
    for i, action in enumerate(plan['immediate_actions'], 1):
        print(f"   {i}. {action}")
    
    # Nutrition plan
    print(f"\n🍽️ NUTRITION PLAN:")
    nutrition = plan['nutrition_plan']
    print(f"   • Phase: {nutrition['phase']}")
    print(f"   • Current Weight: {nutrition['current_weight']}")
    print(f"   • Target Weight: {nutrition['target_weight']}")
    
    if 'rutf_sachets_daily' in nutrition:
        print(f"   • RUTF Sachets: {nutrition['rutf_sachets_daily']} sachets/day")
        print(f"   • Feeding Schedule: {nutrition['feeding_schedule']}")
    elif 'supplementary_food' in nutrition:
        print(f"   • Supplementary Food: {nutrition['supplementary_food']}")
        print(f"   • Daily Ration: {nutrition['daily_ration']}")
    
    if 'breastfeeding' in nutrition:
        print(f"   • Breastfeeding: {nutrition['breastfeeding']}")
    
    # Medical interventions
    print(f"\n💊 MEDICAL INTERVENTIONS:")
    medical = plan['medical_interventions']
    
    if medical['routine_medications']:
        print(f"   Routine Medications:")
        for med in medical['routine_medications']:
            print(f"     • {med}")
    
    if medical['therapeutic_medications']:
        print(f"   Therapeutic Medications:")
        for med in medical['therapeutic_medications']:
            print(f"     • {med}")
    
    if medical['specialist_referrals']:
        print(f"   Specialist Referrals:")
        for ref in medical['specialist_referrals']:
            print(f"     • {ref}")
    
    # Monitoring schedule
    print(f"\n📊 MONITORING SCHEDULE:")
    monitoring = plan['monitoring_schedule']
    for phase, details in monitoring.items():
        if phase == 'special_note':
            print(f"   Special Note: {details}")
        else:
            print(f"   {phase.replace('_', ' ').title()}:")
            if isinstance(details, dict):
                print(f"     • Duration: {details.get('duration', 'N/A')}")
                print(f"     • Frequency: {details.get('frequency', 'N/A')}")
            else:
                print(f"     • {details}")
    
    # Follow-up plan
    print(f"\n📅 FOLLOW-UP PLAN:")
    for milestone, date in plan['follow_up_plan'].items():
        print(f"   • {milestone.replace('_', ' ').title()}: {date}")
    
    # Success criteria
    print(f"\n🎯 SUCCESS CRITERIA:")
    for timeframe, criteria in plan['success_criteria'].items():
        print(f"   {timeframe.replace('_', ' ').title()}:")
        for criterion in criteria:
            print(f"     • {criterion}")
    
    # Emergency signs
    print(f"\n🚨 EMERGENCY WARNING SIGNS:")
    print(f"   Seek immediate medical attention if child shows:")
    for sign in plan['emergency_signs'][:5]:  # Show first 5
        print(f"     • {sign}")
    print(f"     • ... and {len(plan['emergency_signs'])-5} more signs")

if __name__ == "__main__":
    demonstrate_treatment_plan()
