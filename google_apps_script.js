function doPost(e) {
  var lock = LockService.getScriptLock();
  // Tunggu lock hingga 10 detik agar tidak ada race condition saat submit bersamaan
  if (!lock.tryLock(10000)) {
    return ContentService.createTextOutput(JSON.stringify({
      "status": "error",
      "message": "Server sedang sibuk memproses antrean data."
    })).setMimeType(ContentService.MimeType.JSON);
  }

  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
    var data = JSON.parse(e.postData.contents);

    // Pemetaan 34 kolom sesuai schema tpuirf-platform & ML pipeline
    var row = [
      data.timestamp || new Date().toISOString(),
      data.participant_id || "",
      data.name || "",
      data.birth_date || "",
      data.age || "",
      data.gender || "",
      data.education || "",
      data.occupation || "",
      data.digital_literacy || "",
      data.device_type || "",
      data.visual_preference || "",
      data.context_environment || "",
      data.ai_familiarity || "",
      data.daily_device_usage || "",
      data.device_usage_time || "",
      data.primary_device_purpose || "",
      data.click_count || 0,
      data.scroll_count || 0,
      data.hover_count || 0,
      data.keyboard_count || 0,
      data.avg_task_time || 0,
      data.total_session_time || 0,
      data.pref_theme || "",
      data.pref_color_palette || "",
      data.pref_font_family || "",
      data.pref_font_size || "",
      data.pref_layout_density || "",
      data.pref_navigation_position || "",
      data.pref_icon_style || "",
      data.pref_design_style || "",
      data.ml_model || "",
      data.pred_design_style || "",
      data.pred_confidence || "",
      typeof data.shap_top_features === "object" ? JSON.stringify(data.shap_top_features) : (data.shap_top_features || "")
    ];

    sheet.appendRow(row);

    return ContentService.createTextOutput(JSON.stringify({
      "status": "success",
      "row": sheet.getLastRow()
    })).setMimeType(ContentService.MimeType.JSON);

  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({
      "status": "error",
      "message": err.toString()
    })).setMimeType(ContentService.MimeType.JSON);

  } finally {
    lock.releaseLock();
  }
}
