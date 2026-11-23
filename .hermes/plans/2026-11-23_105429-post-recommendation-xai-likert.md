# Post-Recommendation XAI Likert Evaluation Implementation Plan

> **For Hermes:** Implement this plan task-by-task with TDD. Do not merge this questionnaire into the existing pre-recommendation `trust_evaluations` flow.

**Goal:** Add a required eight-item Likert 1–5 questionnaire after the respondent has seen the frozen AI recommendation and SHAP analysis, then store, export, and report those answers without affecting inference, SHAP, manual target labels, or model retraining.

**Architecture:** Introduce a separate `xai_evaluations` aggregate keyed one-to-one to the frozen recommendation/experiment. Render the questionnaire at the bottom of the recommendation page, after the recommendation configuration and SHAP factors. Submit through a respondent-owned endpoint with server-side validation and idempotent persistence; the resulting research measurements remain evaluation-only and never enter the XGBoost feature or target schemas.

**Tech Stack:** Laravel 13, PHP 8.4, Blade/Tailwind, Alpine.js or progressive-enhancement form behavior already used by the app, SQLite, Laravel validation/Eloquent, PHPUnit feature tests, Google Apps Script webhook, Docker Compose.

---

## 1. Source Instrument

Source document:

`/mnt/arflix/arpanel/.ext4-store/hermes-cache/documents/native-inputs/c078d29b92d7fd6226ed190091b805871904b2b81e3025dc637b1a88a5c24d01/doc_e778b8466377_3EB029D82018B95BDB579D-item_pertanyaan_XAI.docx`

The document contains these eight constructs and statements:

1. **Perceived Transparency**  
   “Saya memahami alasan mengapa sistem merekomendasikan konfigurasi antarmuka ini untuk saya.”
2. **System Trust**  
   “Saya percaya bahwa rekomendasi konfigurasi antarmuka yang diberikan sistem sesuai dengan kebutuhan dan karakteristik saya.”
3. **Decision Confidence**  
   “Saya merasa yakin untuk menggunakan konfigurasi antarmuka yang direkomendasikan oleh sistem ini.”
4. **Overall Satisfaction**  
   “Secara keseluruhan, saya puas dengan proses dan hasil rekomendasi antarmuka pada sistem ini.”
5. **Explanation Understandability**  
   “Penjelasan faktor yang ditampilkan (pengaruh usia, pekerjaan, dan pola interaksi saya) mudah dipahami tanpa memerlukan pengetahuan teknis AI.”
6. **Causal Attribution Coherence**  
   “Saya dapat melihat dengan jelas bagaimana karakteristik profil dan kebiasaan saya memengaruhi setiap elemen tampilan yang direkomendasikan.”
7. **Perceived Scrutability**  
   “Penjelasan analisis faktor tersebut membantu saya memeriksa apakah sistem telah membaca preferensi saya secara akurat.”
8. **Cognitive Load / Appropriateness**  
   “Jumlah dan penyajian informasi pada analisis faktor tersebut ringkas dan tidak membingungkan saya.”

### Likert scale

Use the same labeled scale on every item:

- `1` — Sangat Tidak Setuju
- `2` — Tidak Setuju
- `3` — Netral
- `4` — Setuju
- `5` — Sangat Setuju

Store integer values, not floating-point values.

---

## 2. Timing and Research Boundary

### Correct respondent sequence

```text
profiling + behavioral telemetry
→ manual UI preference tasks
→ existing experiment evaluation
→ complete experiment
→ predict with active XGBoost model
→ freeze recommendation + SHAP
→ display recommendation and SHAP
→ respondent answers the new eight-item XAI questionnaire
→ save XAI evaluation linked to the frozen recommendation
```

### Separation from existing evaluation

The existing `trust_evaluations` flow occurs before the experiment can complete and currently contains trust, transparency, satisfaction, AI-use-frequency, and related fields. The new form is explicitly **post-recommendation** and evaluates a recommendation/SHAP explanation the respondent has now seen. It must therefore use a separate table/model/endpoint.

Reasons not to reuse `trust_evaluations`:

