// Chinese Component Search Application - Client Side
class ChineseComponentSearch {
    constructor() {
        this.isLoading = false;

        // DOM Elements
        this.searchInput = document.getElementById('searchInput');
        this.searchBtn = document.getElementById('searchBtn');
        this.resultsSection = document.getElementById('resultsSection');
        this.resultsGrid = document.getElementById('resultsGrid');
        this.resultCount = document.getElementById('resultCount');
        this.searchInfo = document.getElementById('searchInfo');
        this.loadingIndicator = document.getElementById('loadingIndicator');
        this.emptyState = document.getElementById('emptyState');
        this.modalOverlay = document.getElementById('modalOverlay');
        this.modalContent = document.getElementById('modalContent');
        this.modalClose = document.getElementById('modalClose');
        this.decomposeBtn = document.getElementById('decomposeBtn');
        this.copyBtn = document.getElementById('copyBtn');
        this.toastContainer = document.getElementById('toastContainer');
        this.tipButtons = document.querySelectorAll('.tip-btn');

        this.currentResults = [];

        this.init();
    }

    init() {
        // Setup event listeners
        this.setupEventListeners();
        console.log('App initialized. Backend: api.php');
    }

    setupEventListeners() {
        // Search button click
        this.searchBtn.addEventListener('click', () => this.performSearch());

        // Enter key in search input
        this.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.performSearch();
            }
        });

        // Tip button clicks
        this.tipButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const component = btn.dataset.component;
                this.searchInput.value = component;
                this.performSearch();
            });
        });

        // Modal close
        this.modalClose.addEventListener('click', () => this.closeModal());
        this.modalOverlay.addEventListener('click', (e) => {
            if (e.target === this.modalOverlay) {
                this.closeModal();
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });

        // Decompose button click
        this.decomposeBtn.addEventListener('click', () => this.decomposeInput());

        // Copy button click
        this.copyBtn.addEventListener('click', () => this.copyResults());
    }

    async performSearch() {
        const keyword = this.searchInput.value.trim();

        if (!keyword) {
            this.showEmptyState();
            return;
        }

        this.showLoading(true);

        try {
            const response = await fetch(`api.php?action=search&keyword=${encodeURIComponent(keyword)}`);
            if (!response.ok) throw new Error('Network response was not ok');
            
            const results = await response.json();
            
            if (results.error) {
                this.showError(results.error);
                return;
            }

            this.displayResults(results, keyword);

        } catch (error) {
            console.error('Search error:', error);
            this.showError('搜尋時發生錯誤，請稍後再試。');
        } finally {
            this.showLoading(false);
        }
    }

    displayResults(results, keyword) {
        this.resultsGrid.innerHTML = '';
        this.resultCount.textContent = results.length;
        this.searchInfo.textContent = `搜尋部件：「${keyword}」`;

        if (results.length === 0) {
            this.resultsGrid.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <div class="empty-icon">😕</div>
                    <h3>找不到符合的漢字</h3>
                    <p>請嘗試其他部件關鍵字</p>
                </div>
            `;
            return;
        }



        this.currentResults = results;
        this.copyBtn.classList.add('visible');
        this.emptyState.classList.add('hidden');

        // Note: Backend limits to 500 already
        results.forEach((result, index) => {
            const card = document.createElement('div');
            card.className = 'char-card';
            card.style.animationDelay = `${Math.min(index * 0.02, 0.5)}s`;
            card.innerHTML = `<span>${result.char}</span>`;
            card.addEventListener('click', () => this.showCharacterDetail(result.char, result.data));
            this.resultsGrid.appendChild(card);
        });

        if (results.length >= 500) {
            this.searchInfo.textContent += ` (顯示前 500 個結果)`;
        }
    }

    async showCharacterDetail(char, data = null) {
        // If we don't have data (e.g. from variant link), fetch it
        if (!data) {
            try {
                const response = await fetch(`api.php?action=detail&char=${encodeURIComponent(char)}`);
                const json = await response.json();
                if (json && json.data) {
                    data = json.data;
                } else {
                    this.showToast(`無法讀取「${char}」的詳細資料`, 'error');
                    return;
                }
            } catch (error) {
                console.error('Fetch detail error:', error);
                this.showToast('讀取詳細資料失敗', 'error');
                return;
            }
        }

        const html = this.generateDetailHTML(char, data);
        this.modalContent.innerHTML = html;
        this.modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Setup variant character clicks
        const variantChars = this.modalContent.querySelectorAll('.variant-char');
        variantChars.forEach(el => {
            el.addEventListener('click', () => {
                const variantChar = el.textContent.trim();
                // Recursively call showCharacterDetail with just the char, so it fetches
                this.showCharacterDetail(variantChar); 
            });
        });
    }

    generateDetailHTML(char, data) {
        const formatList = (arr) => {
            if (!arr || arr.length === 0) return '<span class="no-data">無資料</span>';
            return arr.map(item => `<span class="pronunciation-tag">${item}</span>`).join('');
        };

        const formatVariants = (arr) => {
            if (!arr || arr.length === 0) return '<span class="no-data">無資料</span>';
            return arr.map(item => `<span class="variant-char" title="點擊查看">${item}</span>`).join('');
        };

        const formatComponents = (obj) => {
            if (!obj || Object.keys(obj).length === 0) return '<span class="no-data">無資料</span>';
            return Object.entries(obj).map(([key, value]) => `
                <div class="component-item">
                    <span class="component-key">${key}</span>
                    <span class="component-value">${value}</span>
                </div>
            `).join('');
        };

        return `
            <div class="char-detail">
                <div class="char-display">${char}</div>
                <div class="char-unicode">U+${data.unicode_hex || 'N/A'} (${data.unicode || 'N/A'})</div>
                
                <div class="detail-section">
                    <h3>基本資訊</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">部首</span>
                            <span class="detail-value">${data.radical || '無'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">部首筆畫</span>
                            <span class="detail-value">${data.radical_count ?? '無'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">非部首筆畫</span>
                            <span class="detail-value">${data.strokes_count ?? '無'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">總筆畫</span>
                            <span class="detail-value">${data.strokes_total ?? '無'}</span>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>部件組成</h3>
                    <div class="component-grid">
                        ${formatComponents(data.component)}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>普通話發音</h3>
                    <div class="pronunciation-list">
                        ${formatList(data.pronunciation_mandarin_pinyin)}
                    </div>
                    <div class="pronunciation-list" style="margin-top: 0.5rem;">
                        ${formatList(data.pronunciation_mandarin_zhuyin)}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>粵語發音</h3>
                    <div class="pronunciation-list">
                        ${formatList(data.pronunciation_cantonese)}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>閩南語發音</h3>
                    <div class="pronunciation-list">
                        ${formatList(data.pronunciation_southern_min)}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>客家話發音</h3>
                    <div class="pronunciation-list">
                        ${formatList(data.pronunciation_hakka)}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>異體字</h3>
                    <div class="variant-list">
                        ${formatVariants(data.alternate)}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>語義變體</h3>
                    <div class="variant-list">
                        ${formatVariants(data.semantic_variant)}
                    </div>
                </div>
            </div>
        `;
    }

    closeModal() {
        this.modalOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    showLoading(show) {
        this.isLoading = show;
        if (show) {
            this.loadingIndicator.classList.add('active');
            this.emptyState.classList.add('hidden');
            this.copyBtn.classList.remove('visible');
            this.resultsGrid.innerHTML = '';
        } else {
            this.loadingIndicator.classList.remove('active');
        }
    }

    showEmptyState() {
        this.resultsGrid.innerHTML = '';
        this.resultCount.textContent = '0';
        this.searchInfo.textContent = '';

        this.copyBtn.classList.remove('visible');
        this.emptyState.classList.remove('hidden');
    }

    showError(message) {
        this.showLoading(false);
        this.resultsGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <div class="empty-icon">⚠️</div>
                <h3>發生錯誤</h3>
                <p>${message}</p>
            </div>
        `;
    }

    // Decompose input characters via API
    async decomposeInput() {
        const input = this.searchInput.value.trim();

        if (!input) {
            this.showToast('請先輸入要拆解的字', 'warning');
            return;
        }
        
        // Show loading indicator in button or just toast?
        this.showToast('正在拆解...', 'info');

        try {
            const response = await fetch(`api.php?action=decompose&text=${encodeURIComponent(input)}`);
            if (!response.ok) throw new Error('API Error');
            
            const data = await response.json();
            
            if (data.error) throw new Error(data.error);
            
            // Check result
            const result = data.result;
            
            if (data.is_same) {
                this.showToast(`「${input}」已無法再拆解`, 'info');
                return;
            }

            // Update input field
            this.searchInput.value = result;

            if (data.has_undecomposable) {
                this.showToast(`部分文字已拆解：${input} → ${result}`, 'success');
            } else {
                this.showToast(`拆解完成：${input} → ${result}`, 'success');
            }

            // Focus on input for continued editing
            this.searchInput.focus();

        } catch (error) {
            console.error('Decompose error:', error);
            this.showToast('拆解失敗，請稍後再試。', 'error');
        }
    }

    // Copy results to clipboard
    async copyResults() {
        if (!this.currentResults || this.currentResults.length === 0) {
            this.showToast('沒有可複製的結果', 'warning');
            return;
        }

        try {
            const chars = this.currentResults.map(r => r.char).join('');
            await navigator.clipboard.writeText(chars);
            this.showToast(`已複製 ${this.currentResults.length} 個字到剪貼簿`, 'success');
        } catch (err) {
            console.error('Copy failed:', err);
            this.showToast('複製失敗', 'error');
        }
    }

    // Show toast notification
    showToast(message, type = 'info') {
        const icons = {
            success: '✅',
            warning: '⚠️',
            error: '❌',
            info: 'ℹ️'
        };

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${message}</span>
        `;

        this.toastContainer.appendChild(toast);

        // Remove toast after animation completes
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
}

// Initialize the application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ChineseComponentSearch();
});
