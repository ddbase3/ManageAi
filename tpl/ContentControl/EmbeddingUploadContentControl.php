<div id="EmbeddingUploadContentControl" class="contentcontrol">
	<div id="wrap">

		<style>
			#EmbeddingUploadContentControl .upload-box {
				border: 2px dashed #888;
				border-radius: 12px;
				padding: 30px;
				text-align: center;
				cursor: pointer;
				color: #555;
				background: #fafafa;
				transition: all 0.2s ease;
			}
			#EmbeddingUploadContentControl .upload-box.dragover {
				border-color: #3b82f6;
				background: #eef5ff;
				color: #1d4ed8;
			}
			#EmbeddingUploadContentControl .upload-info {
				margin-top: 15px;
				font-size: 14px;
				color: #444;
			}
			#EmbeddingUploadContentControl .result-box {
				margin-top: 25px;
				padding: 15px;
				border-radius: 8px;
				background: #f7f7f7;
				border: 1px solid #ccc;
				font-family: monospace;
				white-space: pre-wrap;
			}
			#EmbeddingUploadContentControl .hidden {
				display: none;
			}
		</style>

		<div id="uploadBox" class="upload-box">
			<b>Upload a document</b><br>
			<span>Drag & Drop here or click to choose a file</span>
			<input type="file" id="fileInput" class="hidden" />
		</div>

		<div id="uploadInfo" class="upload-info hidden"></div>

		<div id="resultBox" class="result-box hidden"></div>

		<script>
			(function() {

				const uploadBox = document.getElementById('uploadBox');
				const fileInput = document.getElementById('fileInput');
				const uploadInfo = document.getElementById('uploadInfo');
				const resultBox = document.getElementById('resultBox');

				// ------------------------
				// Helpers
				// ------------------------

				function showInfo(msg) {
					uploadInfo.textContent = msg;
					uploadInfo.classList.remove('hidden');
				}

				function showResult(obj) {
					resultBox.textContent = JSON.stringify(obj, null, 2);
					resultBox.classList.remove('hidden');
				}

				function uploadFile(file) {

					showInfo("Uploading: " + file.name + " (" + Math.round(file.size/1024) + " KB)...");

					const form = new FormData();
					form.append('file', file);

					fetch('uploadembeddingservice.php', {
						method: 'POST',
						body: form
					})
					.then(r => r.json())
					.then(json => {
						showInfo("Upload done.");
						showResult(json);
					})
					.catch(err => {
						showInfo("Error during upload.");
						showResult({ error: err.toString() });
					});
				}

				// ------------------------
				// Drag & Drop
				// ------------------------

				uploadBox.addEventListener('click', () => fileInput.click());

				uploadBox.addEventListener('dragover', (e) => {
					e.preventDefault();
					uploadBox.classList.add('dragover');
				});

				uploadBox.addEventListener('dragleave', () => {
					uploadBox.classList.remove('dragover');
				});

				uploadBox.addEventListener('drop', (e) => {
					e.preventDefault();
					uploadBox.classList.remove('dragover');

					if (e.dataTransfer.files.length > 0) {
						const file = e.dataTransfer.files[0];
						uploadFile(file);
					}
				});

				// ------------------------
				// Standard File Input
				// ------------------------

				fileInput.addEventListener('change', (e) => {
					if (fileInput.files.length > 0) {
						const file = fileInput.files[0];
						uploadFile(file);
					}
				});

			})();
		</script>

	</div>
</div>