- Different timing and stimulus.
- Different validated instrument and construct names.
- Existing completion logic requires `trust_evaluations` before recommendation generation.
- Reusing columns would blur pre-recommendation and post-recommendation measurements in the journal dataset.
- Separate storage permits clean paired analysis later.

### ML contract

The eight answers are research outcomes only. They must never be added to:

- `ModelDataContract::FEATURES`
- `ModelDataContract::TARGETS`
- XGBoost inference payloads
- model training CSV feature/target columns
- SHAP inputs
- fallback recommendations
- the retraining trigger

Submitting or editing this questionnaire must not retrain the model.

---

## 3. Data Model

### New table: `xai_evaluations`

Create migration:

`database/migrations/<timestamp>_create_xai_evaluations_table.php`

Recommended columns:

```php
$table->uuid('id')->primary();
$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
$table->foreignUuid('experiment_id')->constrained()->cascadeOnDelete();
$table->foreignUuid('recommendation_id')->constrained()->cascadeOnDelete();
$table->unsignedTinyInteger('perceived_transparency');
$table->unsignedTinyInteger('system_trust');
$table->unsignedTinyInteger('decision_confidence');
$table->unsignedTinyInteger('overall_satisfaction');
$table->unsignedTinyInteger('explanation_understandability');
$table->unsignedTinyInteger('causal_attribution_coherence');
$table->unsignedTinyInteger('perceived_scrutability');
$table->unsignedTinyInteger('cognitive_load_appropriateness');
$table->string('instrument_version')->default('XAI-LIKERT-v1');
$table->timestamp('submitted_at');
$table->timestamps();
$table->softDeletes();

$table->unique('recommendation_id');
$table->unique(['user_id', 'experiment_id']);
$table->index(['experiment_id', 'submitted_at']);
```

### New Eloquent model

Create:

`app/Infrastructure/Persistence/Models/XaiEvaluation.php`

Required behavior:

- `HasUuids`, `SoftDeletes`.
- Fillable eight scores, foreign IDs, instrument version, and submission time.
- Cast all scores to integers and `submitted_at` to datetime.
- Relationships to `User`, `Experiment`, and `Recommendation`.

### Relationship additions

Modify:

- `app/Infrastructure/Persistence/Models/Recommendation.php`
- `app/Infrastructure/Persistence/Models/Experiment.php`
- `app/Infrastructure/Persistence/Models/User.php`

Add `hasOne`/`hasMany` relationships as appropriate.

### Why link all three IDs

- `recommendation_id` proves which frozen output was evaluated.
- `experiment_id` supports research joins and exports.
- `user_id` makes authorization and respondent-level analysis straightforward.

The controller must derive these IDs from the authenticated session and owned recommendation; never trust IDs submitted as hidden form fields.

---

## 4. HTTP Contract and Authorization

### Route

Add inside the authenticated respondent + `profile.complete` group:

```php
Route::post('/recommendation/xai-evaluation', [RecommendationController::class, 'storeXaiEvaluation'])
    ->middleware('throttle:10,1')
    ->name('recommendation.xai-evaluation.store');
```

### Form request

Create:

`app/Presentation/Http/Requests/StoreXaiEvaluationRequest.php`

Rules:

```php
return [
    'perceived_transparency' => ['required', 'integer', 'between:1,5'],
    'system_trust' => ['required', 'integer', 'between:1,5'],
    'decision_confidence' => ['required', 'integer', 'between:1,5'],
    'overall_satisfaction' => ['required', 'integer', 'between:1,5'],
    'explanation_understandability' => ['required', 'integer', 'between:1,5'],
    'causal_attribution_coherence' => ['required', 'integer', 'between:1,5'],
    'perceived_scrutability' => ['required', 'integer', 'between:1,5'],
    'cognitive_load_appropriateness' => ['required', 'integer', 'between:1,5'],
];
```

Add Indonesian field names and validation messages so errors identify the unanswered statement clearly.

### Controller behavior

Modify `RecommendationController`:

