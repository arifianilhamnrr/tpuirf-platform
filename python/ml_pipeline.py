import math
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

MODEL_VERSION = "rf+xgboost+shap-multitarget-v3"


class TPUIRFPipeline:
    DEFAULT_TARGETS = [
        "theme",
        "color_palette",
        "font_family",
        "font_size",
        "layout_density",
        "navigation_position",
        "icon_style",
        "design_style",
    ]

    FEATURE_NAMES = [
        "age",
        "gender",
        "education",
        "occupation",
        "digital_literacy",
        "device_type",
        "visual_preference",
        "context_environment",
        "ai_familiarity",
        "daily_device_usage",
        "device_usage_time",
        "primary_device_purpose",
        "click_count",
        "scroll_count",
        "hover_count",
        "keyboard_count",
        "avg_task_time",
        "total_session_time",
    ]

    CATEGORICAL_FEATURES = [
        "gender",
        "education",
        "occupation",
        "device_type",
        "visual_preference",
        "context_environment",
        "daily_device_usage",
        "device_usage_time",
        "primary_device_purpose",
    ]

    FEATURE_LABELS = {
        "age": "usia",
        "gender": "jenis kelamin",
        "education": "tingkat pendidikan",
        "occupation": "pekerjaan",
        "digital_literacy": "literasi digital",
        "device_type": "perangkat utama",
        "visual_preference": "preferensi visual",
        "context_environment": "lingkungan penggunaan",
        "ai_familiarity": "pemahaman AI",
        "daily_device_usage": "durasi penggunaan perangkat",
        "device_usage_time": "waktu penggunaan utama",
        "primary_device_purpose": "tujuan penggunaan perangkat",
        "click_count": "jumlah interaksi klik",
        "scroll_count": "jumlah interaksi gulir",
        "hover_count": "jumlah interaksi hover",
        "keyboard_count": "jumlah interaksi keyboard",
        "avg_task_time": "rata-rata waktu penyelesaian tugas",
        "total_session_time": "total waktu aktif eksperimen",
    }

    TARGET_LABELS = {
        "theme": "tema",
        "color_palette": "palet warna",
        "font_family": "keluarga font",
        "font_size": "ukuran font",
        "layout_density": "kepadatan layout",
        "navigation_position": "posisi navigasi",
        "icon_style": "gaya ikon",
        "design_style": "gaya desain",
    }

    VALUE_LABELS = {
        "morning": "pagi",
        "afternoon": "siang",
        "evening": "sore",
        "night": "malam",
        "under_1": "kurang dari 1 jam per hari",
        "1_3": "1–3 jam per hari",
        "3_5": "3–5 jam per hari",
        "5_8": "5–8 jam per hari",
        "over_8": "lebih dari 8 jam per hari",
        "smartphone": "smartphone",
        "desktop": "komputer desktop",
        "laptop": "laptop",
        "tablet": "tablet",
        "home": "rumah",
        "office": "kantor",
        "school": "sekolah atau kampus",
        "public": "tempat umum",
        "minimal": "minimal",
        "vibrant": "cerah",
        "classic": "klasik",
        "modern": "modern",
        "communication": "komunikasi",
        "education": "pendidikan",
        "shopping": "belanja",
        "entertainment": "hiburan",
        "finance": "keuangan",
        "productivity": "produktivitas",
        "social_media": "media sosial",
        "light": "mode terang",
        "dark": "mode gelap",
        "sans": "Inter",
        "serif": "Merriweather",
        "high_legibility": "Atkinson Hyperlegible",
        "small": "kecil",
        "medium": "sedang",
        "large": "besar",
        "compact": "rapat",
        "comfortable": "sedang",
        "spacious": "renggang",
        "top": "navigasi atas",
        "left": "bilah samping kiri",
        "bottom": "navigasi bawah",
        "outline": "outline",
        "filled": "filled",
        "duotone": "dual tone",
        "minimal_flat": "minimal atau datar",
        "material_elevated": "material atau berelevasi",
        "glassmorphism": "glassmorphism",
        "neumorphism": "neumorphism",
        "high_contrast_brutalist": "neobrutalism",
    }

    def __init__(self, model_dir=None, targets=None):
        self.model_dir = model_dir or os.path.join(os.path.dirname(__file__), "models")
        os.makedirs(self.model_dir, exist_ok=True)
        self.targets = list(targets or self.DEFAULT_TARGETS)
        self.feature_names = list(self.FEATURE_NAMES)
        self.categorical_features = list(self.CATEGORICAL_FEATURES)
        self.models = {}
        self.label_encoders = {}
        self.target_encoders = {}
        self.metrics = {}

    def preprocess(self, frame, fit=False):
        frame = frame.copy()
        for column in self.categorical_features:
            if column in frame:
                values = frame[column].fillna("unknown").astype(str)
            else:
                values = pd.Series(["unknown"] * len(frame), index=frame.index, dtype=str)

            if fit or column not in self.label_encoders:
                self.label_encoders[column] = {
                    value: index for index, value in enumerate(sorted(values.unique()))
                }

            frame[column] = values.map(self.label_encoders[column]).fillna(-1).astype(float)

        for column in self.feature_names:
            if column not in frame:
                frame[column] = 0
            if column not in self.categorical_features:
                frame[column] = pd.to_numeric(frame[column], errors="coerce").fillna(0)

        return frame[self.feature_names].astype(float)

    def train(self, data_path):
        frame = pd.read_csv(data_path)
        if frame.empty:
            raise ValueError("Dataset training masih kosong")

        features = self.preprocess(frame, fit=True)
        trained_models = {}
        trained_encoders = {}
        target_metrics = {}
        skipped_targets = {}

        for target in self.targets:
            if target not in frame:
                skipped_targets[target] = "kolom target tidak tersedia"
                continue

            raw_target = frame[target]
            valid = raw_target.notna() & raw_target.astype(str).str.strip().ne("")
            target_values = raw_target.loc[valid].astype(str)
            class_counts = target_values.value_counts()

            if len(class_counts) < 2:
                skipped_targets[target] = "membutuhkan minimal dua kelas"
                continue
            if int(class_counts.min()) < 2:
                skipped_targets[target] = "setiap kelas membutuhkan minimal dua sampel"
                continue

            encoder = LabelEncoder()
            encoded_target = encoder.fit_transform(target_values)
            target_features = features.loc[valid]
            class_count = len(encoder.classes_)
            test_count = max(class_count, math.ceil(len(target_features) * 0.2))

            if len(target_features) - test_count < class_count:
                skipped_targets[target] = "sampel belum cukup untuk train/test per kelas"
                continue

            train_x, test_x, train_y, test_y = train_test_split(
                target_features,
                encoded_target,
                test_size=test_count,
                random_state=42,
                stratify=encoded_target,
            )
            models = self._new_models(class_count)
            for model in models.values():
                model.fit(train_x, train_y)

            predictions = self._probabilities(models, test_x).argmax(axis=1)
            target_metrics[target] = {
                "accuracy": float(accuracy_score(test_y, predictions)),
                "samples": int(len(target_features)),
                "classes": list(encoder.classes_),
                "classification_report": classification_report(
                    test_y,
                    predictions,
                    labels=list(range(class_count)),
                    target_names=list(encoder.classes_),
                    output_dict=True,
                    zero_division=0,
                ),
            }
            trained_models[target] = models
            trained_encoders[target] = encoder

        if not trained_models:
            details = "; ".join(f"{target}: {reason}" for target, reason in skipped_targets.items())
            raise ValueError(f"Belum ada target yang dapat dilatih. {details}")

        self.models = trained_models
        self.target_encoders = trained_encoders
        accuracies = [metric["accuracy"] for metric in target_metrics.values()]
        self.metrics = {
            "accuracy": float(np.mean(accuracies)),
            "target_metrics": target_metrics,
            "trained_targets": list(trained_models.keys()),
            "skipped_targets": skipped_targets,
            "algorithms": ["random_forest", "xgboost"],
            "model_version": MODEL_VERSION,
        }
        self.save_model()
        return self.metrics

    @staticmethod
    def _new_models(class_count):
        xgboost_options = {
            "n_estimators": 150,
            "max_depth": 5,
            "learning_rate": 0.08,
            "subsample": 0.9,
            "colsample_bytree": 0.9,
            "eval_metric": "logloss" if class_count == 2 else "mlogloss",
            "random_state": 42,
            "n_jobs": 1,
        }
        if class_count == 2:
            xgboost_options["objective"] = "binary:logistic"
        else:
            xgboost_options.update({"objective": "multi:softprob", "num_class": class_count})

        return {
            "random_forest": RandomForestClassifier(
                n_estimators=200,
                max_depth=10,
                random_state=42,
                n_jobs=1,
            ),
            "xgboost": XGBClassifier(**xgboost_options),
        }

    @staticmethod
    def _probabilities(models, features):
        probabilities = [model.predict_proba(features) for model in models.values()]
        return np.mean(probabilities, axis=0)

    @staticmethod
    def _shap_values(model, features, class_index):
        values = np.asarray(shap.TreeExplainer(model)(features).values)
        if values.ndim == 3:
            values = values[0, :, min(class_index, values.shape[2] - 1)]
        elif values.ndim == 2:
            values = values[0]
            if class_index == 0:
                values = -values
        return [float(value) for value in values]

    def predict(self, user_data):
        if not self.models:
            raise RuntimeError("Model belum dilatih")

        features = self.preprocess(pd.DataFrame([user_data]))
        recommended_config = {}
        confidence_by_target = {}
        probabilities_by_target = {}
        model_outputs = {}
        explanations = []

        for target, models in self.models.items():
            encoder = self.target_encoders[target]
            probabilities = self._probabilities(models, features)[0]
            prediction_index = int(np.argmax(probabilities))
            prediction = str(encoder.inverse_transform([prediction_index])[0])
            confidence = float(np.max(probabilities))
            recommended_config[target] = prediction
            confidence_by_target[target] = confidence
            probabilities_by_target[target] = {
                str(name): float(value)
                for name, value in zip(encoder.classes_, probabilities)
            }

            target_outputs = {}
            for name, model in models.items():
                model_probability = model.predict_proba(features)[0]
                model_index = int(np.argmax(model_probability))
                target_outputs[name] = {
                    "prediction": str(encoder.inverse_transform([model_index])[0]),
                    "confidence": float(np.max(model_probability)),
                }
            model_outputs[target] = target_outputs

            shap_values = self._shap_values(models["xgboost"], features, prediction_index)
            target_explanations = sorted(
                [
                    self._explanation(
                        target,
                        prediction,
                        feature,
                        user_data.get(feature),
                        shap_value,
                    )
                    for feature, shap_value in zip(self.feature_names, shap_values)
                ],
                key=lambda item: item["impact"],
                reverse=True,
            )[:3]
            explanations.extend(target_explanations)

        overall_confidence = float(np.mean(list(confidence_by_target.values())))
        primary_recommendation = recommended_config.get("design_style") or next(
            iter(recommended_config.values())
        )
        sorted_explanations = sorted(
            explanations,
            key=lambda item: item["impact"],
            reverse=True,
        )

        return {
            "recommended_ui": primary_recommendation,
            "recommended_config": recommended_config,
            "confidence_score": overall_confidence,
            "confidence_by_target": confidence_by_target,
            "probabilities": probabilities_by_target,
            "shap_values": [item["shap_value"] for item in sorted_explanations],
            "features": [item["feature"] for item in sorted_explanations],
            "explanations": sorted_explanations,
            "model_outputs": model_outputs,
            "model_version": MODEL_VERSION,
        }

    def _explanation(self, target, recommendation, feature, feature_value, shap_value):
        impact = abs(float(shap_value))
        direction = "increases" if shap_value >= 0 else "decreases"
        feature_label = self.FEATURE_LABELS.get(feature, feature.replace("_", " "))
        target_label = self.TARGET_LABELS.get(target, target.replace("_", " "))
        value_label = self.VALUE_LABELS.get(str(feature_value), str(feature_value or "tidak tersedia"))
        recommendation_label = self.VALUE_LABELS.get(recommendation, recommendation)

        if direction == "increases":
            human_readable = (
                f"Data {feature_label} Anda ({value_label}) mendukung rekomendasi "
                f"{target_label} {recommendation_label} menurut pola yang dipelajari model."
            )
        else:
            human_readable = (
                f"Data {feature_label} Anda ({value_label}) sedikit mengurangi keyakinan pada "
                f"{target_label} {recommendation_label}; kombinasi faktor lain memberi dukungan lebih kuat."
            )

        return {
            "target": target,
            "recommended_value": recommendation,
            "feature": feature,
            "feature_value": feature_value,
            "shap_value": round(float(shap_value), 6),
            "impact": round(impact, 6),
            "direction": direction,
            "human_readable": human_readable,
        }

    def explain(self, user_data):
        return self.predict(user_data)

    def feature_importance(self):
        if not self.models:
            return []

        target_importances = {}
        for target, models in self.models.items():
            target_importances[target] = np.mean(
                [model.feature_importances_ for model in models.values()],
                axis=0,
            )
        aggregate = np.mean(list(target_importances.values()), axis=0)

        return sorted(
            [
                {
                    "feature": feature,
                    "importance": float(importance),
                    "targets": {
                        target: float(values[index])
                        for target, values in target_importances.items()
                    },
                }
                for index, (feature, importance) in enumerate(zip(self.feature_names, aggregate))
            ],
            key=lambda item: item["importance"],
            reverse=True,
        )

    def save_model(self):
        bundle = {
            "format_version": 3,
            "model_version": MODEL_VERSION,
            "models": self.models,
            "label_encoders": self.label_encoders,
            "target_encoders": self.target_encoders,
            "targets": self.targets,
            "metrics": self.metrics,
        }
        joblib.dump(bundle, os.path.join(self.model_dir, "multitarget_bundle.pkl"))

    def load_model(self):
        bundle = joblib.load(os.path.join(self.model_dir, "multitarget_bundle.pkl"))
        if bundle.get("format_version") != 3:
            raise RuntimeError("Format model tidak didukung")
        self.models = bundle["models"]
        self.label_encoders = bundle["label_encoders"]
        self.target_encoders = bundle["target_encoders"]
        self.targets = bundle["targets"]
        self.metrics = bundle["metrics"]
