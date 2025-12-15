<?php
$allSettings = $settings->getAll();

// Grupuj według kategorii
$grouped = [];
foreach ($allSettings as $setting) {
    $grouped[$setting['category']][] = $setting;
}
?>  
    <div class="container">
        <div class="customize-header">
            <h1>🎨 Customizacja Strony</h1>
            <div class="customize-actions">
                <a href="/admin" class="btn">← Panel Admina</a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✅</span>
                <div class="alert-content">
                    <strong>Sukces!</strong>
                    <p><?= htmlspecialchars($_SESSION['success']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">🚫</span>
                <div class="alert-content">
                    <strong>Błąd!</strong>
                    <p><?= htmlspecialchars($_SESSION['error']) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <form method="POST" action="/admin/customize/save" class="customize-form">
            <div class="customize-tabs">
                <button type="button" class="tab-btn active" data-tab="general">⚙️ Ogólne</button>
                <button type="button" class="tab-btn" data-tab="design">🎨 Wygląd</button>
                <button type="button" class="tab-btn" data-tab="features">✨ Funkcje</button>
            </div>
            
            <?php foreach ($grouped as $category => $categorySettings): ?>
                <div class="tab-content" id="tab-<?= $category ?>" style="<?= $category !== 'general' ? 'display: none;' : '' ?>">
                    <h2>
                        <?php if ($category === 'general'): ?>⚙️ Ogólne Ustawienia<?php endif; ?>
                        <?php if ($category === 'design'): ?>🎨 Wygląd i Kolory<?php endif; ?>
                        <?php if ($category === 'features'): ?>✨ Funkcje<?php endif; ?>
                    </h2>
                    
                    <div class="settings-grid">
                        <?php foreach ($categorySettings as $setting): ?>
                            <div class="setting-item">
                                <label for="<?= $setting['setting_key'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $setting['setting_key'])) ?>
                                </label>
                                
                                <?php if ($setting['setting_type'] === 'text'): ?>
                                    <input 
                                        type="text" 
                                        id="<?= $setting['setting_key'] ?>"
                                        name="setting_<?= $setting['setting_key'] ?>"
                                        value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                        class="form-control"
                                    >
                                
                                <?php elseif ($setting['setting_type'] === 'textarea'): ?>
                                    <textarea 
                                        id="<?= $setting['setting_key'] ?>"
                                        name="setting_<?= $setting['setting_key'] ?>"
                                        rows="6"
                                        class="form-control"
                                    ><?= htmlspecialchars($setting['setting_value']) ?></textarea>
                                
                                <?php elseif ($setting['setting_type'] === 'color'): ?>
                                    <div class="color-picker-wrapper">
                                        <input 
                                            type="color" 
                                            id="<?= $setting['setting_key'] ?>"
                                            name="setting_<?= $setting['setting_key'] ?>"
                                            value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                            class="color-picker"
                                        >
                                        <input 
                                            type="text" 
                                            value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                            class="color-value"
                                            readonly
                                        >
                                    </div>
                                
                                <?php elseif ($setting['setting_type'] === 'boolean'): ?>
                                    <label class="toggle-switch">
                                        <input 
                                            type="checkbox" 
                                            name="setting_<?= $setting['setting_key'] ?>"
                                            value="1"
                                            <?= $setting['setting_value'] == '1' ? 'checked' : '' ?>
                                        >
                                        <span class="toggle-slider"></span>
                                    </label>
                                
                                <?php elseif ($setting['setting_type'] === 'number'): ?>
                                    <input 
                                        type="number" 
                                        id="<?= $setting['setting_key'] ?>"
                                        name="setting_<?= $setting['setting_key'] ?>"
                                        value="<?= htmlspecialchars($setting['setting_value']) ?>"
                                        class="form-control"
                                    >
                                
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz Zmiany</button>
                <button type="button" class="btn btn-secondary" onclick="location.reload()">🔄 Anuluj</button>
            </div>
        </form>
    </div>

<style>
/* Customize Header */
.customize-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--border-subtle);
}

.customize-header h1 {
    margin: 0;
    color: var(--accent-main);
    font-size: 2em;
}

.customize-actions {
    display: flex;
    gap: 10px;
}

/* Alert Boxes */
.alert {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    margin-bottom: 25px;
    border-radius: 12px;
    border: 1px solid;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: rgba(34, 197, 94, 0.1);
    border-color: rgba(34, 197, 94, 0.3);
    color: #86efac;
}

.alert-danger {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: #fca5a5;
}

.alert-icon {
    font-size: 1.5em;
}

.alert-content strong {
    display: block;
    margin-bottom: 5px;
    font-size: 1.05em;
}

.alert-content p {
    margin: 0;
    opacity: 0.9;
}

/* Tabs */
.customize-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border-bottom: 2px solid var(--border-subtle);
}

