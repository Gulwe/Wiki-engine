<?php
$stats = $analytics->getGeneralStats();
$topPages = $analytics->getTopPages(10);
$topCategories = $analytics->getTopCategories(5);
$topCommenters = $analytics->getTopCommenters(5);
$recentActivity = $analytics->getRecentActivity(15);
?>

<!-- Dodaj Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="container">
    <div class="dashboard-header">
        <h1>📊 Analytics Dashboard</h1>
        <div class="dashboard-actions">
            <a href="/admin" class="btn">⚙️ Panel Admina</a>
            <a href="/" class="btn">🏠 Strona Główna</a>
        </div>
    </div>
    
    <!-- Statystyki główne -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📄</div>
            <div class="stat-value"><?= number_format($stats['total_pages']) ?></div>
            <div class="stat-label">Wszystkie Strony</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👁️</div>
            <div class="stat-value"><?= number_format($stats['total_views']) ?></div>
            <div class="stat-label">Łączne Wyświetlenia</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-value"><?= number_format($stats['total_comments']) ?></div>
            <div class="stat-label">Komentarze</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
            <div class="stat-label">Użytkownicy</div>
        </div>
        
        <div class="stat-card highlight">
            <div class="stat-icon">🔥</div>
            <div class="stat-value"><?= number_format($stats['views_today']) ?></div>
            <div class="stat-label">Wyświetlenia Dzisiaj</div>
        </div>
        
        <div class="stat-card highlight">
            <div class="stat-icon">📈</div>
            <div class="stat-value"><?= number_format($stats['views_week']) ?></div>
            <div class="stat-label">Wyświetlenia w Tygodniu</div>
        </div>
    </div>
    
    <!-- Wykres wyświetleń -->
    <div class="chart-section">
        <h2>📈 Wyświetlenia w Ostatnich Dniach</h2>
        <div class="chart-controls">
            <button onclick="loadChart(7)" class="btn btn-sm">7 dni</button>
            <button onclick="loadChart(30)" class="btn btn-sm active">30 dni</button>
            <button onclick="loadChart(90)" class="btn btn-sm">90 dni</button>
        </div>
        <canvas id="viewsChart" height="80"></canvas>
    </div>
    
    <div class="analytics-grid">
        <!-- Najpopularniejsze strony -->
        <div class="analytics-panel">
            <h3>🏆 Najpopularniejsze Strony</h3>
            <div class="top-list">
                <?php foreach ($topPages as $index => $page): ?>
                    <div class="top-item">
                        <div class="top-rank">#<?= $index + 1 ?></div>
                        <div class="top-info">
                            <a href="/page/<?= htmlspecialchars($page['slug']) ?>" class="top-title">
                                <?= htmlspecialchars($page['title']) ?>
                            </a>
                            <div class="top-meta">
                                👤 <?= htmlspecialchars($page['author'] ?? 'Nieznany') ?> | 
                                👁️ <?= number_format($page['views']) ?> wyświetleń
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Top kategorie -->
        <div class="analytics-panel">
            <h3>📂 Najpopularniejsze Kategorie</h3>
            <div class="top-list">
                <?php foreach ($topCategories as $index => $cat): ?>
                    <div class="top-item">
                        <div class="top-rank">#<?= $index + 1 ?></div>
                        <div class="top-info">
                            <a href="/category/<?= $cat['category_id'] ?>" class="top-title">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                            <div class="top-meta">
                                📄 <?= $cat['page_count'] ?> stron | 
                                👁️ <?= number_format($cat['total_views']) ?> wyświetleń
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <div class="analytics-grid">
        <!-- Najaktywniejsze użytkownicy -->
        <div class="analytics-panel">
            <h3>💪 Najaktywniejsze Komentarze</h3>
            <div class="top-list">
                <?php foreach ($topCommenters as $index => $user): ?>
                    <div class="top-item">
                        <div class="top-rank">#<?= $index + 1 ?></div>
                        <div class="top-info">
                            <div class="top-title">
                                👤 <?= htmlspecialchars($user['username']) ?>
                            </div>
                            <div class="top-meta">
                                💬 <?= number_format($user['comment_count']) ?> komentarzy
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Ostatnia aktywność -->
        <div class="analytics-panel">
            <h3>🕐 Ostatnia Aktywność</h3>
            <div class="activity-list">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <?php if ($activity['type'] === 'page_view'): ?>
                            <span class="activity-icon">👁️</span>
                            <span class="activity-text">
                                <strong><?= htmlspecialchars($activity['username'] ?? 'Gość') ?></strong> 
                                obejrzał 
                                <a href="/page/<?= htmlspecialchars($activity['page_slug']) ?>">
                                    <?= htmlspecialchars($activity['page_title']) ?>
                                </a>
                            </span>
                        <?php else: ?>
                            <span class="activity-icon">💬</span>
                            <span class="activity-text">
                                <strong><?= htmlspecialchars($activity['username']) ?></strong> 
                                skomentował 
                                <a href="/page/<?= htmlspecialchars($activity['page_slug']) ?>">
                                    <?= htmlspecialchars($activity['page_title']) ?>
                                </a>
                            </span>
                        <?php endif; ?>
                        <span class="activity-time">
                            <?= date('H:i', strtotime($activity['timestamp'])) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard Header */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.dashboard-actions {
    display: flex;
    gap: 10px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 14px;
    border: 1px solid var(--border-subtle);
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-card.highlight {
    background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
    border-color: rgba(139, 92, 246, 0.3);
}

.stat-icon {
    font-size: 2.5em;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 2em;
    font-weight: 700;
    color: var(--accent-main);
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.9em;
}

/* Chart Section */
.chart-section {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 14px;
    border: 1px solid var(--border-subtle);
    margin-bottom: 30px;
}

.chart-section h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: var(--accent-main);
}

