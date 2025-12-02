<?php
$stats = $analytics->getGeneralStats();
$topPages = $analytics->getTopPages(10);
$topCategories = $analytics->getTopCategories(5);
$topCommenters = $analytics->getTopCommenters(5);
$recentActivity = $analytics->getRecentActivity(15);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Wiki Engine</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    
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
    
    <script>
    let viewsChart = null;
    
    function loadChart(days = 30) {
        // Zmień aktywny przycisk
        $('.chart-controls .btn').removeClass('active');
        $('.chart-controls .btn').eq(days === 7 ? 0 : days === 30 ? 1 : 2).addClass('active');
        
        $.get('/api/analytics/views?days=' + days, function(data) {
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
        });
    }
    
    // Załaduj wykres przy starcie
    $(document).ready(function() {
        loadChart(30);
    });
    </script>
</body>
</html>
