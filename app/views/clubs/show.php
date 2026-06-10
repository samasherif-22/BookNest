<?php

require_once BASE_PATH . '/app/views/partials/header.php';
require_once BASE_PATH . '/app/views/partials/navbar.php';

$isOrganizer = (currentRole() === 'CLUB_ORGANIZER' && isset($club['organizer_id'])
                && (int)$club['organizer_id'] === currentUserId())
              || currentRole() === 'SYSTEM_ADMIN';
?>

<div class="container py-4">
  <!-- ── Club Header ─────────────────────────────────────────────────────── -->
  <div class="card border-0 shadow-sm mb-4 p-4" style="background:var(--primary); color:#fff; border-radius:16px;">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h1 class="fw-bold mb-1"><?= sanitize($club['name']) ?></h1>
        <p class="mb-2" style="color:rgba(255,255,255,.75);"><?= sanitize($club['description'] ?? '') ?></p>
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge bg-white text-dark"><?= sanitize($club['genre'] ?? 'All Genres') ?></span>
          <?php if ($club['is_private']): ?>
            <span class="badge bg-warning text-dark"><i class="bi bi-lock me-1"></i>Private</span>
          <?php else: ?>
            <span class="badge bg-success"><i class="bi bi-unlock me-1"></i>Public</span>
          <?php endif; ?>
          <span class="badge bg-info text-dark"><i class="bi bi-people me-1"></i><?= count($members) ?> members</span>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <?php if (!$isMember && currentRole() === 'READER'): ?>
        <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=joinClub">
          <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
          <button class="btn btn-light fw-bold">
            <?= $club['is_private'] ? ' Request to Join' : ' Join Club' ?>
          </button>
        </form>
        <?php elseif ($isMember): ?>
          <span class="badge bg-success px-3 py-2" style="font-size:.9rem;">✓ You are a member</span>
        <?php endif; ?>

        <?php if ($isOrganizer): ?>
        <div class="mt-2 d-flex gap-2 justify-content-md-end flex-wrap">
          <a href="<?= BASE_URL ?>index.php?page=clubs&action=requests&id=<?= (int)$club['id'] ?>"
             class="btn btn-outline-light btn-sm">
            <i class="bi bi-person-check me-1"></i>Manage Requests
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- ── LEFT: Goals, Library, Discussions, Voting ─────────────────────────────── -->
    <div class="col-lg-8">

      <!-- Reading Goals  -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold">Reading Goals </h5>
          <?php if ($isOrganizer): ?>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#formGoal">
            + New Goal
          </button>
          <?php endif; ?>
        </div>
        <?php if ($isOrganizer): ?>
        <div class="collapse px-3 pb-3" id="formGoal">
          <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=createGoal" class="mt-3">
            <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
            <div class="row g-2">
              <div class="col-md-4">
                <input type="text"  name="label" class="form-control form-control-sm" placeholder="Label (e.g. Chapters 1–5)">
              </div>
              <div class="col-md-3">
                <input type="number" name="target_chapter" class="form-control form-control-sm" placeholder="Target ch." min="1" required>
              </div>
              <div class="col-md-3">
                <input type="date" name="due_date" class="form-control form-control-sm" required>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Create</button>
              </div>
            </div>
          </form>
        </div>
        <?php endif; ?>

        <div class="card-body">
          <?php if (empty($goals)): ?>
            <p class="text-muted small mb-0">No reading goals set yet.</p>
          <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($goals as $goal): ?>
            <div class="list-group-item px-0">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <strong><?= sanitize($goal['label'] ?? 'Goal') ?></strong>
                <span class="badge bg-info">Target: Ch. <?= (int)$goal['target_chapter'] ?></span>
              </div>
              <div class="text-muted small mb-2">Due: <?= sanitize($goal['due_date']) ?></div>

              <!-- Update my progress -->
              <?php if ($isMember): ?>
              <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=updateProgress"
                    class="d-flex gap-2">
                <input type="hidden" name="goal_id" value="<?= (int)$goal['id'] ?>">
                <input type="number" name="current_chapter" class="form-control form-control-sm"
                       style="width:100px;" placeholder="My chapter" min="1">
                <button type="submit" class="btn btn-sm btn-outline-success">Update Progress</button>
              </form>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ── Club Shared Library (Member Books) ─────────────────────────────────── -->
      <?php if (currentRole() === 'READER' && $isMember): ?> 
      <div class="card border-0 shadow-sm mb-4 border-start border-4 border-info">
          <div class="card-header bg-white fw-bold py-3 d-flex align-items-center">
              <i class="bi bi-bookshelf text-info me-2"></i> Club Shared Library
          </div>
          <div class="card-body p-3">
              <?php if (empty($clubLibrary)): ?>
                  <p class="text-muted small mb-0 text-center py-3">No books are currently available from other members to borrow.</p>
              <?php else: ?>
                  <div class="row g-3">
                      <?php foreach ($clubLibrary as $libBook): ?>
                          <div class="col-md-6">
                              <div class="border rounded p-3 text-center h-100 position-relative bg-light shadow-sm">
                                  <h6 class="fw-bold mb-1 text-truncate" title="<?= sanitize($libBook['title']) ?>">
                                      <i class="bi bi-book text-secondary me-1"></i> <?= sanitize($libBook['title']) ?>
                                  </h6>
                                  <p class="small text-muted mb-3">
                                      Owned by: <span class="fw-semibold text-dark"><?= sanitize($libBook['owner_name']) ?></span>
                                  </p>
                                  
                                  <div class="d-flex gap-2">
                                      <a href="<?= BASE_URL ?>index.php?page=books&action=show&id=<?= (int)$libBook['book_id'] ?>" 
                                         class="btn btn-outline-secondary btn-sm flex-fill fw-bold">
                                          <i class="bi bi-eye me-1"></i> Details
                                      </a>
                                      
                                      <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=requestToBorrow" class="flex-fill m-0">
                                          <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
                                          <input type="hidden" name="owner_id" value="<?= (int)$libBook['owner_id'] ?>">
                                          <input type="hidden" name="book_id" value="<?= (int)$libBook['book_id'] ?>">
                                          <input type="hidden" name="book_title" value="<?= sanitize($libBook['title']) ?>">
                                          <button type="submit" class="btn btn-info text-white btn-sm w-100 fw-bold">
                                              <i class="bi bi-hand-index-thumb me-1"></i> Borrow
                                          </button>
                                      </form>
                                  </div>
                              </div>
                          </div>
                      <?php endforeach; ?>
                  </div>
              <?php endif; ?>
          </div>
      </div>
      <?php endif; ?>

      <!-- Discussions  -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold">Discussions </h5>
          <?php if ($isMember): ?>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#formDiscussion">
            + New Topic
          </button>
          <?php endif; ?>
        </div>
        <?php if ($isMember): ?>
        <div class="collapse px-3 pb-3" id="formDiscussion">
          <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=createDiscussion" class="mt-3">
            <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
            <div class="mb-2">
              <input type="text" name="title" class="form-control form-control-sm"
                     placeholder="Discussion title" required>
            </div>
            <div class="mb-2">
              <textarea name="body" class="form-control form-control-sm" rows="2"
                        placeholder="What's on your mind?" required></textarea>
            </div>
            <div class="row g-2">
              <div class="col-md-6">
                <input type="number" name="required_chapter" class="form-control form-control-sm"
                       placeholder="Min chapter (0 = open to all)" min="0" value="0">
              </div>
              <div class="col-md-6">
                <button type="submit" class="btn btn-primary btn-sm w-100">Post Discussion</button>
              </div>
            </div>
          </form>
        </div>
        <?php endif; ?>

        <div class="card-body">
          <?php if (empty($discussions)): ?>
            <p class="text-muted small mb-0">No discussions yet. Start one!</p>
          <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($discussions as $disc): ?>
            <div class="list-group-item px-0">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong><?= sanitize($disc['title']) ?></strong>
                  <?php if ((int)$disc['required_chapter'] > 0): ?>
                    <span class="badge bg-warning text-dark ms-2">Ch. <?= (int)$disc['required_chapter'] ?>+ required</span>
                  <?php endif; ?>
                  <div class="text-muted small mt-1">
                    by <?= sanitize($disc['poster_name'] ?? '?') ?> —
                    <?= date('d M Y', strtotime($disc['created_at'] ?? 'now')) ?>
                  </div>
                </div>
                <?php if (!($disc['accessible'] ?? true)): ?>
                  <span class="badge bg-secondary"><i class="bi bi-lock me-1"></i>Locked</span>
                <?php endif; ?>
              </div>
              <?php if ($disc['accessible'] ?? true): ?>
              <p class="text-muted small mb-0 mt-1"><?= sanitize(mb_strimwidth($disc['body'] ?? '', 0, 120, '…')) ?></p>
              <?php else: ?>
              <p class="text-muted small mb-0 fst-italic">Read to chapter <?= (int)$disc['required_chapter'] ?> to unlock this discussion.</p>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Vote for Next  -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0 fw-bold">Vote for Next Read </h5>
          <?php if ($isMember): ?>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#formNominate">
            + Nominate
          </button>
          <?php endif; ?>
        </div>
        <?php if ($isMember): ?>
        <div class="collapse px-3 pb-3" id="formNominate">
          <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=nominate" class="mt-3">
            <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
            <div class="input-group">
              <input type="text" name="book_title" class="form-control" placeholder="Book title to nominate" required>
              <button type="submit" class="btn btn-primary">Nominate</button>
            </div>
          </form>
        </div>
        <?php endif; ?>

        <div class="card-body">
          <?php if (empty($nominations)): ?>
            <p class="text-muted small mb-0">No nominations yet.</p>
          <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($nominations as $nom): ?>
            <div class="list-group-item px-0 d-flex align-items-center gap-3">
              <div class="flex-grow-1">
                <strong><?= sanitize($nom['book_title']) ?></strong>
                <div class="text-muted small">Nominated by <?= sanitize($nom['nominator_name'] ?? '?') ?></div>
              </div>
              <span class="badge bg-primary rounded-pill px-3" style="font-size:.9rem;">
                <?= (int)$nom['vote_count'] ?> votes
              </span>
              <?php if ($isMember): ?>
              <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=castVote">
                <input type="hidden" name="nomination_id" value="<?= (int)$nom['id'] ?>">
                <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary">👍 Vote</button>
              </form>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ── RIGHT: Members & Ask Author ─────────────────────────────────────────── -->
    <div class="col-lg-4">
      <!-- Members -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">👥 Members</div>
        <div class="card-body p-2">
          <?php if (empty($members)): ?>
            <p class="text-muted small p-2 mb-0">No members yet.</p>
          <?php else: ?>
          <ul class="list-group list-group-flush">
            <?php foreach (array_slice($members, 0, 10) as $m): ?>
            <li class="list-group-item d-flex align-items-center gap-2 px-2 py-1">
              <i class="bi bi-person-circle text-muted"></i>
              <span class="small"><?= sanitize($m['name']) ?></span>
            </li>
            <?php endforeach; ?>
            <?php if (count($members) > 10): ?>
            <li class="list-group-item text-muted small px-2 py-1">
              + <?= count($members) - 10 ?> more members
            </li>
            <?php endif; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>

      <!-- Ask Author a Question  -->
      <?php if (isLoggedIn() && currentRole() === 'READER' && $isMember): ?>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Ask the Author</div>
        <div class="card-body">
          <form method="POST" action="<?= BASE_URL ?>index.php?page=clubs&action=askQuestion">
            <input type="hidden" name="club_id" value="<?= (int)$club['id'] ?>">
            <textarea name="question" class="form-control form-control-sm mb-2" rows="3"
                      placeholder="Your question for the author…" required></textarea>
            <button type="submit" class="btn btn-primary btn-sm w-100">Send Question</button>
          </form>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/views/partials/footer.php'; ?>