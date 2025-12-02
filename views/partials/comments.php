<?php
require_once __DIR__ . '/../../models/Comment.php';

$commentModel = new Comment();
$comments = $commentModel->getByPageId($page['page_id']);
$commentCount = count($comments);

// Organizuj komentarze w drzewo (parent -> replies)
$commentTree = [];
$replies = [];

foreach ($comments as $comment) {
    if ($comment['parent_id'] === null) {
        $commentTree[] = $comment;
    } else {
        $replies[$comment['parent_id']][] = $comment;
    }
}
?>

<div class="comments-section" id="comments">
    <h3>💬 Komentarze (<?= $commentCount ?>)</h3>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="comment-form">
            <h4>Dodaj komentarz</h4>
            <form id="add-comment-form">
                <input type="hidden" name="page_id" value="<?= $page['page_id'] ?>">
                <input type="hidden" name="parent_id" id="parent_id" value="">
                
                <textarea 
                    name="content" 
                    id="comment-content" 
                    rows="4" 
                    placeholder="Napisz swój komentarz..."
                    required
                ></textarea>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">📤 Wyślij</button>
                    <button type="button" class="btn btn-secondary" id="cancel-reply" style="display:none;">❌ Anuluj odpowiedź</button>
                </div>
                
                <div id="reply-info" style="display:none; margin-top: 10px; color: #a78bfa;"></div>
            </form>
        </div>
    <?php else: ?>
        <div class="comment-login-prompt">
            <p>🔒 <a href="/login">Zaloguj się</a> aby dodać komentarz</p>
        </div>
    <?php endif; ?>
    
    <div class="comments-list">
        <?php if (empty($commentTree)): ?>
            <p class="no-comments">Brak komentarzy. Bądź pierwszy!</p>
        <?php else: ?>
            <?php foreach ($commentTree as $comment): ?>
                <?php renderComment($comment, $replies, $_SESSION['user_id'] ?? null); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
function renderComment($comment, $replies, $currentUserId, $level = 0) {
    $isOwner = $currentUserId === $comment['user_id'];
    $indent = $level * 40;
    ?>
    
    <div class="comment" id="comment-<?= $comment['comment_id'] ?>" style="margin-left: <?= $indent ?>px;">
        <div class="comment-header">
            <div class="comment-author">
                <strong>👤 <?= htmlspecialchars($comment['username']) ?></strong>
                <span class="comment-date">🕐 <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                <?php if ($comment['created_at'] !== $comment['updated_at']): ?>
                    <span class="comment-edited">(edytowano)</span>
                <?php endif; ?>
            </div>
            
            <div class="comment-actions">
                <?php if ($currentUserId): ?>
                    <button class="btn-reply" onclick="replyTo(<?= $comment['comment_id'] ?>, '<?= htmlspecialchars($comment['username']) ?>')">
                        💬 Odpowiedz
                    </button>
                <?php endif; ?>
                
                <?php if ($isOwner): ?>
                    <button class="btn-delete" onclick="deleteComment(<?= $comment['comment_id'] ?>)">
                        🗑️ Usuń
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="comment-content">
            <?= nl2br(htmlspecialchars($comment['content'])) ?>
        </div>
    </div>
    
    <?php
    // Renderuj odpowiedzi (rekurencyjnie)
    if (isset($replies[$comment['comment_id']])) {
        foreach ($replies[$comment['comment_id']] as $reply) {
            renderComment($reply, $replies, $currentUserId, $level + 1);
        }
    }
}
?>

<script>
// Dodawanie komentarza
$('#add-comment-form').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '/comment/add',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            location.reload();
        },
        error: function(xhr) {
            alert('Błąd: ' + (xhr.responseJSON?.error || 'Nie udało się dodać komentarza'));
        }
    });
});

// Odpowiedz na komentarz
function replyTo(commentId, username) {
    $('#parent_id').val(commentId);
    $('#reply-info').html('↩️ Odpowiadasz na komentarz użytkownika <strong>' + username + '</strong>').show();
    $('#cancel-reply').show();
    $('#comment-content').focus();
    
    // Scroll do formularza
    $('html, body').animate({
        scrollTop: $('#add-comment-form').offset().top - 100
    }, 500);
}

// Anuluj odpowiedź
$('#cancel-reply').on('click', function() {
    $('#parent_id').val('');
    $('#reply-info').hide();
    $(this).hide();
});

// Usuń komentarz
function deleteComment(commentId) {
    if (!confirm('Czy na pewno chcesz usunąć ten komentarz?')) {
        return;
    }
    
    $.ajax({
        url: '/comment/' + commentId + '/delete',
        method: 'POST',
        success: function() {
            $('#comment-' + commentId).fadeOut(300, function() {
                $(this).remove();
            });
        },
        error: function(xhr) {
            alert('Błąd: ' + (xhr.responseJSON?.error || 'Nie udało się usunąć komentarza'));
        }
    });
}
</script>