1. Resolve authenticated respondent.
2. Resolve latest frozen recommendation belonging to that respondent.
3. Reject submission if no recommendation exists or if it is a fallback/pending result without SHAP evidence.
4. Resolve recommendation’s experiment and verify ownership.
5. Persist with `updateOrCreate(['recommendation_id' => ...])` so refresh/retry is idempotent.
6. Set `instrument_version = XAI-LIKERT-v1` and `submitted_at = now()` on first submit; for an approved editable design, update values and `updated_at` but preserve the original submission timestamp.
7. For standard HTML submission, redirect back to the recommendation page with a concise success flash.
8. If JSON is later needed, return JSON only when `$request->expectsJson() || $request->ajax()`.

### Ownership tests

Prove that a respondent cannot submit against or overwrite another respondent’s recommendation, even by manipulating request data.

---

## 5. Recommendation Page UX

Modify:

`resources/views/recommendation/show.blade.php`

### Placement

Place the questionnaire after:

1. AI recommendation configuration.
2. Manual-choice research note.
3. “Mengapa Tampilan Ini Direkomendasikan?” SHAP analysis.

This placement guarantees the respondent has the full stimulus before answering.

### Section copy

Suggested heading:

**Evaluasi Rekomendasi dan Penjelasan AI**

Suggested introduction:

> Setelah melihat rekomendasi dan analisis faktor di atas, berikan penilaian Anda pada setiap pernyataan berikut. Pilih satu jawaban dari 1 (Sangat Tidak Setuju) sampai 5 (Sangat Setuju).

Suggested research note:

> Jawaban ini digunakan untuk mengevaluasi kualitas rekomendasi dan penjelasan AI. Jawaban tidak mengubah rekomendasi yang sudah Anda terima dan tidak digunakan sebagai input prediksi untuk sesi ini.

### Item presentation

For each of the eight statements:

- Show sequential number, construct label in a small secondary label, and Indonesian statement as the primary content.
- Render five radio controls in one semantic `fieldset` with a `legend`.
- Desktop: five evenly spaced choices.
- Mobile: either horizontally scroll-safe or vertically stacked labels; no compressed tiny radio targets.
- Each choice needs a minimum 44×44 px click/touch target.
- Display endpoint text (`Sangat Tidak Setuju`, `Sangat Setuju`) without relying on color alone.
- Preserve old input after validation errors.
- Use `aria-describedby` for scale guidance and item errors.
- Do not default any item to `3`; forced defaults create invalid research responses.

### Completion state

If no XAI evaluation exists:

- Show the form and “Kirim Evaluasi” button.
- Require all eight answers client-side only as convenience; server validation remains authoritative.

If an evaluation exists:

- Show a success state: “Evaluasi rekomendasi telah dikirim.”
- Show submitted values read-only for transparency.
- Recommended default: allow one “Ubah Jawaban” action while preserving the same database row; do not create duplicates.

### Navigation behavior

- Recommendation remains viewable before answering.
- Dashboard should show an explicit status badge/action:
  - `Belum diisi` → link to `respondent.recommendation.show#evaluasi-xai`
  - `Sudah dikirim` → completed badge.
- Do not block model retraining or recommendation visibility on this questionnaire.
- The form is required for research completion status, but failure to answer must not invalidate the already completed preference experiment or frozen model output.

---

## 6. Recommendation Controller View Data

Modify:

`app/Presentation/Http/Controllers/RecommendationController.php`

In `show()`:

- Load existing `XaiEvaluation` by recommendation ID and authenticated user ID.
- Pass it as `$xaiEvaluation`.
- Pass a centralized item definition as `$xaiItems`; recommended source is config rather than duplicating item text in Blade.

Create:

`config/xai_evaluation.php`

Example structure:

```php
return [
    'instrument_version' => 'XAI-LIKERT-v1',
    'scale' => [
        1 => 'Sangat Tidak Setuju',
        2 => 'Tidak Setuju',
        3 => 'Netral',
        4 => 'Setuju',
        5 => 'Sangat Setuju',
    ],
    'items' => [
        'perceived_transparency' => [
            'construct' => 'Perceived Transparency',
            'statement' => 'Saya memahami alasan mengapa sistem merekomendasikan konfigurasi antarmuka ini untuk saya.',
        ],
        // seven remaining items exactly as supplied
    ],
];
```

Benefits:

- One authoritative item wording source.
- Instrument version can be exported with answers.
- Tests can assert all configured items have validation and database fields.
- Future questionnaire revisions can use a new version without silently changing historical interpretation.

