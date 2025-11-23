<?php /* unset($_SESSION['missionbay_vectorstore']); */ ?>

<div id="SessionStoreContentControl" class="contentcontrol">

	<div id="wrap">

		<style>
			#mb-debug-toolbar {
				display: flex;
				justify-content: space-between;
				margin-bottom: 8px;
				align-items: center;
				gap: 8px;
			}

			#mb-debug-search {
				flex: 1;
				padding: 6px 10px;
				border-radius: 6px;
				border: 1px solid #555;
				background: #222;
				color: #eee;
				font-size: 13px;
			}

			#mb-debug-copy {
				background: #444;
				color: #fff;
				border: none;
				padding: 6px 12px;
				border-radius: 6px;
				cursor: pointer;
				font-size: 12px;
				white-space: nowrap;
			}

			#mb-debug-copy:hover {
				background: #666;
			}

			#mb-debug-panel {
				font-family: monospace;
				background: #1e1e1e;
				color: #dcdcdc;
				padding: 12px 16px;
				border-radius: 10px;
				overflow: auto;
				max-height: 500px;
				border: 1px solid #333;
				font-size: 12px;
			}

			/* JSON Farben */
			.json-key   { color: #9cdcfe; }
			.json-str   { color: #ce9178; }
			.json-num   { color: #b5cea8; }
			.json-bool  { color: #569cd6; }
			.json-null  { color: #569cd6; }

			/* Zeilen & Layout */
			.mb-line {
				white-space: pre;
			}

			.mb-chunk {
				margin: 0 0 4px 0;
			}

			.mb-chunk-footer {
				margin-left: 2ch;
			}

			.mb-chunk-body {
				margin-left: 2ch;
				margin-bottom: 4px;
				white-space: pre;
			}

			.mb-fold {
				display: inline-block;
				width: 14px;
				color: #aaa;
				user-select: none;
				cursor: pointer;
			}

			.mb-chunk-header {
				cursor: pointer;
			}

			#mb-debug-panel mark {
				background: #444d;
				color: #fff;
				padding: 0 1px;
				border-radius: 2px;
			}

			#mb-debug-panel .mb-empty {
				color: #777;
				font-style: italic;
			}
		</style>

		<div id="mb-debug-toolbar">
			<input type="text" id="mb-debug-search" placeholder="Search in keys & text…">
			<button id="mb-debug-copy">Copy JSON</button>
		</div>

		<div id="mb-debug-panel"></div>

	</div>

</div>

<script>
	(function() {
		const rawJson = <?php echo json_encode($_SESSION['missionbay_vectorstore'] ?? [], JSON_UNESCAPED_UNICODE); ?>;

		// ---------------------------------------------------------------------
		// Helpers
		// ---------------------------------------------------------------------

		function escapeHtml(str) {
			return String(str)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;');
		}

		// Compress large vectors -> einzeilig, gekürzt
		function compressVectors(data) {
			function recurse(obj) {
				if (Array.isArray(obj)) {
					return obj.map(recurse);
				}
				if (typeof obj === 'object' && obj !== null) {
					const clone = {};
					for (let k in obj) {
						if (k === 'vector' && Array.isArray(obj[k])) {
							const v = obj[k];
							let preview = v.slice(0, 5).join(', ');
							if (v.length > 5) {
								preview += ', …, ' + v[v.length - 1];
							}
							clone[k] = '[ ' + preview + ' ] (' + v.length + ' dims)';
						} else {
							clone[k] = recurse(obj[k]);
						}
					}
					return clone;
				}
				return obj;
			}
			return recurse(data);
		}

		const displayJson = compressVectors(rawJson);

		// Syntax highlighting für Wert / Objekt
		function syntaxHighlight(obj) {
			let json = JSON.stringify(obj, null, 2);
			json = escapeHtml(json);

			return json.replace(
				/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
				function (match) {
					let cls = 'json-num';
					if (/^"/.test(match)) {
						cls = /:$/.test(match) ? 'json-key' : 'json-str';
					} else if (/true|false/.test(match)) {
						cls = 'json-bool';
					} else if (/null/.test(match)) {
						cls = 'json-null';
					}
					return '<span class="' + cls + '">' + match + '</span>';
				}
			);
		}

		// Chunk HTML bauen – A1: Pfeil links, JSON-ähnlich
		function createChunkHtml(key, value, searchTerm, addComma) {
			let inner = syntaxHighlight(value);

			if (searchTerm) {
				const escaped = searchTerm.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
				const re = new RegExp('(' + escaped + ')', 'gi');
				inner = inner.replace(re, '<mark>$1</mark>');
			}

			const keyLabel = escapeHtml(key);
			const comma = addComma ? ',' : '';

			return ''
				+ '<div class="mb-chunk">'
				+   '<div class="mb-line mb-chunk-header">'
				+     '<span class="mb-fold" data-open="1">▾</span>'
				+     '"<span class="json-key">' + keyLabel + '</span>":'
				+   '</div>'
				+   '<div class="mb-line mb-chunk-body">' + inner + comma + '</div>'
				+ '</div>';
		}

		function renderDebugger(filterTerm = '') {
			const panel = document.getElementById('mb-debug-panel');

			if (!displayJson || Object.keys(displayJson).length === 0) {
				panel.innerHTML = '<div class="mb-line"><span class="mb-empty">// No vectors stored in current session.</span></div>';
				return;
			}

			const term = filterTerm.trim().toLowerCase();
			const keys = Object.keys(displayJson);

			let html = '';
			html += '<div class="mb-line">{</div>';

			let rendered = [];

			for (let i = 0; i < keys.length; i++) {
				const k = keys[i];
				const value = displayJson[k];

				if (term) {
					const raw = JSON.stringify(rawJson[k] || displayJson[k] || '', null, 2).toLowerCase();
					if (!raw.includes(term)) continue;
				}

				rendered.push(createChunkHtml(k, value, term, true));
			}

			if (rendered.length === 0) {
				html += '<div class="mb-line">  <span class="mb-empty">// No matching chunks.</span></div>';
				html += '<div class="mb-line">}</div>';
				panel.innerHTML = html;
				return;
			}

			// Letztes Chunk ohne Komma
			for (let i = 0; i < rendered.length; i++) {
				if (i === rendered.length - 1) {
					// letztes: Komma im Footer entfernen
					rendered[i] = rendered[i].replace(/<\/div>\s*<\/div>\s*$/, '</div></div>')
						.replace(/<\/div>\s*<\/div>$/, '</div></div>')
						.replace(/}\s*,\s*<\/div>\s*<\/div>$/, '}</div></div>');
				}
				html += rendered[i];
			}

			html += '<div class="mb-line">}</div>';

			panel.innerHTML = html;
			attachChunkFoldEvents(panel);
		}

		function attachChunkFoldEvents(panel) {
			const chunks = panel.querySelectorAll('.mb-chunk');
			chunks.forEach(chunk => {
				const header = chunk.querySelector('.mb-chunk-header');
				const fold = chunk.querySelector('.mb-fold');
				const body = chunk.querySelector('.mb-chunk-body');
				if (!header || !fold || !body) return;

				const toggle = () => {
					const isOpen = fold.getAttribute('data-open') === '1';
					if (isOpen) {
						fold.textContent = '▸';
						fold.setAttribute('data-open', '0');
						body.style.display = 'none';
					} else {
						fold.textContent = '▾';
						fold.setAttribute('data-open', '1');
						body.style.display = '';
					}
				};

				fold.addEventListener('click', e => {
					e.stopPropagation();
					toggle();
				});

				header.addEventListener('click', e => {
					if (!e.target.classList.contains('mb-fold')) {
						toggle();
					}
				});
			});
		}

		// Suche
		document.getElementById('mb-debug-search').addEventListener('input', function () {
			renderDebugger(this.value);
		});

		// Copy JSON
		document.getElementById('mb-debug-copy').addEventListener('click', () => {
			navigator.clipboard.writeText(JSON.stringify(rawJson, null, 2));
		});

		// Init
		renderDebugger();
	})();
</script>
