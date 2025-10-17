#!/usr/bin/env python3
"""
Gradio API Client for Image Classification
Processes image through Gradio API and returns structured results
"""

import sys
import json
import logging
import os
from typing import Dict, Any
from pathlib import Path

# Add user site-packages to path for Windows
import site
sys.path.insert(0, site.USER_SITE)

# Suppress all output except our JSON
import warnings
warnings.filterwarnings('ignore')

# Redirect stderr to devnull to suppress gradio_client messages
sys.stderr = open(os.devnull, 'w')

try:
    from gradio_client import Client, handle_file
except ImportError:
    print(json.dumps({
        "status": "error",
        "message": "gradio_client not installed. Run: pip install gradio_client"
    }))
    sys.exit(1)

# Configure logging to go nowhere
logging.basicConfig(
    level=logging.CRITICAL,
    handlers=[logging.NullHandler()]
)
logger = logging.getLogger(__name__)

# Configuration
GRADIO_ENDPOINT = "chandruganesh00/patholens-ai"
API_NAME = "/classify_image"
TIMEOUT = 120  # seconds
MAX_RETRIES = 2


def validate_image_path(path: str) -> bool:
    """Validate that the image file exists and is accessible"""
    image_path = Path(path)
    if not image_path.exists():
        raise FileNotFoundError(f"Image file not found: {path}")
    if not image_path.is_file():
        raise ValueError(f"Path is not a file: {path}")
    return True


def classify_image(image_path: str) -> Dict[str, Any]:
    """
    Classify image using Gradio API
    
    Args:
        image_path: Path to the image file
        
    Returns:
        Dictionary with classification results
    """
    for attempt in range(MAX_RETRIES):
        try:
            # Validate input
            validate_image_path(image_path)
            
            # Initialize Gradio client with timeout (suppress output by redirecting)
            client = Client(GRADIO_ENDPOINT)
            
            # Make prediction with timeout
            result = client.predict(
                img=handle_file(image_path),
                api_name=API_NAME
            )
            
            # Extract classification data (assuming last element contains the result)
            if not result or len(result) == 0:
                raise ValueError("Empty result from Gradio API")
            
            output = result[-1]
            
            # Validate output structure
            if not isinstance(output, dict):
                raise ValueError(f"Unexpected output format: {type(output)}")
            
            if 'label' not in output or 'confidence' not in output:
                raise ValueError("Missing required fields in API response")
            
            # Parse confidence value (handle both percentage strings and floats)
            confidence_value = output['confidence']
            if isinstance(confidence_value, str):
                # Remove '%' sign and convert to float, then divide by 100
                confidence_value = confidence_value.strip().rstrip('%')
                confidence = float(confidence_value) / 100.0
            else:
                confidence = float(confidence_value)
            
            # Ensure confidence is between 0 and 1
            confidence = max(0.0, min(1.0, confidence))
            
            # Return structured response
            return {
                "status": "success",
                "data": {
                    "label": str(output['label']),
                    "confidence": round(confidence, 4)
                }
            }
            
        except FileNotFoundError as e:
            return {
                "status": "error",
                "message": f"Image file error: {str(e)}"
            }
        
        except Exception as e:
            if attempt < MAX_RETRIES - 1:
                continue
            else:
                return {
                    "status": "error",
                    "message": f"Classification failed: {str(e)}"
                }
    
    return {
        "status": "error",
        "message": "Classification failed after all retry attempts"
    }


def main():
    """Main execution function"""
    if len(sys.argv) < 2:
        result = {
            "status": "error",
            "message": "Usage: python gradio_api.py <image_path>"
        }
    else:
        image_path = sys.argv[1]
        result = classify_image(image_path)
    
    # Output ONLY JSON to stdout (no extra text)
    print(json.dumps(result, indent=None), flush=True)


if __name__ == "__main__":
    main()