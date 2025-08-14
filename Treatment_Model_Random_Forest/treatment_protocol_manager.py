"""
Flexible Treatment Protocol System
Allows loading and customizing treatment protocols from external JSON files
"""

import json
import os
from pathlib import Path
from typing import Dict, Any, List, Optional
import logging

class TreatmentProtocolManager:
    """
    Manages flexible treatment protocols loaded from JSON configuration files
    """
    
    def __init__(self, protocol_directory: str = "treatment_protocols"):
        """
        Initialize the protocol manager
        
        Args:
            protocol_directory: Directory containing protocol JSON files
        """
        self.protocol_directory = Path(protocol_directory)
        self.protocols = {}
        self.active_protocol = "who_standard"
        self.logger = self._setup_logger()
        
        # Ensure protocol directory exists
        self.protocol_directory.mkdir(exist_ok=True)
        
        # Load all available protocols
        self.load_all_protocols()
    
    def _setup_logger(self):
        """Setup logging for the protocol manager"""
        logger = logging.getLogger('TreatmentProtocol')
        logger.setLevel(logging.INFO)
        
        if not logger.handlers:
            handler = logging.StreamHandler()
            formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
            handler.setFormatter(formatter)
            logger.addHandler(handler)
        
        return logger
    
    def load_all_protocols(self):
        """Load all protocol files from the protocol directory"""
        try:
            for protocol_file in self.protocol_directory.glob("*.json"):
                protocol_name = protocol_file.stem
                self.load_protocol(protocol_name)
            
            if not self.protocols:
                self.logger.warning("No protocol files found. Creating default WHO protocol.")
                self._create_default_protocol()
            
        except Exception as e:
            self.logger.error(f"Error loading protocols: {e}")
            self._create_default_protocol()
    
    def load_protocol(self, protocol_name: str) -> bool:
        """
        Load a specific protocol from JSON file
        
        Args:
            protocol_name: Name of the protocol file (without .json extension)
            
        Returns:
            bool: True if loaded successfully, False otherwise
        """
        try:
            protocol_file = self.protocol_directory / f"{protocol_name}.json"
            
            if not protocol_file.exists():
                self.logger.error(f"Protocol file not found: {protocol_file}")
                return False
            
            with open(protocol_file, 'r', encoding='utf-8') as f:
                protocol_data = json.load(f)
            
            # Validate protocol structure
            if self._validate_protocol(protocol_data):
                self.protocols[protocol_name] = protocol_data
                self.logger.info(f"Successfully loaded protocol: {protocol_name}")
                return True
            else:
                self.logger.error(f"Invalid protocol structure in: {protocol_name}")
                return False
                
        except Exception as e:
            self.logger.error(f"Error loading protocol {protocol_name}: {e}")
            return False
    
    def _validate_protocol(self, protocol_data: Dict[str, Any]) -> bool:
        """
        Validate protocol data structure
        
        Args:
            protocol_data: Protocol data to validate
            
        Returns:
            bool: True if valid, False otherwise
        """
        required_fields = ['version', 'description', 'protocols']
        
        # Check top-level required fields
        for field in required_fields:
            if field not in protocol_data:
                self.logger.error(f"Missing required field: {field}")
                return False
        
        # Check protocol structure
        protocols = protocol_data.get('protocols', {})
        expected_statuses = [
            "Severe Acute Malnutrition (SAM)",
            "Moderate Acute Malnutrition (MAM)",
            "Normal"
        ]
        
        for status in expected_statuses:
            if status not in protocols:
                self.logger.warning(f"Missing protocol for status: {status}")
        
        return True
    
    def set_active_protocol(self, protocol_name: str) -> bool:
        """
        Set the active protocol to use for recommendations
        
        Args:
            protocol_name: Name of the protocol to activate
            
        Returns:
            bool: True if successful, False otherwise
        """
        if protocol_name in self.protocols:
            self.active_protocol = protocol_name
            self.logger.info(f"Activated protocol: {protocol_name}")
            return True
        else:
            self.logger.error(f"Protocol not found: {protocol_name}")
            return False
    
    def get_available_protocols(self) -> List[str]:
        """Get list of available protocol names"""
        return list(self.protocols.keys())
    
    def get_protocol_info(self, protocol_name: str = None) -> Dict[str, Any]: # type: ignore
        """
        Get information about a protocol
        
        Args:
            protocol_name: Name of protocol (uses active if None)
            
        Returns:
            Dict containing protocol information
        """
        if protocol_name is None:
            protocol_name = self.active_protocol
        
        if protocol_name not in self.protocols:
            return {}
        
        protocol = self.protocols[protocol_name]
        return {
            'name': protocol_name,
            'version': protocol.get('version', 'Unknown'),
            'description': protocol.get('description', 'No description'),
            'statuses': list(protocol.get('protocols', {}).keys())
        }
    
    def get_treatment_recommendation(self, status: str, patient_data: Dict[str, Any], 
                                   protocol_name: str = None) -> Dict[str, Any]: # type: ignore
        """
        Get treatment recommendation based on active protocol
        
        Args:
            status: Malnutrition status
            patient_data: Patient data dictionary
            protocol_name: Protocol to use (uses active if None)
            
        Returns:
            Dict containing treatment recommendation
        """
        if protocol_name is None:
            protocol_name = self.active_protocol
        
        if protocol_name not in self.protocols:
            return self._get_fallback_recommendation(status, patient_data)
        
        try:
            protocol = self.protocols[protocol_name]
            protocols_data = protocol.get('protocols', {})
            
            if status not in protocols_data:
                return self._get_fallback_recommendation(status, patient_data)
            
            status_protocols = protocols_data[status]
            
            # Determine specific protocol based on conditions
            if status == "Severe Acute Malnutrition (SAM)":
                if patient_data.get('edema', False):
                    protocol_key = 'with_edema'
                else:
                    protocol_key = 'without_edema'
            else:
                protocol_key = 'standard'
            
            if protocol_key in status_protocols:
                recommendation = status_protocols[protocol_key].copy()
                
                # Add protocol metadata
                recommendation['protocol_used'] = protocol_name
                recommendation['protocol_version'] = protocol.get('version', 'Unknown')
                
                # Add risk assessment if available
                recommendation.update(self._assess_risk_factors(patient_data, protocol))
                
                return recommendation
            else:
                return self._get_fallback_recommendation(status, patient_data)
                
        except Exception as e:
            self.logger.error(f"Error getting recommendation: {e}")
            return self._get_fallback_recommendation(status, patient_data)
    
    def _assess_risk_factors(self, patient_data: Dict[str, Any], 
                           protocol: Dict[str, Any]) -> Dict[str, Any]:
        """
        Assess additional risk factors based on protocol
        
        Args:
            patient_data: Patient data
            protocol: Protocol configuration
            
        Returns:
            Dict with risk assessment
        """
        risk_info = {}
        
        risk_factors = protocol.get('risk_factors', {})
        
        # Check high priority conditions
        high_priority = risk_factors.get('high_priority_conditions', [])
        triggered_conditions = []
        
        for condition in high_priority:
            if condition == 'age_months < 6' and patient_data.get('age_months', 12) < 6:
                triggered_conditions.append('infant_under_6_months')
            elif condition in patient_data and patient_data[condition]:
                triggered_conditions.append(condition)
        
        if triggered_conditions:
            risk_info['high_priority_conditions'] = triggered_conditions
            risk_info['requires_urgent_attention'] = True
        
        # Check emergency criteria
        emergency_criteria = protocol.get('emergency_criteria', {})
        immediate_referral = emergency_criteria.get('immediate_referral', [])
        
        emergency_flags = []
        for criteria in immediate_referral:
            if criteria == 'whz_score < -4' and patient_data.get('whz_score', 0) < -4:
                emergency_flags.append('severe_wasting')
            elif criteria in patient_data and patient_data[criteria]:
                emergency_flags.append(criteria)
        
        if emergency_flags:
            risk_info['emergency_referral_needed'] = True
            risk_info['emergency_reasons'] = emergency_flags
        
        return risk_info
    
    def _get_fallback_recommendation(self, status: str, patient_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Provide fallback recommendation when protocol fails
        
        Args:
            status: Malnutrition status
            patient_data: Patient data
            
        Returns:
            Basic treatment recommendation
        """
        fallback_recommendations = {
            "Severe Acute Malnutrition (SAM)": {
                'treatment': 'Immediate medical attention required',
                'details': 'Refer to nearest health facility for SAM management',
                'follow_up': 'Urgent medical evaluation needed',
                'priority': 'critical'
            },
            "Moderate Acute Malnutrition (MAM)": {
                'treatment': 'Nutritional support program',
                'details': 'Provide supplementary feeding and nutrition counseling',
                'follow_up': 'Regular monitoring recommended',
                'priority': 'medium'
            },
            "Normal": {
                'treatment': 'Routine care',
                'details': 'Continue regular nutrition and health monitoring',
                'follow_up': 'Standard follow-up schedule',
                'priority': 'low'
            }
        }
        
        recommendation = fallback_recommendations.get(status, {
            'treatment': 'Consult healthcare provider',
            'details': 'Unable to determine specific treatment',
            'follow_up': 'Medical evaluation recommended',
            'priority': 'unknown'
        })
        
        recommendation['protocol_used'] = 'fallback'
        recommendation['note'] = 'Using fallback recommendation due to protocol error'
        
        return recommendation
    
    def _create_default_protocol(self):
        """Create a basic default protocol if none exist"""
        default_protocol = {
            "version": "1.0",
            "description": "Basic default WHO protocol",
            "protocols": {
                "Severe Acute Malnutrition (SAM)": {
                    "with_edema": {
                        "treatment": "Inpatient therapeutic care",
                        "details": "Immediate medical attention required",
                        "follow_up": "Daily monitoring",
                        "priority": "critical"
                    },
                    "without_edema": {
                        "treatment": "Outpatient therapeutic care",
                        "details": "RUTF and medical supervision",
                        "follow_up": "Weekly monitoring",
                        "priority": "high"
                    }
                },
                "Moderate Acute Malnutrition (MAM)": {
                    "standard": {
                        "treatment": "Supplementary feeding",
                        "details": "Nutritional support and counseling",
                        "follow_up": "Bi-weekly monitoring",
                        "priority": "medium"
                    }
                },
                "Normal": {
                    "standard": {
                        "treatment": "Routine care",
                        "details": "Regular health monitoring",
                        "follow_up": "Monthly check-ups",
                        "priority": "low"
                    }
                }
            }
        }
        
        self.protocols['default'] = default_protocol
        self.active_protocol = 'default'
    
    def create_custom_protocol(self, protocol_name: str, protocol_data: Dict[str, Any]) -> bool:
        """
        Create a new custom protocol
        
        Args:
            protocol_name: Name for the new protocol
            protocol_data: Protocol configuration data
            
        Returns:
            bool: True if created successfully
        """
        try:
            if not self._validate_protocol(protocol_data):
                return False
            
            protocol_file = self.protocol_directory / f"{protocol_name}.json"
            
            with open(protocol_file, 'w', encoding='utf-8') as f:
                json.dump(protocol_data, f, indent=2, ensure_ascii=False)
            
            self.protocols[protocol_name] = protocol_data
            self.logger.info(f"Created custom protocol: {protocol_name}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error creating custom protocol: {e}")
            return False
    
    def export_protocol(self, protocol_name: str, output_path: str) -> bool:
        """
        Export a protocol to a JSON file
        
        Args:
            protocol_name: Name of protocol to export
            output_path: Path to save the exported protocol
            
        Returns:
            bool: True if exported successfully
        """
        try:
            if protocol_name not in self.protocols:
                self.logger.error(f"Protocol not found: {protocol_name}")
                return False
            
            with open(output_path, 'w', encoding='utf-8') as f:
                json.dump(self.protocols[protocol_name], f, indent=2, ensure_ascii=False)
            
            self.logger.info(f"Exported protocol {protocol_name} to {output_path}")
            return True
            
        except Exception as e:
            self.logger.error(f"Error exporting protocol: {e}")
            return False
