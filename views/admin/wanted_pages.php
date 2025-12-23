<div class="container">
    <h1>📋 Brakujące strony (Wanted pages)</h1>
    <p class="info">
        Strony, do których prowadzą linki <code>[[...]]</code>, ale same jeszcze nie istnieją.
    </p>

    <?php if (empty($wanted)): ?>
        <div class="alert alert-success">
            <span class="alert-icon">✅</span>
            <div class="alert-content">
                <strong>Wszystkie linki prowadzą do istniejących stron!</strong>
            </div>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Brakująca strona (slug)</th>
                    <th>Liczba odwołań</th>
                    <th>Przykład źródła</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($wanted as $slug => $count): ?>
                    <tr>
                        <td>
                            <a href="/page/<?= htmlspecialchars($slug) ?>">
                                <?= htmlspecialchars($slug) ?>
                            </a>
                        </td>
                        <td><strong><?= (int)$count ?></strong></td>
                        <td>
                            <a href="/page/<?= htmlspecialchars($examples[$slug]) ?>">
                                <?= htmlspecialchars($examples[$slug]) ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <a href="/admin" class="btn">← Panel admina</a>
    </div>
</div>