.chart-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.9em;
}

.btn-sm.active {
    background: var(--accent-main);
    color: white;
}

/* Analytics Grid */
.analytics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.analytics-panel {
    background: var(--card-bg);
    padding: 25px;
    border-radius: 14px;
    border: 1px solid var(--border-subtle);
}

.analytics-panel h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: var(--accent-main);
}

/* Top Lists */
.top-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.top-item {
    display: flex;
    gap: 15px;
    align-items: center;
    padding: 12px;
    background: var(--bg-surface);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.top-item:hover {
    background: var(--bg-surface-alt);
    transform: translateX(5px);
}

.top-rank {
    font-size: 1.5em;
    font-weight: 700;
    color: var(--accent-main);
    min-width: 40px;
    text-align: center;
}

.top-info {
    flex: 1;
}

.top-title {
    color: var(--text-primary);
    font-weight: 600;
    text-decoration: none;
    display: block;
    margin-bottom: 5px;
}

.top-title:hover {
    color: var(--accent-main);
}

.top-meta {
    color: var(--text-muted);
    font-size: 0.85em;
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    display: flex;
    gap: 10px;
    align-items: center;
    padding: 10px;
    background: var(--bg-surface);
    border-radius: 8px;
    font-size: 0.9em;
}

.activity-icon {
    font-size: 1.2em;
}

.activity-text {
    flex: 1;
}

.activity-text strong {
    color: var(--accent-main);
}

.activity-text a {
    color: var(--text-primary);
    text-decoration: none;
}

.activity-text a:hover {
    color: var(--accent-main);
}

.activity-time {
    color: var(--text-muted);
    font-size: 0.85em;
}

@media (max-width: 768px) {
    .analytics-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
}
</style>

<script>
let viewsChart = null;

function loadChart(days = 30) {
    // Zmień aktywny przycisk
    $('.chart-controls .btn').removeClass('active');
    $('.chart-controls .btn').each(function() {
        if ($(this).text().includes(days + ' dni')) {
            $(this).addClass('active');
        }
    });
    
    $.get('/api/analytics/views?days=' + days, function(data) {
        console.log('Chart data:', data);
        
        if (!data || data.length === 0) {
            console.warn('No data for chart');
            return;
        }
        
        const labels = data.map(d => d.date);
        const values = data.map(d => parseInt(d.views));
        
        const ctx = document.getElementById('viewsChart').getContext('2d');
        
        if (viewsChart) {
            viewsChart.destroy();
        }
        
        viewsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Wyświetlenia',
                    data: values,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(20, 0, 40, 0.95)',
                        titleColor: '#c4b5fd',
                        bodyColor: '#d1d5ff',
                        borderColor: 'rgba(139, 92, 246, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#a78bfa',
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(139, 92, 246, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#a78bfa'
                        },
                        grid: {
                            color: 'rgba(139, 92, 246, 0.1)'
                        }
                    }
                }
            }
        });
    }).fail(function(err) {
        console.error('Failed to load chart data:', err);
    });
}

// Załaduj wykres przy starcie
$(document).ready(function() {
    loadChart(30);
});
</script>