---

## 7. Dashboard and Research Completion Status

Modify:

- `app/Presentation/Http/Controllers/DashboardController.php`
- `resources/views/dashboard/respondent.blade.php`

### Data loading

Load XAI evaluation through the latest recommendation instead of querying only by user. This ensures status corresponds to the exact recommendation displayed.

### UI status

When a recommendation exists:

- If unanswered: show “Evaluasi rekomendasi belum diisi” and CTA “Isi Evaluasi Akhir”.
- If answered: show “Evaluasi rekomendasi sudah dikirim”.

Do not conflate this with the existing pre-recommendation `trustEvaluation` status.

---

## 8. Google Sheets and Dataset Exports

### Google Sheets

The current Google Sheets sync runs immediately after recommendation generation, before the respondent can answer the new form. Therefore, do not assume the original row can be updated reliably unless a stable unique key/update operation is added.

Recommended approach:

1. Keep the existing experiment/recommendation sync unchanged.
2. Create a second async job after XAI submission:
   - `app/Application/Jobs/SyncXaiEvaluationToGoogleSheetsJob.php`
3. Post a payload with an explicit record type and stable identifiers:

```text
record_type = xai_evaluation
participant_id
experiment_id
recommendation_id
model_version
instrument_version
submitted_at
perceived_transparency
system_trust
decision_confidence
overall_satisfaction
explanation_understandability
causal_attribution_coherence
perceived_scrutability
cognitive_load_appropriateness
```

4. Extend `google_apps_script.js` to upsert into a dedicated sheet such as `XAI_Evaluations` keyed by `recommendation_id`.
5. Keep the 34-column training/recommendation dataset untouched; XAI answers must not enter the ML training CSV.

### Research exports

Modify `DatasetExportService`:

- Add dataset type `xai-evaluations`.
- Export one row per frozen recommendation/evaluation.
- Include IDs, model version, algorithm, prediction timestamp, questionnaire submission timestamp, instrument version, and eight item scores.
- Do not add these columns to `training`, `testing`, or `full` model datasets.

Optional derived fields for researcher/admin analytics only:

- `xai_mean_score`
- `recommendation_quality_mean` (items 1–4)
- `explanation_quality_mean` (items 5–8)

Keep raw item scores in all exports; derived means must never replace them.

---

## 9. Analytics and Admin Visibility

Minimum implementation:

- Show total submitted XAI evaluations.
- Show completion rate among respondents with frozen recommendations.
- Show mean per construct/item.

Likely files:

- `app/Application/Services/AnalyticsService.php`
- `app/Presentation/Http/Controllers/AnalyticsController.php`
- relevant researcher/admin analytics Blade view

Research caveat:

- Treat Likert items as ordinal in raw reporting.
- Means may be displayed as descriptive summaries, but preserve frequency distribution per response value for formal analysis.
- Do not combine all eight items into one reliability construct without later psychometric justification; the supplied items span distinct constructs.

---

## 10. TDD Task Breakdown

### Task 1: Lock the questionnaire contract in tests

**Objective:** Establish item keys, wording, scale, and ML exclusion before schema changes.

**Files:**
- Create: `tests/Unit/XaiEvaluationConfigTest.php`
- Modify: `tests/Feature/PreferenceExperimentBackendTest.php`

**Tests:**

1. Config contains exactly eight item keys.
2. Every item has `construct` and exact `statement` text.
3. Scale is exactly integer `1..5` with specified labels.
4. No XAI item key exists in `ModelDataContract::FEATURES` or `TARGETS`.
5. Recommendation page contains the section only after a real recommendation exists.

**RED command:**

```bash
vendor/bin/phpunit --filter=XaiEvaluationConfigTest
```

Expected: FAIL because config and feature do not exist.

**Commit:**

```bash
git add tests
git commit -m "test(xai): define post-recommendation likert instrument"
```

---

### Task 2: Add schema, model, and relationships

**Objective:** Persist one post-recommendation questionnaire per frozen recommendation.

**Files:**
- Create migration and `XaiEvaluation` model.
- Modify `User`, `Experiment`, and `Recommendation` models.
- Create model/unit tests.