.tab-btn {
    padding: 12px 24px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--text-muted);
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    color: var(--text-primary);
    background: var(--bg-surface);
}

.tab-btn.active {
    color: var(--accent-main);
    border-bottom-color: var(--accent-main);
    background: var(--bg-surface);
}

/* Tab Content */
.tab-content {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 14px;
    border: 1px solid var(--border-subtle);
    margin-bottom: 25px;
}

.tab-content h2 {
    margin-top: 0;
    margin-bottom: 25px;
    color: var(--accent-main);
    font-size: 1.6em;
}

/* Settings Grid */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.setting-item {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.setting-item label {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95em;
}

.form-control {
    padding: 12px 16px;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.95em;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--accent-main);
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

textarea.form-control {
    resize: vertical;
    font-family: 'Courier New', monospace;
}

/* Color Picker */
.color-picker-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

.color-picker {
    width: 60px;
    height: 40px;
    border: 2px solid var(--border-subtle);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.color-picker:hover {
    border-color: var(--accent-main);
    transform: scale(1.05);
}

.color-value {
    flex: 1;
    padding: 10px 14px;
    background: var(--bg-surface);
    border: 1px solid var(--border-subtle);
    border-radius: 8px;
    color: var(--text-primary);
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 32px;
    cursor: pointer;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--bg-surface);
    border: 2px solid var(--border-subtle);
    border-radius: 32px;
    transition: all 0.3s ease;
}

.toggle-slider:before {
    content: "";
    position: absolute;
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background: var(--text-muted);
    border-radius: 50%;
    transition: all 0.3s ease;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--accent-main);
    border-color: var(--accent-main);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(28px);
    background: white;
}

.toggle-switch:hover .toggle-slider {
    box-shadow: 0 0 8px rgba(139, 92, 246, 0.3);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 2px solid var(--border-subtle);
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 1em;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: var(--accent-main);
    color: white;
}

.btn-primary:hover {
    background: var(--accent-secondary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-secondary {
    background: var(--bg-surface);
    color: var(--text-secondary);
    border: 1px solid var(--border-subtle);
}

.btn-secondary:hover {
    background: var(--bg-surface-alt);
    border-color: var(--accent-main);
}

/* Maintenance Preview Bar */
#maintenance-preview-bar {
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .customize-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .customize-tabs {
        overflow-x: auto;
        flex-wrap: nowrap;
    }
    
    .tab-btn {
        white-space: nowrap;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<script>
// Zakładki
$('.tab-btn').on('click', function() {
    const tab = $(this).data('tab');
    
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    
    $('.tab-content').hide();
    $('#tab-' + tab).show();
});

// Color picker
$('.color-picker').on('input', function() {
    const value = $(this).val();
    $(this).next('.color-value').val(value);

    // LIVE PREVIEW: primary/secondary/background/header
    const id = $(this).attr('id');

    const root = document.documentElement;
    if (id === 'primary_color') {
        root.style.setProperty('--primary-color', value);
    }
    if (id === 'secondary_color') {
        root.style.setProperty('--secondary-color', value);
    }
    if (id === 'background_color') {
        root.style.setProperty('--background-color', value);
        document.body.style.background =
            `linear-gradient(135deg, ${value} 0%, #1a0033 100%)`;
    }
    if (id === 'header_color') {
        // dla modern-header
        document.querySelectorAll('.modern-header').forEach(el => {
            el.style.backgroundColor = value;
        });
    }
});

// Boolean / feature toggles – np. maintenance_mode podgląd ikony/alertu
$('input[type="checkbox"][name^="setting_"]').on('change', function() {
    const key = this.name.replace('setting_', '');
    const checked = this.checked;

    if (key === 'maintenance_mode') {
        // Możesz np. pokazać alert na górze strony jako preview
        let bar = document.getElementById('maintenance-preview-bar');
        if (!bar && checked) {
            bar = document.createElement('div');
            bar.id = 'maintenance-preview-bar';
            bar.textContent = 'Tryb konserwacji będzie WŁĄCZONY dla zwykłych użytkowników.';
            bar.style.cssText = 'position:fixed;top:0;left:0;right:0;padding:10px 20px;' +
                'background:rgba(251,191,36,0.15);border-bottom:1px solid #fbbf24;' +
                'color:#fde68a;font-size:14px;z-index:2000;text-align:center;';
            document.body.appendChild(bar);
        } else if (bar && !checked) {
            bar.remove();
        }
    }
});

// Ostrzeżenie przed opuszczeniem
let formChanged = false;
$('.customize-form input, .customize-form textarea, .customize-form select').on('change input', function() {
    formChanged = true;
});

$(window).on('beforeunload', function() {
    if (formChanged) {
        return 'Masz niezapisane zmiany.';
    }
});

$('.customize-form').on('submit', function() {
    formChanged = false;
});
</script>
