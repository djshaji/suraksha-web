<?php
$title = "SPMR Suraksha";
$description = "Security Application for Govt. SPMR College";
$LOGIN_URI = $_SERVER['REQUEST_URI'] ?? '/';
include 'lib/header.php';
?>

<div class="container ">
  <div class="w-100 text-center">
    <a href="/"><img class="img-fluid abstract-brand" src="/logo-wide.png" alt=""></a>
  </div>
  <div class="col-md-6 mx-auto text-center">
    <h1 class="display-4 mt-4 mb-3">SPMR Suraksha</h1>
    <p class="lead mb-4">Security Application for Govt. SPMR College</p>
    <?php include "lib/login.php"; ?>
  </div>

  <style>
    .scan-shell {
      --scan-accent: #0f9d58;
      --scan-surface: #f7fbf8;
      --scan-border: #d9e7dd;
      --scan-ink: #13281a;
      --scan-danger: #9d0208;
      --scan-radius: 18px;
      color: var(--scan-ink);
    }

    .scan-card {
      background: #ffffff;
      border: 1px solid var(--scan-border);
      border-radius: var(--scan-radius);
      box-shadow: 0 12px 28px rgba(16, 40, 24, 0.08);
    }

    .scan-video-wrap {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      background: #0c1f14;
      min-height: 260px;
    }

    .scan-video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .scan-overlay {
      position: absolute;
      inset: 0;
      pointer-events: none;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .scan-frame {
      width: min(78%, 340px);
      aspect-ratio: 2 / 1;
      border: 3px solid rgba(255, 255, 255, 0.9);
      border-radius: 14px;
      box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.28);
      transition: width 180ms ease, border-color 180ms ease, box-shadow 180ms ease;
    }

    .scan-frame.assist-mode {
      width: min(62%, 280px);
      border-color: rgba(255, 224, 120, 0.95);
      box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4);
    }

    .scan-status {
      background: var(--scan-surface);
      border: 1px solid var(--scan-border);
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 0.95rem;
    }

    .checkmark-layer {
      position: absolute;
      inset: 0;
      display: none;
      align-items: center;
      justify-content: center;
      background: rgba(10, 22, 15, 0.6);
    }

    .checkmark-layer.active {
      display: flex;
      flex-direction: column;
      gap: 10px;
      animation: fadeIn 180ms ease-out;
    }

    .checkmark-circle {
      width: 94px;
      height: 94px;
      border-radius: 50%;
      background: var(--scan-accent);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 28px rgba(15, 157, 88, 0.4);
      transform: scale(0.6);
      animation: popIn 220ms ease-out forwards;
    }

    .checkmark-svg {
      width: 52px;
      height: 52px;
    }

    .checkmark-path {
      stroke-dasharray: 80;
      stroke-dashoffset: 80;
      animation: drawCheck 260ms ease-out 120ms forwards;
    }

    .checkmark-roll {
      margin: 0;
      color: #ffffff;
      font-weight: 700;
      letter-spacing: 0.02em;
      text-shadow: 0 2px 12px rgba(0, 0, 0, 0.45);
    }

    .scan-label {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #567061;
      font-weight: 600;
      margin-bottom: 2px;
    }

    .scan-value {
      font-weight: 600;
      margin-bottom: 0;
    }

    .scan-muted {
      color: #567061;
      font-size: 0.9rem;
    }

    .scan-inline-hint {
      font-size: 0.85rem;
      font-weight: 600;
      color: #7f0106;
      background: rgba(157, 2, 8, 0.08);
      border: 1px solid rgba(157, 2, 8, 0.2);
      border-radius: 10px;
      padding: 6px 10px;
    }

    .token-state {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border-radius: 999px;
      padding: 6px 12px;
      font-size: 0.82rem;
      font-weight: 600;
      border: 1px solid #d7e3da;
      background: #f6faf7;
      color: #355346;
    }

    .token-state-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #809a8d;
    }

    .token-state.is-ok {
      border-color: rgba(15, 157, 88, 0.28);
      background: rgba(15, 157, 88, 0.1);
      color: #0b6c3d;
    }

    .token-state.is-ok .token-state-dot {
      background: #0f9d58;
    }

    .token-state.is-error {
      border-color: rgba(157, 2, 8, 0.22);
      background: rgba(157, 2, 8, 0.08);
      color: #7f0106;
    }

    .token-state.is-error .token-state-dot {
      background: #9d0208;
    }

    .token-state.is-loading {
      border-color: rgba(30, 95, 193, 0.25);
      background: rgba(30, 95, 193, 0.1);
      color: #1e5fc1;
    }

    .token-state.is-loading .token-state-dot {
      background: #1e5fc1;
      animation: pulseDot 900ms ease-in-out infinite;
    }

    @keyframes pulseDot {
      0% {
        opacity: 0.45;
      }
      50% {
        opacity: 1;
      }
      100% {
        opacity: 0.45;
      }
    }

    @keyframes drawCheck {
      to {
        stroke-dashoffset: 0;
      }
    }

    @keyframes popIn {
      to {
        transform: scale(1);
      }
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }
  </style>

  <section class="scan-shell scan-card p-3 p-md-4 mb-4">
    <div class="row g-3 g-md-4">
      <div class="col-lg-7">
        <div class="scan-video-wrap mb-3">
          <video id="scanVideo" class="scan-video" autoplay playsinline muted></video>
          <div class="scan-overlay">
            <div id="scanFrame" class="scan-frame"></div>
          </div>
          <div id="checkmarkLayer" class="checkmark-layer">
            <div class="checkmark-circle">
              <svg class="checkmark-svg" viewBox="0 0 52 52" fill="none" aria-hidden="true">
                <path class="checkmark-path" d="M12 28 L22 37 L41 17" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </div>
            <p id="checkmarkRoll" class="checkmark-roll"></p>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
          <button id="startScanBtn" class="btn btn-success">Start Camera</button>
          <button id="stopScanBtn" class="btn btn-outline-secondary" disabled>Stop Camera</button>
          <span id="submitEntryWrap" class="d-inline-block" tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" title="Scan barcode and load token first.">
            <button id="submitEntryBtn" class="btn btn-primary" disabled>Record Entry</button>
          </span>
          <button id="sessionTokenBtn" class="btn btn-outline-primary">Use Session Login</button>
          <div class="form-check form-switch d-inline-flex align-items-center ms-md-1">
            <input class="form-check-input" type="checkbox" id="autoRecordToggle" checked>
            <label class="form-check-label ms-2" for="autoRecordToggle">Auto Record</label>
          </div>
          <div class="form-check form-switch d-inline-flex align-items-center ms-md-1">
            <input class="form-check-input" type="checkbox" id="webcamAssistToggle" checked>
            <label class="form-check-label ms-2" for="webcamAssistToggle">Webcam Assist</label>
          </div>
        </div>
        <div id="tokenRequiredHint" class="scan-inline-hint mb-3" hidden>Session login will be used automatically. JWT token is optional.</div>

        <div id="scanStatus" class="scan-status">Ready. Start camera to scan barcode.</div>
        <p class="scan-muted mt-2 mb-0">Accepted barcode payload formats: plain roll number, <span class="mono">ROLL|NAME</span>, or JSON <span class="mono">{"userId":"...","name":"..."}</span>.</p>
      </div>

      <div class="col-lg-5">
        <div class="scan-card p-3 h-100" style="background:#fbfdfb;">
          <div class="mb-3 d-none">
            <label class="form-label" for="accessTokenInput">Access Token (JWT, optional)</label>
            <input id="accessTokenInput" type="password" class="form-control" placeholder="Optional: paste token for API use outside web session">
            <div class="form-text">For this page, active web session auth is used automatically. Token is optional fallback.</div>
          </div>

          <div class="mb-3 d-none">
            <div id="tokenState" class="token-state">
              <span class="token-state-dot"></span>
              <span id="tokenStateText">Token state: not loaded</span>
            </div>
            <div id="tokenStateMeta" class="form-text mt-1">Session token not requested yet.</div>
          </div>

          <div class="mb-3">
            <div class="scan-label">Roll / User ID</div>
            <p id="scanUserId" class="scan-value">-</p>
          </div>
          <div class="mb-3">
            <div class="scan-label">Student Name</div>
            <p id="scanStudentName" class="scan-value">Unknown Student</p>
          </div>
          <div class="mb-3">
            <div class="scan-label">Entry Date</div>
            <p id="scanDate" class="scan-value">-</p>
          </div>
          <div class="mb-0">
            <div class="scan-label">Entry Time</div>
            <p id="scanTime" class="scan-value">-</p>
          </div>
        </div>
      </div>
    </div>
  </section>

