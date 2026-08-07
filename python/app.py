from flask import Flask, request, jsonify
from ml_pipeline import TPUIRFPipeline
import os

app = Flask(__name__)
pipeline = TPUIRFPipeline()

try:
    pipeline.load_model()
    print("Model loaded successfully")
except:
    print("No trained model found. Please train first.")

@app.route('/predict', methods=['POST'])
def predict():
    data = request.json
    try:
        result = pipeline.predict(data)
        return jsonify(result)
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
    data_path = request.json.get('data_path', os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'exports', 'full_dataset.csv'))
    try:
        result = pipeline.train(data_path)
        return jsonify(result)
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/accuracy', methods=['GET'])
def accuracy():
    return jsonify({'accuracy': getattr(pipeline.model, 'score', lambda: 0)})

@app.route('/feature_importance', methods=['GET'])
def feature_importance():
    if pipeline.model is None:
        return jsonify({'error': 'Model not trained'}), 400
    importances = pipeline.model.feature_importances_
    features = [{'feature': name, 'importance': float(imp)} for name, imp in zip(pipeline.feature_names, importances)]
    features.sort(key=lambda x: x['importance'], reverse=True)
    return jsonify(features)

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'healthy', 'model_loaded': pipeline.model is not None})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
