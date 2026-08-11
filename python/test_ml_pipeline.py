import os
import tempfile
import unittest

import pandas as pd

from ml_pipeline import MODEL_VERSION, TPUIRFPipeline


class TPUIRFPipelineTest(unittest.TestCase):
    def make_dataset(self, path, rows=40):
        records = []
        for index in range(rows):
            night = index % 2 == 0
            records.append({
                "age": 24,
                "gender": "male",
                "education": "bachelor",
                "occupation": "student",
                "digital_literacy": 4,
                "device_type": "smartphone",
                "visual_preference": "modern",
                "context_environment": "home",
                "ai_familiarity": 3,
                "daily_device_usage": "5_8",
                "device_usage_time": "night" if night else "morning",
                "primary_device_purpose": "productivity",
                "click_count": 20,
                "scroll_count": 8,
                "hover_count": 0,
                "keyboard_count": 2,
                "avg_task_time": 1800,
                "total_session_time": 16000,
                "theme": "dark" if night else "light",
                "color_palette": "blue" if night else "orange",
                "font_family": "high_legibility" if night else "sans",
                "font_size": "large" if night else "medium",
                "layout_density": "comfortable" if night else "compact",
                "navigation_position": "bottom" if night else "top",
                "icon_style": "filled" if night else "outline",
                "design_style": "minimal_flat" if night else "material_elevated",
            })
        pd.DataFrame(records).to_csv(path, index=False)

    def test_trains_and_predicts_all_targets_without_target_leakage(self):
        with tempfile.TemporaryDirectory() as directory:
            data_path = os.path.join(directory, "training.csv")
            self.make_dataset(data_path)
            pipeline = TPUIRFPipeline(model_dir=directory)

            metrics = pipeline.train(data_path)
            prediction = pipeline.predict({
                "age": 24,
                "gender": "male",
                "education": "bachelor",
                "occupation": "student",
                "digital_literacy": 4,
                "device_type": "smartphone",
                "visual_preference": "modern",
                "context_environment": "home",
                "ai_familiarity": 3,
                "daily_device_usage": "5_8",
                "device_usage_time": "night",
                "primary_device_purpose": "productivity",
                "click_count": 20,
                "scroll_count": 8,
                "hover_count": 0,
                "keyboard_count": 2,
                "avg_task_time": 1800,
                "total_session_time": 16000,
            })

            self.assertEqual(set(TPUIRFPipeline.DEFAULT_TARGETS), set(metrics["trained_targets"]))
            self.assertNotIn("theme", pipeline.feature_names)
            self.assertNotIn("design_style", pipeline.feature_names)
            self.assertIn("device_usage_time", pipeline.feature_names)
            self.assertEqual(set(TPUIRFPipeline.DEFAULT_TARGETS), set(prediction["recommended_config"]))
            self.assertEqual("dark", prediction["recommended_config"]["theme"])
            self.assertEqual(MODEL_VERSION, prediction["model_version"])
            self.assertTrue(any(
                item["target"] == "theme"
                and item["feature"] == "device_usage_time"
                and "malam" in item["human_readable"]
                for item in prediction["explanations"]
            ))

            loaded = TPUIRFPipeline(model_dir=directory)
            loaded.load_model()
            self.assertEqual(set(pipeline.models), set(loaded.models))

    def test_rejects_targets_without_enough_samples_per_class(self):
        with tempfile.TemporaryDirectory() as directory:
            data_path = os.path.join(directory, "small.csv")
            self.make_dataset(data_path, rows=2)
            pipeline = TPUIRFPipeline(model_dir=directory, targets=["theme"])

            with self.assertRaisesRegex(ValueError, "minimal dua sampel"):
                pipeline.train(data_path)


if __name__ == "__main__":
    unittest.main()
