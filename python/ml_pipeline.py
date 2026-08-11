import os
import warnings

import joblib
import numpy as np
import pandas as pd
import shap
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from xgboost import XGBClassifier

warnings.filterwarnings("ignore")

MODEL_DIR = os.path.join(os.path.dirname(__file__), "models")
os.makedirs(MODEL_DIR, exist_ok=True)


class TPUIRFPipeline:
    def __init__(self):
        self.models = {}
        self.model = None
        self.label_encoders = {}
        self.target_encoder = LabelEncoder()
        self.feature_names = [
            "age", "gender", "education", "occupation", "digital_literacy",
            "device_type", "visual_preference", "context_environment", "ai_familiarity",
            "click_count", "scroll_count", "hover_count", "keyboard_count",
            "avg_task_time", "total_session_time",
            "theme", "color_palette", "font_family", "font_size",
            "layout_density", "navigation_position", "icon_style", "design_style",
        ]
        self.categorical_features = [
            "gender", "education", "occupation", "device_type",
            "visual_preference", "context_environment",
            "theme", "color_palette", "font_family", "font_size",
            "layout_density", "navigation_position", "icon_style", "design_style",
        ]
        self.class_names = []
        self.metrics = {}

    def preprocess(self, frame, fit=False):
        frame = frame.copy()
        for column in self.categorical_features:
            values = frame[column].fillna("unknown").astype(str) if column in frame else pd.Series(["unknown"])
            if fit or column not in self.label_encoders:
                self.label_encoders[column] = {value: index for index, value in enumerate(sorted(values.unique()))}
            frame[column] = values.map(self.label_encoders[column]).fillna(-1).astype(float)
        for column in self.feature_names:
            if column not in frame:
                frame[column] = 0
            if column not in self.categorical_features:
                frame[column] = pd.to_numeric(frame[column], errors="coerce").fillna(0)
        return frame[self.feature_names].astype(float)

    def train(self, data_path):
        frame = pd.read_csv(data_path)
        frame = frame.dropna(subset=["preferred_ui"])
        if frame["preferred_ui"].nunique() < 2:
            raise ValueError("Dataset training membutuhkan minimal dua kelas preferred_ui")
        features = self.preprocess(frame, fit=True)
        target = self.target_encoder.fit_transform(frame["preferred_ui"].astype(str))
        self.class_names = list(self.target_encoder.classes_)
        train_x, test_x, train_y, test_y = train_test_split(
            features, target, test_size=0.2, random_state=42, stratify=target if len(set(target)) > 1 else None
        )
        class_count = max(len(self.class_names), 2)
        self.models = {
            "random_forest": RandomForestClassifier(n_estimators=200, max_depth=10, random_state=42),
            "xgboost": XGBClassifier(
                n_estimators=150, max_depth=5, learning_rate=0.08, subsample=0.9,
                colsample_bytree=0.9, objective="multi:softprob", num_class=class_count,
                eval_metric="mlogloss", random_state=42,
            ),
        }
        for model in self.models.values():
            model.fit(train_x, train_y)
        self.model = self.models["xgboost"]
        predictions = self._probabilities(test_x).argmax(axis=1)
        self.metrics = {
            "accuracy": float(accuracy_score(test_y, predictions)),
            "classification_report": classification_report(test_y, predictions, output_dict=True, zero_division=0),
            "models": list(self.models.keys()),
        }
        self.save_model()
        return self.metrics

    def _probabilities(self, features):
        probabilities = [model.predict_proba(features) for model in self.models.values()]
        return np.mean(probabilities, axis=0)

    def _shap_values(self, model, features, class_index):
        values = shap.TreeExplainer(model)(features).values
        if values.ndim == 3:
            values = values[0, :, class_index]
        elif values.ndim == 2:
            values = values[0]
        return [float(value) for value in values]

    def predict(self, user_data):
        if not self.models:
            raise RuntimeError("Model belum dilatih")
        features = self.preprocess(pd.DataFrame([user_data]))
        probabilities = self._probabilities(features)[0]
        prediction = int(np.argmax(probabilities))
        model_results = {}
        explanations = []
        for name, model in self.models.items():
            model_probability = model.predict_proba(features)[0]
            model_results[name] = {
                "prediction": self.class_names[int(np.argmax(model_probability))],
                "confidence": float(np.max(model_probability)),
            }
            if name == "xgboost":
                shap_values = self._shap_values(model, features, prediction)
                explanations = sorted([
                    {
                        "feature": feature,
                        "shap_value": round(value, 6),
                        "impact": round(abs(value), 6),
                        "direction": "increases" if value >= 0 else "decreases",
                    }
                    for feature, value in zip(self.feature_names, shap_values)
                ], key=lambda item: item["impact"], reverse=True)[:10]
        return {
            "recommended_ui": self.class_names[prediction],
            "confidence_score": float(np.max(probabilities)),
            "probabilities": {name: float(value) for name, value in zip(self.class_names, probabilities)},
            "shap_values": [item["shap_value"] for item in explanations],
            "features": [item["feature"] for item in explanations],
            "explanations": explanations,
            "model_outputs": model_results,
            "model_version": "rf+xgboost+shap-v2",
        }

    def explain(self, user_data):
        result = self.predict(user_data)
        result["explanations"] = result.get("explanations", [])
        return result

    def feature_importance(self):
        if not self.models:
            return []
        importances = np.mean([
            model.feature_importances_ for model in self.models.values()
        ], axis=0)
        return sorted([
            {"feature": feature, "importance": float(importance), "models": list(self.models.keys())}
            for feature, importance in zip(self.feature_names, importances)
        ], key=lambda item: item["importance"], reverse=True)

    def save_model(self):
        joblib.dump(self.models, os.path.join(MODEL_DIR, "models.pkl"))
        joblib.dump(self.label_encoders, os.path.join(MODEL_DIR, "label_encoders.pkl"))
        joblib.dump(self.target_encoder, os.path.join(MODEL_DIR, "target_encoder.pkl"))
        joblib.dump(self.class_names, os.path.join(MODEL_DIR, "class_names.pkl"))
        joblib.dump(self.metrics, os.path.join(MODEL_DIR, "metrics.pkl"))

    def load_model(self):
        self.models = joblib.load(os.path.join(MODEL_DIR, "models.pkl"))
        self.model = self.models.get("xgboost")
        self.label_encoders = joblib.load(os.path.join(MODEL_DIR, "label_encoders.pkl"))
        self.target_encoder = joblib.load(os.path.join(MODEL_DIR, "target_encoder.pkl"))
        self.class_names = joblib.load(os.path.join(MODEL_DIR, "class_names.pkl"))
        self.metrics = joblib.load(os.path.join(MODEL_DIR, "metrics.pkl"))
