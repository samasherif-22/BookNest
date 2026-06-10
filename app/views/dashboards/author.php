<?php
/*
 * app/views/dashboards/author.php
 * --------------------------------
 * Dashboard for AUTHOR role.
 * Shows: event list, Q&A answer links, notifications.
 * Variables from HomeController::dashboard():  $events, $notifications
 */
require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$statusColors = ['upcoming'=>'bg-primary','live'=>'bg-success','ended'=>'bg-secondary'];

// جلب الأسئلة التي لم يتم الرد عليها من قاعدة البيانات
$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT q.id, q.question, c.name as club_name, u.name as reader_name 
    FROM qa_questions q
    JOIN clubs c ON q.club_id = c.id
    JOIN users u ON q.user_id = u.id
    LEFT JOIN qa_answers a ON q.id = a.question_id
    WHERE a.id IS NULL
    ORDER BY q.id DESC
");
$unansweredQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
  <h1 class="section-title mb-1"> Author Dashboard</h1>
  <p class="text-muted mb-4">Manage your events and engage with your readers.</p>

  <!-- Quick Actions -->
  <div class="d-flex gap-2 flex-wrap mb-4">
    <a href="<?= BASE_URL ?>index.php?page=events&action=create" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg me-1"></i>Create Event
    </a>
    <a href="<?= BASE_URL ?>index.php?page=events" class="btn btn-outline-primary btn-sm">
      <i class="bi bi-calendar me-1"></i>All Events
    </a>
    <a href="<?= BASE_URL ?>index.php?page=notifications" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-bell me-1"></i>Notifications
    </a>
  </div>

  <div class="row g-4">
    <!-- ── Left Column: Events & Questions ──────────────────────────────── -->
    <div class="col-md-8">
      
      <!-- My Events -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">My Events</div>
        <?php if (empty($events)): ?>
        <div class="card-body text-center text-muted py-4">
          <i class="bi bi-calendar-plus display-4 d-block mb-2"></i>
          No events yet.
          <br>
          <a href="<?= BASE_URL ?>index.php?page=events&action=create" class="btn btn-primary mt-3 btn-sm">
            Create Your First Event
          </a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead><tr><th>Event</th><th>Date</th><th class="text-center">Status</th><th>Manage</th></tr></thead>
            <tbody>
            <?php foreach ($events as $ev): ?>
            <tr>
              <td class="fw-bold"><?= sanitize($ev['title']) ?></td>
              <td class="small text-muted"><?= date('d M Y', strtotime($ev['event_date'])) ?></td>
              <td class="text-center">
                <span class="badge <?= $statusColors[$ev['status']] ?? 'bg-secondary' ?>">
                  <?= ucfirst($ev['status']) ?>
                </span>
              </td>
              <td>
                <a href="<?= BASE_URL ?>index.php?page=events&action=show&id=<?= (int)$ev['id'] ?>"
                   class="btn btn-sm btn-outline-primary py-0">Manage</a>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Reader Questions -->
      <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white fw-bold">
          <i class="bi bi-question-circle text-primary me-1"></i> Reader Questions
        </div>
        <div class="card-body p-0">
          <?php if (empty($unansweredQuestions)): ?>
            <p class="text-muted small mb-0 text-center py-4">No pending questions from readers.</p>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($unansweredQuestions as $q): ?>
                <div class="list-group-item p-4">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <span class="badge bg-secondary mb-2" style="font-size: 0.75rem;">
                        Club: <?= sanitize($q['club_name']) ?>
                      </span>
                      <strong class="d-block text-dark"><?= sanitize($q['reader_name']) ?> asks:</strong>
                    </div>
                  </div>
                  <p class="mb-3 bg-light p-3 rounded small border-start border-3 border-primary shadow-sm">
                    <?= sanitize($q['question']) ?>
                  </p>
                  
                  <!-- Answer Form -->
                  <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=answerQuestion">
                    <input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">
                    <div class="d-flex gap-2 align-items-start">
                      <textarea name="answer" class="form-control form-control-sm" rows="2" 
                                placeholder="Write your answer to <?= sanitize($q['reader_name']) ?> here..." required></textarea>
                      <button type="submit" class="btn btn-success btn-sm px-4 shadow-sm">Reply</button>
                    </div>
                  </form>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- ── Right Column: Notifications & Stats ──────────────────────────── -->
    <div class="col-md-4">
      
      <!-- Notifications -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
          <span><i class="bi bi-bell me-1"></i>Notifications</span>
          <a href="<?= BASE_URL ?>index.php?page=notifications" class="small text-accent">All</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($notifications)): ?>
          <p class="text-muted text-center p-3 small mb-0">No notifications.</p>
          <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach (array_slice($notifications, 0, 6) as $n): ?>
            <a href="<?= sanitize($n['link'] ?? '#') ?>"
               class="list-group-item list-group-item-action py-2 px-3 <?= $n['is_read'] ? '' : 'fw-bold' ?>">
              <div style="font-size:.82rem;"><?= sanitize(mb_strimwidth($n['message'],0,60,'…')) ?></div>
              <div class="text-muted" style="font-size:.7rem;"><?= date('d M', strtotime($n['created_at'])) ?></div>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Author Stats -->
      <div class="card border-0 shadow-sm mt-4 p-4">
        <h6 class="fw-bold mb-3">Your Stats</h6>
        <div class="d-flex justify-content-between small">
          <span class="text-muted">Total Events</span>
          <strong><?= count($events) ?></strong>
        </div>
        <div class="d-flex justify-content-between small mt-2">
          <span class="text-muted">Live Now</span>
          <strong class="text-success">
            <?= count(array_filter($events, fn($e) => $e['status'] === 'live')) ?>
          </strong>
        </div>
        <div class="d-flex justify-content-between small mt-2">
          <span class="text-muted">Ended</span>
          <strong class="text-muted">
            <?= count(array_filter($events, fn($e) => $e['status'] === 'ended')) ?>
          </strong>
        </div>
        <div class="d-flex justify-content-between small mt-2 pt-2 border-top">
          <span class="text-muted">Pending Questions</span>
          <strong class="text-primary"><?= count($unansweredQuestions) ?></strong>
        </div>
      </div>
      
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>