</div>

<script>
(() => {
  const video = document.getElementById('scanVideo');
  const startBtn = document.getElementById('startScanBtn');
  const stopBtn = document.getElementById('stopScanBtn');
  const submitBtn = document.getElementById('submitEntryBtn');
  const submitEntryWrap = document.getElementById('submitEntryWrap');
  const sessionTokenBtn = document.getElementById('sessionTokenBtn');
  const statusBox = document.getElementById('scanStatus');
  const scanFrame = document.getElementById('scanFrame');
  const tokenInput = document.getElementById('accessTokenInput');
  const checkmarkLayer = document.getElementById('checkmarkLayer');
  const checkmarkRoll = document.getElementById('checkmarkRoll');
  const tokenState = document.getElementById('tokenState');
  const tokenStateText = document.getElementById('tokenStateText');
  const tokenStateMeta = document.getElementById('tokenStateMeta');
  const tokenRequiredHint = document.getElementById('tokenRequiredHint');
  const autoRecordToggle = document.getElementById('autoRecordToggle');
  const webcamAssistToggle = document.getElementById('webcamAssistToggle');

  const userIdEl = document.getElementById('scanUserId');
  const studentNameEl = document.getElementById('scanStudentName');
  const dateEl = document.getElementById('scanDate');
  const timeEl = document.getElementById('scanTime');

  let mediaStream = null;
  let detector = null;
  let zxingModule = null;
  let zxingReader = null;
  let zxingControls = null;
  let scannerMode = 'native';
  let rafId = null;
  let nativeFallbackTimer = null;
  let scanning = false;
  let isSubmitting = false;
  let cooldownUntil = 0;
  let lastRawValue = '';
  let analysisCanvas = null;
  let analysisContext = null;
  let previousGrayFrame = null;
  let lastQualityCheckAt = 0;
  let blurPauseActive = false;
  let lastBlurStatusAt = 0;

  const scanData = {
    userId: '',
    name: 'Unknown Student',
    date: '',
    time: ''
  };

  function isWebcamAssistEnabled() {
    return Boolean(webcamAssistToggle && webcamAssistToggle.checked);
  }

  function updateAssistFrameStyle() {
    if (!scanFrame) {
      return;
    }

    scanFrame.classList.toggle('assist-mode', isWebcamAssistEnabled());
  }

  function ensureAnalysisCanvas() {
    if (analysisCanvas && analysisContext) {
      return;
    }

    analysisCanvas = document.createElement('canvas');
    analysisCanvas.width = 160;
    analysisCanvas.height = 90;
    analysisContext = analysisCanvas.getContext('2d', { willReadFrequently: true });
  }

  function shouldPauseDecodeForBlur() {
    if (!isWebcamAssistEnabled()) {
      blurPauseActive = false;
      return false;
    }

    if (!video || video.readyState < 2 || video.videoWidth === 0 || video.videoHeight === 0) {
      return false;
    }

    const now = Date.now();
    if ((now - lastQualityCheckAt) < 120) {
      return blurPauseActive;
    }
    lastQualityCheckAt = now;

    ensureAnalysisCanvas();
    if (!analysisContext || !analysisCanvas) {
      return false;
    }

    analysisContext.drawImage(video, 0, 0, analysisCanvas.width, analysisCanvas.height);
    const imageData = analysisContext.getImageData(0, 0, analysisCanvas.width, analysisCanvas.height);
    const pixels = imageData.data;
    const pxCount = analysisCanvas.width * analysisCanvas.height;
    const gray = new Uint8Array(pxCount);

    for (let i = 0, p = 0; i < pxCount; i++, p += 4) {
      gray[i] = (pixels[p] * 299 + pixels[p + 1] * 587 + pixels[p + 2] * 114) / 1000;
    }

    let edgeEnergy = 0;
    for (let y = 1; y < analysisCanvas.height - 1; y++) {
      const row = y * analysisCanvas.width;
      for (let x = 1; x < analysisCanvas.width - 1; x++) {
        const idx = row + x;
        const gx = Math.abs(gray[idx + 1] - gray[idx - 1]);
        const gy = Math.abs(gray[idx + analysisCanvas.width] - gray[idx - analysisCanvas.width]);
        edgeEnergy += gx + gy;
      }
    }

    let motionEnergy = 0;
    if (previousGrayFrame) {
      for (let i = 0; i < pxCount; i++) {
        motionEnergy += Math.abs(gray[i] - previousGrayFrame[i]);
      }
      motionEnergy /= pxCount;
    }

    previousGrayFrame = gray;

    const edgeScore = edgeEnergy / pxCount;
    const isBlurry = edgeScore < 18;
    const isMovingFast = motionEnergy > 14;
    blurPauseActive = isBlurry && isMovingFast;

    if (blurPauseActive && (now - lastBlurStatusAt) > 1400) {
      lastBlurStatusAt = now;
      setStatus('Webcam Assist: motion blur detected, pausing decode. Hold code steady for a moment.');
    }

    return blurPauseActive;
  }

  function preferredVideoConstraints() {
    return {
      facingMode: { ideal: 'environment' },
      width: { ideal: 1920 },
      height: { ideal: 1080 },
      frameRate: { ideal: 24, max: 30 },
      // Browsers ignore unsupported constraints, but use them when available.
      focusMode: 'continuous',
      advanced: [
        { focusMode: 'continuous' },
        { width: 1920, height: 1080 }
      ]
    };
  }

  async function openPreferredCameraStream() {
    try {
      return await navigator.mediaDevices.getUserMedia({
        video: preferredVideoConstraints(),
        audio: false
      });
    } catch (err) {
      // Fallback for webcams/drivers that reject advanced constraints.
      return navigator.mediaDevices.getUserMedia({
        video: true,
        audio: false
      });
    }
  }

  function setTokenState(mode, text, metaText = '') {
    tokenState.classList.remove('is-ok', 'is-error', 'is-loading');
    if (mode === 'ok') {
      tokenState.classList.add('is-ok');
    } else if (mode === 'error') {
      tokenState.classList.add('is-error');
    } else if (mode === 'loading') {
      tokenState.classList.add('is-loading');
    }

    tokenStateText.textContent = text;
    tokenStateMeta.textContent = metaText;
  }

  function setStatus(message, isError = false) {
    statusBox.textContent = message;
    statusBox.style.borderColor = isError ? 'rgba(157, 2, 8, 0.35)' : '#d9e7dd';
    statusBox.style.background = isError ? 'rgba(157, 2, 8, 0.08)' : '#f7fbf8';
  }

  function formatNow() {
    const now = new Date();
    const pad = (v) => String(v).padStart(2, '0');
    return {
      date: `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`,
      time: `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`
    };
  }

  function updatePreview() {
    userIdEl.textContent = scanData.userId || '-';
    studentNameEl.textContent = scanData.name || 'Unknown Student';
    dateEl.textContent = scanData.date || '-';
    timeEl.textContent = scanData.time || '-';
    updateSubmitAvailability();
  }

  function hasUsableToken() {
    return tokenInput.value.trim().length > 0;
  }

  function updateSubmitAvailability() {
    const hasScanData = Boolean(scanData.userId);
    submitBtn.disabled = !hasScanData || isSubmitting;
    tokenRequiredHint.hidden = true;

    let tooltipReason = '';
    if (!hasScanData) {
      tooltipReason = 'Scan a barcode before recording entry.';
    }

    if (isSubmitting) {
      tooltipReason = 'Recording entry in progress.';
    }

    if (submitEntryWrap) {
      submitEntryWrap.setAttribute('title', tooltipReason || 'Ready to record entry.');
      submitEntryWrap.setAttribute('data-bs-original-title', tooltipReason || 'Ready to record entry.');
    }
  }

  function parseBarcodeText(raw) {
    const trimmed = (raw || '').trim();
    if (!trimmed) {
      return null;
    }

    try {
      const parsedJson = JSON.parse(trimmed);
      if (parsedJson && typeof parsedJson === 'object' && parsedJson.userId) {
        return {
          userId: String(parsedJson.userId).trim(),
          name: parsedJson.name ? String(parsedJson.name).trim() : 'Unknown Student'
        };
      }
    } catch (err) {
      // Not JSON payload. Continue with text parsing.
    }

    if (trimmed.includes('|')) {
      const parts = trimmed.split('|');
      return {
        userId: String(parts[0] || '').trim(),
        name: String(parts[1] || 'Unknown Student').trim() || 'Unknown Student'
      };
    }

    return {
      userId: trimmed,
      name: 'Unknown Student'
    };
  }

  function processDecodedRaw(rawValue) {
    const value = (rawValue || '').trim();
    if (!value) {
      return;
    }

    const nowMs = Date.now();
    if (nowMs < cooldownUntil) {
      return;
    }

    if (value === lastRawValue) {
      return;
    }

    const wasApplied = applyDecodedValue(value);
    if (!wasApplied) {
      return;
    }

    clearNativeFallbackTimer();
    lastRawValue = value;
    cooldownUntil = nowMs + 2000;
  }

  async function getZxingModule() {
    if (zxingModule) {
      return zxingModule;
    }

    zxingModule = await import('https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/+esm');
    return zxingModule;
  }

  function clearNativeFallbackTimer() {
    if (!nativeFallbackTimer) {
      return;
    }

    clearTimeout(nativeFallbackTimer);
    nativeFallbackTimer = null;
  }

  async function startZxingScanner() {
    scannerMode = 'zxing';
    setStatus('Starting ZXing fallback scanner...');

    const ZXing = await getZxingModule();
    const hints = new Map();
    if (ZXing.DecodeHintType && ZXing.BarcodeFormat) {
      hints.set(ZXing.DecodeHintType.TRY_HARDER, true);
      hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, [
        ZXing.BarcodeFormat.QR_CODE,
        ZXing.BarcodeFormat.CODE_128,
        ZXing.BarcodeFormat.CODE_39,
        ZXing.BarcodeFormat.EAN_13,
        ZXing.BarcodeFormat.UPC_A
      ]);
    }

    zxingReader = new ZXing.BrowserMultiFormatReader(hints, 150);

    const onDecode = (result) => {
      if (shouldPauseDecodeForBlur()) {
        return;
      }

      if (result && typeof result.getText === 'function') {
        processDecodedRaw(result.getText());
      }
    };

    if (typeof zxingReader.decodeFromConstraints === 'function') {
      try {
        zxingControls = await zxingReader.decodeFromConstraints(
          { video: preferredVideoConstraints(), audio: false },
          video,
          onDecode
        );
      } catch (err) {
        zxingControls = await zxingReader.decodeFromVideoDevice(undefined, video, onDecode);
      }
    } else {
      zxingControls = await zxingReader.decodeFromVideoDevice(undefined, video, onDecode);
    }

    scanning = true;
    startBtn.disabled = true;
    stopBtn.disabled = false;
    setStatus('Camera started. Using ZXing fallback scanner. For fixed-focus webcams, hold code 20-35 cm away and fill most of the frame.');
  }

  async function switchToZxingFallback() {
    if (!scanning || scannerMode === 'zxing') {
      return;
    }

    clearNativeFallbackTimer();

    if (rafId) {
      cancelAnimationFrame(rafId);
      rafId = null;
    }

    detector = null;

    if (mediaStream) {
      mediaStream.getTracks().forEach((track) => track.stop());
      mediaStream = null;
    }

    video.srcObject = null;
    await startZxingScanner();
  }

  async function requestCameraPermission() {
    if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
      throw new Error('Camera API is not available in this browser.');
    }

    const probeStream = await navigator.mediaDevices.getUserMedia({
      video: true,
      audio: false
    });

    probeStream.getTracks().forEach((track) => track.stop());
  }

  function applyDecodedValue(rawValue) {
    const parsed = parseBarcodeText(rawValue);
    if (!parsed || !parsed.userId) {
      return false;
    }

    const now = formatNow();
    scanData.userId = parsed.userId;
    scanData.name = parsed.name;
    scanData.date = now.date;
    scanData.time = now.time;
    updatePreview();
    setStatus(`Detected barcode: ${parsed.userId}. Ready to record entry.`);

    if (autoRecordToggle && autoRecordToggle.checked) {
      void triggerAutoRecord();
    }

    return true;
  }

  async function triggerAutoRecord() {
    if (isSubmitting || !scanData.userId) {
      return;
    }

    await submitEntry({ fromAuto: true });
  }

  function showSuccessAnimation() {
    if (checkmarkRoll) {
      checkmarkRoll.textContent = scanData.userId ? `Roll: ${scanData.userId}` : '';
    }

    checkmarkLayer.classList.add('active');
    setTimeout(() => {
      checkmarkLayer.classList.remove('active');
      if (checkmarkRoll) {
        checkmarkRoll.textContent = '';
      }
    }, 3000);
  }

  async function scanLoop() {
    if (!scanning || !detector) {
      return;
    }

    if (shouldPauseDecodeForBlur()) {
      rafId = requestAnimationFrame(scanLoop);
      return;
    }

    try {
      const barcodes = await detector.detect(video);
      if (Array.isArray(barcodes) && barcodes.length > 0) {
        const first = barcodes.find((item) => item.rawValue) || barcodes[0];
        processDecodedRaw(first.rawValue || '');
      }
    } catch (err) {
      // Ignore transient detect errors and continue scanning.
    }

    rafId = requestAnimationFrame(scanLoop);
  }

  async function startCamera() {
    if (!window.isSecureContext) {
      setStatus('Camera requires secure context. Open over HTTPS or localhost.', true);
      return;
    }

    try {
      setStatus('Requesting camera permission...');
      await requestCameraPermission();

      if ('BarcodeDetector' in window) {
        scannerMode = 'native';
        detector = new BarcodeDetector({
          formats: ['qr_code', 'code_128', 'code_39', 'ean_13', 'upc_a']
        });

        mediaStream = await openPreferredCameraStream();

        video.srcObject = mediaStream;
        await video.play();

        scanning = true;
        startBtn.disabled = true;
        stopBtn.disabled = false;
        setStatus('Camera started. Using native BarcodeDetector. For fixed-focus webcams, hold code 20-35 cm away and fill most of the frame.');
        scanLoop();

        clearNativeFallbackTimer();
        nativeFallbackTimer = setTimeout(() => {
          if (!scanning || scannerMode !== 'native') {
            return;
          }

          setStatus('Native scanner could not decode barcode. Switching to ZXing fallback...');
          void switchToZxingFallback();
        }, 5000);
        return;
      }

      await startZxingScanner();
    } catch (err) {
      setStatus('Unable to access camera/scanner. Check permission, HTTPS, and network for fallback library.', true);
    }
  }

  function stopCamera() {
    scanning = false;
    clearNativeFallbackTimer();

    if (rafId) {
      cancelAnimationFrame(rafId);
      rafId = null;
    }

    if (zxingControls && typeof zxingControls.stop === 'function') {
      zxingControls.stop();
      zxingControls = null;
    }

    if (zxingReader && typeof zxingReader.reset === 'function') {
      zxingReader.reset();
    }

    if (mediaStream) {
      mediaStream.getTracks().forEach((track) => track.stop());
      mediaStream = null;
    }

    previousGrayFrame = null;
    blurPauseActive = false;

    detector = null;
    video.srcObject = null;
    startBtn.disabled = false;
    stopBtn.disabled = true;
    setStatus('Camera stopped.');
  }

  async function submitEntry(options = {}) {
    const fromAuto = Boolean(options.fromAuto);

    if (isSubmitting) {
      return;
    }

    if (!scanData.userId) {
      setStatus('No barcode data detected yet.', true);
      return;
    }

    let accessToken = tokenInput.value.trim();

    isSubmitting = true;
    updateSubmitAvailability();
    setStatus('Recording entry...');

    try {
      const payload = {
        userId: scanData.userId,
        date: scanData.date,
        time: scanData.time
      };

      const requestHeaders = {
        'Content-Type': 'application/json'
      };

      if (accessToken) {
        requestHeaders.Authorization = `Bearer ${accessToken}`;
      }

      let response = await fetch('/api/record_entry.php', {
        method: 'POST',
        headers: requestHeaders,
        body: JSON.stringify(payload)
      });

      if (response.status === 401) {
        const refreshed = await loadSessionToken({ silent: true });
        const nextToken = tokenInput.value.trim();
        if (refreshed && nextToken) {
          response = await fetch('/api/record_entry.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${nextToken}`
            },
            body: JSON.stringify(payload)
          });
        }
      }

      const body = await response.json();
      if (!response.ok || !body.ok) {
        const message = body && body.error && body.error.message ? body.error.message : 'Failed to record entry.';
        throw new Error(message);
      }

      showSuccessAnimation();
      setStatus('Entry recorded successfully. Ready for next scan.');
      lastRawValue = '';
      const now = formatNow();
      scanData.date = now.date;
      scanData.time = now.time;
      updatePreview();
    } catch (err) {
      setStatus(err && err.message ? err.message : 'Request failed.', true);
    } finally {
      isSubmitting = false;
      updateSubmitAvailability();
    }
  }

  async function loadSessionToken(options = {}) {
    const silent = Boolean(options.silent);
    sessionTokenBtn.disabled = true;

    setTokenState('loading', 'Token state: loading', 'Requesting JWT from active web session...');

    if (!silent) {
      setStatus('Loading token from active session...');
    }

    try {
      const response = await fetch('/api/session_token.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        }
      });

      const body = await response.json();
      if (!response.ok || !body.ok || !body.data || !body.data.access_token) {
        const message = body && body.error && body.error.message ? body.error.message : 'Unable to get token from session.';
        setTokenState('error', 'Token state: unavailable', message);
        if (!silent) {
          setStatus(message, true);
        }
        return false;
      }

      tokenInput.value = String(body.data.access_token);
      const expiry = body && body.data && body.data.expires_in ? Number(body.data.expires_in) : 0;
      const expiryMeta = expiry > 0 ? `Session token loaded. Expires in ~${expiry}s.` : 'Session token loaded successfully.';
      setTokenState('ok', 'Token state: ready', expiryMeta);
      if (!silent) {
        setStatus('Session token loaded. Scanner is ready.');
      }
      return true;
    } catch (err) {
      setTokenState('error', 'Token state: error', 'Failed to load token from session endpoint.');
      if (!silent) {
        setStatus('Failed to load session token.', true);
      }
      return false;
    } finally {
      sessionTokenBtn.disabled = false;
      updateSubmitAvailability();
    }
  }

  startBtn.addEventListener('click', startCamera);
  stopBtn.addEventListener('click', stopCamera);
  submitBtn.addEventListener('click', submitEntry);
  sessionTokenBtn.addEventListener('click', () => {
    loadSessionToken({ silent: false });
  });

  tokenInput.addEventListener('input', () => {
    if (tokenInput.value.trim()) {
      setTokenState('ok', 'Token state: manual', 'Using manually pasted access token.');
    } else {
      setTokenState('error', 'Token state: empty', 'Provide token manually or use session login.');
    }
    updateSubmitAvailability();
  });

  if (webcamAssistToggle) {
    webcamAssistToggle.addEventListener('change', () => {
      updateAssistFrameStyle();
      if (!webcamAssistToggle.checked) {
        blurPauseActive = false;
        setStatus('Webcam Assist disabled. Scanner running normally.');
      } else {
        setStatus('Webcam Assist enabled. Tighter frame and blur pause are active.');
      }
    });
  }

  window.addEventListener('beforeunload', () => {
    stopCamera();
  });

  updatePreview();
  updateAssistFrameStyle();
  setTokenState('ok', 'Token state: optional', 'Session login auth is used automatically on this page.');
  updateSubmitAvailability();

  if (window.bootstrap && window.bootstrap.Tooltip && submitEntryWrap) {
    window.bootstrap.Tooltip.getOrCreateInstance(submitEntryWrap);
  }

})();
</script>
