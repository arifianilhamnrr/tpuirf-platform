import pandas as pd
import numpy as np
import json
import sys
import os
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder, StandardScaler
from sklearn.metrics import accuracy_score, classification_report
import shap
import joblib
import warnings
warnings.filterwarnings('ignore')

MODEL_DIR = os.path.join(os.path.dirname(__file__), 'models')
os.makedirs(MODEL_DIR, exist_ok=True)

class TPUIRFPipeline:
    def __init__(self):
        self.model = None
        self.label_encoders = {}
        self.scaler = StandardScaler()
        self.feature_names = [
            'age', 'gender', 'education', 'occupation',
            'digital_literacy', 'device_type', 'visual_preference',
            'context_environment', 'ai_familiarity',
            'click_count', 'scroll_count', 'hover_count',
            'keyboard_count', 'avg_task_time', 'total_session_time'
        ]
        self.categorical_features = [
            'gender', 'education', 'occupation', 'device_type',
            'visual_preference', 'context_environment'
        ]

    def preprocess(self, df):
        df = df.copy()
        for col in self.categorical_features:
            if col in df.columns:
                if col not in self.label_encoders:
                    self.label_encoders[col] = LabelEncoder()
                    df[col] = self.label_encoders[col].fit_transform(df[col].astype(str))
                else:
                    df[col] = self.label_encoders[col].transform(df[col].astype(str))
        numerical_features = [f for f in self.feature_names if f not in self.categorical_features]
        for col in numerical_features:
            if col in df.columns:
                df[col] = pd.to_numeric(df[col], errors='coerce').fillna(0)
        return df

    def train(self, data_path):
        df = pd.read_csv(data_path)
        X = df[self.feature_names]
        y = LabelEncoder().fit_transform(df['preferred_ui'])
        X = self.preprocess(X)
        X_scaled = self.scaler.fit_transform(X)
        X_train, X_test, y_train, y_test = train_test_split(
            X_scaled, y, test_size=0.2, random_state=42
        )
        self.model = RandomForestClassifier(
            n_estimators=100, max_depth=10, random_state=42
        )
        self.model.fit(X_train, y_train)
        y_pred = self.model.predict(X_test)
        accuracy = accuracy_score(y_test, y_pred)
        self.save_model()
        return {
            'accuracy': float(accuracy),
            'classification_report': classification_report(y_test, y_pred, output_dict=True)
        }

    def predict(self, user_data):
        df = pd.DataFrame([user_data])
        df = self.preprocess(df)
        X_scaled = self.scaler.transform(df[self.feature_names])
        prediction = self.model.predict(X_scaled)[0]
        probabilities = self.model.predict_proba(X_scaled)[0]
        ui_versions = ['A', 'B', 'C', 'D']
        explainer = shap.TreeExplainer(self.model)
        shap_values = explainer.shap_values(X_scaled)
        class_index = prediction
        shap_vals = shap_values[class_index][0].tolist()
        return {
            'recommended_ui': ui_versions[int(prediction)],
            'confidence_score': float(max(probabilities)),
            'probabilities': {ui: float(p) for ui, p in zip(ui_versions, probabilities)},
            'shap_values': shap_vals,
            'features': self.feature_names,
            'model_version': '1.0'
        }

    def explain(self, user_data):
        result = self.predict(user_data)
        explanations = []
        feature_labels = {
            'age': 'Age', 'gender': 'Gender', 'education': 'Education Level',
            'occupation': 'Occupation', 'digital_literacy': 'Digital Literacy',
            'device_type': 'Device Type', 'visual_preference': 'Visual Preference',
            'context_environment': 'Context Environment', 'ai_familiarity': 'AI Familiarity',
            'click_count': 'Click Rate', 'scroll_count': 'Scroll Depth',
            'hover_count': 'Hover Frequency', 'keyboard_count': 'Keyboard Usage',
            'avg_task_time': 'Task Completion Time', 'total_session_time': 'Session Duration'
        }
        for i, (feature, shap_val) in enumerate(zip(self.feature_names, result['shap_values'])):
            explanations.append({
                'feature': feature_labels.get(feature, feature),
                'shap_value': round(shap_val, 4),
                'impact': round(abs(shap_val), 4),
                'direction': 'increases' if shap_val > 0 else 'decreases',
            })
        explanations.sort(key=lambda x: x['impact'], reverse=True)
        return {
            'recommended_ui': result['recommended_ui'],
            'confidence_score': result['confidence_score'],
            'explanations': explanations[:5]
        }

    def save_model(self):
        joblib.dump(self.model, os.path.join(MODEL_DIR, 'model.pkl'))
        joblib.dump(self.label_encoders, os.path.join(MODEL_DIR, 'label_encoders.pkl'))
        joblib.dump(self.scaler, os.path.join(MODEL_DIR, 'scaler.pkl'))

    def load_model(self):
        self.model = joblib.load(os.path.join(MODEL_DIR, 'model.pkl'))
        self.label_encoders = joblib.load(os.path.join(MODEL_DIR, 'label_encoders.pkl'))
        self.scaler = joblib.load(os.path.join(MODEL_DIR, 'scaler.pkl'))
