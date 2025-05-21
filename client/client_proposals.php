<?php
require_once 'core/dbConfig.php';
require_once 'core/models.php';

session_start();
if (!isset($_SESSION['username']) || $_SESSION['is_client'] == 0) {
    header("Location: login.php");
    exit;
}

$client_id = $_SESSION['user_id']; // adjust according to your session

// Fetch all proposals made for gigs posted by this client
// Assuming proposals table has: id, gig_id, freelancer_id, proposal_text, date_submitted
// And gigs table has: id, client_id, title, description

$stmt = $pdo->prepare("
    SELECT p.id as proposal_id, p.proposal_text, p.date_submitted, g.title AS gig_title, f.username AS freelancer_username
    FROM proposals p
    JOIN gigs g ON p.gig_id = g.id
    JOIN users f ON p.freelancer_id = f.id
    WHERE g.client_id = ?
    ORDER BY p.date_submitted DESC
");
$stmt->execute([$client_id]);
$proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
  <title>Your Proposals</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
  <h2 class="mb-4">All Proposals Made for Your Gigs</h2>

  <?php if (!empty($proposals)) : ?>
    <div class="row">
      <?php foreach ($proposals as $proposal) : ?>
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($proposal['gig_title']); ?></h5>
              <h6 class="card-subtitle mb-2 text-muted">By <?= htmlspecialchars($proposal['freelancer_username']); ?></h6>
              <p class="card-text"><?= nl2br(htmlspecialchars(substr($proposal['proposal_text'], 0, 150))) ?>...</p>
              <p class="text-muted"><small>Submitted on <?= date('F j, Y, g:i a', strtotime($proposal['date_submitted'])); ?></small></p>
              <a href="get_gig_proposals.php?proposal_id=<?= $proposal['proposal_id']; ?>" class="btn btn-primary">View Proposal</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>No proposals have been made for your gigs yet.</p>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
