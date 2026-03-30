"""
Enhanced Treatment Plan Generator
Generates realistic, personalized treatment plans based on Random Forest predictions
and comprehensive patient assessment.
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
    """Generates personalized treatment plans based on comprehensive assessment."""

    def __init__(self):
        """Initialize treatment planner with evidence-based protocols."""

        # Load treatment protocols from JSON files
        self.protocols = self._load_treatment_protocols()

        # Age-specific feeding guidelines
        self.feeding_guidelines = {
            'infant':      (0,  6),   # 0-6 months
            'young_child': (6,  24),  # 6-24 months
            'child':       (24, 60),  # 24-60 months
        }

        # Medication protocols by weight
        self.medication_protocols = {
            'rutf_daily': {
                'sache_per_kg':        1.0,
                'calories_per_sachet': 500,
                'duration_weeks':      8,
            },
            'vitamin_a': {
                'dose_under_12m': '100,000 IU',
                'dose_over_12m':  '200,000 IU',
                'frequency':      'Every 6 months',
            },
            'iron_folate': {
                'dose_per_kg':    '2-6 mg/kg/day',
                'duration_weeks': 12,
            },
        }

    # ------------------------------------------------------------------
    # Protocol loading
    # ------------------------------------------------------------------

    def _load_treatment_protocols(self):
        """Load evidence-based treatment protocols from JSON files."""
        script_dir = os.path.dirname(os.path.abspath(__file__))
        protocol_files = {
            'who_standard':       'who_standard.json',
            'community_based':    'community_based.json',
            'hospital_intensive': 'hospital_intensive.json',
        }

        loaded = {}
        for key, filename in protocol_files.items():
            path = os.path.join(script_dir, 'treatment_protocols', filename)
            try:
                with open(path, 'r', encoding='utf-8') as fh:
                    loaded[key] = json.load(fh)
            except Exception as e:
                print(f"Warning: could not load {filename}: {e}")

        # Lightweight summary dict for callers that expect the legacy shape
        loaded.setdefault('_summary', {
            'sam_protocol':        {'name': 'Severe Acute Malnutrition Protocol',   'duration_weeks': 8},
            'mam_protocol':        {'name': 'Moderate Acute Malnutrition Protocol', 'duration_weeks': 12},
            'normal_protocol':     {'name': 'Preventive Nutrition Protocol',        'duration_weeks': 4},
            'overweight_protocol': {'name': 'Overweight Management Protocol',       'duration_weeks': 24},
            'obese_protocol':      {'name': 'Obesity Management Protocol',          'duration_weeks': 999},
        })
        return loaded

    # ------------------------------------------------------------------
    # Main entry point
    # ------------------------------------------------------------------

    def generate_comprehensive_treatment_plan(self, patient_data: Dict, ml_result: Dict,
                                              risk_assessment: Dict,
                                              who_assessment: Dict) -> Dict:
        """Generate a comprehensive, personalized treatment plan."""

        primary_diagnosis = ml_result.get('prediction', 'Unknown')
        confidence = (max(ml_result.get('probabilities', {}).values())
                      if ml_result.get('probabilities') else 0)

        age_months    = int(patient_data.get('age_months', 12))
        weight        = float(patient_data.get('weight', 10))
        has_edema     = patient_data.get('edema', False)
        breastfeeding = patient_data.get('breastfeeding', 'No')

        total_risk_score  = sum(r.get('risk_score', 0) for r in risk_assessment.values())
        high_risk_factors = [k for k, v in risk_assessment.items()
                             if v.get('risk_level') in ['High', 'Very High']]

        return {
            'patient_info': {
                'name':             patient_data.get('name', 'Patient'),
                'age_months':       age_months,
                'diagnosis':        primary_diagnosis,
                'confidence_level': confidence,
                'assessment_date':  datetime.now().strftime('%Y-%m-%d'),
                'plan_created_by':  'AI-Enhanced Malnutrition Assessment System',
            },
            'immediate_actions':     self._get_immediate_actions(
                primary_diagnosis, has_edema, confidence),
            'nutrition_plan':        self._generate_nutrition_plan(
                primary_diagnosis, age_months, weight, breastfeeding, has_edema),
            'medical_interventions': self._generate_medical_plan(
                primary_diagnosis, age_months, weight, has_edema, high_risk_factors),
            'monitoring_schedule':   self._generate_monitoring_schedule(
                primary_diagnosis, confidence, total_risk_score),
            'follow_up_plan':        self._generate_follow_up_plan(
                primary_diagnosis, age_months),
            'family_education':      self._generate_family_education(
                patient_data, primary_diagnosis),
            'success_criteria':      self._define_success_criteria(
                primary_diagnosis, age_months),
            'discharge_criteria':    self._define_discharge_criteria(primary_diagnosis),
            'emergency_signs':       self._define_emergency_signs(age_months),
        }

    # ------------------------------------------------------------------
    # Utility
    # ------------------------------------------------------------------

    def get_available_protocols(self) -> Dict[str, Any]:
        """Return available treatment protocols for API endpoints."""
        return {
            'protocols':             list(self.protocols.keys()),
            'feeding_guidelines':    self.feeding_guidelines,
            'medication_protocols':  self.medication_protocols,
            'available_assessments': [
                'malnutrition_status', 'nutrition_plan', 'medical_interventions',
                'monitoring_schedule', 'follow_up_plan', 'family_education',
                'success_criteria', 'emergency_signs',
            ],
        }

    # ------------------------------------------------------------------
    # Private generation methods
    # ------------------------------------------------------------------

    def _get_immediate_actions(self, diagnosis: str, has_edema: bool,
                                confidence: float) -> List[str]:
        """Generate immediate action items based on diagnosis."""
        actions: List[str] = []

        if has_edema:
            actions.append("URGENT: Immediate medical evaluation for oedema — possible SAM")
            actions.append("Admit to stabilisation centre immediately")

        if 'Severe' in diagnosis:
            actions.extend([
                "Start RUTF (Ready-to-Use Therapeutic Food) immediately",
                "Check for medical complications",
                "Ensure adequate hydration",
                "Monitor vital signs every 4 hours",
            ])
        elif 'Moderate' in diagnosis:
            actions.extend([
                "Enrol in supplementary feeding programme",
                "Provide specialised nutritious foods (SNF)",
                "Assess feeding practices with caregiver",
            ])
        elif diagnosis == 'Obese':
            actions.extend([
                "Refer to paediatrician for obesity comorbidity screening "
                "(blood pressure, blood glucose, lipids)",
                "Provide family dietary counselling: reduce caloric-density foods, "
                "eliminate sweetened beverages",
                "Prescribe structured daily active play — minimum 30 min/day",
                "Rule out endocrine or genetic cause if rapid onset or very high BMI-for-age",
            ])
        elif diagnosis == 'Overweight':
            actions.extend([
                "Provide family dietary counselling: reduce fried foods, sweetened drinks, "
                "and excess-portion foods",
                "Promote locally available fruits, vegetables, and water",
                "Encourage at least 60 min of active play per day",
                "Schedule monthly growth monitoring",
            ])
        else:  # Normal
            actions.extend([
                "Continue current feeding practices",
                "Provide nutrition counselling to prevent malnutrition",
            ])

        if confidence < 0.6:
            actions.append("Low confidence — obtain clinical second opinion")

        return actions

    def _generate_nutrition_plan(self, diagnosis: str, age_months: int, weight: float,
                                  breastfeeding: str, has_edema: bool) -> Dict[str, Any]:
        """Generate detailed nutrition plan."""

        if age_months <= 6:
            age_group = 'infant'
        elif age_months <= 24:
            age_group = 'young_child'
        else:
            age_group = 'child'

        plan: Dict[str, Any] = {
            'age_group':      age_group,
            'current_weight': f"{weight} kg",
            'target_weight':  f"{self._calculate_target_weight(age_months, weight)} kg",
        }

        if 'Severe' in diagnosis:
            plan['phase']               = 'Therapeutic feeding'
            plan['rutf_sachets_daily']  = max(2, int(weight * 1.0))
            plan['rutf_calories_daily'] = max(1000, int(weight * 500))
            plan['feeding_frequency']   = '6 times per day'
            plan['feeding_schedule']    = 'Every 2-3 hours during day; 4-hour gap at night'
            plan['special_instructions'] = [
                'Start with smaller frequent feeds if appetite is poor',
                'Ensure RUTF is given undiluted',
                'Monitor for signs of refeeding syndrome',
            ]

        elif 'Moderate' in diagnosis:
            plan['phase']              = 'Supplementary feeding'
            plan['supplementary_food'] = 'Specialised nutritious foods (SNF)'
            plan['daily_ration']       = f"{int(weight * 75)} kcal/kg/day supplementary"
            plan['feeding_frequency']  = '3 main meals + 2 snacks'
            plan['foods_to_increase']  = [
                'Protein-rich foods (eggs, fish, meat, legumes)',
                'Energy-dense foods (nuts, oils, avocado)',
                'Micronutrient-rich foods (vegetables, fruits)',
            ]

        elif diagnosis == 'Obese':
            plan['phase']             = 'Weight management — caloric-density reduction'
            plan['feeding_frequency'] = '3 structured meals + 1 healthy snack; no grazing between meals'
            plan['foods_to_reduce']   = [
                'Fried and fast foods',
                'Sweetened beverages (juice, soft drinks, flavoured milk)',
                'High-sugar snacks and desserts',
                'Excess refined carbohydrates (white rice, white bread)',
            ]
            plan['foods_to_increase'] = [
                'Fresh fruits and vegetables (aim for half the plate)',
                'Lean proteins (fish, eggs, legumes)',
                'Whole grains in age-appropriate portions',
                'Water as primary beverage',
            ]
            plan['physical_activity'] = ('Minimum 30 min structured active play per day; '
                                         'limit screen time to <1 hr/day')
            plan['special_instructions'] = [
                'Do NOT place child on calorie-restricted diet without specialist guidance',
                'Focus on improving food quality, not restricting volume at meal times',
                'Whole-family dietary changes are more effective than child-only interventions',
                'Refer to paediatrician for comorbidity screening',
            ]

        elif diagnosis == 'Overweight':
            plan['phase']             = 'Dietary quality improvement'
            plan['feeding_frequency'] = '3 structured meals + 1-2 healthy snacks'
            plan['foods_to_reduce']   = [
                'Sweetened beverages and fruit juices',
                'High-fat processed snacks',
                'Excess added sugars and oils',
            ]
            plan['foods_to_increase'] = [
                'Fruits and vegetables at every meal',
                'Adequate dietary fibre (wholegrains, legumes)',
                'Water as the main drink',
            ]
            plan['physical_activity'] = 'At least 60 min of active play per day'
            plan['special_instructions'] = [
                'Do not restrict meals; improve food choices and portion sizes',
                'Involve the whole family in healthy eating habits',
                'Avoid using food as reward or comfort',
            ]

        else:  # Normal
            plan['phase']             = 'Maintenance nutrition'
            plan['feeding_frequency'] = '3 main meals + 1-2 snacks'
            plan['diet_diversity']    = 'Include foods from all food groups daily'
            plan['portion_guidance']  = 'Age-appropriate portions'

        if age_months <= 24:
            plan['breastfeeding'] = (
                'Continue exclusive/complementary breastfeeding'
                if breastfeeding.lower() == 'yes'
                else 'Restart breastfeeding if possible, or ensure adequate milk intake'
            )

        return plan

    def _generate_medical_plan(self, diagnosis: str, age_months: int, weight: float,
                                has_edema: bool,
                                high_risk_factors: List[str]) -> Dict[str, Any]:
        """Generate medical intervention plan."""

        plan: Dict[str, Any] = {
            'routine_medications':     [],
            'therapeutic_medications': [],
            'medical_monitoring':      [],
            'specialist_referrals':    [],
        }

        # Routine micronutrients apply only to under-nutrition
        if 'Severe' in diagnosis or 'Moderate' in diagnosis:
            plan['routine_medications'].extend([
                (f"Vitamin A: {'100,000 IU' if age_months < 12 else '200,000 IU'} "
                 f"immediately, then every 6 months"),
                "Iron + Folate: 2-6 mg/kg/day for 12 weeks",
                "Zinc: 10-20 mg daily for 10-14 days if diarrhoea present",
            ])

        if 'Severe' in diagnosis:
            plan['therapeutic_medications'].extend([
                "Amoxicillin: 15 mg/kg TID for 7 days (routine antibiotic)",
                "Deworming: Albendazole 400 mg single dose (if >2 years)",
                "Measles vaccination if not up to date",
            ])
            if has_edema:
                plan['therapeutic_medications'].append(
                    "Monitor electrolytes closely — risk of refeeding syndrome"
                )
            plan['medical_monitoring'] = [
                "Daily weight monitoring",
                "Temperature, pulse, respiratory rate every 4 hours",
                "Watch for signs of infection",
                "Monitor for refeeding syndrome",
                "Assess for medical complications daily",
            ]

        elif 'Moderate' in diagnosis:
            plan['medical_monitoring'] = [
                "Weekly weight monitoring",
                "Bi-weekly health assessment",
                "Monitor for signs of deterioration",
            ]

        elif diagnosis == 'Obese':
            plan['therapeutic_medications'].extend([
                "Screen for Vitamin D deficiency; supplement if <20 ng/mL",
                "Screen for iron-deficiency anaemia",
                "No anti-obesity pharmacotherapy under 5 years without specialist authorisation",
            ])
            plan['specialist_referrals'].extend([
                "Paediatrician — comorbidity screening (hypertension, insulin resistance, dyslipidaemia)",
                "Registered dietitian — individualised meal plan",
                "Paediatric endocrinology — if endocrine or genetic cause suspected",
            ])
            plan['medical_monitoring'] = [
                "Monthly weight, height, BMI-for-age",
                "Blood pressure at every visit (if >= 3 years)",
                "Fasting glucose and lipid profile every 6 months if clinically indicated",
                "Dietary intake diary reviewed at each visit",
            ]

        elif diagnosis == 'Overweight':
            plan['therapeutic_medications'].extend([
                "Screen for Vitamin D deficiency; supplement if deficient",
                "Screen for iron-deficiency anaemia",
            ])
            plan['specialist_referrals'].append(
                "Registered dietitian or nutrition counselor for family dietary guidance"
            )
            plan['medical_monitoring'] = [
                "Monthly weight and height",
                "BMI-for-age trend tracked at each visit",
                "Blood pressure if >= 3 years",
                "Dietary assessment every 3 months",
            ]

        else:  # Normal
            plan['medical_monitoring'] = [
                "Monthly growth monitoring",
                "Routine health checks as per schedule",
            ]

        # Risk-factor-driven specialist referrals (additive)
        if 'anthropometric_risk' in high_risk_factors:
            plan['specialist_referrals'].append("Pediatric nutritionist consultation")
        if 'clinical_risk' in high_risk_factors:
            plan['specialist_referrals'].append("Pediatrician evaluation for underlying conditions")
        if 'socioeconomic_risk' in high_risk_factors:
            plan['specialist_referrals'].append("Social worker assessment for family support")

        return plan

    def _generate_monitoring_schedule(self, diagnosis: str, confidence: float,
                                       total_risk_score: int) -> Dict[str, Any]:
        """Generate monitoring and follow-up schedule."""

        if 'Severe' in diagnosis:
            schedule: Dict[str, Any] = {
                'phase_1_stabilization': {
                    'duration':    '7-10 days',
                    'frequency':   'Daily visits',
                    'assessments': [
                        'Weight, height, MUAC',
                        'Appetite test',
                        'Medical examination',
                        'RUTF intake monitoring',
                    ],
                },
                'phase_2_rehabilitation': {
                    'duration':    '6-8 weeks',
                    'frequency':   'Weekly visits',
                    'assessments': [
                        'Weight monitoring',
                        'RUTF consumption',
                        'Medical complications screen',
                    ],
                },
            }

        elif 'Moderate' in diagnosis:
            schedule = {
                'supplementary_feeding': {
                    'duration':    '8-12 weeks',
                    'frequency':   'Weekly visits first month, then bi-weekly',
                    'assessments': [
                        'Weight gain monitoring',
                        'Food distribution and consumption',
                        'Feeding practice assessment',
                    ],
                },
            }

        elif diagnosis == 'Obese':
            schedule = {
                'weight_management_monitoring': {
                    'duration':    'Ongoing',
                    'frequency':   'Monthly for first 3 months, then quarterly physician review',
                    'assessments': [
                        'Weight, height, BMI-for-age',
                        'Blood pressure (if >= 3 years)',
                        'Fasting glucose and lipids (if clinically indicated)',
                        'Dietary intake diary review',
                        'Physical activity log',
                    ],
                },
                'annual_review': {
                    'duration':    'Yearly',
                    'frequency':   'Annual comprehensive assessment',
                    'assessments': [
                        'Developmental milestone review',
                        'Psychological well-being screen',
                        'Comorbidity re-evaluation',
                    ],
                },
            }

        elif diagnosis == 'Overweight':
            schedule = {
                'dietary_monitoring': {
                    'duration':    '6 months active, then quarterly',
                    'frequency':   'Monthly for first 6 months',
                    'assessments': [
                        'Weight and height monthly',
                        'BMI-for-age trend',
                        'Dietary practices review',
                        'Physical activity assessment',
                    ],
                },
            }

        else:  # Normal
            schedule = {
                'preventive_monitoring': {
                    'duration':    'Ongoing',
                    'frequency':   'Monthly growth monitoring',
                    'assessments': [
                        'Growth monitoring (weight, height)',
                        'Feeding practice review',
                        'General health assessment',
                    ],
                },
            }

        if confidence < 0.6 or total_risk_score > 10:
            schedule['special_note'] = (
                'Increased monitoring frequency due to low confidence or high risk score'
            )

        return schedule

    def _generate_follow_up_plan(self, diagnosis: str, age_months: int) -> Dict:
        """Generate long-term follow-up plan."""
        today = datetime.now()

        if 'Severe' in diagnosis:
            return {
                'next_assessment':          (today + timedelta(days=3)).strftime('%Y-%m-%d'),
                'key_milestone_appetite':   (today + timedelta(weeks=2)).strftime('%Y-%m-%d') + ' — Appetite return',
                'key_milestone_weight':     (today + timedelta(weeks=6)).strftime('%Y-%m-%d') + ' — Target weight achievement',
                'discharge_evaluation':     (today + timedelta(weeks=8)).strftime('%Y-%m-%d'),
                'post_discharge_followup':  (today + timedelta(weeks=12)).strftime('%Y-%m-%d'),
            }

        elif 'Moderate' in diagnosis:
            return {
                'next_assessment':        (today + timedelta(days=7)).strftime('%Y-%m-%d'),
                'monthly_review':         (today + timedelta(weeks=4)).strftime('%Y-%m-%d'),
                'target_achievement':     (today + timedelta(weeks=12)).strftime('%Y-%m-%d'),
                'graduation_evaluation':  (today + timedelta(weeks=16)).strftime('%Y-%m-%d'),
            }

        elif diagnosis == 'Obese':
            return {
                'next_assessment':             (today + timedelta(days=30)).strftime('%Y-%m-%d'),
                'monthly_review_1':            (today + timedelta(weeks=4)).strftime('%Y-%m-%d'),
                'monthly_review_2':            (today + timedelta(weeks=8)).strftime('%Y-%m-%d'),
                'monthly_review_3':            (today + timedelta(weeks=12)).strftime('%Y-%m-%d'),
                'quarterly_physician_review':  (today + timedelta(weeks=16)).strftime('%Y-%m-%d'),
                'annual_comprehensive_review': (today + timedelta(weeks=52)).strftime('%Y-%m-%d'),
                'monitoring_note':             (
                    'Continue monthly until BMI-for-age below +2.5 SD, then quarterly'
                ),
            }

        elif diagnosis == 'Overweight':
            return {
                'next_assessment':        (today + timedelta(days=30)).strftime('%Y-%m-%d'),
                'month_3_review':         (today + timedelta(weeks=12)).strftime('%Y-%m-%d'),
                'month_6_review':         (today + timedelta(weeks=24)).strftime('%Y-%m-%d'),
                'transition_to_quarterly': (today + timedelta(weeks=28)).strftime('%Y-%m-%d'),
                'monitoring_note':        (
                    'If BMI-for-age normalises at 6 months, shift to quarterly growth check'
                ),
            }

        else:  # Normal
            return {
                'next_routine_checkup':    (today + timedelta(weeks=4)).strftime('%Y-%m-%d'),
                'growth_monitoring':       'Every 3 months until 5 years old',
                'nutritional_counseling':  'Annual or as needed',
            }

    def _generate_family_education(self, patient_data: Dict, diagnosis: str) -> List[str]:
        """Generate family education and counselling points."""
        education = [
            "Importance of consistent feeding and medication compliance",
            "Signs of improvement to watch for",
            "Warning signs requiring immediate medical attention",
        ]

        age_months = int(patient_data.get('age_months', 12))

        if age_months <= 6:
            education.extend([
                "Exclusive breastfeeding techniques and benefits",
                "Proper positioning and attachment for breastfeeding",
                "When and how to introduce complementary foods",
            ])
        elif age_months <= 24:
            education.extend([
                "Continued breastfeeding alongside complementary foods",
                "Appropriate food textures and consistency for age",
                "Responsive feeding practices",
            ])
        else:
            education.extend([
                "Family meal planning for nutritious diets",
                "Food hygiene and safety practices",
                "Encouraging self-feeding skills",
            ])

        if 'Severe' in diagnosis:
            education.extend([
                "Critical importance of RUTF compliance",
                "How to prepare and store RUTF properly",
                "Signs of medical complications requiring immediate visit",
            ])
        elif 'Moderate' in diagnosis:
            education.extend([
                "Local foods that are energy and nutrient dense",
                "Cost-effective ways to improve diet quality",
                "Recipe modifications to increase nutritional value",
            ])
        elif diagnosis == 'Obese':
            education.extend([
                "Understanding BMI-for-age and what it means for child health",
                "Practical strategies to reduce high-calorie foods at home",
                "How to promote active play without stigmatising the child",
                "Importance of the whole family adopting healthy habits",
            ])
        elif diagnosis == 'Overweight':
            education.extend([
                "Traffic-light food system: go, slow, and whoa foods",
                "Role of physical activity in healthy child growth",
                "Healthy snack alternatives to high-sugar/high-fat options",
            ])

        if patient_data.get('4ps_beneficiary') == 'Yes':
            education.append("Available social support programmes and how to access them")

        return education

    def _define_success_criteria(self, diagnosis: str, age_months: int) -> Dict:
        """Define measurable success criteria for treatment."""

        if 'Severe' in diagnosis:
            return {
                'short_term': [
                    'Weight gain >= 5 g/kg/day for 3 consecutive days',
                    'Improved appetite and increased RUTF consumption',
                    'Absence of medical complications',
                ],
                'medium_term': [
                    'WHZ score improvement > -2 SD',
                    'MUAC >= 125 mm (if applicable)',
                    'Sustained weight gain for 2 weeks',
                ],
                'long_term': [
                    'Normal growth velocity maintenance',
                    'No relapse for 6 months post-discharge',
                    'Age-appropriate developmental milestones',
                ],
            }

        elif 'Moderate' in diagnosis:
            return {
                'short_term': [
                    'Consistent weight gain >= 3 g/kg/day',
                    'Improved dietary diversity',
                    'Good programme compliance',
                ],
                'medium_term': [
                    'WHZ score >= -2 SD',
                    'Maintained weight gain for 4 weeks',
                    'Improved feeding practices',
                ],
                'long_term': [
                    'Normal growth pattern establishment',
                    'Sustained nutritional improvement',
                    'Family self-sufficiency in nutrition management',
                ],
            }

        elif diagnosis == 'Obese':
            return {
                'short_term': [
                    'No further increase in BMI-for-age SD score',
                    'Reduction in sweetened beverages and processed foods',
                    'Increase in active play to >= 30 min/day',
                ],
                'medium_term': [
                    'BMI-for-age below +3 SD (or downward trend over 3 months)',
                    'No new comorbidities; existing comorbidities managed',
                    'Whole-family dietary habits improved',
                ],
                'long_term': [
                    'BMI-for-age below +2 SD sustained for 2 consecutive visits',
                    'Normal blood pressure and metabolic markers (if previously abnormal)',
                    'Age-appropriate physical activity maintained',
                ],
            }

        elif diagnosis == 'Overweight':
            return {
                'short_term': [
                    'No further increase in BMI-for-age SD score',
                    'Improved dietary quality (more fruits, vegetables, water)',
                    'Active play >= 60 min/day established',
                ],
                'medium_term': [
                    'BMI-for-age below +2 SD or evidence of downward trend',
                    'Sustained healthy eating habits over 3 months',
                ],
                'long_term': [
                    'Normal BMI-for-age maintained at 6-month follow-up',
                    'Family demonstrates independent healthy lifestyle practices',
                ],
            }

        else:  # Normal
            return {
                'ongoing': [
                    'Maintain normal growth velocity',
                    'Prevent malnutrition occurrence',
                    'Optimal feeding practice continuation',
                ],
            }

    def _define_discharge_criteria(self, diagnosis: str) -> List[str]:
        """Define criteria for programme discharge/graduation."""

        if 'Severe' in diagnosis:
            return [
                'WHZ score >= -2 SD maintained for 2 consecutive weeks',
                'Weight gain >= 5 g/kg/day for minimum 3 consecutive days',
                'Absence of oedema for 2 weeks',
                'Good appetite and eating habits established',
                'No medical complications present',
                'Caregiver demonstrates proper feeding practices',
            ]

        elif 'Moderate' in diagnosis:
            return [
                'WHZ score >= -2 SD for 2 consecutive measurements',
                'Consistent weight gain over 4 weeks',
                'Improved dietary diversity demonstrated',
                'Family shows good feeding practices',
                'No signs of deterioration',
            ]

        elif diagnosis == 'Obese':
            return [
                'BMI-for-age below +2.5 SD for 2 consecutive monthly visits',
                'No active comorbidities (or comorbidities fully managed)',
                'Family demonstrates sustained behaviour change in diet and physical activity',
                'Child shows age-appropriate physical activity habits',
            ]

        elif diagnosis == 'Overweight':
            return [
                'BMI-for-age below +2 SD for 2 consecutive monthly visits',
                'Sustained healthy eating practices documented at home',
                'Active play >= 60 min/day maintained for at least 1 month',
            ]

        else:  # Normal
            return [
                'Continued normal growth trajectory',
                'Optimal feeding practices maintained',
                'No nutritional concerns identified',
            ]

    def _define_emergency_signs(self, age_months: int) -> List[str]:
        """Define emergency warning signs requiring immediate medical attention."""
        signs = [
            'High fever (>38.5 C)',
            'Difficulty breathing or fast breathing',
            'Vomiting everything eaten',
            'Blood in stool',
            'Severe dehydration',
            'Convulsions or loss of consciousness',
            'Severe oedema or rapid oedema increase',
        ]

        if age_months < 12:
            signs.extend([
                'Not breastfeeding or drinking',
                'Lethargy or difficult to wake',
                'Sunken fontanelle',
            ])
        else:
            signs.extend([
                'Not eating or drinking anything',
                'Extreme weakness',
                'Persistent crying',
            ])

        return signs

    # ------------------------------------------------------------------
    # Helper methods
    # ------------------------------------------------------------------

    def _calculate_target_weight(self, age_months: int, current_weight: float) -> float:
        """Calculate realistic target weight based on age (WHO approximation)."""
        age_months = int(age_months)

        if age_months <= 12:
            target = (age_months * 0.5) + 3.5
        elif age_months <= 24:
            target = ((age_months - 12) * 0.25) + 9.5
        else:
            target = ((age_months - 24) * 0.2) + 12.5

        # For under-nourished children, target should be at least 10 % above current weight
        return round(max(target, current_weight * 1.1), 2)


# ----------------------------------------------------------------------
# Demo / manual test
# ----------------------------------------------------------------------

def demonstrate_treatment_plan():
    """Demonstrate comprehensive treatment plan generation."""
    print("COMPREHENSIVE TREATMENT PLAN DEMONSTRATION")
    print("=" * 70)

    patient_data = {
        'name':                   'Sofia Mendez',
        'age_months':             18,
        'sex':                    'female',
        'weight':                 8.0,
        'height':                 75.0,
        'municipality':           'Quezon City',
        'total_household':        5,
        'adults':                 2,
        'children':               3,
        'twins':                  0,
        '4ps_beneficiary':        'Yes',
        'breastfeeding':          'No',
        'edema':                  False,
        'tuberculosis':           'No',
        'malaria':                'No',
        'congenital_anomalies':   'No',
        'other_medical_problems': 'No',
        'whz_score':              -2.8,
    }

    ml_result = {
        'prediction': 'Moderate Acute Malnutrition (MAM)',
        'probabilities': {
            'Normal':                             0.15,
            'Moderate Acute Malnutrition (MAM)': 0.70,
            'Severe Acute Malnutrition (SAM)':   0.15,
        },
        'bmi': 14.22,
    }

    risk_assessment = {
        'anthropometric_risk': {'risk_level': 'High',     'risk_score': 6},
        'clinical_risk':       {'risk_level': 'Low',      'risk_score': 0},
        'socioeconomic_risk':  {'risk_level': 'Moderate', 'risk_score': 3},
    }

    planner = PersonalizedTreatmentPlanner()
    plan = planner.generate_comprehensive_treatment_plan(
        patient_data, ml_result, risk_assessment, {}
    )

    print(f"\nPatient: {plan['patient_info']['name']}")
    print(f"Diagnosis: {plan['patient_info']['diagnosis']}")
    print(f"Assessment Date: {plan['patient_info']['assessment_date']}")

    print("\nImmediate Actions:")
    for action in plan['immediate_actions']:
        print(f"  - {action}")

    print("\nNutrition Plan:")
    for k, v in plan['nutrition_plan'].items():
        print(f"  {k}: {v}")

    print("\nFollow-up Dates:")
    for k, v in plan['follow_up_plan'].items():
        print(f"  {k}: {v}")

    return plan


if __name__ == '__main__':
    demonstrate_treatment_plan()