**Tests:**

- Valid eight-item record persists.
- Recommendation uniqueness prevents duplicates.
- All casts return integers.
- Relationship navigation works.
- Cascading deletion behavior is covered.

**GREEN command:**

```bash
vendor/bin/phpunit --filter=XaiEvaluation
```

**Commit:**

```bash
git add app database tests
git commit -m "feat(xai): persist post-recommendation evaluation"
```

---

### Task 3: Add validated respondent-owned submission endpoint

**Objective:** Securely save all eight Likert answers for the authenticated respondent’s latest frozen recommendation.

**Files:**
- Create `StoreXaiEvaluationRequest.php`.
- Modify `RecommendationController.php`.
- Modify `routes/web.php`.
- Create `tests/Feature/XaiEvaluationSubmissionTest.php`.

**Tests:**

1. Each missing field yields HTTP 422.
2. Values `0`, `6`, floats, strings outside allowed integers fail.
3. Values `1..5` for all items pass.
4. Anonymous and non-respondent access is rejected.
5. Respondent cannot target another recommendation.
6. No recommendation → submission rejected.
7. First submission creates one row.
8. Retry/update changes the same row, not row count.
9. Submission does not call `/train` and does not dispatch `RetrainRecommendationModelJob`.
10. Existing recommendation, SHAP, model version, and `input_features` remain byte-for-byte unchanged.

**Commit:**

```bash
git add app routes tests
git commit -m "feat(xai): accept validated respondent evaluation"
```

---

### Task 4: Render the accessible Likert form after SHAP

**Objective:** Present the instrument only after the respondent sees their recommendation and explanation.

**Files:**
- Create `config/xai_evaluation.php`.
- Modify `RecommendationController.php`.
- Modify `resources/views/recommendation/show.blade.php`.
- Test in `tests/Feature/XaiEvaluationPageTest.php`.

**Tests:**

- All eight exact statements render.
- Each statement has exactly five labeled radios.
- Nothing is preselected on first view.
- Existing answers render selected/read-only after submission.
- Validation errors preserve old input.
- Section appears after SHAP section in rendered HTML order.
- Form action and CSRF token are present.
- Mobile controls use reachable, non-overlapping labels.

**Commit:**

```bash
git add config app resources tests
git commit -m "feat(xai): add accessible likert form after ai explanation"
```

---

### Task 5: Add dashboard completion state

**Objective:** Make unanswered final evaluations visible without blocking the frozen recommendation.

**Files:**
- Modify `DashboardController.php`.
- Modify `resources/views/dashboard/respondent.blade.php`.
- Add feature tests.

**Tests:**

- Recommendation without evaluation shows CTA and anchor link.
- Submitted evaluation shows completed badge.
- Status corresponds to latest recommendation.
- Existing pre-recommendation trust evaluation remains independently displayed.

**Commit:**

```bash
git add app resources tests
git commit -m "feat(xai): show final evaluation status on respondent dashboard"
```

---

### Task 6: Add dedicated XAI export and Sheets sync

**Objective:** Deliver research answers downstream without contaminating training data.

**Files:**
- Create `SyncXaiEvaluationToGoogleSheetsJob.php`.
- Modify controller submission flow.
- Modify `DatasetExportService.php`.
- Modify `google_apps_script.js`.
- Add job/export tests.

**Tests:**

1. `xai-evaluations` export has one row and eight raw scores.
2. `training` export headers do not contain any XAI item.
3. Sheets payload uses `record_type=xai_evaluation`.
4. Apps Script upserts on `recommendation_id` in `XAI_Evaluations` sheet.
5. Queue retry remains idempotent.
6. A failed webhook does not roll back the saved evaluation.

**Commit:**

```bash
git add app google_apps_script.js tests
git commit -m "feat(xai): export and sync evaluation responses"
```

---

### Task 7: Add researcher summary analytics

**Objective:** Give researchers completion and response-distribution visibility.

**Files:**
- Modify analytics service/controller/view.
- Add analytics tests.

**Tests:**

- Completion rate denominator = frozen recommendations.
- Means exclude missing responses.
- Distribution counts 1–5 exactly.
- No XAI score is sent to ML endpoints.

