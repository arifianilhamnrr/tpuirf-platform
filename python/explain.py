import sys
import json
from ml_pipeline import TPUIRFPipeline

if __name__ == '__main__':
    user_data = json.loads(sys.argv[1]) if len(sys.argv) > 1 else {}
    pipeline = TPUIRFPipeline()
    pipeline.load_model()
    result = pipeline.explain(user_data)
    print(json.dumps(result, indent=2))
