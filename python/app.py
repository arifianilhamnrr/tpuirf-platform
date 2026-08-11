from flask import Flask, request, jsonify
from ml_pipeline import MODEL_VERSION, TPUIRFPipeline
import os

app = Flask(__name__)
pipeline = TPUIRFPipeline()

try:
    pipeline.load_model()
    print("Model loaded successfully")
except Exception:
    print("No compatible trained model found. Please train first.")

@app.route('/predict', methods=['POST'])
def predict():
    data = request.json
    try:
        result = pipeline.predict(data)
        return jsonify(result)
    except ValueError as e:
        return jsonify({'error': str(e)}), 422
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/explain', methods=['POST'])
def explain():
    data = request.json
    try:
        result = pipeline.explain(data)
        return jsonify(result)
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/train', methods=['POST'])
def train():
    data_path = (request.json or {}).get('data_path', os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'exports', 'full_dataset.csv'))
    try:
        result = pipeline.train(data_path)
        return jsonify(result)
    except ValueError as e:
        return jsonify({'error': str(e)}), 422
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/accuracy', methods=['GET'])
def accuracy():
    return jsonify({
        'accuracy': pipeline.metrics.get('accuracy', 0),
        'models': ['random_forest', 'xgboost'] if pipeline.models else [],
        'targets': list(pipeline.models.keys()),
        'target_metrics': pipeline.metrics.get('target_metrics', {}),
        'model_version': MODEL_VERSION,
    })

@app.route('/feature_importance', methods=['GET'])
def feature_importance():
    if not pipeline.models:
        return jsonify({'error': 'Model not trained'}), 400
    return jsonify(pipeline.feature_importance())

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'healthy',
        'model_loaded': bool(pipeline.models),
        'models': ['random_forest', 'xgboost'] if pipeline.models else [],
        'targets': list(pipeline.models.keys()),
        'target_metrics': pipeline.metrics.get('target_metrics', {}),
        'skipped_targets': pipeline.metrics.get('skipped_targets', {}),
        'model_version': MODEL_VERSION,
        'accuracy': pipeline.metrics.get('accuracy'),
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