**Commit:**

```bash
git add app resources tests
git commit -m "feat(analytics): summarize xai evaluation responses"
```

---

### Task 8: Full regression and production deployment

**Local/CI verification:**

```bash
vendor/bin/phpunit
python3 -m unittest discover -s python -p 'test_*.py' -v
git diff --check
```

**Leakage assertions:**

- XAI keys absent from ML feature/target contracts.
- No `/train` request during XAI submission.
- Recommendation row unchanged after questionnaire submission.
- Training CSV unchanged in schema.

**Production backup gate:**

Before migration or rebuild on `tencent1`:

1. Git bundle/current commit.
2. SQLite native backup + `PRAGMA integrity_check`.
3. Active XGBoost model artifact.
4. Container image IDs and current Compose config.
5. Report backup paths and wait for Ar’s explicit deploy confirmation.

**Deployment:**

```bash
ssh tencent1
cd /opt/tpuirf-platform
git pull --ff-only origin main
docker compose build web
docker compose run --rm --no-deps web php artisan migrate --force
docker compose up -d web
```

**Live verification:**

1. Public recommendation page returns through normal DNS.
2. Controlled respondent sees all eight items after SHAP.
3. Submit `1..5` values and verify one database row.
4. Refresh shows submitted state; second submit updates same row.
5. Recommendation/model version/SHAP unchanged.
6. No retraining job is queued by the XAI submission.
7. Dedicated Sheets record arrives/upserts correctly.
8. Queue worker and containers remain healthy.

---

## 11. Files Likely to Change

### New

- `config/xai_evaluation.php`
- `database/migrations/<timestamp>_create_xai_evaluations_table.php`
- `app/Infrastructure/Persistence/Models/XaiEvaluation.php`
- `app/Presentation/Http/Requests/StoreXaiEvaluationRequest.php`
- `app/Application/Jobs/SyncXaiEvaluationToGoogleSheetsJob.php`
- `tests/Unit/XaiEvaluationConfigTest.php`
- `tests/Feature/XaiEvaluationSubmissionTest.php`
- `tests/Feature/XaiEvaluationPageTest.php`

### Modify

- `routes/web.php`
- `app/Presentation/Http/Controllers/RecommendationController.php`
- `app/Presentation/Http/Controllers/DashboardController.php`
- `app/Infrastructure/Persistence/Models/Recommendation.php`
- `app/Infrastructure/Persistence/Models/Experiment.php`
- `app/Infrastructure/Persistence/Models/User.php`
- `app/Application/Services/DatasetExportService.php`
- analytics service/controller/view
- `resources/views/recommendation/show.blade.php`
- `resources/views/dashboard/respondent.blade.php`
- `google_apps_script.js`

---

## 12. Acceptance Criteria

The feature is complete when:

- All eight supplied statements appear exactly after the AI recommendation and SHAP analysis.
- Every answer is required and validated as integer 1–5.
- Scale labels are visible and accessible.
- A respondent can only evaluate their own frozen recommendation.
- Repeated submission is idempotent and does not create duplicate records.
- The evaluation is versioned as `XAI-LIKERT-v1`.
- The questionnaire does not alter the frozen recommendation, model version, confidence, SHAP, or manual preference labels.
- The questionnaire does not trigger model training.
- XAI fields are absent from all XGBoost feature and target schemas.
- Researchers can export raw item-level responses separately.
- Google Sheets receives XAI data in a dedicated, upsertable record/sheet.
- Dashboard identifies whether final XAI evaluation has been submitted.
- Tests and live production verification pass after the backup/approval gate.

---

## 13. Decisions Assumed for Implementation

- The supplied eight statements are preserved verbatim.
- Likert endpoints use standard agreement labels from “Sangat Tidak Setuju” to “Sangat Setuju.”
- The questionnaire is shown on the same recommendation page, below SHAP.
- It is required for research completion status but does not block viewing the recommendation.
- Existing pre-recommendation `trust_evaluations` data remains unchanged and analytically separate.
- Responses are editable through an idempotent update of the same row; no duplicate questionnaire attempts are created.
- XAI answers are evaluation outcomes only and never enter XGBoost training or inference.
