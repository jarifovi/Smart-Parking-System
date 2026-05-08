<?php
$pageTitle  = 'User Management';
$sidebarKey = 'admin_users';
require_once 'helper_layout_admin.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== (int)$_SESSION['user_id']) { // avoid self-delete
        $databaseConnection->query("DELETE FROM users WHERE id=$id");
    }
    header('Location: admin_user_management.php');
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$where  = '';
if ($filter === 'admins')  $where = 'WHERE is_admin=1';
if ($filter === 'regular') $where = 'WHERE is_admin=0';

$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $safe = $databaseConnection->real_escape_string($search);
    $where .= ($where ? ' AND ' : 'WHERE ') . "(full_name LIKE '%$safe%' OR email LIKE '%$safe%')";
}

$result = $databaseConnection->query("SELECT * FROM users $where ORDER BY created_at DESC");
?>
<div class="page-header-main">
    <div class="page-header-title">User Management</div>
    <div class="page-header-sub">Manage registered accounts, roles, and system access.</div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3" method="get">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 border-secondary-subtle">
                        <i class="bi bi-search text-secondary"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search name or email" value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="filter" class="form-select">
                    <option value="all"     <?php echo $filter==='all'?'selected':''; ?>>All Access Roles</option>
                    <option value="admins"  <?php echo $filter==='admins'?'selected':''; ?>>Administrators</option>
                    <option value="regular" <?php echo $filter==='regular'?'selected':''; ?>>Standard Users</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="auth_register.php" class="btn btn-success w-100">
                    <i class="bi bi-person-plus me-1"></i> Add User
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>System Role</th>
                    <th>Registration Date</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows): while ($u=$result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $u['id']; ?></td>
                        <td class="fw-semibold"><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td>
                            <?php if ($u['is_admin']): ?>
                                <span class="badge bg-danger"><i class="bi bi-shield-lock me-1"></i>ADMIN</span>
                            <?php else: ?>
                                <span class="badge bg-info"><i class="bi bi-person me-1"></i>USER</span>
                            <?php endif; ?>
                        </td>
                        <td class="small"><?php echo date('M d, Y H:i', strtotime($u['created_at'])); ?></td>
                        <td class="text-end">
                            <a href="?delete=<?php echo $u['id']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Permanently delete this user?')"
                               title="Delete User">
                                <i class="bi bi-person-x-fill"></i>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-people text-secondary fs-1 mb-2 d-block"></i>
                            <div class="text-secondary">No users found matching criteria.</div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// helper_layout_admin.php will close tags
?>
