<?php
/**
 * Manage Feedback - Admin
 * Blood Bank Management System
 */

$pageTitle = 'Feedback';
require_once '../includes/header.php';
requireRole('admin');

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM feedback WHERE id = $id")) {
        setFlashMessage('success', 'Feedback deleted successfully.');
    }
    header("Location: feedback.php");
    exit();
}

// Handle mark as read
if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    mysqli_query($conn, "UPDATE feedback SET status = 'read' WHERE id = $id");
    header("Location: feedback.php");
    exit();
}

// Get all feedback
$feedbackQuery = "SELECT * FROM feedback ORDER BY created_at DESC";
$feedbacks = mysqli_fetch_all(mysqli_query($conn, $feedbackQuery), MYSQLI_ASSOC);

$unreadCount = 0;
foreach ($feedbacks as $f) {
    if ($f['status'] === 'unread') $unreadCount++;
}

require_once '../includes/admin_sidebar.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-comments"></i> All Feedback</h3>
        <span class="badge badge-warning"><?php echo $unreadCount; ?> Unread</span>
    </div>
    <div class="card-body">
        <?php if (count($feedbacks) > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $index => $feedback): ?>
                            <tr style="<?php echo $feedback['status'] === 'unread' ? 'background: #fffbea;' : ''; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($feedback['name']); ?></td>
                                <td><a href="mailto:<?php echo htmlspecialchars($feedback['email']); ?>"><?php echo htmlspecialchars($feedback['email']); ?></a></td>
                                <td><?php echo htmlspecialchars($feedback['subject'] ?? 'N/A'); ?></td>
                                <td style="max-width: 300px;"><?php echo htmlspecialchars(substr($feedback['message'], 0, 100)); ?><?php echo strlen($feedback['message']) > 100 ? '...' : ''; ?></td>
                                <td><?php echo formatDateTime($feedback['created_at']); ?></td>
                                <td>
                                    <span class="badge <?php echo $feedback['status'] === 'read' ? 'badge-success' : 'badge-warning'; ?>">
                                        <?php echo ucfirst($feedback['status']); ?>
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <?php if ($feedback['status'] === 'unread'): ?>
                                        <a href="feedback.php?mark_read=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-success" title="Mark as Read">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="feedback.php?delete=<?php echo $feedback['id']; ?>" class="btn btn-sm btn-danger" 
                                       data-confirm-delete="Are you sure you want to delete this feedback?" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h4>No Feedback Yet</h4>
                <p>Feedback from visitors will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
