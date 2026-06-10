/**
 * public/assets/js/app.js
 * ------------------------
 * Client-side JavaScript for BookNest.
 */

document.addEventListener('DOMContentLoaded', function () {

   // ──────────────────────────────────────────────────────────────────────────
    // 1. AJAX LIVE BOOK SEARCH (BULLETPROOF VERSION)
    // ──────────────────────────────────────────────────────────────────────────
// ──────────────────────────────────────────────────────────────────────────
    // 1. AJAX LIVE BOOK SEARCH (FINAL FIX)
    // ──────────────────────────────────────────────────────────────────────────

    const searchWrappers = document.querySelectorAll('.search-wrapper');

    searchWrappers.forEach(function (wrapper) {
        // بنجيب شريط البحث وعلبة النتايج اللي جوه العلبة دي بسسس
        const searchInput = wrapper.querySelector('input[type="text"]');
        const resultsDiv  = wrapper.querySelector('.dynamic-search-results') || wrapper.querySelector('div[id="search-results"]');

        if (searchInput && resultsDiv) {
            let debounceTimer = null;

            searchInput.addEventListener('input', function () {
                const query = this.value.trim();
                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    resultsDiv.innerHTML = '';
                    resultsDiv.style.display = 'none'; // نخفيها لو مفيش كلام
                    return;
                }

                debounceTimer = setTimeout(function () {
                    fetch('index.php?page=books&action=ajaxSearch&q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(books => {
                            resultsDiv.style.display = 'block'; // نظهر العلبة
                            
                            if (books.length === 0) {
                                resultsDiv.innerHTML = '<div class="list-group shadow"><p class="text-muted p-3 mb-0">No books found.</p></div>';
                                return;
                            }

                            let html = '<div class="list-group shadow border-0">';
                            books.forEach(book => {
                                const price = parseFloat(book.final_price).toFixed(2);
                                html += `
                                    <a href="index.php?page=books&action=show&id=${book.id}"
                                       class="list-group-item list-group-item-action py-2 px-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${escapeHtml(book.title)}</strong>
                                                <small class="text-muted d-block">by ${escapeHtml(book.author_name)}</small>
                                            </div>
                                            <span class="badge bg-success ms-2">EGP ${price}</span>
                                        </div>
                                    </a>`;
                            });
                            html += '</div>';
                            resultsDiv.innerHTML = html;
                        })
                        .catch(err => {
                            console.error('Search error:', err);
                            resultsDiv.innerHTML = '';
                        });
                }, 300);
            });

            // إخفاء النتايج لو دوسنا بره
            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target)) {
                    resultsDiv.innerHTML = '';
                    resultsDiv.style.display = 'none';
                }
            });
        }
    });

    // ──────────────────────────────────────────────────────────────────────────
    // 2. AUTO-DISMISS FLASH ALERTS
    // ──────────────────────────────────────────────────────────────────────────

    const flashAlert = document.querySelector('.flash-alert');
    if (flashAlert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(flashAlert);
            bsAlert.close();
        }, 4500);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 3. BOOK COVER IMAGE PREVIEW BEFORE UPLOAD
    // ──────────────────────────────────────────────────────────────────────────

    const coverInput = document.getElementById('cover-upload');
    const coverPreview = document.getElementById('cover-preview');

    if (coverInput && coverPreview) {
        coverInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    coverPreview.src = e.target.result;
                    coverPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // 4. CONFIRM DIALOGS FOR DESTRUCTIVE ACTIONS
    // ──────────────────────────────────────────────────────────────────────────

    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // ──────────────────────────────────────────────────────────────────────────
    // 5. READING PROGRESS BAR ANIMATION
    // ──────────────────────────────────────────────────────────────────────────

    document.querySelectorAll('.progress-bar[data-width]').forEach(function (bar) {
        const target = parseInt(bar.getAttribute('data-width'), 10);
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = target + '%'; }, 200);
    });

    // ──────────────────────────────────────────────────────────────────────────
    // HELPER: Escape HTML to prevent XSS
    // ──────────────────────────────────────────────────────────────────────────
    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

});