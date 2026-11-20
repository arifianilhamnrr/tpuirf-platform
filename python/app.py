from flask import Flask, request, jsonify
from ml_pipeline import TPUIRFPipeline
import os
import threading

app = Flask(__name__)
active_pipeline = TPUIRFPipeline()
state_lock = threading.RLock()
train_lock = threading.Lock()
training_state = {
    "in_progress": False,
    "last_status": "never",
    "last_error": None,
}

try:
    active_pipeline.load_model()
    print("Model loaded successfully")
except Exception:
    print("No compatible trained model found. Please train first.")


def current_pipeline():
    with state_lock:
        return active_pipeline


@app.route('/predict', methods=['POST'])
def predict():
    data = request.json or {}
    try:
        result = current_pipeline().predict(data)
        return jsonify(result)
    except ValueError as e:
        return jsonify({'error': str(e)}), 422
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/explain', methods=['POST'])
def explain():
    data = request.json or {}
    try:
        result = current_pipeline().explain(data)
        return jsonify(result)
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/train', methods=['POST'])
def train():
    global active_pipeline
    data_path = (request.json or {}).get(
        'data_path',
        os.path.join(os.path.dirname(__file__), '..', 'storage', 'app', 'exports', 'training_dataset.csv'),
    )

    if not train_lock.acquire(blocking=False):
        return jsonify({'error': 'Training already in progress'}), 409

    training_state.update({"in_progress": True, "last_status": "training", "last_error": None})
    try:
        candidate = TPUIRFPipeline(model_dir=active_pipeline.model_dir)
        result = candidate.train(data_path, save=False)
        if not candidate.models:
            raise ValueError('Candidate model tidak berisi target terlatih')

        # Persist only after candidate validation; os.replace() inside save_model
        # makes artifact promotion atomic for future worker starts.
        candidate.save_model()
        with state_lock:
            active_pipeline = candidate
        training_state.update({"last_status": "success", "last_error": None})
        return jsonify(result)
    except ValueError as e:
        training_state.update({"last_status": "failed", "last_error": str(e)})
        return jsonify({'error': str(e)}), 422
    except Exception as e:
        training_state.update({"last_status": "failed", "last_error": str(e)})
        return jsonify({'error': str(e)}), 500
    finally:
        training_state["in_progress"] = False
        train_lock.release()


@app.route('/accuracy', methods=['GET'])
def accuracy():
    pipeline = current_pipeline()
    return jsonify({
        'accuracy': pipeline.metrics.get('accuracy', 0),
        'models': ['xgboost'] if pipeline.models else [],
        'targets': list(pipeline.models.keys()),
        'target_metrics': pipeline.metrics.get('target_metrics', {}),
        'model_version': pipeline.model_version,
    })


@app.route('/feature_importance', methods=['GET'])
def feature_importance():
    pipeline = current_pipeline()
    if not pipeline.models:
        return jsonify({'error': 'Model not trained'}), 400
    return jsonify(pipeline.feature_importance())


@app.route('/health', methods=['GET'])
def health():
    pipeline = current_pipeline()
    return jsonify({
        'status': 'healthy',
        'model_loaded': bool(pipeline.models),
        'models': ['xgboost'] if pipeline.models else [],
        'targets': list(pipeline.models.keys()),
        'feature_names': pipeline.feature_names,
        'target_names': pipeline.targets,
        'target_metrics': pipeline.metrics.get('target_metrics', {}),
        'skipped_targets': pipeline.metrics.get('skipped_targets', {}),
        'model_version': pipeline.model_version,
        'accuracy': pipeline.metrics.get('accuracy'),
        'training_in_progress': training_state['in_progress'],
        'last_training_status': training_state['last_status'],
    })


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
